<?php

namespace App\Services;

use App\Models\Akaun;
use App\Models\Belanja;
use App\Models\KategoriBelanja;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BelanjaManagementService
{
    public function create(User $actor, array $data): Belanja
    {
        return DB::transaction(function () use ($actor, $data): Belanja {
            $payload = $this->sanitizePayload($actor, $data);
            $this->validateRelations($payload);

            return Belanja::query()->create($payload);
        });
    }

    public function update(Belanja $belanja, User $actor, array $data): Belanja
    {
        return DB::transaction(function () use ($belanja, $actor, $data): Belanja {
            $this->ensureScoped($belanja, $actor);
            $this->ensureNotLocked($belanja);
            $payload = $this->sanitizePayload($actor, $data);
            $this->validateRelations($payload);
            $payload['created_by'] = $belanja->created_by;
            $payload['is_deleted'] = $belanja->is_deleted;
            $payload['deleted_by'] = $belanja->deleted_by;
            $payload['deleted_at'] = $belanja->deleted_at;
            // bukti_fail is resolved by the controller before calling this method

            $belanja->update($payload);

            return $belanja->refresh();
        });
    }

    public function softDelete(Belanja $belanja, User $actor): Belanja
    {
        return DB::transaction(function () use ($belanja, $actor): Belanja {
            $this->ensureScoped($belanja, $actor);
            $this->ensureNotLocked($belanja);
            $belanja->update([
                'is_deleted' => true,
                'deleted_by' => $actor->id,
                'deleted_at' => now(),
            ]);

            return $belanja->refresh();
        });
    }

    private function sanitizePayload(User $actor, array $data): array
    {
        $masjidId = $actor->peranan === 'superadmin' ? ($data['id_masjid'] ?? null) : $actor->id_masjid;
        return [
            'id_masjid' => $masjidId,
            'tarikh' => $data['tarikh'],
            'id_akaun' => $data['id_akaun'],
            'id_kategori_belanja' => $data['id_kategori_belanja'],
            'amaun' => (float) $data['amaun'],
            'id_tabung_khas' => null,
            'id_program' => null,
            'penerima' => $data['penerima'] ?? null,
            'catatan' => $data['catatan'] ?? null,
            'bukti_fail' => $data['bukti_fail'] ?? null,
            'created_by' => $actor->id,
            'status' => 'DRAF',
            'is_deleted' => false,
            'deleted_by' => null,
            'deleted_at' => null,
            'dilulus_oleh' => null,
            'tarikh_lulus' => null,
            'approval_step' => 0,
            'bendahari_lulus_oleh' => null,
            'bendahari_lulus_pada' => null,
            'bendahari_signature' => null,
            'pengerusi_lulus_oleh' => null,
            'pengerusi_lulus_pada' => null,
            'pengerusi_signature' => null,
            'is_baucar_locked' => false,
            'locked_at' => null,
            'locked_by' => null,
        ];
    }

    private function ensureNotLocked(Belanja $belanja): void
    {
        if ($belanja->is_baucar_locked) {
            throw ValidationException::withMessages([
                'status' => 'Baucar telah diluluskan muktamad dan dikunci. Rekod tidak boleh diubah.',
            ]);
        }
    }

    private function validateRelations(array $payload): void
    {
        $masjidId = $payload['id_masjid'] ?? null;

        if (!$masjidId) {
            throw ValidationException::withMessages([
                'id_masjid' => 'Masjid diperlukan untuk rekod belanja.',
            ]);
        }

        $akaun = Akaun::query()->find($payload['id_akaun']);
        $kategori = KategoriBelanja::query()->find($payload['id_kategori_belanja']);

        if (!$akaun || $akaun->id_masjid !== $masjidId) {
            throw ValidationException::withMessages([
                'id_akaun' => 'Akaun yang dipilih tidak sepadan dengan masjid transaksi.',
            ]);
        }

        if (!$kategori || $kategori->id_masjid !== $masjidId) {
            throw ValidationException::withMessages([
                'id_kategori_belanja' => 'Kategori belanja yang dipilih tidak sepadan dengan masjid transaksi.',
            ]);
        }
    }

    private function ensureScoped(Belanja $belanja, User $actor): void
    {
        if ($actor->peranan === 'superadmin') {
            return;
        }

        abort_unless(
            $actor->id_masjid !== null && $actor->id_masjid === $belanja->id_masjid,
            403,
            'Unauthorized'
        );
    }
}
