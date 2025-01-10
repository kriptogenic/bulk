<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use App\MoonShine\Resources\RoleResource;
use App\MoonShine\Resources\UserResource;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\ColorManager\ColorManager;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\UI\Components\Layout\Layout;
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

//            MenuGroup::make('System2', [
//                MenuItem::make('Admins', \Sweet1s\MoonshineRBAC\Resource\UserResource::class, 'users'),
//                MenuItem::make('Roles', \Sweet1s\MoonshineRBAC\Resource\RoleResource::class, 'shield-exclamation'),
//                MenuItem::make('Permissions', \Sweet1s\MoonshineRBAC\Resource\PermissionResource::class, 'shield-exclamation'),
//            ], 'user-group'),
//            ...parent::menu(),
            MenuItem::make('Tasks', TaskResource::class),
            MenuItem::make('TaskChats', TaskChatResource::class),
        ];
    }

    /**
     * @param ColorManager $colorManager
     */
    protected function colors(ColorManagerContract $colorManager): void
    {
        parent::colors($colorManager);

        // $colorManager->primary('#00000');
    }

    public function build(): Layout
    {
        return parent::build();
    }
}
