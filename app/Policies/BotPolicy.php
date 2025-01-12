<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Bot;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BotPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('BotResource.viewAny');
    }

    public function view(User $user, Bot $bot): bool
    {
        return $this->checkOwnership($user, $bot, __FUNCTION__);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('BotResource.create');
    }

    public function update(User $user, Bot $bot): bool
    {
        return $this->checkOwnership($user, $bot, __FUNCTION__);
    }

    public function delete(User $user, Bot $bot): bool
    {
        return $this->checkOwnership($user, $bot, __FUNCTION__);
    }

    public function restore(User $user, Bot $bot): bool
    {
        return $this->checkOwnership($user, $bot, __FUNCTION__);
    }

    public function forceDelete(User $user, Bot $bot): bool
    {
        return $this->checkOwnership($user, $bot, __FUNCTION__);
    }

    private function checkOwnership(User $user, Bot $bot, string $method): bool
    {
        if (!$user->hasPermissionTo('BotResource.' . $method)) {
            return false;
        }

        return $user->bots()->where('id', $bot->id)->exists();
    }
}
