<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KategoriBelanjaStoreRequest;
use App\Http\Requests\Admin\KategoriBelanjaUpdateRequest;
use App\Models\KategoriBelanja;
use App\Models\Masjid;
use App\Services\KategoriBelanjaManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriBelanjaManagementController extends Controller
{
    public function __construct(private readonly KategoriBelanjaManagementService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', KategoriBelanja::class);

        $actor = $request->user();
        $masjidScope = $actor->peranan === 'superadmin' ? null : $actor->id_masjid;

        $query = KategoriBelanja::query()
            ->when($masjidScope, fn ($builder) => $builder->byMasjid($masjidScope))
            ->with('masjid:id,nama')
            ->latest('id');

        $status = (string) $request->query('status', 'all');
        $search = trim((string) $request->query('q', ''));

        if ($status === 'active') {
            $query->aktif();
        } elseif ($status === 'inactive') {
            $query->where('aktif', false);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('kod', 'like', "%{$search}%")
                    ->orWhere('nama_kategori', 'like', "%{$search}%");
            });
        }

        $kategoriBelanja = $query->paginate(15)->withQueryString();

        $baseStats = KategoriBelanja::query()->when($masjidScope, fn ($builder) => $builder->byMasjid($masjidScope));

        $stats = [
            'total' => (clone $baseStats)->count(),
            'active' => (clone $baseStats)->aktif()->count(),
            'inactive' => (clone $baseStats)->where('aktif', false)->count(),
        ];

        return view('admin.kategori-belanja.index', [
            'kategoriBelanja' => $kategoriBelanja,
            'status' => $status,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', KategoriBelanja::class);

        return view('admin.kategori-belanja.create', [
            'masjidOptions' => $this->masjidOptions($request),
        ]);
    }

    public function store(KategoriBelanjaStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', KategoriBelanja::class);

        $kategoriBelanja = $this->service->create($request->user(), $request->validated());

        return redirect()
            ->route('admin.kategori-belanja.edit', $kategoriBelanja)
            ->with('status', 'Kategori belanja berjaya ditambah.');
    }

    public function edit(Request $request, KategoriBelanja $kategoriBelanja): View
    {
        $this->authorize('update', $kategoriBelanja);

        return view('admin.kategori-belanja.edit', [
            'kategoriBelanja' => $kategoriBelanja,
            'masjidOptions' => $this->masjidOptions($request),
        ]);
    }

    public function update(KategoriBelanjaUpdateRequest $request, KategoriBelanja $kategoriBelanja): RedirectResponse
    {
        $this->authorize('update', $kategoriBelanja);

        $this->service->update($kategoriBelanja, $request->user(), $request->validated());

        return redirect()
            ->route('admin.kategori-belanja.edit', $kategoriBelanja)
            ->with('status', 'Kategori belanja berjaya dikemaskini.');
    }

    public function toggleStatus(Request $request, KategoriBelanja $kategoriBelanja): RedirectResponse
    {
        $this->authorize('toggleStatus', $kategoriBelanja);

        $this->service->toggleStatus($kategoriBelanja, $request->user());

        return redirect()
            ->route('admin.kategori-belanja.index')
            ->with('status', $kategoriBelanja->fresh()->aktif ? 'Kategori diaktifkan.' : 'Kategori dinyahaktifkan.');
    }

    public function destroy(Request $request, KategoriBelanja $kategoriBelanja): RedirectResponse
    {
        $this->authorize('delete', $kategoriBelanja);

        $this->service->delete($kategoriBelanja, $request->user());

        return redirect()
            ->route('admin.kategori-belanja.index')
            ->with('status', 'Kategori belanja berjaya dipadamkan.');
    }

    private function masjidOptions(Request $request)
    {
        if ($request->user()->peranan !== 'superadmin') {
            return Masjid::query()->whereKey($request->user()->id_masjid)->get(['id', 'nama']);
        }

        return Masjid::query()->orderBy('nama')->get(['id', 'nama']);
    }
}
