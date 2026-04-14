<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProgramMasjidStoreRequest;
use App\Http\Requests\Admin\ProgramMasjidUpdateRequest;
use App\Models\Masjid;
use App\Models\ProgramMasjid;
use App\Services\ProgramMasjidManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramMasjidManagementController extends Controller
{
    public function __construct(private readonly ProgramMasjidManagementService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ProgramMasjid::class);

        $actor = $request->user();
        $masjidScope = $actor->peranan === 'superadmin' ? null : $actor->id_masjid;
        $status = (string) $request->query('status', 'all');
        $search = trim((string) $request->query('q', ''));

        $query = ProgramMasjid::query()
            ->when($masjidScope, fn ($builder) => $builder->byMasjid($masjidScope))
            ->with('masjid:id,nama')
            ->withCount(['hasil', 'belanja'])
            ->latest('id');

        if ($status === 'active') {
            $query->aktif();
        } elseif ($status === 'inactive') {
            $query->where('aktif', false);
        } elseif ($status === 'linked') {
            $query->where(function ($builder) {
                $builder->has('hasil')->orHas('belanja');
            });
        }

        if ($search !== '') {
            $query->where('nama_program', 'like', "%{$search}%");
        }

        $programMasjid = $query->paginate(15)->withQueryString();

        $baseStats = ProgramMasjid::query()->when($masjidScope, fn ($builder) => $builder->byMasjid($masjidScope));

        $stats = [
            'total' => (clone $baseStats)->count(),
            'active' => (clone $baseStats)->aktif()->count(),
            'linked' => (clone $baseStats)->where(function ($builder) {
                $builder->has('hasil')->orHas('belanja');
            })->count(),
        ];

        return view('admin.program-masjid.index', [
            'programMasjid' => $programMasjid,
            'status' => $status,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ProgramMasjid::class);

        return view('admin.program-masjid.create', [
            'masjidOptions' => $this->masjidOptions($request),
        ]);
    }

    public function store(ProgramMasjidStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', ProgramMasjid::class);

        $programMasjid = $this->service->create($request->user(), $request->validated());

        return redirect()
            ->route('admin.program-masjid.edit', $programMasjid)
            ->with('status', 'Program masjid berjaya ditambah. Kini ia boleh dipautkan pada transaksi hasil atau belanja.');
    }

    public function edit(Request $request, ProgramMasjid $programMasjid): View
    {
        $this->authorize('update', $programMasjid);

        $programMasjid->loadCount(['hasil', 'belanja']);

        return view('admin.program-masjid.edit', [
            'programMasjid' => $programMasjid,
            'masjidOptions' => $this->masjidOptions($request),
        ]);
    }

    public function update(ProgramMasjidUpdateRequest $request, ProgramMasjid $programMasjid): RedirectResponse
    {
        $this->authorize('update', $programMasjid);

        $this->service->update($programMasjid, $request->user(), $request->validated());

        return redirect()
            ->route('admin.program-masjid.edit', $programMasjid)
            ->with('status', 'Program masjid berjaya dikemaskini.');
    }

    public function toggleStatus(Request $request, ProgramMasjid $programMasjid): RedirectResponse
    {
        $this->authorize('toggleStatus', $programMasjid);

        $this->service->toggleStatus($programMasjid, $request->user());

        return redirect()
            ->route('admin.program-masjid.index')
            ->with('status', $programMasjid->fresh()->aktif ? 'Program masjid diaktifkan.' : 'Program masjid dinyahaktifkan. Hanya program aktif patut dipilih untuk transaksi baharu.');
    }

    public function destroy(Request $request, ProgramMasjid $programMasjid): RedirectResponse
    {
        $this->authorize('delete', $programMasjid);

        $this->service->delete($programMasjid, $request->user());

        return redirect()
            ->route('admin.program-masjid.index')
            ->with('status', 'Program masjid berjaya dipadamkan.');
    }

    private function masjidOptions(Request $request)
    {
        if ($request->user()->peranan !== 'superadmin') {
            return Masjid::query()->whereKey($request->user()->id_masjid)->get(['id', 'nama']);
        }

        return Masjid::query()->orderBy('nama')->get(['id', 'nama']);
    }
}
