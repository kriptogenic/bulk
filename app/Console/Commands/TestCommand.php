<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Task;
use App\Services\TaskJobsWaiter;
use Illuminate\Console\Command;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\Contracts\TagRepository;

class TestCommand extends Command
{
    protected $signature = 'test';

    protected $description = 'Command description';

    public function handle(TagRepository $tagRepository, JobRepository $jobRepository, TaskJobsWaiter $taskJobsWaiter): void
    {
        $taskJobsWaiter->wait(new Task());
    }
}
