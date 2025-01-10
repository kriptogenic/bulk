<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Role;
use App\Models\User;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use Sweet1s\MoonshineRBAC\Resource\PermissionResource;
use Sweet1s\MoonshineRBAC\Traits\WithPermissionsFormComponent;
use Sweet1s\MoonshineRBAC\Traits\WithRolePermissions;

#[Icon('bookmark')]
/**
 * @extends ModelResource<User>
 */
class RoleResource extends ModelResource
{
    use WithRolePermissions;
    use WithPermissionsFormComponent;
    protected string $model = Role::class;

    protected string $column = 'name';

    protected bool $createInModal = true;

    protected bool $cursorPaginate = true;

    public function getTitle(): string
    {
        return __('moonshine::ui.resource.role');
    }

//    protected function activeActions(): ListOf
//    {
//        return parent::activeActions()->except(Action::VIEW);
//    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make(__('moonshine::ui.resource.role_name'), 'name'),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ...$this->indexFields(),
            HasMany::make('Permissions', 'permissions', PermissionResource::class),
            ];
    }

    protected function formFields(): iterable
    {
        return [
            Box::make([
                ID::make()->sortable(),
                Text::make(__('moonshine::ui.resource.role_name'), 'name')
                    ->required(),
            ]),
        ];
    }

    /**
     * @return array{name: array|string}
     */
    protected function rules($item): array
    {
        return [
            'name' => ['required', 'min:3'],
        ];
    }

    protected function search(): array
    {
        return [
            'id',
            'name',
        ];
    }
}
