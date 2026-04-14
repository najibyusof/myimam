<?php

namespace App\Services;

use App\Models\TabungKhas;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TabungKhasManagementService
{
    public function create(User $actor, array $data): TabungKhas
    {
        return DB::transaction(function () use ($actor, $data): TabungKhas {
            return TabungKhas::query()->create($this->sanitizePayload($actor, $data));
        });
    }

    public function update(TabungKhas $tabungKhas, User $actor, array $data): TabungKhas
    {
        return DB::transaction(function () use ($tabungKhas, $actor, $data): TabungKhas {
            $this->ensureScoped($tabungKhas, $actor);
            $tabungKhas->update($this->sanitizePayload($actor, $data));

            return $tabungKhas->refresh();
        });
    }

    public function toggleStatus(TabungKhas $tabungKhas, User $actor): TabungKhas
    {
        return DB::transaction(function () use ($tabungKhas, $actor): TabungKhas {
            $this->ensureScoped($tabungKhas, $actor);
            $tabungKhas->update(['aktif' => !$tabungKhas->aktif]);

            return $tabungKhas->refresh();
        });
    }

    public function delete(TabungKhas $tabungKhas, User $actor): void
    {
        DB::transaction(function () use ($tabungKhas, $actor): void {
            $this->ensureScoped($tabungKhas, $actor);
            $linkedTransactions = $tabungKhas->hasil()->count() + $tabungKhas->belanja()->count();

            if ($linkedTransactions > 0) {
                throw ValidationException::withMessages([
                    'tabung_khas' => 'Tabung khas ini telah digunakan pada transaksi hasil atau belanja dan tidak boleh dipadamkan.',
                ]);
            }

            $tabungKhas->delete();
        });
    }

    private function sanitizePayload(User $actor, array $data): array
    {
        return [
            'id_masjid' => $actor->peranan === 'superadmin' ? ($data['id_masjid'] ?? null) : $actor->id_masjid,
            'nama_tabung' => $data['nama_tabung'],
            'aktif' => (bool) ($data['aktif'] ?? true),
        ];
    }

    private function ensureScoped(TabungKhas $tabungKhas, User $actor): void
    {
        if ($actor->peranan === 'superadmin') {
            return;
        }

        abort_unless(
            $actor->id_masjid !== null && $actor->id_masjid === $tabungKhas->id_masjid,
            403,
            'Unauthorized'
        );
    }
}
