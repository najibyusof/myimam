<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MasjidStoreRequest;
use App\Http\Requests\Admin\MasjidUpdateRequest;
use App\Models\Masjid;
use App\Models\User;
use App\Services\MasjidManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasjidManagementController extends Controller
{
    public function __construct(private MasjidManagementService $masjidService)
    {
    }

    public function index(Request $request)
    {
        $this->ensureSuperAdmin();
        $this->authorize('viewAny', Masjid::class);

        $query = Masjid::withCount([
            'users',
            'hasil',
            'belanja',
            'users as admin_count' => fn ($q) => $q->where('peranan', 'admin'),
        ])->latest('id');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->q . '%')
                    ->orWhere('code', 'like', '%' . $request->q . '%')
                    ->orWhere('negeri', 'like', '%' . $request->q . '%')
                    ->orWhere('daerah', 'like', '%' . $request->q . '%');
            });
        }

        $masjids = $query->paginate(15);

        return view('admin.masjid.index', [
            'masjids' => $masjids,
            'q' => $request->q,
        ]);
    }

    public function create()
    {
        $this->ensureSuperAdmin();
        $this->authorize('create', Masjid::class);

        return view('admin.masjid.create', [
            'adminCandidates' => User::query()
                ->whereNull('id_masjid')
                ->where('aktif', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function store(MasjidStoreRequest $request)
    {
        $this->ensureSuperAdmin();
        $this->authorize('create', Masjid::class);

        $this->masjidService->create($request->validated(), $request->user());

        return redirect()->route('admin.masjid.index')
            ->with('status', 'Masjid berjaya ditambah.');
    }

    public function edit(Masjid $masjid)
    {
        $this->ensureSuperAdmin();
        $this->authorize('update', $masjid);

        return view('admin.masjid.edit', [
            'masjid' => $masjid,
            'adminCandidates' => User::query()
                ->where(function ($q) use ($masjid) {
                    $q->whereNull('id_masjid')
                        ->orWhere('id_masjid', $masjid->id);
                })
                ->where('aktif', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function update(MasjidUpdateRequest $request, Masjid $masjid)
    {
        $this->ensureSuperAdmin();
        $this->authorize('update', $masjid);

        $this->masjidService->update($masjid, $request->validated());

        return redirect()->route('admin.masjid.edit', $masjid)
            ->with('status', 'Masjid berjaya dikemaskini.');
    }

    public function destroy(Masjid $masjid)
    {
        $this->ensureSuperAdmin();
        $this->authorize('delete', $masjid);

        $this->masjidService->delete($masjid);

        return redirect()->route('admin.masjid.index')
            ->with('status', 'Masjid berjaya dipadamkan.');
    }

    public function suspend(Masjid $masjid)
    {
        $this->ensureSuperAdmin();
        $this->authorize('update', $masjid);

        $this->masjidService->suspend($masjid);

        return redirect()->route('admin.masjid.index')
            ->with('status', 'Tenant berjaya digantung.');
    }

    public function activate(Masjid $masjid)
    {
        $this->ensureSuperAdmin();
        $this->authorize('update', $masjid);

        $this->masjidService->activate($masjid);

        return redirect()->route('admin.masjid.index')
            ->with('status', 'Tenant berjaya diaktifkan.');
    }

    private function ensureSuperAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->peranan === 'superadmin', 403);
    }
}
