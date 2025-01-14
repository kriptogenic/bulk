<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SaveChatsBackgroundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 0;

    public function __construct(private Task $task, private array $chats) {}

    public function handle(): void
    {
        collect($this->chats)
            ->chunk(15_000)
            ->each(function (Collection $chats) {
                $chats = $chats->map(fn(int|string $chat) => ['chat_id' => (int)$chat]);
                $this->task->chats()->createMany($chats);
            });
        $this->task->status = TaskStatus::Pending;
        $this->task->save();
    }
}
