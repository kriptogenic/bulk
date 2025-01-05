<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\TaskChat;
use Illuminate\Http\Request;

/**
 * @extends JsonResource<TaskChat>
 * @mixin TaskChat
 */
final class TaskChatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'chat_id' => $this->resource->chat_id,
            'status' => $this->resource->status,

            'task_id' => $this->resource->task_id,
        ];
    }
}
