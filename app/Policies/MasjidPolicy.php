<?php

namespace App\Policies;

use App\Models\Masjid;
use App\Models\User;

class MasjidPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->can('masjid.view');
    }

    public function view(User $authUser, Masjid $masjid): bool
    {
        return $authUser->can('masjid.view');
    }

    public function create(User $authUser): bool
    {
        return $authUser->can('masjid.create');
    }

    public function update(User $authUser, Masjid $masjid): bool
    {
        return $authUser->can('masjid.update');
    }

    public function delete(User $authUser, Masjid $masjid): bool
    {
        return $authUser->can('masjid.delete');
    }
}
