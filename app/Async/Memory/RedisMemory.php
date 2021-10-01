<?php

declare(strict_types=1);

namespace App\Async\Memory;

class RedisMemory
{
    public function __construct(private RedisConnectionPool $connectionPool)
    {
    }

    /**
     * @return ?int[]
     */
    public function getChatsId(int $bot_id, int $count): ?array
    {
        $connection = $this->connectionPool->get();
        $chats_id = $connection->sPop('chats_id:' . $bot_id, $count);
        $this->connectionPool->put($connection);
        return $chats_id;
    }

    public function getMeta(int $bot_id): Meta
    {
        $connection = $this->connectionPool->get();
        $meta = $connection->hGetAll('bot_data:' . $bot_id);
        $this->connectionPool->put($connection);
        return new Meta($meta);
    }

    public function getMethodData(int $bot_id): array
    {
        $connection = $this->connectionPool->get();
        $data = $connection->hGetAll('request_data:' . $bot_id);
        $this->connectionPool->put($connection);
        return $data;
    }

    public function putResult(int $bot_id, array $result)
    {
        $connection = $this->connectionPool->get();
        foreach ($result as $key => $chats_id) {
            $connection->sAddArray('result:' . $bot_id . 'key' . $key, $chats_id);
        }
        $this->connectionPool->put($connection);
    }


    public function method(int $bot_id)
    {
        $connection = $this->connectionPool->get();

        $this->connectionPool->put($connection);
    }
}
