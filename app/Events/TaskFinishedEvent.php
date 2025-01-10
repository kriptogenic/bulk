<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;

class TaskFinishedEvent implements ShouldQueue
{
    use Dispatchable;

    public function __construct(public Task $task) {}
}
