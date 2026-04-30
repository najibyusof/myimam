<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Requests\Admin\PindahanAkaunStoreRequest;
use App\Http\Requests\Admin\PindahanAkaunUpdateRequest;
use App\Models\PindahanAkaun;
use App\Services\PindahanAkaunManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PindahanAkaunController extends BaseFinanceController
{
    public function __construct(private readonly PindahanAkaunManagementService $service) {}

    public function index(Request $request): JsonResponse
    {
        $actor = $this->actor($request);
        $query = PindahanAkaun::query()->withoutTenantScope()->with(['dariAkaun', 'keAkaun']);

        $this->applyActorMasjidScope($query, $actor);

        if ($from = $request->input('tarikh_mula')) {
            $query->whereDate('tarikh', '>=', $from);
        }

        if ($to = $request->input('tarikh_tamat')) {
            $query->whereDate('tarikh', '<=', $to);
        }

        if ($akaunId = $request->integer('akaun_id')) {
            $query->forAkaun($akaunId);
        }

        $records = $query->latest('tarikh')->latest('id')->paginate($this->perPage($request));

        return $this->paginatedResponse($records);
    }

    public function store(PindahanAkaunStoreRequest $request): JsonResponse
    {
        $actor = $this->actor($request);
        $validated = $request->validated();
        $this->ensureSuperadminMasjidProvided($actor, $validated);

        $created = $this->service->create($actor, $validated);

        return response()->json($created, 201);
    }

    public function show(string $id): JsonResponse
    {
        $actor = $this->actor(request());
        $record = PindahanAkaun::query()->withoutTenantScope()->with(['dariAkaun', 'keAkaun'])->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        return response()->json($record);
    }

    public function update(PindahanAkaunUpdateRequest $request, string $id): JsonResponse
    {
        $actor = $this->actor($request);
        $record = PindahanAkaun::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $validated = $request->validated();
        $this->ensureSuperadminMasjidProvided($actor, $validated);

        $updated = $this->service->update($record, $actor, $validated);

        return response()->json($updated);
    }

    public function destroy(string $id): JsonResponse
    {
        $actor = $this->actor(request());
        $record = PindahanAkaun::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $this->service->delete($record, $actor);

        return response()->json([
            'message' => 'Pindahan akaun deleted successfully',
        ]);
    }
}
