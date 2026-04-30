<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Requests\Admin\BelanjaStoreRequest;
use App\Http\Requests\Admin\BelanjaUpdateRequest;
use App\Models\Belanja;
use App\Services\BelanjaManagementService;
use App\Services\LogAktivitiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BelanjaController extends BaseFinanceController
{
    public function __construct(
        private readonly BelanjaManagementService $service,
        private readonly LogAktivitiService $log,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $actor = $this->actor($request);
        $query = Belanja::query()
            ->withoutTenantScope()
            ->notDeleted()
            ->with(['akaun', 'kategoriBelanja', 'baucar']);

        $this->applyActorMasjidScope($query, $actor);

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('penerima', 'like', "%{$search}%")
                    ->orWhere('catatan', 'like', "%{$search}%");
            });
        }

        if ($status = strtolower($request->string('status')->toString())) {
            if (in_array($status, ['draft', 'draf', 'pending'], true)) {
                $query->where('status', 'DRAF');
            } elseif (in_array($status, ['submitted', 'approved', 'lulus'], true)) {
                $query->where('status', 'LULUS');
            } elseif (in_array($status, ['menunggu-pengerusi', 'pending-chair'], true)) {
                $query->where('status', 'DRAF')->where('approval_step', 1);
            }
        }

        if ($from = $request->input('tarikh_mula')) {
            $query->whereDate('tarikh', '>=', $from);
        }

        if ($to = $request->input('tarikh_tamat')) {
            $query->whereDate('tarikh', '<=', $to);
        }

        if ($kategoriId = $request->integer('id_kategori_belanja')) {
            $query->where('id_kategori_belanja', $kategoriId);
        }

        $records = $query->latest('tarikh')->latest('id')->paginate($this->perPage($request));

        return $this->paginatedResponse($records);
    }

    public function store(BelanjaStoreRequest $request): JsonResponse
    {
        $actor = $this->actor($request);
        $validated = $request->validated();
        $this->ensureSuperadminMasjidProvided($actor, $validated);

        if ($request->hasFile('bukti_fail')) {
            $validated['bukti_fail'] = $request->file('bukti_fail')->store('belanja-bukti', 'public');
        } elseif ($request->hasFile('bukti_fail_camera')) {
            $validated['bukti_fail'] = $request->file('bukti_fail_camera')->store('belanja-bukti', 'public');
        }

        $created = $this->service->create($actor, $validated);

        return response()->json($created, 201);
    }

    public function show(string $id): JsonResponse
    {
        $actor = $this->actor(request());
        $record = Belanja::query()->withoutTenantScope()->with(['akaun', 'kategoriBelanja', 'baucar'])->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        return response()->json($record);
    }

    public function update(BelanjaUpdateRequest $request, string $id): JsonResponse
    {
        $actor = $this->actor($request);
        $record = Belanja::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $validated = $request->validated();
        $this->ensureSuperadminMasjidProvided($actor, $validated);

        if ($request->boolean('remove_bukti_fail') && $record->bukti_fail) {
            Storage::disk('public')->delete($record->bukti_fail);
            $validated['bukti_fail'] = null;
        } elseif ($request->hasFile('bukti_fail')) {
            if ($record->bukti_fail) {
                Storage::disk('public')->delete($record->bukti_fail);
            }
            $validated['bukti_fail'] = $request->file('bukti_fail')->store('belanja-bukti', 'public');
        } elseif ($request->hasFile('bukti_fail_camera')) {
            if ($record->bukti_fail) {
                Storage::disk('public')->delete($record->bukti_fail);
            }
            $validated['bukti_fail'] = $request->file('bukti_fail_camera')->store('belanja-bukti', 'public');
        } else {
            $validated['bukti_fail'] = $record->bukti_fail;
        }

        $updated = $this->service->update($record, $actor, $validated);

        return response()->json($updated);
    }

    public function destroy(string $id): JsonResponse
    {
        $actor = $this->actor(request());
        $record = Belanja::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $deleted = $this->service->softDelete($record, $actor);

        return response()->json([
            'message' => 'Belanja soft-deleted successfully',
            'data' => $deleted,
        ]);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $actor = $this->actor($request);
        $record = Belanja::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        if ($record->is_baucar_locked) {
            return response()->json([
                'message' => 'Baucar telah dikunci.',
            ], 422);
        }

        if (empty($actor->signature_path)) {
            $this->log->record(LogAktivitiService::JENIS_APPROVE, 'Baucar', 'Kelulusan Ditolak - Tiada Tandatangan', [
                'rujukan_id' => $record->id,
                'butiran'    => 'Percubaan meluluskan baucar ditolak kerana pengguna tiada tandatangan digital.',
            ], $request);

            return response()->json([
                'message' => 'Sila muat naik tandatangan digital pada profil sebelum meluluskan baucar.',
            ], 422);
        }

        if ((int) $record->approval_step === 0) {
            abort_unless($actor->hasRole('Bendahari') || $actor->peranan === 'superadmin', 403, 'Unauthorized');

            $record->update([
                'status'               => 'DRAF',
                'approval_step'        => 1,
                'bendahari_lulus_oleh' => $actor->id,
                'bendahari_lulus_pada' => now(),
                'bendahari_signature'  => $this->generateDigitalSignature($record, $actor->id, 'bendahari'),
                'ditolak_oleh'         => null,
                'tarikh_tolak'         => null,
                'catatan_tolak'        => null,
            ]);

            $this->log->record(LogAktivitiService::JENIS_APPROVE, 'Baucar', 'Lulus Bendahari', [
                'rujukan_id' => $record->id,
                'butiran'    => 'Baucar ' . ($record->no_baucar ?: '#' . $record->id) . ' telah disemak dan diluluskan oleh Bendahari.',
            ], $request);

            return response()->json([
                'message' => 'Semakan bendahari selesai. Menunggu kelulusan pengerusi.',
                'data' => $record->refresh(),
            ]);
        }

        abort_unless((int) $record->approval_step === 1, 422, 'Langkah kelulusan tidak sah.');
        abort_unless($actor->hasRole('Pengerusi') || $actor->peranan === 'superadmin', 403, 'Unauthorized');

        $record->update([
            'status'                => 'LULUS',
            'approval_step'         => 2,
            'pengerusi_lulus_oleh'  => $actor->id,
            'pengerusi_lulus_pada'  => now(),
            'pengerusi_signature'   => $this->generateDigitalSignature($record, $actor->id, 'pengerusi'),
            'dilulus_oleh'          => $actor->id,
            'tarikh_lulus'          => now(),
            'is_baucar_locked'      => true,
            'locked_at'             => now(),
            'locked_by'             => $actor->id,
        ]);

        $this->log->record(LogAktivitiService::JENIS_APPROVE, 'Baucar', 'Lulus Pengerusi', [
            'rujukan_id' => $record->id,
            'butiran'    => 'Baucar ' . ($record->no_baucar ?: '#' . $record->id) . ' telah diluluskan oleh Pengerusi dan dikunci.',
        ], $request);

        return response()->json([
            'message' => 'Belanja approved successfully',
            'data' => $record->refresh(),
        ]);
    }

    private function generateDigitalSignature(Belanja $belanja, int $approverId, string $stage): string
    {
        $payload = implode('|', [
            $belanja->id,
            $belanja->no_baucar ?: 'pending',
            $approverId,
            $stage,
            now()->format('YmdHis'),
            Str::random(6),
        ]);

        return strtoupper(substr(hash_hmac('sha256', $payload, (string) config('app.key')), 0, 24));
    }
}
