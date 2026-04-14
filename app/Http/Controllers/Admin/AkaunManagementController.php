<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AkaunStoreRequest;
use App\Http\Requests\Admin\AkaunUpdateRequest;
use App\Models\Akaun;
use App\Models\Masjid;
use App\Services\AkaunManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AkaunManagementController extends Controller
{
    public function __construct(private readonly AkaunManagementService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Akaun::class);

        $actor = $request->user();
        $masjidScope = $actor->peranan === 'superadmin' ? null : $actor->id_masjid;

        $query = Akaun::query()
            ->when($masjidScope, fn ($builder) => $builder->byMasjid($masjidScope))
            ->with('masjid:id,nama')
            ->latest('id');

        $jenis = (string) $request->query('jenis', 'all');
        $status = (string) $request->query('status', 'all');
        $search = trim((string) $request->query('q', ''));

        if ($jenis === 'tunai') {
            $query->tunai();
        } elseif ($jenis === 'bank') {
            $query->jenis('bank');
        }

        if ($status === 'active') {
            $query->aktif();
        } elseif ($status === 'inactive') {
            $query->where('status_aktif', false);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('nama_akaun', 'like', "%{$search}%")
                    ->orWhere('no_akaun', 'like', "%{$search}%")
                    ->orWhere('nama_bank', 'like', "%{$search}%");
            });
        }

        $akaun = $query->paginate(15)->withQueryString();

        $baseStats = Akaun::query()->when($masjidScope, fn ($builder) => $builder->byMasjid($masjidScope));

        $stats = [
            'total' => (clone $baseStats)->count(),
            'active' => (clone $baseStats)->aktif()->count(),
            'tunai' => (clone $baseStats)->tunai()->count(),
            'bank' => (clone $baseStats)->jenis('bank')->count(),
        ];

        return view('admin.akaun.index', [
            'akaun' => $akaun,
            'jenis' => $jenis,
            'status' => $status,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Akaun::class);

        return view('admin.akaun.create', [
            'masjidOptions' => $this->masjidOptions($request),
        ]);
    }

    public function store(AkaunStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Akaun::class);

        $akaun = $this->service->create($request->user(), $request->validated());

        return redirect()
            ->route('admin.akaun.edit', $akaun)
            ->with('status', 'Akaun berjaya ditambah.');
    }

    public function edit(Request $request, Akaun $akaun): View
    {
        $this->authorize('update', $akaun);

        return view('admin.akaun.edit', [
            'akaun' => $akaun,
            'masjidOptions' => $this->masjidOptions($request),
        ]);
    }

    public function update(AkaunUpdateRequest $request, Akaun $akaun): RedirectResponse
    {
        $this->authorize('update', $akaun);

        $this->service->update($akaun, $request->user(), $request->validated());

        return redirect()
            ->route('admin.akaun.edit', $akaun)
            ->with('status', 'Akaun berjaya dikemaskini.');
    }

    public function destroy(Request $request, Akaun $akaun): RedirectResponse
    {
        $this->authorize('delete', $akaun);

        $this->service->delete($akaun, $request->user());

        return redirect()
            ->route('admin.akaun.index')
            ->with('status', 'Akaun berjaya dipadamkan.');
    }

    private function masjidOptions(Request $request)
    {
        if ($request->user()->peranan !== 'superadmin') {
            return Masjid::query()
                ->whereKey($request->user()->id_masjid)
                ->get(['id', 'nama']);
        }

        return Masjid::query()->orderBy('nama')->get(['id', 'nama']);
    }
}
