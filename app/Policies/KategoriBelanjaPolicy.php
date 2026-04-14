<?php

namespace App\Policies;

use App\Models\KategoriBelanja;
use App\Models\User;

class KategoriBelanjaPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Bendahari', 'AJK']);
    }

    public function view(User $authUser, KategoriBelanja $kategoriBelanja): bool
    {
        return $this->inScope($authUser, $kategoriBelanja) && $authUser->hasAnyRole(['Admin', 'Bendahari', 'AJK']);
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    public function update(User $authUser, KategoriBelanja $kategoriBelanja): bool
    {
        return $this->inScope($authUser, $kategoriBelanja) && $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    public function delete(User $authUser, KategoriBelanja $kategoriBelanja): bool
    {
        return $this->inScope($authUser, $kategoriBelanja) && $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    public function toggleStatus(User $authUser, KategoriBelanja $kategoriBelanja): bool
    {
        return $this->inScope($authUser, $kategoriBelanja) && $authUser->hasAnyRole(['Admin', 'Bendahari']);
    }

    private function inScope(User $authUser, KategoriBelanja $kategoriBelanja): bool
    {
        return $authUser->id_masjid !== null
            && $authUser->id_masjid === $kategoriBelanja->id_masjid;
    }
}
