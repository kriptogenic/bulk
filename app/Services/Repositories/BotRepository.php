<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\Models\Bot;

class BotRepository
{
    public function findOrCreate(int $botId, string $username): Bot
    {
        return Bot::firstOrCreate(['bot_id' => $botId], ['username' => $username]);
    }
}
