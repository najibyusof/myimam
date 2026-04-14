<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MasjidResource;
use App\Models\Masjid;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasjidController extends Controller
{
    /**
     * Get all masjids (paginated)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Masjid::query();

        // Search by name or location
        if ($search = $request->input('search')) {
            $query->where('nama', 'like', "%{$search}%")
                  ->orWhere('bandar', 'like', "%{$search}%")
                  ->orWhere('negeri', 'like', "%{$search}%");
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by state
        if ($state = $request->input('negeri')) {
            $query->where('negeri', $state);
        }

        $masjids = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => MasjidResource::collection($masjids->items()),
            'pagination' => [
                'total' => $masjids->total(),
                'per_page' => $masjids->perPage(),
                'current_page' => $masjids->currentPage(),
                'last_page' => $masjids->lastPage(),
                'from' => $masjids->firstItem(),
                'to' => $masjids->lastItem(),
            ],
        ]);
    }

    /**
     * Create new masjid
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'bandar' => ['required', 'string'],
            'negeri' => ['required', 'string'],
            'poskod' => ['nullable', 'string'],
            'no_telefon' => ['nullable', 'string'],
            'emel' => ['nullable', 'email'],
            'kapasiti_solat' => ['nullable', 'integer'],
            'imam' => ['nullable', 'string'],
            'tahun_ditubuhkan' => ['nullable', 'integer'],
            'koordinat_lat' => ['nullable', 'numeric'],
            'koordinat_long' => ['nullable', 'numeric'],
            'status' => ['nullable', 'in:active,inactive,pending'],
        ]);

        $masjid = Masjid::create($validated);

        return response()->json(new MasjidResource($masjid), 201);
    }

    /**
     * Get masjid details
     */
    public function show(Masjid $masjid): JsonResponse
    {
        return response()->json(new MasjidResource($masjid));
    }

    /**
     * Update masjid
     */
    public function update(Request $request, Masjid $masjid): JsonResponse
    {
        $validated = $request->validate([
            'nama' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'bandar' => ['nullable', 'string'],
            'negeri' => ['nullable', 'string'],
            'poskod' => ['nullable', 'string'],
            'no_telefon' => ['nullable', 'string'],
            'emel' => ['nullable', 'email'],
            'kapasiti_solat' => ['nullable', 'integer'],
            'imam' => ['nullable', 'string'],
            'tahun_ditubuhkan' => ['nullable', 'integer'],
            'koordinat_lat' => ['nullable', 'numeric'],
            'koordinat_long' => ['nullable', 'numeric'],
            'status' => ['nullable', 'in:active,inactive,pending'],
        ]);

        $masjid->update(array_filter($validated));

        return response()->json(new MasjidResource($masjid));
    }

    /**
     * Delete masjid
     */
    public function destroy(Masjid $masjid): JsonResponse
    {
        $masjid->delete();

        return response()->json([
            'message' => 'Masjid deleted successfully',
        ]);
    }

    /**
     * Get masjid programs
     */
    public function getPrograms(Request $request, Masjid $masjid): JsonResponse
    {
        $programs = $masjid->programs()
                          ->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => $programs->items(),
            'pagination' => [
                'total' => $programs->total(),
                'per_page' => $programs->perPage(),
                'current_page' => $programs->currentPage(),
                'last_page' => $programs->lastPage(),
            ],
        ]);
    }

    /**
     * Get masjid members (users assigned to this masjid)
     */
    public function getMembers(Request $request, Masjid $masjid): JsonResponse
    {
        $members = $masjid->users()
                         ->paginate($request->input('per_page', 15));

        return response()->json([
            'data' => MasjidResource::collection($members->items()),
            'pagination' => [
                'total' => $members->total(),
                'per_page' => $members->perPage(),
                'current_page' => $members->currentPage(),
                'last_page' => $members->lastPage(),
            ],
        ]);
    }
}
