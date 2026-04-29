<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Requests\Admin\TabungKhasStoreRequest;
use App\Http\Requests\Admin\TabungKhasUpdateRequest;
use App\Models\TabungKhas;
use App\Services\TabungKhasManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TabungKhasController extends BaseFinanceController
{
    public function __construct(private readonly TabungKhasManagementService $service) {}

    public function index(Request $request): JsonResponse
    {
        $actor = $this->actor($request);
        $query = TabungKhas::query()->withoutTenantScope();

        $this->applyActorMasjidScope($query, $actor);

        if ($search = $request->string('search')->toString()) {
            $query->where('nama_tabung', 'like', "%{$search}%");
        }

        if ($request->has('aktif')) {
            $query->where('aktif', $request->boolean('aktif'));
        }

        $records = $query->orderBy('nama_tabung')->paginate($this->perPage($request));

        return $this->paginatedResponse($records);
    }

    public function store(TabungKhasStoreRequest $request): JsonResponse
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
        $record = TabungKhas::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        return response()->json($record);
    }

    public function update(TabungKhasUpdateRequest $request, string $id): JsonResponse
    {
        $actor = $this->actor($request);
        $record = TabungKhas::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $validated = $request->validated();
        $this->ensureSuperadminMasjidProvided($actor, $validated);

        $updated = $this->service->update($record, $actor, $validated);

        return response()->json($updated);
    }

    public function destroy(string $id): JsonResponse
    {
        $actor = $this->actor(request());
        $record = TabungKhas::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $this->service->delete($record, $actor);

        return response()->json([
            'message' => 'Tabung khas deleted successfully',
        ]);
    }

    public function toggleStatus(Request $request, string $id): JsonResponse
    {
        $actor = $this->actor($request);
        $record = TabungKhas::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $updated = $this->service->toggleStatus($record, $actor);

        return response()->json($updated);
    }
}
