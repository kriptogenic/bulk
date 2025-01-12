<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class TelegramAuthController extends Controller
{
    public function index()
    {
        Log::info('Telegram auth start');
        return Socialite::driver('telegram')->redirect();
    }

    public function store(Request $request)
    {
        $user = Socialite::driver('telegram')->user();
        Log::info('callbacked', [
            'user' => $user,
            'request' => $request->method(),
            'response' => $request->all(),
        ]);
    }
}
