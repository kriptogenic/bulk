<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Task;
use App\Services\Repositories\TaskRepository;
use Illuminate\Http\Request;

/**
 * @extends JsonResource<Task>
 * @mixin Task
 */
final class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'bot_id' => $this->resource->bot_id,
            'username' => $this->resource->username,
            'status' => $this->resource->status,
            'webhook' => $this->resource->webhook,
            'started_at' => $this->resource->started_at,
            'finished_at' => $this->resource->finished_at,
            'stats' => app(TaskRepository::class)->getStats($this->resource->id),
            'chats' => TaskChatResource::collection($this->whenLoaded('chats')),
        ];
    }
}
