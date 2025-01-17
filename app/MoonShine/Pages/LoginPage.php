<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\MoonShine\Components\TelegramLoginButtonComponent;
use Generator;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Forms\LoginForm;
use MoonShine\Laravel\Pages\LoginPage as BaseLogin;

class LoginPage extends BaseLogin
{
    /**
     * @return Generator<ComponentContract>
     */
    protected function components(): iterable
	{
        yield moonshineConfig()->getForm('login', LoginForm::class);
        yield TelegramLoginButtonComponent::make();
	}
}
