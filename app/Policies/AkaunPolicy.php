<?php

namespace App\Policies;

use App\Models\Akaun;
use App\Models\User;

class AkaunPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Bendahari', 'AJK']);
    }

    public function view(User $authUser, Akaun $akaun): bool
    {
        return $this->inScope($authUser, $akaun) && $authUser->hasAnyRole(['Admin', 'Bendahari', 'AJK']);
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    public function update(User $authUser, Akaun $akaun): bool
    {
        return $this->inScope($authUser, $akaun) && $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    public function delete(User $authUser, Akaun $akaun): bool
    {
        return $this->inScope($authUser, $akaun) && $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    private function inScope(User $authUser, Akaun $akaun): bool
    {
        return $authUser->hasRole('Admin') || (
            $authUser->id_masjid !== null
            && $akaun->id_masjid === $authUser->id_masjid
        );
    }
}
