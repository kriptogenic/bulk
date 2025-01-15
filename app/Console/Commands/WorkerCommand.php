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
use App\Services\TaskProcessor;
use App\Services\TaskRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Laravel\Horizon\Contracts\TagRepository;
use Throwable;

class WorkerCommand extends Command
{
    protected $signature = 'worker';

    protected $description = 'Command description';
    private TaskProcessor $taskProcessor;

    public function handle(
        TaskRepository $taskRepository,
        TaskProcessor $taskProcessor,
    ): void
    {
        $this->taskProcessor = $taskProcessor;

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

        $chats = $task->chats()->pluck('chat_id');

        if ($task->prefetch_type !== null) {
            $this->taskProcessor->process($task, $chats, true);
            $chats = $task->chats()->where('prefetch_status', MessageStatus::Delivered)->pluck('chat_id');
        }

        $this->taskProcessor->process($task, $chats, false);
    }
}
