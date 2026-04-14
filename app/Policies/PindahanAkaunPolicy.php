<?php

namespace App\Policies;

use App\Models\PindahanAkaun;
use App\Models\User;

class PindahanAkaunPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer', 'AJK']);
    }

    public function view(User $authUser, PindahanAkaun $pindahanAkaun): bool
    {
        return $this->inScope($authUser, $pindahanAkaun)
            && $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer', 'AJK']);
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer']);
    }

    public function update(User $authUser, PindahanAkaun $pindahanAkaun): bool
    {
        return $this->inScope($authUser, $pindahanAkaun)
            && $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer']);
    }

    public function delete(User $authUser, PindahanAkaun $pindahanAkaun): bool
    {
        return $this->inScope($authUser, $pindahanAkaun)
            && $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer']);
    }

    private function inScope(User $authUser, PindahanAkaun $pindahanAkaun): bool
    {
        return $authUser->id_masjid !== null
            && $authUser->id_masjid === $pindahanAkaun->id_masjid;
    }
}
