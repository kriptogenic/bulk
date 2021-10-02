<?php

declare(strict_types=1);

use App\Console\Kernel;
use Swoole\Coroutine\Scheduler;

const BASE_DIR = __DIR__ . '/';

require BASE_DIR . 'vendor/autoload.php';

$scheduler = new Scheduler();

$scheduler->add(function (){
    $app = new Kernel();
    $app->start();
});
$scheduler->set(['hook_flags' => SWOOLE_HOOK_ALL]);
$scheduler->start();
