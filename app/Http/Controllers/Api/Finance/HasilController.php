<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Requests\Admin\HasilStoreRequest;
use App\Http\Requests\Admin\HasilUpdateRequest;
use App\Models\Hasil;
use App\Services\HasilManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HasilController extends BaseFinanceController
{
    public function __construct(private readonly HasilManagementService $service) {}

    public function index(Request $request): JsonResponse
    {
        $actor = $this->actor($request);
        $query = Hasil::query()->withoutTenantScope()->with(['akaun', 'sumberHasil', 'tabungKhas', 'programMasjid']);

        $this->applyActorMasjidScope($query, $actor);

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('no_resit', 'like', "%{$search}%")
                    ->orWhere('catatan', 'like', "%{$search}%");
            });
        }

        if ($from = $request->input('tarikh_mula')) {
            $query->whereDate('tarikh', '>=', $from);
        }

        if ($to = $request->input('tarikh_tamat')) {
            $query->whereDate('tarikh', '<=', $to);
        }

        if ($idAkaun = $request->integer('id_akaun')) {
            $query->where('id_akaun', $idAkaun);
        }

        if ($idSumber = $request->integer('id_sumber_hasil')) {
            $query->where('id_sumber_hasil', $idSumber);
        }

        $records = $query->latest('tarikh')->latest('id')->paginate($this->perPage($request));

        return $this->paginatedResponse($records);
    }

    public function store(HasilStoreRequest $request): JsonResponse
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
        $record = Hasil::query()->withoutTenantScope()->with(['akaun', 'sumberHasil', 'tabungKhas', 'programMasjid'])->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        return response()->json($record);
    }

    public function update(HasilUpdateRequest $request, string $id): JsonResponse
    {
        $actor = $this->actor($request);
        $record = Hasil::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $validated = $request->validated();
        $this->ensureSuperadminMasjidProvided($actor, $validated);

        $updated = $this->service->update($record, $actor, $validated);

        return response()->json($updated);
    }

    public function destroy(string $id): JsonResponse
    {
        $actor = $this->actor(request());
        $record = Hasil::query()->withoutTenantScope()->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        $this->service->delete($record, $actor);

        return response()->json([
            'message' => 'Hasil deleted successfully',
        ]);
    }

    public function receipt(string $id): JsonResponse
    {
        $actor = $this->actor(request());
        $record = Hasil::query()->withoutTenantScope()->with(['masjid', 'akaun', 'sumberHasil', 'tabungKhas'])->findOrFail($id);
        $this->enforceActorScopeForModel($actor, $record);

        if ($record->jenis_jumaat !== null) {
            return response()->json([
                'message' => 'Kutipan Jumaat tidak boleh dicetak resit.',
            ], 403);
        }

        return response()->json([
            'message' => 'Receipt data retrieved successfully',
            'data' => $record,
        ]);
    }
}
