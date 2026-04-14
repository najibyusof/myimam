<?php

namespace App\Services;

use App\Models\SumberHasil;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SumberHasilManagementService
{
    public function create(User $actor, array $data): SumberHasil
    {
        return DB::transaction(function () use ($actor, $data): SumberHasil {
            return SumberHasil::query()->create($this->sanitizePayload($actor, $data));
        });
    }

    public function update(SumberHasil $sumberHasil, User $actor, array $data): SumberHasil
    {
        return DB::transaction(function () use ($sumberHasil, $actor, $data): SumberHasil {
            $this->ensureScoped($sumberHasil, $actor);
            $sumberHasil->update($this->sanitizePayload($actor, $data));

            return $sumberHasil->refresh();
        });
    }

    public function toggleStatus(SumberHasil $sumberHasil, User $actor): SumberHasil
    {
        return DB::transaction(function () use ($sumberHasil, $actor): SumberHasil {
            $this->ensureScoped($sumberHasil, $actor);
            $sumberHasil->update(['aktif' => !$sumberHasil->aktif]);

            return $sumberHasil->refresh();
        });
    }

    public function delete(SumberHasil $sumberHasil, User $actor): void
    {
        DB::transaction(function () use ($sumberHasil, $actor): void {
            $this->ensureScoped($sumberHasil, $actor);
            $sumberHasil->delete();
        });
    }

    private function sanitizePayload(User $actor, array $data): array
    {
        return [
            'id_masjid' => $actor->peranan === 'superadmin' ? ($data['id_masjid'] ?? null) : $actor->id_masjid,
            'kod' => $data['kod'],
            'nama_sumber' => $data['nama_sumber'],
            'jenis' => $data['jenis'],
            'aktif' => (bool) ($data['aktif'] ?? true),
        ];
    }

    private function ensureScoped(SumberHasil $sumberHasil, User $actor): void
    {
        if ($actor->peranan === 'superadmin') {
            return;
        }

        abort_unless(
            $actor->id_masjid !== null && $sumberHasil->id_masjid === $actor->id_masjid,
            403,
            'Unauthorized'
        );
    }
}
