<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Requests\Admin\KategoriBelanjaStoreRequest;
use App\Http\Requests\Admin\KategoriBelanjaUpdateRequest;
use App\Models\KategoriBelanja;
use App\Services\KategoriBelanjaManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KategoriBelanjaController extends BaseFinanceController
{
    public function __construct(private readonly KategoriBelanjaManagementService $service) {}

    public function index(Request $request): JsonResponse
    {
        $actor = $this->actor($request);
        $query = KategoriBelanja::query()->withoutTenantScope();

        $this->applyActorMasjidScope($query, $actor);

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('nama_kategori', 'like', "%{$search}%")
                    ->orWhere('kod', 'like', "%{$search}%");
            });
        }

        if ($request->has('aktif')) {
            $query->where('aktif', $request->boolean('aktif'));
        }

        $records = $query->orderBy('nama_kategori')->paginate($this->perPage($request));

        return $this->paginatedResponse($records);
    }

    public function store(KategoriBelanjaStoreRequest $request): JsonResponse
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
        $record = KategoriBelanja::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        return response()->json($record);
    }

    public function update(KategoriBelanjaUpdateRequest $request, string $id): JsonResponse
    {
        $actor = $this->actor($request);
        $record = KategoriBelanja::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $validated = $request->validated();
        $this->ensureSuperadminMasjidProvided($actor, $validated);

        $updated = $this->service->update($record, $actor, $validated);

        return response()->json($updated);
    }

    public function destroy(string $id): JsonResponse
    {
        $actor = $this->actor(request());
        $record = KategoriBelanja::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $this->service->delete($record, $actor);

        return response()->json([
            'message' => 'Kategori belanja deleted successfully',
        ]);
    }

    public function toggleStatus(Request $request, string $id): JsonResponse
    {
        $actor = $this->actor($request);
        $record = KategoriBelanja::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $updated = $this->service->toggleStatus($record, $actor);

        return response()->json($updated);
    }
}
