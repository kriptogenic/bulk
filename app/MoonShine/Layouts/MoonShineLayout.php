<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use App\MoonShine\Resources\RoleResource;
use App\MoonShine\Resources\UserResource;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\MenuManager\MenuGroup;
use App\MoonShine\Resources\TaskResource;
use MoonShine\MenuManager\MenuItem;
use App\MoonShine\Resources\TaskChatResource;

final class MoonShineLayout extends AppLayout
{
    protected function assets(): array
    {
        return [
            ...parent::assets(),
        ];
    }

    protected function menu(): array
    {
        return [
            MenuGroup::make(static fn () => __('moonshine::ui.resource.system'), [
                MenuItem::make(
                    static fn () => __('moonshine::ui.resource.admins_title'),
                    UserResource::class
                ),
                MenuItem::make(
                    static fn () => __('moonshine::ui.resource.role_title'),
                    RoleResource::class
                ),
            ]),

            MenuItem::make('Tasks', TaskResource::class),
            MenuItem::make('TaskChats', TaskChatResource::class),
        ];
    }

    protected function getFooterCopyright(): string
    {
        return '';
    }

    protected function getFooterMenu(): array
    {
        return ['https://github.com/kriptogenic/zaraz' => 'Github'];
    }
}
