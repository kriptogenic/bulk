<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SocialiteProviders\Telegram\Provider;

class TelegramAuthController extends Controller
{
    public function __construct(private Provider $provider) {}

    public function __invoke(Request $request)
    {
        $user = $this->provider->user();
        Log::info('callbacked', [
            'user' => $user,
            'request' => $request->method(),
            'response' => $request->all(),
        ]);
    }
}
