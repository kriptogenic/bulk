<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SendMethod;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Concerns\HasVersion7Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasVersion7Uuids;

    protected function casts(): array
    {
        return [
            'method' => SendMethod::class,
            'params' => 'json',
            'status' => TaskStatus::class,
            'started_at' => 'immutable_datetime',
            'finished_at' => 'immutable_datetime',
        ];
    }

    public function chats(): HasMany
    {
        return $this->hasMany(TaskChat::class);
    }
}
