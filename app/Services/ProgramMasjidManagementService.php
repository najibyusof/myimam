<?php

namespace App\Services;

use App\Models\ProgramMasjid;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProgramMasjidManagementService
{
    public function create(User $actor, array $data): ProgramMasjid
    {
        return DB::transaction(function () use ($actor, $data): ProgramMasjid {
            return ProgramMasjid::query()->create($this->sanitizePayload($actor, $data));
        });
    }

    public function update(ProgramMasjid $programMasjid, User $actor, array $data): ProgramMasjid
    {
        return DB::transaction(function () use ($programMasjid, $actor, $data): ProgramMasjid {
            $this->ensureScoped($programMasjid, $actor);
            $programMasjid->update($this->sanitizePayload($actor, $data));

            return $programMasjid->refresh();
        });
    }

    public function toggleStatus(ProgramMasjid $programMasjid, User $actor): ProgramMasjid
    {
        return DB::transaction(function () use ($programMasjid, $actor): ProgramMasjid {
            $this->ensureScoped($programMasjid, $actor);
            $programMasjid->update(['aktif' => !$programMasjid->aktif]);

            return $programMasjid->refresh();
        });
    }

    public function delete(ProgramMasjid $programMasjid, User $actor): void
    {
        DB::transaction(function () use ($programMasjid, $actor): void {
            $this->ensureScoped($programMasjid, $actor);
            $linkedTransactions = $programMasjid->hasil()->count() + $programMasjid->belanja()->count();

            if ($linkedTransactions > 0) {
                throw ValidationException::withMessages([
                    'program_masjid' => 'Program masjid ini telah digunakan pada transaksi hasil atau belanja dan tidak boleh dipadamkan.',
                ]);
            }

            $programMasjid->delete();
        });
    }

    private function sanitizePayload(User $actor, array $data): array
    {
        return [
            'id_masjid' => $actor->peranan === 'superadmin' ? ($data['id_masjid'] ?? null) : $actor->id_masjid,
            'nama_program' => $data['nama_program'],
            'aktif' => (bool) ($data['aktif'] ?? true),
        ];
    }

    private function ensureScoped(ProgramMasjid $programMasjid, User $actor): void
    {
        if ($actor->peranan === 'superadmin') {
            return;
        }

        abort_unless(
            $actor->id_masjid !== null && $actor->id_masjid === $programMasjid->id_masjid,
            403,
            'Unauthorized'
        );
    }
}
