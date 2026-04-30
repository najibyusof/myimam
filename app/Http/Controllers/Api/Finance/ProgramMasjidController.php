<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Requests\Admin\ProgramMasjidStoreRequest;
use App\Http\Requests\Admin\ProgramMasjidUpdateRequest;
use App\Models\ProgramMasjid;
use App\Services\ProgramMasjidManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramMasjidController extends BaseFinanceController
{
    public function __construct(private readonly ProgramMasjidManagementService $service) {}

    public function index(Request $request): JsonResponse
    {
        $actor = $this->actor($request);
        $query = ProgramMasjid::query()->withoutTenantScope();

        $this->applyActorMasjidScope($query, $actor);

        if ($search = $request->string('search')->toString()) {
            $query->where('nama_program', 'like', "%{$search}%");
        }

        if ($request->has('aktif')) {
            $query->where('aktif', $request->boolean('aktif'));
        }

        $records = $query->orderBy('nama_program')->paginate($this->perPage($request));

        return $this->paginatedResponse($records);
    }

    public function store(ProgramMasjidStoreRequest $request): JsonResponse
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
        $record = ProgramMasjid::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        return response()->json($record);
    }

    public function update(ProgramMasjidUpdateRequest $request, string $id): JsonResponse
    {
        $actor = $this->actor($request);
        $record = ProgramMasjid::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $validated = $request->validated();
        $this->ensureSuperadminMasjidProvided($actor, $validated);

        $updated = $this->service->update($record, $actor, $validated);

        return response()->json($updated);
    }

    public function destroy(string $id): JsonResponse
    {
        $actor = $this->actor(request());
        $record = ProgramMasjid::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $this->service->delete($record, $actor);

        return response()->json([
            'message' => 'Program masjid deleted successfully',
        ]);
    }

    public function toggleStatus(Request $request, string $id): JsonResponse
    {
        $actor = $this->actor($request);
        $record = ProgramMasjid::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $updated = $this->service->toggleStatus($record, $actor);

        return response()->json($updated);
    }
}
