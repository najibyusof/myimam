<?php

namespace App\Services;

use App\Models\KategoriBelanja;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class KategoriBelanjaManagementService
{
    public function create(User $actor, array $data): KategoriBelanja
    {
        return DB::transaction(function () use ($actor, $data): KategoriBelanja {
            return KategoriBelanja::query()->create($this->sanitizePayload($actor, $data));
        });
    }

    public function update(KategoriBelanja $kategoriBelanja, User $actor, array $data): KategoriBelanja
    {
        return DB::transaction(function () use ($kategoriBelanja, $actor, $data): KategoriBelanja {
            $this->ensureScoped($kategoriBelanja, $actor);
            $kategoriBelanja->update($this->sanitizePayload($actor, $data));

            return $kategoriBelanja->refresh();
        });
    }

    public function toggleStatus(KategoriBelanja $kategoriBelanja, User $actor): KategoriBelanja
    {
        return DB::transaction(function () use ($kategoriBelanja, $actor): KategoriBelanja {
            $this->ensureScoped($kategoriBelanja, $actor);
            $kategoriBelanja->update(['aktif' => !$kategoriBelanja->aktif]);

            return $kategoriBelanja->refresh();
        });
    }

    public function delete(KategoriBelanja $kategoriBelanja, User $actor): void
    {
        DB::transaction(function () use ($kategoriBelanja, $actor): void {
            $this->ensureScoped($kategoriBelanja, $actor);
            $kategoriBelanja->delete();
        });
    }

    private function sanitizePayload(User $actor, array $data): array
    {
        return [
            'id_masjid' => $actor->hasRole('Admin') ? ($data['id_masjid'] ?? null) : $actor->id_masjid,
            'kod' => $data['kod'],
            'nama_kategori' => $data['nama_kategori'],
            'aktif' => (bool) ($data['aktif'] ?? true),
        ];
    }

    private function ensureScoped(KategoriBelanja $kategoriBelanja, User $actor): void
    {
        if ($actor->hasRole('Admin')) {
            return;
        }

        abort_unless(
            $actor->id_masjid !== null && $actor->id_masjid === $kategoriBelanja->id_masjid,
            403,
            'Unauthorized'
        );
    }
}
