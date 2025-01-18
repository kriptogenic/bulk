<?php

declare(strict_types=1);

use App\Http\Controllers\TaskChatController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::apiResource('tasks', TaskController::class)->only(['store', 'show', 'destroy']);
Route::get('tasks/{task}/chats', [TaskChatController::class, 'index']);
