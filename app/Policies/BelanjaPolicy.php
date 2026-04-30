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
        return !$belanja->is_baucar_locked
            && $this->inScope($authUser, $belanja)
            && $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer']);
    }

    public function delete(User $authUser, Belanja $belanja): bool
    {
        return !$belanja->is_baucar_locked
            && $this->inScope($authUser, $belanja)
            && $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer']);
    }

    public function approveBendahari(User $authUser, Belanja $belanja): bool
    {
        if ($belanja->is_baucar_locked) {
            return false;
        }

        if ($authUser->peranan === 'superadmin') {
            return $belanja->status === 'DRAF' && (int) $belanja->approval_step === 0;
        }

        return $this->inScope($authUser, $belanja)
            && $authUser->hasRole('Bendahari')
            && $belanja->status === 'DRAF'
            && (int) $belanja->approval_step === 0;
    }

    public function approvePengerusi(User $authUser, Belanja $belanja): bool
    {
        if ($belanja->is_baucar_locked) {
            return false;
        }

        if ($authUser->peranan === 'superadmin') {
            return $belanja->status === 'DRAF' && (int) $belanja->approval_step === 1;
        }

        return $this->inScope($authUser, $belanja)
            && $authUser->hasRole('Pengerusi')
            && $belanja->status === 'DRAF'
            && (int) $belanja->approval_step === 1;
    }

    public function approve(User $authUser, Belanja $belanja): bool
    {
        return $this->approveBendahari($authUser, $belanja)
            || $this->approvePengerusi($authUser, $belanja);
    }

    public function reject(User $authUser, Belanja $belanja): bool
    {
        if ($belanja->is_baucar_locked) {
            return false;
        }

        if ($authUser->peranan === 'superadmin') {
            return in_array((int) $belanja->approval_step, [0, 1], true);
        }

        return $this->inScope($authUser, $belanja)
            && $authUser->hasAnyRole(['Bendahari', 'Pengerusi'])
            && in_array((int) $belanja->approval_step, [0, 1], true);
    }

    private function inScope(User $authUser, Belanja $belanja): bool
    {
        return $authUser->id_masjid !== null
            && $authUser->id_masjid === $belanja->id_masjid;
    }
}
