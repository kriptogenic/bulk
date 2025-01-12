<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\User;
use App\Services\BotRepository;
use App\Services\TelegramService;
use App\Models\Bot;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\MoonShineAuth;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Link;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use Sweet1s\MoonshineRBAC\Traits\WithRolePermissions;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @extends ModelResource<Bot>
 */
#[Icon('at-symbol')]
class BotResource extends ModelResource
{
    use WithRolePermissions;

    protected string $model = Bot::class;

    protected string $title = 'Bots';

    protected bool $withPolicy = true;

    protected function modifyQueryBuilder(Builder $builder): Builder
    {
        $user = MoonShineAuth::getGuard()->user();
        if (!$user instanceof User) {
            throw new UnauthorizedHttpException('login');
        }

        if ($user->isSuperAdmin()) {
            return $builder;
        }

        return $builder->whereHas('users', function (Builder $builder) use ($user) {
            return $builder->where('id', $user->id);
        });
    }

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            Text::make('Username')
                ->changePreview(fn(string $value) => Link::make('https://t.me/' . $value, '@' . $value)),
            Number::make('Bot ID'),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                Text::make('Token')
                    ->hint('We don\'t store tokens. We will use only to authorize the bot.')
                    ->required(),
            ]),
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            Text::make('Username')
                ->changePreview(fn(string $value) => Link::make('https://t.me/' . $value, '@' . $value)),
            Number::make('Bot ID'),
            HasMany::make('Tasks', 'tasks', TaskResource::class),
        ];
    }

    /**
     * @param Bot $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [
            'token' => [
                'required',
                'string',
                'regex:/^[0-9]{4,10}:[a-zA-Z0-9_-]{35}$/',
            ],
        ];
    }

    public function save(mixed $item, ?FieldsContract $fields = null): mixed
    {
        $fields ??= $this->getFormFields()->onlyFields();
        $tokenField = $fields->first(fn(FieldContract $field): bool => $field->getColumn() === 'token')
            ?? throw new BadRequestHttpException('Token field is required.');
        $token = $tokenField->getRequestValue();

        $telegramService = new TelegramService($token);
        unset($token, $tokenField); // Clear token
        /** @var BotRepository $botRepository */
        $botRepository = app()->make(BotRepository::class);
        $botInfo = $telegramService->getMe();
        $bot = $botRepository->findOrCreate($botInfo->id, $botInfo->username);

        $user = MoonShineAuth::getGuard()->user();
        if (!$user instanceof User) {
            throw new UnauthorizedHttpException('login');
        }
//        dd($user);

        $user->bots()->attach($bot);

        $this->setItem($bot);

        return $bot;
    }
}
