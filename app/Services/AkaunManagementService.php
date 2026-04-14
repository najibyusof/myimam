<?php

namespace App\Services;

use App\Models\Akaun;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AkaunManagementService
{
    public function create(User $actor, array $data): Akaun
    {
        return DB::transaction(function () use ($actor, $data): Akaun {
            $payload = $this->sanitizePayload($actor, $data);

            return Akaun::query()->create($payload);
        });
    }

    public function update(Akaun $akaun, User $actor, array $data): Akaun
    {
        return DB::transaction(function () use ($akaun, $actor, $data): Akaun {
            $akaun->update($this->sanitizePayload($actor, $data));

            return $akaun->refresh();
        });
    }

    public function delete(Akaun $akaun, User $actor): void
    {
        DB::transaction(function () use ($akaun, $actor): void {
    if ($actor->peranan !== 'superadmin' && $akaun->id_masjid !== $actor->id_masjid) {
            }

            $akaun->delete();
        });
    }

    private function sanitizePayload(User $actor, array $data): array
    {
        $jenis = (string) ($data['jenis'] ?? 'tunai');

        return [
            'id_masjid' => $actor->peranan === 'superadmin' ? ($data['id_masjid'] ?? null) : $actor->id_masjid,
            'nama_akaun' => $data['nama_akaun'],
            'jenis' => $jenis,
            'no_akaun' => $jenis === 'bank' ? ($data['no_akaun'] ?? null) : null,
            'nama_bank' => $jenis === 'bank' ? ($data['nama_bank'] ?? null) : null,
            'status_aktif' => (bool) ($data['status_aktif'] ?? true),
        ];
    }
}
