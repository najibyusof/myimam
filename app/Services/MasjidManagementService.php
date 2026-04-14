<?php

namespace App\Services;

use App\Models\Masjid;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasjidManagementService
{
    /**
     * Create a new masjid with the provided data.
     */
    public function create(array $data, ?User $creator = null): Masjid
    {
        return DB::transaction(function () use ($data, $creator) {
            $masjid = Masjid::create([
                'nama' => $data['nama'],
                'code' => $this->resolveCode($data),
                'alamat' => $data['alamat'] ?? null,
                'daerah' => $data['daerah'] ?? null,
                'negeri' => $data['negeri'] ?? null,
                'no_pendaftaran' => $data['no_pendaftaran'] ?? null,
                'tarikh_daftar' => $data['tarikh_daftar'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'subscription_status' => $data['subscription_status'] ?? 'none',
                'subscription_expiry' => $data['subscription_expiry'] ?? null,
                'created_by' => $creator?->id,
            ]);

            $this->handleAdminAssignment($masjid, $data);

            return $masjid;
        });
    }

    /**
     * Update an existing masjid with the provided data.
     */
    public function update(Masjid $masjid, array $data): Masjid
    {
        return DB::transaction(function () use ($masjid, $data) {
            $masjid->update([
                'nama' => $data['nama'],
                'code' => $data['code'] ?? $masjid->code,
                'alamat' => $data['alamat'] ?? null,
                'daerah' => $data['daerah'] ?? null,
                'negeri' => $data['negeri'] ?? null,
                'no_pendaftaran' => $data['no_pendaftaran'] ?? null,
                'tarikh_daftar' => $data['tarikh_daftar'] ?? null,
                'status' => $data['status'] ?? $masjid->status,
                'subscription_status' => $data['subscription_status'] ?? $masjid->subscription_status,
                'subscription_expiry' => $data['subscription_expiry'] ?? $masjid->subscription_expiry,
            ]);

            $this->handleAdminAssignment($masjid, $data);

            return $masjid->refresh();
        });
    }

    /**
     * Delete a masjid.
     */
    public function delete(Masjid $masjid): bool
    {
        return DB::transaction(function () use ($masjid) {
            return $masjid->delete();
        });
    }

    public function suspend(Masjid $masjid): Masjid
    {
        $masjid->update(['status' => 'suspended']);
        return $masjid->refresh();
    }

    public function activate(Masjid $masjid): Masjid
    {
        $masjid->update(['status' => 'active']);
        return $masjid->refresh();
    }

    private function handleAdminAssignment(Masjid $masjid, array $data): void
    {
        if (!empty($data['admin_user_id'])) {
            $admin = User::query()->findOrFail((int) $data['admin_user_id']);
            $admin->update([
                'id_masjid' => $masjid->id,
                'peranan' => 'admin',
                'aktif' => true,
            ]);
            $admin->assignRole('Admin');
            return;
        }

        if (!empty($data['admin_email']) && !empty($data['admin_name']) && !empty($data['admin_password'])) {
            $admin = User::create([
                'id_masjid' => $masjid->id,
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'peranan' => 'admin',
                'aktif' => true,
            ]);
            $admin->assignRole('Admin');
        }
    }

    private function resolveCode(array $data): string
    {
        if (!empty($data['code'])) {
            return $data['code'];
        }

        $base = Str::slug($data['nama']) ?: 'masjid';
        $code = $base;
        $i = 1;

        while (Masjid::query()->where('code', $code)->exists()) {
            $i++;
            $code = $base . '-' . $i;
        }

        return $code;
    }
}
