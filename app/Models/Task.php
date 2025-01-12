<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SendMethod;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Concerns\HasVersion7Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SergiX44\Nutgram\Telegram\Properties\ChatAction;

class Task extends Model
{
    use HasVersion7Uuids;

    protected $with = ['bot'];

    protected function casts(): array
    {
        return [
            'method' => SendMethod::class,
            'prefetch_type' => ChatAction::class,
            'params' => 'json',
            'status' => TaskStatus::class,
            'started_at' => 'immutable_datetime',
            'finished_at' => 'immutable_datetime',
        ];
    }

    public function bot(): BelongsTo
    {
        return $this->belongsTo(Bot::class, 'bot_id', 'bot_id');
    }

    public function chats(): HasMany
    {
        return $this->hasMany(TaskChat::class);
    }
}
