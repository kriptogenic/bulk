<?php

use App\Http\Controllers\TelegramAuthController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', static fn(): RedirectResponse => redirect('/admin'));

Route::get('socialite/telegram/callback', TelegramAuthController::class)->name('telegram.callback');
