<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SumberHasilStoreRequest;
use App\Http\Requests\Admin\SumberHasilUpdateRequest;
use App\Models\Masjid;
use App\Models\SumberHasil;
use App\Services\SumberHasilManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SumberHasilManagementController extends Controller
{
    public function __construct(private readonly SumberHasilManagementService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SumberHasil::class);

        $actor = $request->user();
        $masjidScope = $actor->hasRole('Admin') ? null : $actor->id_masjid;

        $query = SumberHasil::query()
            ->when($masjidScope, fn ($builder) => $builder->byMasjid($masjidScope))
            ->with('masjid:id,nama')
            ->latest('id');

        $status = (string) $request->query('status', 'all');
        $jenis = trim((string) $request->query('jenis', ''));
        $search = trim((string) $request->query('q', ''));

        if ($status === 'active') {
            $query->aktif();
        } elseif ($status === 'inactive') {
            $query->where('aktif', false);
        }

        if ($jenis !== '') {
            $query->where('jenis', $jenis);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('kod', 'like', "%{$search}%")
                    ->orWhere('nama_sumber', 'like', "%{$search}%")
                    ->orWhere('jenis', 'like', "%{$search}%");
            });
        }

        $sumberHasil = $query->paginate(15)->withQueryString();

        $baseStats = SumberHasil::query()->when($masjidScope, fn ($builder) => $builder->byMasjid($masjidScope));

        $stats = [
            'total' => (clone $baseStats)->count(),
            'active' => (clone $baseStats)->aktif()->count(),
            'inactive' => (clone $baseStats)->where('aktif', false)->count(),
        ];

        $jenisOptions = SumberHasil::query()
            ->when($masjidScope, fn ($builder) => $builder->byMasjid($masjidScope))
            ->select('jenis')
            ->distinct()
            ->orderBy('jenis')
            ->pluck('jenis');

        return view('admin.sumber-hasil.index', [
            'sumberHasil' => $sumberHasil,
            'status' => $status,
            'jenis' => $jenis,
            'search' => $search,
            'stats' => $stats,
            'jenisOptions' => $jenisOptions,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', SumberHasil::class);

        return view('admin.sumber-hasil.create', [
            'masjidOptions' => $this->masjidOptions($request),
        ]);
    }

    public function store(SumberHasilStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', SumberHasil::class);

        $sumberHasil = $this->service->create($request->user(), $request->validated());

        return redirect()
            ->route('admin.sumber-hasil.edit', $sumberHasil)
            ->with('status', 'Sumber hasil berjaya ditambah.');
    }

    public function edit(Request $request, SumberHasil $sumberHasil): View
    {
        $this->authorize('update', $sumberHasil);

        return view('admin.sumber-hasil.edit', [
            'sumberHasil' => $sumberHasil,
            'masjidOptions' => $this->masjidOptions($request),
        ]);
    }

    public function update(SumberHasilUpdateRequest $request, SumberHasil $sumberHasil): RedirectResponse
    {
        $this->authorize('update', $sumberHasil);

        $this->service->update($sumberHasil, $request->user(), $request->validated());

        return redirect()
            ->route('admin.sumber-hasil.edit', $sumberHasil)
            ->with('status', 'Sumber hasil berjaya dikemaskini.');
    }

    public function toggleStatus(Request $request, SumberHasil $sumberHasil): RedirectResponse
    {
        $this->authorize('toggleStatus', $sumberHasil);

        $this->service->toggleStatus($sumberHasil, $request->user());

        return redirect()
            ->route('admin.sumber-hasil.index')
            ->with('status', $sumberHasil->fresh()->aktif ? 'Sumber hasil diaktifkan.' : 'Sumber hasil dinyahaktifkan.');
    }

    public function destroy(Request $request, SumberHasil $sumberHasil): RedirectResponse
    {
        $this->authorize('delete', $sumberHasil);

        $this->service->delete($sumberHasil, $request->user());

        return redirect()
            ->route('admin.sumber-hasil.index')
            ->with('status', 'Sumber hasil berjaya dipadamkan.');
    }

    private function masjidOptions(Request $request)
    {
        if (!$request->user()->hasRole('Admin')) {
            return Masjid::query()->whereKey($request->user()->id_masjid)->get(['id', 'nama']);
        }

        return Masjid::query()->orderBy('nama')->get(['id', 'nama']);
    }
}
