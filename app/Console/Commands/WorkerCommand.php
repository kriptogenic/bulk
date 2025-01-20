<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\WorkerJob;
use App\Services\Repositories\TaskRepository;
use Illuminate\Console\Command;

class WorkerCommand extends Command
{
    protected $signature = 'worker';

    protected $description = 'Command description';

    public function handle(TaskRepository $taskRepository): void
    {
        while (true) {
            $task = $taskRepository->reserveTask();
            if ($task === null) {
                $this->warn('Task not found');
                sleep(5);
                continue;
            }

            $this->info('Processing task ' . $task->id);
            WorkerJob::dispatchSync($task);
        }
    }
}
