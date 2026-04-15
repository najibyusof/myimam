<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * RolePolicy — hierarchical access control for Spatie role management.
 *
 * Level rules:
 *   SuperAdmin   → full access to all roles (except deleting level-1 system roles)
 *   Masjid Admin → CRUD only on their own masjid's level-3 roles
 *   Others       → no access
 *
 * Auto-discovered by Laravel because App\Models\Role + App\Policies\RolePolicy follow
 * the Model → Policy naming convention.
 */
class RolePolicy
{
    use HandlesAuthorization;

    // ─── Internal helpers ─────────────────────────────────────────────────────

    private function isSuperAdmin(User $user): bool
    {
        return Role::actorIsSuperAdmin($user);
    }

    private function isMasjidAdmin(User $user): bool
    {
        return $user->hasRole('Admin') && !empty($user->id_masjid);
    }

    // ─── Policy methods ───────────────────────────────────────────────────────

    /**
     * List / browse roles.
     * SuperAdmin sees all. Masjid Admin sees their own masjid's roles.
     */
    public function viewAny(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || ($this->isMasjidAdmin($user) && $user->can('roles.assign'));
    }

    /**
     * View a single role.
     */
    public function view(User $user, Role $role): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ($this->isMasjidAdmin($user)) {
            return (int) $role->masjid_id === (int) $user->id_masjid;
        }

        return false;
    }

    /**
     * Create a new role.
     * SuperAdmin: any role.
     * Masjid Admin: only level-3 roles within their own masjid.
     */
    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || ($this->isMasjidAdmin($user) && $user->can('roles.assign'));
    }

    /**
     * Edit / update a role.
     * SuperAdmin: any role except system-level (level 1).
     * Masjid Admin: only their own masjid's level-3 non-global roles.
     */
    public function update(User $user, Role $role): bool
    {
        if ($this->isSuperAdmin($user)) {
            // Prevent accidental modification of system-reserved roles
            return !$role->isSystemLevel();
        }

        if ($this->isMasjidAdmin($user)) {
            return !$role->isGlobal()
                && (int) $role->masjid_id === (int) $user->id_masjid
                && (int) $role->level >= 3;
        }

        return false;
    }

    /**
     * Delete a role.
     * SuperAdmin: any role except level-1 system roles.
     * Masjid Admin: only their own masjid's level-3 non-global roles.
     *
     * Additional safety: cannot delete a role that still has users.
     */
    public function delete(User $user, Role $role): bool
    {
        if ($this->isSuperAdmin($user)) {
            return !$role->isSystemLevel();
        }

        if ($this->isMasjidAdmin($user)) {
            return !$role->isGlobal()
                && (int) $role->masjid_id === (int) $user->id_masjid
                && (int) $role->level >= 3;
        }

        return false;
    }

    /**
     * Assign / sync permissions on a role.
     * Follows the same rules as `update`.
     *
     * Additionally, SuperAdmin cannot grant permissions they don't hold
     * themselves — but since SuperAdmin bypasses Gates, this is not enforced here.
     */
    public function syncPermissions(User $user, Role $role): bool
    {
        return $this->update($user, $role);
    }

    /**
     * Assign a role to a user.
     *
     * SuperAdmin: any role to any user.
     * Masjid Admin:
     *   – Target user must be in the same masjid.
     *   – Can assign global level-3 roles (e.g. Bendahari, AJK, Auditor) OR
     *     tenant-scoped level-3 roles belonging to their own masjid.
     *   – Cannot assign level-1 / level-2 roles (Admin, Superadmin).
     *   – Cannot assign roles belonging to a different masjid.
     */
    public function assign(User $user, Role $role, ?User $targetUser = null): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ($this->isMasjidAdmin($user) && !empty($user->id_masjid)) {
            // Target user must belong to the same tenant
            if ($targetUser && (int) $targetUser->id_masjid !== (int) $user->id_masjid) {
                return false;
            }

            // Cannot assign level-1 (system) or level-2 (admin) roles
            if ((int) $role->level < 3) {
                return false;
            }

            // Can assign this role only when it is:
            //   (a) a global role (masjid_id = null) — standard system-wide roles, OR
            //   (b) a tenant-scoped role belonging to this masjid
            if ($role->isGlobal()) {
                return true;
            }

            return (int) $role->masjid_id === (int) $user->id_masjid;
        }

        return false;
    }
}
