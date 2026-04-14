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
        return $authUser->hasRole('Admin')
            && $this->sameTenant($authUser, $user);
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasRole('Admin');
    }

    public function update(User $authUser, User $user): bool
    {
        return $authUser->hasRole('Admin')
            && $this->sameTenant($authUser, $user);
    }

    public function delete(User $authUser, User $user): bool
    {
        return $authUser->hasRole('Admin')
            && $authUser->id !== $user->id
            && $this->sameTenant($authUser, $user);
    }

    public function resetPassword(User $authUser, User $user): bool
    {
        return $authUser->hasRole('Admin')
            && $authUser->id !== $user->id
            && $this->sameTenant($authUser, $user);
    }

    public function toggleStatus(User $authUser, User $user): bool
    {
        return $authUser->hasRole('Admin')
            && $authUser->id !== $user->id
            && $this->sameTenant($authUser, $user);
    }

    private function sameTenant(User $authUser, User $user): bool
    {
        return $authUser->id_masjid !== null
            && $user->id_masjid !== null
            && (int) $authUser->id_masjid === (int) $user->id_masjid;
    }
}
