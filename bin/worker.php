<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Async\BotApi\Pool as BotApiPool;
use Swoole\Coroutine\Channel;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\go;

//$chats_id = require 'users.php';
$chats_id = [47543915];
run(function ($chats_id){
    $logger  = new \Mix\Monolog\Logger('API', [new \Mix\Monolog\Handler\ConsoleHandler()], [new \Monolog\Processor\PsrLogMessageProcessor()]);
    $handler = new \Mix\Monolog\Handler\RotatingFileHandler(sprintf('%s/runtime/logs/api.log', __DIR__ . '/../'), 7);
//    $handler = new \Monolog\Handler\StreamHandler('php://stderr');
    $logger->pushHandler($handler);
    $pool = new BotApiPool($logger);
    send($chats_id, $pool);
}, $chats_id);


function send(array $chats_id, BotApiPool $pool){
    $result = [];
    $s = microtime(true);
    foreach (array_chunk($chats_id, 100) as $chunk) {
        $start = microtime(true);
        $messages = new Channel();
        $n = count($chunk);
        foreach ($chunk as $chat_id) {
            go(function () use ($chat_id, $pool, $messages){
                $bot = $pool->get();
//                $message = $bot->execute(
//                    'BOT_TOKEN',
//                    'forwardMessage',
//                    $chat_id,
//                    [
//                        'from_chat_id' => -1001122696702,
//                        'message_id' => 548,
//                        'disable_notification' => true
//                    ]
//                );
                $message = $bot->execute(
                    'BOT_TOKEN',
                    'sendChatAction',
                    $chat_id,
                    [
                        'action' => 'typing'
                    ]
                );
                $pool->put($bot);
                $messages->push($message);
            });
        }
        $retry_after = 0;
        while ($n--) {
            /** @var \App\Async\BotApi\Message $message */
            $message = $messages->pop();
            $status = $message->getStatus();
            if ($status === \App\Async\BotApi\Message::STATUS_TOO_MANY_REQUESTS){
                if ($message->getRetryAfter() > $retry_after) {
                    $retry_after = $message->getRetryAfter();
                }
            }
            $result[$status][] = $message->getChatId();
//            echo $message->getChatId() . '-' . $message->getStatus() . "\n";
        }

        if ($retry_after > 0) {
            sleep($retry_after);
            echo 'Retried after: ' . $retry_after .PHP_EOL;
        }

        $time = microtime(true) - $start;
        if ($time < 1) {
            echo $time, PHP_EOL;
            \Swoole\Coroutine\System::sleep(1 - $time);
        }
    }

    echo "\n\n------------------\n";
    foreach ($result as $k => $r){
        $c = count($r);
        echo  "$k - $c\n";
    }
    echo "------------------\n";
    echo "time: " , timed(microtime(true) - $s) , "\n";
    file_put_contents('result.json', json_encode($result));

    return $result;
}
function timed($seconds) {
    $t = round($seconds);
    return sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60);
}