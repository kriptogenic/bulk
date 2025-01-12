<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('TaskResource.viewAny');
    }

    public function view(User $user, Task $task): bool
    {
        return $this->checkOwnership($user, $task, __FUNCTION__);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('TaskResource.create');
    }

    public function update(User $user, Task $task): bool
    {
        return $this->checkOwnership($user, $task, __FUNCTION__);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->checkOwnership($user, $task, __FUNCTION__);
    }

    public function restore(User $user, Task $task): bool
    {
        return $this->checkOwnership($user, $task, __FUNCTION__);
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $this->checkOwnership($user, $task, __FUNCTION__);
    }

    private function checkOwnership(User $user, Task $task, string $method): bool
    {
        if (!$user->hasPermissionTo('BotResource.' . $method)) {
            return false;
        }

        return $task->bot->users()->where('id', $user->id)->exists();
    }
}
