<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\ConfiguratorContract;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;
use App\MoonShine\Resources\UserResource;
use App\MoonShine\Resources\RoleResource;
use App\MoonShine\Resources\TaskResource;
use App\MoonShine\Resources\TaskChatResource;
use App\MoonShine\Resources\BotResource;
use App\MoonShine\Pages\TaskDetailPage;
use Sweet1s\MoonshineRBAC\Resource\PermissionResource;

class MoonShineServiceProvider extends ServiceProvider
{
    /**
     * @param  MoonShine  $core
     * @param  MoonShineConfigurator  $config
     *
     */
    public function boot(CoreContract $core, ConfiguratorContract $config): void
    {
        // $config->authEnable();

        $core
            ->resources([
                UserResource::class,
                RoleResource::class,
                TaskResource::class,
                TaskChatResource::class,
                BotResource::class,
                PermissionResource::class,
            ])
            ->pages([
                ...$config->getPages(),
                TaskDetailPage::class,
            ])
        ;
    }
}
