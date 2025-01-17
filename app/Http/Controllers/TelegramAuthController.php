<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MoonShine\Laravel\MoonShineAuth;
use SocialiteProviders\Telegram\Provider;

class TelegramAuthController extends Controller
{
    public function __construct(private Provider $provider) {}

    public function __invoke(Request $request)
    {
        $telegramUser = $this->provider->user();

        Log::info('telegram callbacked', [
            'user' => $telegramUser,
            'request' => $request->all(),
        ]);

        $user = User::firstOrCreate([
            'telegram_id' => $telegramUser->getId(),
        ], [
            'name' => $telegramUser->getNickname(),
            'email' => $telegramUser->getId() . '@t.me',
            'password' => Hash::make(Str::random()),
        ]);

        if ($user->wasRecentlyCreated) {
            $user->assignRole('user');
        }

        MoonShineAuth::getGuard()->login($user);

        return redirect('/admin');
    }
}
