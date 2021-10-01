<?php

declare(strict_types=1);

namespace App\Async;

use App\Async\BotApi\Message;
use App\Async\BotApi\Pool;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\System;

class Sender
{
    private Pool $pool;

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    public function send(array $chats_id, string $token, string $method, array $data): array
    {
        $start = microtime(true);
        $messages = new Channel();
        $n = count($chats_id);
        foreach ($chats_id as $chat_id) {
            go(function () use ($chat_id, $token, $method, $data, $messages){
                $chat_id = intval($chat_id);
                $bot = $this->pool->get();
                $message = $bot->execute($token, $method, $chat_id, $data);
                $this->pool->put($bot);
                $messages->push($message);
            });
        }
        $retry_after = 0;

        $result = [];
        while ($n--) {
            /** @var Message $message */
            $message = $messages->pop();
            $status = $message->getStatus();
            if ($status === Message::STATUS_TOO_MANY_REQUESTS){
                if ($message->getRetryAfter() > $retry_after) {
                    $retry_after = $message->getRetryAfter();
                }
            }
            $result[$status][] = $message->getChatId();
        }

        if ($retry_after > 0) {
            sleep($retry_after);
            echo 'Retried after: ' . $retry_after .PHP_EOL;
        }

        $time = microtime(true) - $start;
        if ($time < 1) {
            echo $time, PHP_EOL;
            System::sleep(1 - $time);
        }

        return $result;
    }
}