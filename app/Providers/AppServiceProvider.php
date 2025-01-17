<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\DateFactory;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory;
use SocialiteProviders\Telegram\Provider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            Provider::class,
            static fn(Application $app): Provider => $app->make(Factory::class)->driver('telegram'),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(DateFactory $dateFactory): void
    {
        $dateFactory->use(CarbonImmutable::class);
    }
}
