<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SendMethod;
use App\Enums\TaskStatus;
use App\Exceptions\TaskCancelledException;
use App\Jobs\SaveTaskResultsJob;
use App\Models\Task;
use Illuminate\Support\Collection;

class TaskProcessor
{
    public function __construct(
        private TaskJobsWaiter $taskJobsWaiter,
        private Sender $sender,
    ) {}

    /**
     * @throws TaskCancelledException
     */
    public function process(Task $task, Collection $chats, bool $isPrefetch): void
    {
        $method = $isPrefetch ? SendMethod::SendChatAction : $task->method;
        $params = $isPrefetch ? ['action' => $task->prefetch_type->value] : $task->params;

        $this->taskJobsWaiter->start($task);
        $results = $this->sender->send(
            $task->token,
            $method,
            $chats,
            $params,
        );
        $this->traverseAndSaveResults($results, $task, $isPrefetch);
        $this->taskJobsWaiter->wait($task);
    }

    /**
     * @throws TaskCancelledException
     */
    private function traverseAndSaveResults(\Generator $results, Task $task, bool $isPrefetch): void
    {
        foreach ($results as $result) {
            SaveTaskResultsJob::dispatch($task, $result, $isPrefetch);
            if ($this->shouldUpdate($task->method)) {
                $task->refresh();
                if ($task->status !== TaskStatus::InProgress) {
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
