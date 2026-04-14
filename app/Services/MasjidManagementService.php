<?php

namespace App\Services;

use App\Models\Masjid;
use Illuminate\Support\Facades\DB;

class MasjidManagementService
{
    /**
     * Create a new masjid with the provided data.
     */
    public function create(array $data): Masjid
    {
        return DB::transaction(function () use ($data) {
            return Masjid::create([
                'nama' => $data['nama'],
                'alamat' => $data['alamat'] ?? null,
                'daerah' => $data['daerah'] ?? null,
                'negeri' => $data['negeri'] ?? null,
                'no_pendaftaran' => $data['no_pendaftaran'] ?? null,
                'tarikh_daftar' => $data['tarikh_daftar'] ?? null,
            ]);
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
                'alamat' => $data['alamat'] ?? null,
                'daerah' => $data['daerah'] ?? null,
                'negeri' => $data['negeri'] ?? null,
                'no_pendaftaran' => $data['no_pendaftaran'] ?? null,
                'tarikh_daftar' => $data['tarikh_daftar'] ?? null,
            ]);

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
}
