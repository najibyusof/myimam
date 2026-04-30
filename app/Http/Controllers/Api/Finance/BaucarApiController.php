<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Resources\Api\Finance\BaucarResource;
use App\Models\Belanja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BaucarApiController extends BaseFinanceController
{
    /**
     * GET /api/finance/baucar
     *
     * List baucar (belanja records) with filters.
     *
     * Query params:
     *   search           – penerima / catatan  (string)
     *   status           – draft | pending-pengerusi | rejected | approved  (string)
     *   tarikh_mula      – YYYY-MM-DD  (string)
     *   tarikh_tamat     – YYYY-MM-DD  (string)
     *   id_kategori_belanja – integer
     *   per_page         – 1-100, default 15  (integer)
     *   page             – default 1  (integer)
     */
    public function index(Request $request): JsonResponse
    {
        $actor = $this->actor($request);

        $query = Belanja::query()
            ->withoutTenantScope()
            ->notDeleted()
            ->with([
                'akaun:id,nama_akaun',
                'kategoriBelanja:id,nama_kategori',
                'bendahariLulusOleh:id,name',
                'pengerusiLulusOleh:id,name',
                'createdBy:id,name',
                'ditolakOleh:id,name',
            ]);

        $this->applyActorMasjidScope($query, $actor);

        // --- Filters ---

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('penerima', 'like', "%{$search}%")
                    ->orWhere('catatan', 'like', "%{$search}%");
            });
        }

        if ($status = strtolower($request->string('status')->toString())) {
            match ($status) {
                'approved', 'lulus'       => $query->where('is_baucar_locked', true),
                'pending-pengerusi'        => $query->where('approval_step', 1)->where('is_baucar_locked', false),
                'rejected'                 => $query->where('approval_step', 0)->whereNotNull('catatan_tolak'),
                'draft', 'draf', 'pending' => $query->where('approval_step', 0)->whereNull('catatan_tolak')->where('is_baucar_locked', false),
                default                    => null,
            };
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

        return $this->paginatedBaucarResponse(
            $records,
            BaucarResource::collection($records->items()),
        );
    }

    /**
     * GET /api/finance/baucar/{id}
     *
     * Show a single baucar.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $actor = $this->actor($request);

        $record = Belanja::query()
            ->withoutTenantScope()
            ->notDeleted()
            ->with([
                'akaun:id,nama_akaun',
                'kategoriBelanja:id,nama_kategori',
                'bendahariLulusOleh:id,name',
                'pengerusiLulusOleh:id,name',
                'createdBy:id,name',
                'ditolakOleh:id,name',
            ])
            ->findOrFail($id);

        $this->enforceActorScopeForModel($actor, $record);

        return response()->json(new BaucarResource($record));
    }

    /**
     * GET /api/finance/baucar/{id}/pdf
     *
     * Stub endpoint — client should use the web route baucar.pdf for now.
     * Prepared for future standalone API PDF generation.
     */
    public function pdf(Request $request, string $id): JsonResponse
    {
        $actor = $this->actor($request);

        $record = Belanja::query()
            ->withoutTenantScope()
            ->notDeleted()
            ->findOrFail($id);

        $this->enforceActorScopeForModel($actor, $record);

        return response()->json([
            'message'  => 'PDF generation via API is not yet implemented. Use the web route.',
            'pdf_url'  => route('baucar.pdf', ['belanja_id' => $record->id]),
            'baucar_id' => $record->id,
        ], 501);
    }

    /**
     * Override paginatedResponse to accept a pre-transformed ResourceCollection.
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator
     * @param \Illuminate\Http\Resources\Json\ResourceCollection|iterable $data
     */
    protected function paginatedBaucarResponse(
        \Illuminate\Pagination\LengthAwarePaginator $paginator,
        mixed $data
    ): JsonResponse {
        return response()->json([
            'data'       => $data,
            'pagination' => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
        ]);
    }
}
