<?php

namespace App\Services;

use App\Models\Akaun;
use App\Models\Hasil;
use App\Models\SumberHasil;
use App\Models\TabungKhas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HasilManagementService
{
    public function __construct(private readonly RunningNoManagementService $runningNoService) {}

    public function create(User $actor, array $data): Hasil
    {
        return DB::transaction(function () use ($actor, $data): Hasil {
            $payload = $this->sanitizePayload($actor, $data);
            $this->validateRelations($payload);
            $payload['no_resit'] = $this->generateReceiptNumber($payload);

            return Hasil::query()->create($payload);
        });
    }

    public function update(Hasil $hasil, User $actor, array $data): Hasil
    {
        return DB::transaction(function () use ($hasil, $actor, $data): Hasil {
            $this->ensureScoped($hasil, $actor);
            $payload = $this->sanitizePayload($actor, $data);
            $this->validateRelations($payload);

            $payload['created_by'] = $hasil->created_by;
            $payload['no_resit'] = $hasil->no_resit;

            $hasil->update($payload);

            return $hasil->refresh();
        });
    }

    public function delete(Hasil $hasil, User $actor): void
    {
        DB::transaction(function () use ($hasil, $actor): void {
            $this->ensureScoped($hasil, $actor);
            $hasil->delete();
        });
    }

    private function sanitizePayload(User $actor, array $data): array
    {
        $masjidId = $actor->peranan === 'superadmin' ? ($data['id_masjid'] ?? null) : $actor->id_masjid;
        $amaun = $data['amaun'];

        return [
            'id_masjid' => $masjidId,
            'tarikh' => $data['tarikh'],
            'no_resit' => null,
            'id_akaun' => $data['id_akaun'],
            'id_sumber_hasil' => $data['id_sumber_hasil'],
            'amaun_tunai' => $amaun,
            'amaun_online' => 0,
            'jumlah' => $amaun,
            'id_tabung_khas' => $data['id_tabung_khas'] ?? null,
            'id_program' => null,
            'jenis_jumaat' => !empty($data['is_jumaat']) ? 'biasa' : null,
            'catatan' => $data['catatan'] ?? null,
            'created_by' => $actor->id,
        ];
    }

    private function validateRelations(array $payload): void
    {
        $masjidId = $payload['id_masjid'] ?? null;

        if (!$masjidId) {
            throw ValidationException::withMessages([
                'id_masjid' => 'Masjid diperlukan untuk transaksi hasil.',
            ]);
        }

        $akaun = Akaun::query()->find($payload['id_akaun']);
        $sumberHasil = SumberHasil::query()->find($payload['id_sumber_hasil']);
        $tabungKhas = !empty($payload['id_tabung_khas']) ? TabungKhas::query()->find($payload['id_tabung_khas']) : null;

        if (!$akaun || $akaun->id_masjid !== $masjidId) {
            throw ValidationException::withMessages([
                'id_akaun' => 'Akaun yang dipilih tidak sepadan dengan masjid transaksi.',
            ]);
        }

        if (!$sumberHasil || $sumberHasil->id_masjid !== $masjidId) {
            throw ValidationException::withMessages([
                'id_sumber_hasil' => 'Sumber hasil yang dipilih tidak sepadan dengan masjid transaksi.',
            ]);
        }

        if ($tabungKhas && $tabungKhas->id_masjid !== $masjidId) {
            throw ValidationException::withMessages([
                'id_tabung_khas' => 'Tabung khas yang dipilih tidak sepadan dengan masjid transaksi.',
            ]);
        }
    }

    private function generateReceiptNumber(array $payload): string
    {
        $tarikh = Carbon::parse($payload['tarikh']);

        return $this->runningNoService->generate(
            (int) $payload['id_masjid'],
            'RESIT',
            (int) $tarikh->format('Y'),
            (int) $tarikh->format('n')
        );
    }

    public function generateReceiptNo(Hasil $hasil): string
    {
        $tarikh = Carbon::parse($hasil->tarikh);

        return $this->runningNoService->generate(
            (int) $hasil->id_masjid,
            'RESIT',
            (int) $tarikh->format('Y'),
            (int) $tarikh->format('n')
        );
    }

    private function ensureScoped(Hasil $hasil, User $actor): void
    {
        if ($actor->peranan === 'superadmin') {
            return;
        }

        abort_unless(
            $actor->id_masjid !== null && $actor->id_masjid === $hasil->id_masjid,
            403,
            'Unauthorized'
        );
    }
}
