<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\MessageStatus;
use App\Enums\SendMethod;
use App\Enums\TaskStatus;
use App\Exceptions\TaskCancelledException;
use App\Jobs\SaveTaskResultsJob;
use App\Models\Task;
use App\Services\Sender;
use App\Services\TaskJobsWaiter;
use App\Services\TaskRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Laravel\Horizon\Contracts\TagRepository;
use Throwable;

class WorkerCommand extends Command
{
    protected $signature = 'worker';

    protected $description = 'Command description';
    private Sender $sender;
    private TaskJobsWaiter $taskJobsWaiter;

    public function handle(
        Sender $sender,
        TaskRepository $taskRepository,
        TaskJobsWaiter $taskJobsWaiter,
    ): void
    {
        $this->sender = $sender;
        $this->taskJobsWaiter = $taskJobsWaiter;

        while (true) {
            $task = $taskRepository->reserveTask();
            if ($task === null) {
                $this->warn('Task not found');
                sleep(5);
                continue;
            }
            try {
                $this->processTask($task);
                $taskRepository->finishTask($task, TaskStatus::Completed);
            } catch (TaskCancelledException) {
                $taskRepository->finishTask($task, null);
            } catch (Throwable $exception) {
                report($exception);
                $taskRepository->finishTask($task, TaskStatus::Failed);
            }
        }
    }

    /**
     * @throws TaskCancelledException
     */
    private function processTask(Task $task): void
    {
        $this->info('Processing task ' . $task->id);

        $chats = $task->chats->pluck('chat_id');

        if ($task->method !== SendMethod::SendChatAction) {
            $this->taskJobsWaiter->start($task);
            $results = $this->sender->send($task->token, SendMethod::SendChatAction, $chats, [
                'action' => $task->method->prefetchAction()->value,
            ]);
            $this->traverseAndSaveResults($results, $task, true);
            $this->taskJobsWaiter->wait($task);
            $chats = $task->chats()->where('prefetch_status', MessageStatus::Delivered)->pluck('chat_id');
        }
        dump($chats);

        $this->taskJobsWaiter->start($task);
        $results = $this->sender->send(
            $task->token,
            $task->method,
            $chats,
            $task->params,
        );
        $this->traverseAndSaveResults($results, $task, false);
        $this->taskJobsWaiter->wait($task);
    }

    private function traverseAndSaveResults(\Generator $results, Task $task, bool $isPrefetch): void
    {
        foreach ($results as $result) {
            SaveTaskResultsJob::dispatch($task, $result, $isPrefetch);
            if ($this->shouldUpdate($task->method)) {
                $task->refresh();
                if ($task->status !== TaskStatus::InProgress) {
                    $this->error('Task canceled');
                    throw new TaskCancelledException();
                }
            }
        }
    }

    private function shouldUpdate(SendMethod $method): bool
    {
        $chance = min(100, $method->perSecond());
        return mt_rand(1, 100) <= $chance;
    }
}
