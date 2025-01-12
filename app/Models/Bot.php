<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasVersion7Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bot extends Model
{
    use HasVersion7Uuids;

    protected $guarded = [];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, foreignKey: 'bot_id', localKey: 'bot_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, parentKey: 'bot_id');
    }
}
