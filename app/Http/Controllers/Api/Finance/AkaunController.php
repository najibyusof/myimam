<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Requests\Admin\AkaunStoreRequest;
use App\Http\Requests\Admin\AkaunUpdateRequest;
use App\Models\Akaun;
use App\Services\AkaunManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AkaunController extends BaseFinanceController
{
    public function __construct(private readonly AkaunManagementService $service) {}

    public function index(Request $request): JsonResponse
    {
        $actor = $this->actor($request);
        $query = Akaun::query()->withoutTenantScope();

        $this->applyActorMasjidScope($query, $actor);

        if ($search = $request->string('search')->toString()) {
            $query->where('nama_akaun', 'like', "%{$search}%");
        }

        if ($request->has('status_aktif')) {
            $query->where('status_aktif', $request->boolean('status_aktif'));
        }

        if ($jenis = $request->string('jenis')->toString()) {
            $query->where('jenis', $jenis);
        }

        $records = $query->orderBy('nama_akaun')->paginate($this->perPage($request));

        return $this->paginatedResponse($records);
    }

    public function store(AkaunStoreRequest $request): JsonResponse
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
        $record = Akaun::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        return response()->json($record);
    }

    public function update(AkaunUpdateRequest $request, string $id): JsonResponse
    {
        $actor = $this->actor($request);
        $record = Akaun::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $validated = $request->validated();
        $this->ensureSuperadminMasjidProvided($actor, $validated);

        $updated = $this->service->update($record, $actor, $validated);

        return response()->json($updated);
    }

    public function destroy(string $id): JsonResponse
    {
        $actor = $this->actor(request());
        $record = Akaun::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $this->service->delete($record, $actor);

        return response()->json([
            'message' => 'Akaun deleted successfully',
        ]);
    }
}
