<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Enums\SendMethod;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\MoonShineAuth;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Link;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\DateRange;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Url;
use RuntimeException;
use SergiX44\Nutgram\Telegram\Properties\ChatAction;
use Sweet1s\MoonshineRBAC\Traits\WithRolePermissions;

/**
 * @extends ModelResource<Task>
 */
class TaskResource extends ModelResource
{
    use WithRolePermissions;

    protected string $model = Task::class;

    protected string $title = 'Tasks';

    protected function modifyQueryBuilder(Builder $builder): Builder
    {
        $user = MoonShineAuth::getGuard()->user();
        if (!$user instanceof User) {
            throw new RuntimeException('User not authenticated');
        }

        if ($user->roles->pluck('id')->containsStrict(User::SUPER_ADMIN_ROLE_ID)) {
            return $builder;
        }

        return $builder;
    }

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            Date::make('Created At'),
            ID::make()->sortable(),
            Text::make('Username')
                ->changePreview(fn(string $value) => Link::make('https://t.me/' . $value, '@' . $value)),
            Enum::make('Method')->attach(SendMethod::class),
            Enum::make('Prefetch type')->attach(ChatAction::class),
            Url::make('Webhook'),
            Enum::make('Status')->attach(TaskStatus::class),

            Date::make('Started At'),
            Date::make('Finished At'),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Enum::make('Status')->attach(TaskStatus::class),
            ]),
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            Date::make('Created At'),
            ID::make()->sortable(),
            Number::make('Bot ID'),
            Text::make('Token'),
            Text::make('Username')
                ->changePreview(fn(string $value) => Link::make('https://t.me/' . $value, '@' . $value)),
            Enum::make('Method')->attach(SendMethod::class),
            Enum::make('Prefetch type')->attach(ChatAction::class),
            Url::make('Webhook'),
            Enum::make('Status')->attach(TaskStatus::class),

            Date::make('Started At'),
            Date::make('Finished At'),

            HasMany::make('Chats', resource: TaskChatResource::class),
        ];
    }

    /**
     * @param Task $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }

    protected function search(): array
    {
        return ['id', 'username'];
    }

    protected function filters(): iterable
    {
        return [
            DateRange::make('Created At'),
            Enum::make('Method')->attach(SendMethod::class)->nullable(),
            Enum::make('Prefetch type')->attach(ChatAction::class)->nullable(),
            Enum::make('Status')->attach(TaskStatus::class)->nullable(),
        ];
    }
}
