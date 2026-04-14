<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ResolvesTenantAccess
{
    protected function isSuperAdmin(User $authUser): bool
    {
        return $authUser->peranan === 'superadmin';
    }

    protected function sharesTenant(User $authUser, ?int $resourceMasjidId): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->id_masjid !== null
            && $resourceMasjidId !== null
            && (int) $authUser->id_masjid === (int) $resourceMasjidId;
    }

    protected function canManageUserInTenant(User $authUser, User $subject): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->id_masjid !== null
            && $subject->id_masjid !== null
            && (int) $authUser->id_masjid === (int) $subject->id_masjid;
    }
}