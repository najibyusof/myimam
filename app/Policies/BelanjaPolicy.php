<?php

namespace App\Policies;

use App\Models\Belanja;
use App\Models\User;

class BelanjaPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer', 'AJK']);
    }

    public function view(User $authUser, Belanja $belanja): bool
    {
        return $this->inScope($authUser, $belanja) && $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer', 'AJK']);
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer']);
    }

    public function update(User $authUser, Belanja $belanja): bool
    {
        return $this->inScope($authUser, $belanja) && $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer']);
    }

    public function delete(User $authUser, Belanja $belanja): bool
    {
        return $this->inScope($authUser, $belanja) && $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer']);
    }

    private function inScope(User $authUser, Belanja $belanja): bool
    {
        return $authUser->id_masjid !== null
            && $authUser->id_masjid === $belanja->id_masjid;
    }
}
