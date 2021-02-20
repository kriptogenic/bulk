<?php

namespace App\Async\BotApi;

use Psr\Log\LoggerInterface;
use Swoole\Coroutine\Channel;

class Pool
{
    protected Channel $pool;

    public function __construct(LoggerInterface $logger, int $size = 100)
    {
        $this->pool = new Channel($size);

        for($i = 0; $i < $size; $i++){
            $this->pool->push(new CurlClient($logger));
        }
    }

    public function get(): Client
    {
        return $this->pool->pop();
    }

    public function put(Client $botApi)
    {
        return $this->pool->push($botApi);
    }
}
