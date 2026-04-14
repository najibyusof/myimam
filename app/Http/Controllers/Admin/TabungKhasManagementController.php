<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TabungKhasStoreRequest;
use App\Http\Requests\Admin\TabungKhasUpdateRequest;
use App\Models\Masjid;
use App\Models\TabungKhas;
use App\Services\TabungKhasManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TabungKhasManagementController extends Controller
{
    public function __construct(private readonly TabungKhasManagementService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', TabungKhas::class);

        $actor = $request->user();
        $masjidScope = $actor->peranan === 'superadmin' ? null : $actor->id_masjid;
        $status = (string) $request->query('status', 'all');
        $search = trim((string) $request->query('q', ''));

        $query = TabungKhas::query()
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
            $query->where('nama_tabung', 'like', "%{$search}%");
        }

        $tabungKhas = $query->paginate(15)->withQueryString();

        $baseStats = TabungKhas::query()->when($masjidScope, fn ($builder) => $builder->byMasjid($masjidScope));

        $stats = [
            'total' => (clone $baseStats)->count(),
            'active' => (clone $baseStats)->aktif()->count(),
            'linked' => (clone $baseStats)->where(function ($builder) {
                $builder->has('hasil')->orHas('belanja');
            })->count(),
        ];

        return view('admin.tabung-khas.index', [
            'tabungKhas' => $tabungKhas,
            'status' => $status,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', TabungKhas::class);

        return view('admin.tabung-khas.create', [
            'masjidOptions' => $this->masjidOptions($request),
        ]);
    }

    public function store(TabungKhasStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', TabungKhas::class);

        $tabungKhas = $this->service->create($request->user(), $request->validated());

        return redirect()
            ->route('admin.tabung-khas.edit', $tabungKhas)
            ->with('status', 'Tabung khas berjaya ditambah. Kini ia boleh ditetapkan pada transaksi hasil atau belanja.');
    }

    public function edit(Request $request, TabungKhas $tabungKhas): View
    {
        $this->authorize('update', $tabungKhas);

        $tabungKhas->loadCount(['hasil', 'belanja']);

        return view('admin.tabung-khas.edit', [
            'tabungKhas' => $tabungKhas,
            'masjidOptions' => $this->masjidOptions($request),
        ]);
    }

    public function update(TabungKhasUpdateRequest $request, TabungKhas $tabungKhas): RedirectResponse
    {
        $this->authorize('update', $tabungKhas);

        $this->service->update($tabungKhas, $request->user(), $request->validated());

        return redirect()
            ->route('admin.tabung-khas.edit', $tabungKhas)
            ->with('status', 'Tabung khas berjaya dikemaskini.');
    }

    public function toggleStatus(Request $request, TabungKhas $tabungKhas): RedirectResponse
    {
        $this->authorize('toggleStatus', $tabungKhas);

        $this->service->toggleStatus($tabungKhas, $request->user());

        return redirect()
            ->route('admin.tabung-khas.index')
            ->with('status', $tabungKhas->fresh()->aktif ? 'Tabung khas diaktifkan.' : 'Tabung khas dinyahaktifkan. Hanya tabung aktif patut digunakan untuk transaksi baharu.');
    }

    public function destroy(Request $request, TabungKhas $tabungKhas): RedirectResponse
    {
        $this->authorize('delete', $tabungKhas);

        $this->service->delete($tabungKhas, $request->user());

        return redirect()
            ->route('admin.tabung-khas.index')
            ->with('status', 'Tabung khas berjaya dipadamkan.');
    }

    private function masjidOptions(Request $request)
    {
        if ($request->user()->peranan !== 'superadmin') {
            return Masjid::query()->whereKey($request->user()->id_masjid)->get(['id', 'nama']);
        }

        return Masjid::query()->orderBy('nama')->get(['id', 'nama']);
    }
}
