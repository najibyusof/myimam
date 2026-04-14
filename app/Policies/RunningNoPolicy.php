<?php

namespace App\Policies;

use App\Models\RunningNo;
use App\Models\User;

class RunningNoPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer', 'AJK']);
    }

    public function view(User $authUser, RunningNo $runningNo): bool
    {
        return $this->inScope($authUser, $runningNo)
            && $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer', 'AJK']);
    }

    public function generate(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Bendahari', 'FinanceOfficer']);
    }

    public function update(User $authUser, RunningNo $runningNo): bool
    {
        return $this->inScope($authUser, $runningNo)
            && $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    private function inScope(User $authUser, RunningNo $runningNo): bool
    {
        return $authUser->hasRole('Admin') || (
            $authUser->id_masjid !== null
            && $authUser->id_masjid === $runningNo->id_masjid
        );
    }
}
