<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Enums\SendMethod;
use App\Enums\TaskStatus;
use App\MoonShine\Resources\TaskChatResource;
use App\Services\TaskRepository;
use MoonShine\Apexcharts\Components\DonutChartMetric;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Link;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Url;
use SergiX44\Nutgram\Telegram\Properties\ChatAction;
use Throwable;

class TaskDetailPage extends DetailPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
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
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        $repository = app(TaskRepository::class);
        return [
            Grid::make([
                Column::make(
                    parent::mainLayer(),
                    colSpan: 6,
                ),
                Column::make(
                    [
                        DonutChartMetric::make('Send stats')
                            ->values($repository->getStats($this->resource->getItem()->id)->toArray()),
                        DonutChartMetric::make('Prefetch stats')
                            ->values($repository->getPrefetchStats($this->resource->getItem()->id)->toArray()),
                    ],
                    colSpan: 6,
                ),
            ]),

        ];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer(),
        ];
    }
}
