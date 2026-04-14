<?php

namespace App\Services;

use App\Models\Akaun;
use App\Models\PindahanAkaun;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PindahanAkaunManagementService
{
    public function create(User $actor, array $data): PindahanAkaun
    {
        return DB::transaction(function () use ($actor, $data): PindahanAkaun {
            $payload = $this->sanitizePayload($actor, $data);
            $this->validateTransfer($payload);

            return PindahanAkaun::query()->create($payload);
        });
    }

    public function update(PindahanAkaun $pindahanAkaun, User $actor, array $data): PindahanAkaun
    {
        return DB::transaction(function () use ($pindahanAkaun, $actor, $data): PindahanAkaun {
            $this->ensureScoped($pindahanAkaun, $actor);
            $payload = $this->sanitizePayload($actor, $data);
            $this->validateTransfer($payload);
            $payload['created_by'] = $pindahanAkaun->created_by;

            $pindahanAkaun->update($payload);

            return $pindahanAkaun->refresh();
        });
    }

    public function delete(PindahanAkaun $pindahanAkaun, User $actor): void
    {
        DB::transaction(function () use ($pindahanAkaun, $actor): void {
            $this->ensureScoped($pindahanAkaun, $actor);
            $pindahanAkaun->delete();
        });
    }

    private function sanitizePayload(User $actor, array $data): array
    {
        $masjidId = $actor->peranan === 'superadmin' ? ($data['id_masjid'] ?? null) : $actor->id_masjid;

        return [
            'id_masjid'      => $masjidId,
            'tarikh'         => $data['tarikh'],
            'dari_akaun_id'  => (int) $data['dari_akaun_id'],
            'ke_akaun_id'    => (int) $data['ke_akaun_id'],
            'amaun'          => (float) $data['amaun'],
            'catatan'        => $data['catatan'] ?? null,
            'created_by'     => $actor->id,
        ];
    }

    private function validateTransfer(array $payload): void
    {
        $errors = [];

        if ($payload['dari_akaun_id'] === $payload['ke_akaun_id']) {
            $errors['ke_akaun_id'] = ['Akaun tujuan mesti berbeza daripada akaun sumber.'];
        }

        $dari = Akaun::query()->find($payload['dari_akaun_id']);
        $ke   = Akaun::query()->find($payload['ke_akaun_id']);

        if (! $dari || ! $dari->status_aktif) {
            $errors['dari_akaun_id'] = ['Akaun sumber tidak aktif atau tidak dijumpai.'];
        }

        if (! $ke || ! $ke->status_aktif) {
            $errors['ke_akaun_id'] = array_merge(
                $errors['ke_akaun_id'] ?? [],
                ['Akaun tujuan tidak aktif atau tidak dijumpai.']
            );
        }

        if ($dari && $ke && ! isset($errors['dari_akaun_id']) && ! isset($errors['ke_akaun_id'])) {
            if ($dari->id_masjid !== $ke->id_masjid) {
                $errors['ke_akaun_id'] = ['Akaun tujuan mestilah dari masjid yang sama.'];
            }

            if ($payload['id_masjid'] && $dari->id_masjid !== $payload['id_masjid']) {
                $errors['dari_akaun_id'] = ['Akaun sumber tidak tergolong dalam masjid anda.'];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function ensureScoped(PindahanAkaun $pindahanAkaun, User $actor): void
    {
        if ($actor->peranan === 'superadmin') {
            return;
        }

        if ($actor->id_masjid === null || $actor->id_masjid !== $pindahanAkaun->id_masjid) {
            abort(403, 'Akses tidak dibenarkan.');
        }
    }
}
