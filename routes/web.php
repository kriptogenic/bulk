<?php

use App\Http\Controllers\TelegramAuthController;
use Illuminate\Support\Facades\Route;

Route::get('socialite/telegram/callback', TelegramAuthController::class)->name('telegram.callback');

Route::get('/', fn() => view('home'))->name('home');
Route::get('docs', fn() => view('docs.rest-api'))->name('docs');
Route::get('docs/self-hosted', fn() => view('docs.coming-soon'))->name('docs.self-hosted');
Route::get('docs/chats-less', fn() => view('docs.coming-soon'))->name('docs.chats-less');
