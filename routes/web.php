<?php

use App\Http\Controllers\TelegramAuthController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', function () {
    return view('welcome');
});

Route::get('socialite/telegram/redirect', [TelegramAuthController::class, 'index'])->name('telegram.redirect');
Route::any('socialite/telegram/callback', [TelegramAuthController::class, 'store'])->name('telegram.callback');

Route::get('test', function () {
    return Socialite::driver('telegram')->getButton();
});
