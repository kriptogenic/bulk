<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Exceptions\RetryAfterException;
use App\Exceptions\TaskCancelledException;
use App\Models\Task;
use App\Services\Repositories\TaskChatRepository;
use App\Services\Repositories\TaskRepository;
use App\Services\TaskProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class WorkerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 0;
    public null $retryAfter = null;

    public function __construct(private Task $task) {}

    public function handle(
        TaskProcessor $taskProcessor,
        TaskRepository $taskRepository,
        TaskChatRepository $taskChatRepository,
    ): void {
        if (!in_array($this->task->status, [TaskStatus::Pending, TaskStatus::InProgress])) {
            return;
        }

        try {
            if ($this->task->prefetch_type !== null) {
                $chats = $taskChatRepository->getPendingPrefetchChats($this->task);
                $taskProcessor->process($this->task, $chats, true);
            }
            $chats = $taskChatRepository->getPendingSendChats($this->task);

            $taskProcessor->process($this->task, $chats, false);
            $taskRepository->finishTask($this->task, TaskStatus::Completed);
        } catch (TaskCancelledException) {
            $taskRepository->finishTask($this->task, null);
        } catch (RetryAfterException $e) {
            $delay = now()->addSeconds($e->retryAfter)->addMinute();
            self::dispatch($this->task)->delay($delay);
            return;
        } catch (Throwable $exception) {
            report($exception);
            $taskRepository->finishTask($this->task, TaskStatus::Failed);
        }
    }

    /**
     * @throws TaskCancelledException
     */
    private function processTask(Task $task): void {}
}
