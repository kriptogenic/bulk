<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\MessageStatus;
use App\Models\Task;
use App\Services\TaskJobsWaiter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SaveTaskResultsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param Collection<int, MessageStatus> $results
     */
    public function __construct(
        private Task $task,
        private Collection $results,
        private bool $isPrefetch,
    ) {}

    public function handle(): void
    {
        $this->results->each(function (MessageStatus $status, int $chatId): void {
            if ($this->isPrefetch) {
                $values = [
                    'prefetch_status' => $status,
                ];
                if ($status !== MessageStatus::Delivered) {
                    $values['status'] = $status;
                }
            } else {
                $values = ['status' => $status];
            }
            $this->task->chats()->where('chat_id', $chatId)->update($values);
        });
    }

    /**
     * @return non-empty-list<string>
     */
    public function tags(): array
    {
        return [
            'save-results',
            TaskJobsWaiter::getTag($this->task),
        ];
    }
}
