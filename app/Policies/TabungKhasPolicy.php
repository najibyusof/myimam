<?php

namespace App\Policies;

use App\Models\TabungKhas;
use App\Models\User;

class TabungKhasPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Bendahari', 'AJK']);
    }

    public function view(User $authUser, TabungKhas $tabungKhas): bool
    {
        return $this->inScope($authUser, $tabungKhas) && $authUser->hasAnyRole(['Admin', 'Bendahari', 'AJK']);
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    public function update(User $authUser, TabungKhas $tabungKhas): bool
    {
        return $this->inScope($authUser, $tabungKhas) && $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    public function delete(User $authUser, TabungKhas $tabungKhas): bool
    {
        return $this->inScope($authUser, $tabungKhas) && $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    public function toggleStatus(User $authUser, TabungKhas $tabungKhas): bool
    {
        return $this->inScope($authUser, $tabungKhas) && $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    private function inScope(User $authUser, TabungKhas $tabungKhas): bool
    {
        return $authUser->hasRole('Admin') || (
            $authUser->id_masjid !== null
            && $authUser->id_masjid === $tabungKhas->id_masjid
        );
    }
}
