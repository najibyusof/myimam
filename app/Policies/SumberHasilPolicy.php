<?php

namespace App\Policies;

use App\Models\SumberHasil;
use App\Models\User;

class SumberHasilPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Bendahari', 'AJK']);
    }

    public function view(User $authUser, SumberHasil $sumberHasil): bool
    {
        return $this->inScope($authUser, $sumberHasil) && $authUser->hasAnyRole(['Admin', 'Bendahari', 'AJK']);
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    public function update(User $authUser, SumberHasil $sumberHasil): bool
    {
        return $this->inScope($authUser, $sumberHasil) && $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    public function delete(User $authUser, SumberHasil $sumberHasil): bool
    {
        if ($sumberHasil->is_baseline) {
            return false;
        }

        return $this->inScope($authUser, $sumberHasil) && $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    public function toggleStatus(User $authUser, SumberHasil $sumberHasil): bool
    {
        if ($sumberHasil->is_baseline) {
            return false;
        }

        return $this->inScope($authUser, $sumberHasil) && $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    private function inScope(User $authUser, SumberHasil $sumberHasil): bool
    {
        return $authUser->id_masjid !== null
            && $authUser->id_masjid === $sumberHasil->id_masjid;
    }
}
