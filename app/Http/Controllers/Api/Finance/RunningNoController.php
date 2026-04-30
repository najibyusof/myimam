<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Requests\Admin\RunningNoGenerateRequest;
use App\Http\Requests\Admin\RunningNoUpdateRequest;
use App\Models\RunningNo;
use App\Services\RunningNoManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RunningNoController extends BaseFinanceController
{
    public function __construct(private readonly RunningNoManagementService $service) {}

    public function index(Request $request): JsonResponse
    {
        $actor = $this->actor($request);
        $query = RunningNo::query()->withoutTenantScope();

        $this->applyActorMasjidScope($query, $actor);

        if ($prefix = $request->string('prefix')->toString()) {
            $query->where('prefix', strtoupper($prefix));
        }

        if ($tahun = $request->integer('tahun')) {
            $query->where('tahun', $tahun);
        }

        if ($bulan = $request->integer('bulan')) {
            $query->where('bulan', $bulan);
        }

        $records = $query->orderByDesc('tahun')->orderByDesc('bulan')->orderBy('prefix')
            ->paginate($this->perPage($request));

        return $this->paginatedResponse($records);
    }

    public function generate(RunningNoGenerateRequest $request): JsonResponse
    {
        $actor = $this->actor($request);
        $validated = $request->validated();

        $idMasjid = $actor->peranan === 'superadmin'
            ? (int) ($validated['id_masjid'] ?? 0)
            : (int) $actor->id_masjid;

        if ($idMasjid <= 0) {
            return response()->json([
                'message' => 'id_masjid is required for superadmin requests.',
                'errors' => ['id_masjid' => ['The id_masjid field is required.']],
            ], 422);
        }

        $refNo = $this->service->generate(
            $idMasjid,
            (string) $validated['prefix'],
            (int) $validated['tahun'],
            (int) $validated['bulan']
        );

        return response()->json([
            'reference_no' => $refNo,
            'id_masjid' => $idMasjid,
            'prefix' => strtoupper((string) $validated['prefix']),
            'tahun' => (int) $validated['tahun'],
            'bulan' => (int) $validated['bulan'],
        ]);
    }

    public function update(RunningNoUpdateRequest $request, int $idMasjid, string $prefix, int $tahun, int $bulan): JsonResponse
    {
        $actor = $this->actor($request);

        if ($actor->peranan !== 'superadmin' && (int) $actor->id_masjid !== $idMasjid) {
            abort(403, 'Unauthorized');
        }

        $record = RunningNo::query()
            ->withoutTenantScope()
            ->forPeriod($idMasjid, strtoupper($prefix), $tahun, $bulan)
            ->firstOrFail();

        $updated = $this->service->resetCounter($record, (int) $request->validated('last_no'));

        return response()->json($updated);
    }
}
