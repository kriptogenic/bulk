<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\Enums\MessageStatus;
use App\Enums\SendMethod;
use App\Enums\TaskStatus;
use App\Events\TaskFinishedEvent;
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
use stdClass;

class TaskRepository
{
    public function __construct(
        private BotRepository $botRepository,
        private TaskChatRepository $chatRepository,
    ) {}

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
        $this->chatRepository->createChats($task, $chats);
        DB::commit();
        return $task;
    }

    public function getById(string $id): Task
    {
        $task = Task::withCount('chats')->findOrFail($id);
        if ($task->chats_count <= TaskChat::BIG_BOTS_LIMIT) {
            $task->load('chats');
        }
        return $task;
    }

    public function hasPendingTaskForBot(int $botId): bool
    {
        return Task::where('bot_id', $botId)
            ->whereIn('status', [TaskStatus::Creating, TaskStatus::Pending, TaskStatus::InProgress])
            ->exists();
    }

    public function reserveTask(): ?Task
    {
        $lock = Cache::lock('reserved_task', 20);

        try {
            return $lock->block(15, function (): ?Task {
                $task = Task::withCount('chats')
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
            ->mapWithKeys($this->transFormStats(...))
            ->sortKeys();
    }

    /**
     * @return Collection<value-of<MessageStatus>, int>
     */
    public function getPrefetchStats(string $taskId): Collection
    {
        $data = DB::table(TaskChat::make()->getTable())
            ->select(DB::raw('count(*) as count, prefetch_status as status'))
            ->where('task_id', $taskId)
            ->groupBy('prefetch_status')
            ->get();

        return $data
            ->mapWithKeys($this->transFormStats(...))
            ->sortKeys();
    }

    /**
     * @param stdClass{status: ?value-of<MessageStatus>, count: int} $row
     * @return array{value-of<MessageStatus>, int}
     */
    private function transFormStats(stdClass $row): array
    {
        return [$row->status ?? MessageStatus::Pending->value => $row->count];
    }
}
