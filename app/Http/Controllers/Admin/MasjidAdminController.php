<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Masjid;
use Illuminate\Http\JsonResponse;

class MasjidAdminController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Masjid::class);

        $data = Masjid::query()
            ->select(['id', 'nama', 'daerah', 'negeri', 'created_at'])
            ->latest('id')
            ->limit(50)
            ->get();

        return response()->json($data);
    }

    public function update(Masjid $masjid): JsonResponse
    {
        $this->authorize('update', $masjid);

        return response()->json([
            'message' => 'Authorized to update masjid.',
            'masjid_id' => $masjid->id,
        ]);
    }
}
