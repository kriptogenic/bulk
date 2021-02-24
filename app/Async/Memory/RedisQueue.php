<?php

declare(strict_types=1);

namespace App\Async\Memory;

class RedisQueue
{
    public function __construct(private \Redis $connection)
    {
    }

    public function deque(): int
    {
        return $this->connection->zPopMin('waiting_status', 1)[0];
    }

    public function enque(int $botId, int $timeStamp)
    {
        $this->connection->zAdd('waiting_status', $timeStamp, $botId);
    }
}
