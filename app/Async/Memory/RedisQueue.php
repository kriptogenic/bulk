<?php

declare(strict_types=1);

namespace App\Async\Memory;

class RedisQueue
{
    private const STATUS_PENDING = 'pending';

    public function __construct(private \Redis $connection)
    {
    }

    public function deque(): ?int
    {
        return array_keys($this->connection->zPopMin(self::STATUS_PENDING, 1))[0] ?? null;
    }

    public function enque(int $botId, int $timeStamp)
    {
        $this->connection->zAdd(self::STATUS_PENDING, $timeStamp, $botId);
    }
}
