<?php

namespace App\Policies;

use App\Models\LogAktiviti;
use App\Models\User;

class LogAktivitiPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Manager', 'Auditor']);
    }

    public function view(User $authUser, LogAktiviti $logAktiviti): bool
    {
        return $this->inScope($authUser, $logAktiviti)
            && $authUser->hasAnyRole(['Admin', 'Manager', 'Auditor']);
    }

    private function inScope(User $authUser, LogAktiviti $logAktiviti): bool
    {
        return $authUser->id_masjid !== null
            && $authUser->id_masjid === $logAktiviti->id_masjid;
    }
}
