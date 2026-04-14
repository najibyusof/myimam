<?php

namespace App\Policies;

use App\Models\ProgramMasjid;
use App\Models\User;

class ProgramMasjidPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Bendahari', 'AJK']);
    }

    public function view(User $authUser, ProgramMasjid $programMasjid): bool
    {
        return $this->inScope($authUser, $programMasjid) && $authUser->hasAnyRole(['Admin', 'Bendahari', 'AJK']);
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    public function update(User $authUser, ProgramMasjid $programMasjid): bool
    {
        return $this->inScope($authUser, $programMasjid) && $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    public function delete(User $authUser, ProgramMasjid $programMasjid): bool
    {
        return $this->inScope($authUser, $programMasjid) && $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    public function toggleStatus(User $authUser, ProgramMasjid $programMasjid): bool
    {
        return $this->inScope($authUser, $programMasjid) && $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    private function inScope(User $authUser, ProgramMasjid $programMasjid): bool
    {
        return $authUser->id_masjid !== null
            && $authUser->id_masjid === $programMasjid->id_masjid;
    }
}
