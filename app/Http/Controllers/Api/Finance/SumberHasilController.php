<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Requests\Admin\SumberHasilStoreRequest;
use App\Http\Requests\Admin\SumberHasilUpdateRequest;
use App\Models\SumberHasil;
use App\Services\SumberHasilManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SumberHasilController extends BaseFinanceController
{
    public function __construct(private readonly SumberHasilManagementService $service) {}

    public function index(Request $request): JsonResponse
    {
        $actor = $this->actor($request);
        $query = SumberHasil::query()->withoutTenantScope();

        $this->applyActorMasjidScope($query, $actor);

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('nama_sumber', 'like', "%{$search}%")
                    ->orWhere('kod', 'like', "%{$search}%")
                    ->orWhere('jenis', 'like', "%{$search}%");
            });
        }

        if ($request->has('aktif')) {
            $query->where('aktif', $request->boolean('aktif'));
        }

        $records = $query->orderBy('nama_sumber')->paginate($this->perPage($request));

        return $this->paginatedResponse($records);
    }

    public function store(SumberHasilStoreRequest $request): JsonResponse
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
        $record = SumberHasil::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        return response()->json($record);
    }

    public function update(SumberHasilUpdateRequest $request, string $id): JsonResponse
    {
        $actor = $this->actor($request);
        $record = SumberHasil::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $validated = $request->validated();
        $this->ensureSuperadminMasjidProvided($actor, $validated);

        $updated = $this->service->update($record, $actor, $validated);

        return response()->json($updated);
    }

    public function destroy(string $id): JsonResponse
    {
        $actor = $this->actor(request());
        $record = SumberHasil::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $this->service->delete($record, $actor);

        return response()->json([
            'message' => 'Sumber hasil deleted successfully',
        ]);
    }

    public function toggleStatus(Request $request, string $id): JsonResponse
    {
        $actor = $this->actor($request);
        $record = SumberHasil::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $updated = $this->service->toggleStatus($record, $actor);

        return response()->json($updated);
    }
}
