<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Bot;
use App\Models\Task;
use App\Models\User;
use App\Policies\BotPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(Gate $gate): void
    {
        $gate->policy(Bot::class, BotPolicy::class);
        $gate->policy(Task::class, TaskPolicy::class);
        $gate->before(function (User $user, $ability): ?true {
            return $user->isSuperAdmin() ? true : null;
        });
    }
}
