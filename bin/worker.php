<?php

declare(strict_types=1);

require '../vendor/autoload.php';

$scheduler = new \Swoole\Coroutine\Scheduler();

$scheduler->add(function (){
    $app = new \App\Console\Kernel();
    $app->start();
});
$scheduler->set(['hook_flags' => SWOOLE_HOOK_ALL]);
$scheduler->start();
