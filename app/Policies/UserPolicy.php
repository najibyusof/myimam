<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasRole('Admin');
    }

    public function view(User $authUser, User $user): bool
    {
        return $authUser->hasRole('Admin');
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasRole('Admin');
    }

    public function update(User $authUser, User $user): bool
    {
        return $authUser->hasRole('Admin');
    }

    public function delete(User $authUser, User $user): bool
    {
        return $authUser->hasRole('Admin') && $authUser->id !== $user->id;
    }

    public function resetPassword(User $authUser, User $user): bool
    {
        return $authUser->hasRole('Admin') && $authUser->id !== $user->id;
    }

    public function toggleStatus(User $authUser, User $user): bool
    {
        return $authUser->hasRole('Admin') && $authUser->id !== $user->id;
    }
}
