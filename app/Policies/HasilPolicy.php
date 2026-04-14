<?php

namespace App\Policies;

use App\Models\Hasil;
use App\Models\User;

class HasilPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer', 'AJK']);
    }

    public function view(User $authUser, Hasil $hasil): bool
    {
        return $this->inScope($authUser, $hasil) && $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer', 'AJK']);
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer']);
    }

    public function update(User $authUser, Hasil $hasil): bool
    {
        return $this->inScope($authUser, $hasil) && $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer']);
    }

    public function delete(User $authUser, Hasil $hasil): bool
    {
        return $this->inScope($authUser, $hasil) && $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer']);
    }

    private function inScope(User $authUser, Hasil $hasil): bool
    {
        return $authUser->hasRole('Admin') || (
            $authUser->id_masjid !== null
            && $authUser->id_masjid === $hasil->id_masjid
        );
    }
}
