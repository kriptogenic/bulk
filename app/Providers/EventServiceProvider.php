<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\TaskFinishedEvent;
use App\Listeners\SendTaskWebhookListener;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Telegram\Provider as TelegramProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot(Dispatcher $dispatcher): void
    {
        $dispatcher->listen(TaskFinishedEvent::class, SendTaskWebhookListener::class);
        $dispatcher->listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('telegram', TelegramProvider::class);
        });
    }
}
