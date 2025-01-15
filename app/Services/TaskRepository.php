<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MessageStatus;
use App\Enums\SendMethod;
use App\Enums\TaskStatus;
use App\Events\TaskFinishedEvent;
use App\Jobs\SaveChatsBackgroundJob;
use App\Models\Task;
use App\Models\TaskChat;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Telegram\Properties\ChatAction;

class TaskRepository
{
    public function __construct(private BotRepository $botRepository) {}

    /**
     * @param array<string, mixed> $params
     * @param non-empty-list<int|string> $chats
     */
    public function create(
        int $botId,
        string $username,
        string $token,
        SendMethod $method,
        ?ChatAction $prefetchType,
        array $params,
        array $chats,
        ?string $webhook,
    ): Task {
        $bot = $this->botRepository->findOrCreate($botId, $username);
        $task = new Task();

        $task->bot()->associate($bot);
        $task->username = $username; // @todo remove in the future
        $task->token = $token;
        $task->method = $method;
        $task->prefetch_type = $prefetchType;
        $task->params = array_filter($params, static fn(mixed $param): bool => $param !== null);
        $task->webhook = $webhook;
        $task->status = count($chats) > TaskChat::BIG_BOTS_LIMIT ? TaskStatus::Creating : TaskStatus::Pending;


        DB::beginTransaction();
        $task->save();
        $this->createChats($task, $chats);
        DB::commit();
        return $task;
    }

    private function createChats(Task $task, array $chats): void
    {
        if ($task->status === TaskStatus::Creating) {
            SaveChatsBackgroundJob::dispatch($task, $chats);
            return;
        }
        $chats = collect($chats)->map(fn(int|string $chat) => ['chat_id' => (int)$chat]);
        $task->chats()->createMany($chats);
    }

    public function getById(string $id): Task
    {
        $task = Task::withCount('chats')->findOrFail($id);
        if($task->chats_count <= TaskChat::BIG_BOTS_LIMIT) {
            $task->load('chats');
        }
        return $task;
    }

    public function hasPendingTaskForBot(int $botId): bool
    {
        return Task::where('bot_id', $botId)
            ->whereIn('status', [TaskStatus::Pending, TaskStatus::InProgress])
            ->exists();
    }

    public function reserveTask(): ?Task
    {
        $lock = Cache::lock('reserved_task', 20);

        try {
            return $lock->block(15, function (): ?Task {
                $task = Task::with('chats')
                    ->where('status', TaskStatus::Pending)
                    ->orderBy('created_at')
                    ->firstOrFail();

                $task->status = TaskStatus::InProgress;
                $task->started_at = CarbonImmutable::now();
                $task->save();

                return $task;
            });
        } catch (LockTimeoutException|ModelNotFoundException) {
            return null;
        }
    }

    public function finishTask(Task $task, ?TaskStatus $status): void
    {
        if ($status !== null) {
            $task->status = $status;
        }
        $task->token = null;
        $task->finished_at = CarbonImmutable::now();
        $task->save();
        Log::info('Task finished', ['task' => $task->id]);
        TaskFinishedEvent::dispatch($task);
    }

    public function setStatus(Task $task, TaskStatus $status): void
    {
        $task->status = $status;
        $task->save();
    }

    /**
     * @return Collection<value-of<MessageStatus>, int>
     */
    public function getStats(string $taskId): Collection
    {
        $data = DB::table(TaskChat::make()->getTable())
            ->select(DB::raw('count(*) as count, status'))
            ->where('task_id', $taskId)
            ->groupBy('status')
            ->get();

        return $data
            ->mapWithKeys(fn(\stdClass $item) => [$item->status ?? MessageStatus::Pending->value => $item->count])
            ->sortKeys();
    }

    /**
     * @return Collection<value-of<MessageStatus>, int>
     */
    public function getPrefetchStats(string $taskId): Collection
    {
        $data = DB::table(TaskChat::make()->getTable())
            ->select(DB::raw('count(*) as count, prefetch_status'))
            ->where('task_id', $taskId)
            ->groupBy('prefetch_status')
            ->get();

        return $data
            ->mapWithKeys(fn(\stdClass $item) => [$item->prefetch_status ?? MessageStatus::Pending->value => $item->count])
            ->sortKeys();
    }
}
