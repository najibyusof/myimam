<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
    public function create(User $actor, array $data): User
    {
        return DB::transaction(function () use ($actor, $data): User {
            $user = User::query()->create([
                'id_masjid' => $actor->peranan === 'superadmin' ? ($data['id_masjid'] ?? null) : $actor->id_masjid,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'peranan' => $this->mapPerananFromRole($data['role']),
                'aktif' => (bool) ($data['aktif'] ?? true),
            ]);

            $user->syncRoles([$data['role']]);

            return $user;
        });
    }

    public function update(User $actor, User $user, array $data): User
    {
        return DB::transaction(function () use ($actor, $user, $data): User {
            $payload = [
                'id_masjid' => $actor->peranan === 'superadmin' ? ($data['id_masjid'] ?? null) : $actor->id_masjid,
                'name' => $data['name'],
                'email' => $data['email'],
                'peranan' => $this->mapPerananFromRole($data['role']),
                'aktif' => (bool) ($data['aktif'] ?? false),
            ];

            if (!empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->update($payload);
            $user->syncRoles([$data['role']]);

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
}
