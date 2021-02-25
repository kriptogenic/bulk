<?php

declare(strict_types=1);

namespace App\Console;

use App\Async\BotApi\Pool;
use App\Async\Memory\RedisConnectionPool;
use App\Async\Memory\RedisMemory;
use App\Async\Memory\RedisQueue;
use App\Async\Sender;
use Mix\Monolog\Handler\ConsoleHandler;
use Mix\Monolog\Handler\RotatingFileHandler;
use Mix\Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\System;

class Kernel
{
    public function start()
    {
        $killSignal = new Channel();
        $this->registerShutdownFunction($killSignal);

        $senderChannel = new Channel();
        $redisPool = new RedisConnectionPool('@localhost:6379', 11);
        $queue = new RedisQueue($redisPool->get());
        $this->runReactor($queue, $senderChannel, $killSignal);

        $memory = new RedisMemory($redisPool);
        $logger = $this->createLogger();
        $botApiPool = new Pool($logger);
        $sender = new Sender($botApiPool);

        $this->runWorkers($senderChannel, $sender, $memory, 20);
    }

    private function runReactor(RedisQueue $queue, Channel $senderChannel, Channel $killSignal)
    {
        go(static function () use ($queue, $senderChannel, $killSignal){
            while (true){
                if ($killSignal->isFull())
                    break;

                $chat_id = $queue->deque();
                if (is_null($chat_id)) {
                    System::sleep(2);
                    continue;
                }
                $senderChannel->push($chat_id);
            }
        });
    }

    private function runWorkers(Channel $senderChannel, Sender $sender, RedisMemory $memory, int $count)
    {
        while ($count--) {
            go(static function () use ($senderChannel, $sender, $memory) {
                $bot_id = $senderChannel->pop();
                $meta = $memory->getMeta($bot_id);
                $data = $memory->getMethodData($bot_id);

                while (true) {
                    $chats_id = $memory->getChatsId($bot_id, $meta['count']);
                    if (empty($chats_id)) {
                        break;
                    }

                    $sender->send($chats_id, $meta['token'], $meta['method'], $data);
                }
            });
        }
    }

    private function createLogger(): Logger
    {
        $logger  = new Logger('Worker', [new ConsoleHandler()], [new PsrLogMessageProcessor()]);
//        $handler = new RotatingFileHandler(sprintf('%s/runtime/logs/worker.log', __DIR__ . '/../'), 7);
        $handler = new StreamHandler('php://stderr');
        $logger->pushHandler($handler);
        return $logger;
    }

    private function registerShutdownFunction(Channel $callback)
    {
        go(static function () use ($callback){
           System::waitSignal(SIGTERM);
           $callback->push(SIGTERM);
        });
    }
}
