<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
    public function create(User $actor, array $data): User
    {
        return DB::transaction(function () use ($actor, $data): User {
            $role = $this->resolveAssignableRole($actor, (string) $data['role']);

            $targetMasjidId = $actor->peranan === 'superadmin'
                ? ($data['id_masjid'] ?? null)
                : $actor->id_masjid;

            $user = User::query()->create([
                'id_masjid' => $targetMasjidId,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'peranan' => $this->mapPerananFromRole($data['role']),
                'aktif' => (bool) ($data['aktif'] ?? true),
            ]);

            Gate::forUser($actor)->authorize('assign-role', [$role, $user]);
            $user->syncRoles([$role->name]);

            return $user;
        });
    }

    public function update(User $actor, User $user, array $data): User
    {
        return DB::transaction(function () use ($actor, $user, $data): User {
            $role = $this->resolveAssignableRole($actor, (string) $data['role']);

            $targetMasjidId = $actor->peranan === 'superadmin'
                ? ($data['id_masjid'] ?? null)
                : $actor->id_masjid;

            // Apply target tenant first, then validate role assignment against final tenant
            $user->forceFill(['id_masjid' => $targetMasjidId]);
            Gate::forUser($actor)->authorize('assign-role', [$role, $user]);

            $payload = [
                'id_masjid' => $targetMasjidId,
                'name' => $data['name'],
                'email' => $data['email'],
                'peranan' => $this->mapPerananFromRole($data['role']),
                'aktif' => (bool) ($data['aktif'] ?? false),
            ];

            if (!empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->update($payload);
            $user->syncRoles([$role->name]);

            return $user->refresh();
        });
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $user->syncRoles([]);
            $user->delete();
        });
    }

    private function mapPerananFromRole(string $role): string
    {
        return match ($role) {
            'Admin' => 'admin',
            'Bendahari' => 'admin',
            'Manager' => 'admin',
            'AJK' => 'staff',
            default => 'staff',
        };
    }

    private function resolveAssignableRole(User $actor, string $roleName): Role
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->where('name', $roleName)
            ->visibleTo($actor)
            ->firstOrFail();
    }
}
