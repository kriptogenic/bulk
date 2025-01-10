<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\TaskFinishedEvent;
use App\Listeners\SendTaskWebhookListener;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot(Dispatcher $dispatcher): void
    {
        $dispatcher->listen(TaskFinishedEvent::class, SendTaskWebhookListener::class);
    }
}
