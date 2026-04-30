<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Requests\Admin\BelanjaStoreRequest;
use App\Http\Requests\Admin\BelanjaUpdateRequest;
use App\Models\Belanja;
use App\Services\BelanjaManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BelanjaController extends BaseFinanceController
{
    public function __construct(private readonly BelanjaManagementService $service) {}

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

        $record->update([
            'status' => 'LULUS',
            'dilulus_oleh' => $actor->id,
            'tarikh_lulus' => now(),
        ]);

        return response()->json([
            'message' => 'Belanja approved successfully',
            'data' => $record->refresh(),
        ]);
    }
}
