<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MasjidStoreRequest;
use App\Http\Requests\Admin\MasjidUpdateRequest;
use App\Models\Masjid;
use App\Services\MasjidManagementService;
use Illuminate\Http\Request;

class MasjidManagementController extends Controller
{
    public function __construct(private MasjidManagementService $masjidService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Masjid::class);

        $query = Masjid::latest('id');

        if ($request->filled('q')) {
            $query->where('nama', 'like', '%' . $request->q . '%')
                ->orWhere('negeri', 'like', '%' . $request->q . '%')
                ->orWhere('daerah', 'like', '%' . $request->q . '%');
        }

        $masjids = $query->paginate(15);

        return view('admin.masjid.index', [
            'masjids' => $masjids,
            'q' => $request->q,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Masjid::class);

        return view('admin.masjid.create');
    }

    public function store(MasjidStoreRequest $request)
    {
        $this->authorize('create', Masjid::class);

        $this->masjidService->create($request->validated());

        return redirect()->route('admin.masjid.index')
            ->with('status', 'Masjid berjaya ditambah.');
    }

    public function edit(Masjid $masjid)
    {
        $this->authorize('update', $masjid);

        return view('admin.masjid.edit', [
            'masjid' => $masjid,
        ]);
    }

    public function update(MasjidUpdateRequest $request, Masjid $masjid)
    {
        $this->authorize('update', $masjid);

        $this->masjidService->update($masjid, $request->validated());

        return redirect()->route('admin.masjid.edit', $masjid)
            ->with('status', 'Masjid berjaya dikemaskini.');
    }

    public function destroy(Masjid $masjid)
    {
        $this->authorize('delete', $masjid);

        $this->masjidService->delete($masjid);

        return redirect()->route('admin.masjid.index')
            ->with('status', 'Masjid berjaya dipadamkan.');
    }
}
