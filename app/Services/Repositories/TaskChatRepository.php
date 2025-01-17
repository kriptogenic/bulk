<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\Enums\MessageStatus;
use App\Enums\TaskStatus;
use App\Jobs\SaveChatsBackgroundJob;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TaskChatRepository
{
    public function createChats(Task $task, array $chats): void
    {
        if ($task->status === TaskStatus::Creating) {
            SaveChatsBackgroundJob::dispatch($task, $chats);
            return;
        }
        $chats = collect($chats)->map(fn(int|string $chat) => ['chat_id' => (int)$chat]);
        $task->chats()->createMany($chats);
    }

    /**
     * @return Collection<int, int>
     */
    public function getPendingPrefetchChats(Task $task): Collection
    {
        return $this->getPendingChats($task, true);
    }

    /**
     * @return Collection<int, int>
     */
    public function getPendingSendChats(Task $task): Collection
    {
        return $this->getPendingChats($task, false);
    }


    private function getPendingChats(Task $task, bool $isPrefetch): Collection
    {
        $column = $isPrefetch ? 'prefetch_status' : 'status';
        return $task
            ->chats()
            ->where(static fn(Builder $query): Builder
                => $query
                ->where($column, MessageStatus::TooManyRequests)
                ->orWhereNull($column),
            )
            ->orderBy('chat_id')
            ->pluck('chat_id');
    }
}
