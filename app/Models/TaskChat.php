<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MessageStatus;
use Illuminate\Database\Eloquent\Concerns\HasVersion7Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskChat extends Model
{
    use HasVersion7Uuids;

    protected $fillable = [
        'chat_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => MessageStatus::class,
            'prefetch_status' => MessageStatus::class,
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
