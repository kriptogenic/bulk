<?php

declare(strict_types=1);

namespace App\MoonShine\Components;

use MoonShine\UI\Components\MoonShineComponent;
use SocialiteProviders\Telegram\Provider;

/**
 * @method static static make()
 */
final class TelegramLoginButtonComponent extends MoonShineComponent
{
    protected string $view = 'admin.components.telegram-login-button';

    /*
     * @return array<string, mixed>
     */
    protected function viewData(): array
    {
        return [
            'telegramSocialiteProvider' => app(Provider::class),
        ];
    }
}
