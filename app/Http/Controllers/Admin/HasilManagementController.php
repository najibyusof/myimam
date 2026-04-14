<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HasilStoreRequest;
use App\Http\Requests\Admin\HasilUpdateRequest;
use App\Models\Akaun;
use App\Models\Hasil;
use App\Models\Masjid;
use App\Models\SumberHasil;
use App\Models\TabungKhas;
use App\Services\HasilManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HasilManagementController extends Controller
{
    public function __construct(private readonly HasilManagementService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Hasil::class);

        $actor = $request->user();
        $masjidScope = $actor->peranan === 'superadmin' ? null : $actor->id_masjid;
        $from = (string) $request->query('from', '');
        $to = (string) $request->query('to', '');
        $akaunId = (int) $request->query('akaun_id', 0);
        $jumaat = (string) $request->query('jumaat', 'all');

        $query = Hasil::query()
            ->when($masjidScope, fn ($builder) => $builder->byMasjid($masjidScope))
            ->with(['masjid:id,nama', 'akaun:id,nama_akaun', 'sumberHasil:id,nama_sumber', 'tabungKhas:id,nama_tabung'])
            ->latest('tarikh')
            ->latest('id');

        if ($from !== '' && $to !== '') {
            $query->betweenDates($from, $to);
        } elseif ($from !== '') {
            $query->whereDate('tarikh', '>=', $from);
        } elseif ($to !== '') {
            $query->whereDate('tarikh', '<=', $to);
        }

        if ($akaunId > 0) {
            $query->byAkaun($akaunId);
        }

        if ($jumaat === 'yes') {
            $query->jumaat();
        } elseif ($jumaat === 'no') {
            $query->whereNull('jenis_jumaat');
        }

        $hasil = $query->paginate(15)->withQueryString();

        $baseStats = Hasil::query()->when($masjidScope, fn ($builder) => $builder->byMasjid($masjidScope));

        $stats = [
            'total' => (clone $baseStats)->count(),
            'jumlah' => (float) ((clone $baseStats)->sum('jumlah') ?: 0),
            'jumaat' => (clone $baseStats)->jumaat()->count(),
        ];

        $akaunOptions = Akaun::query()
            ->when($masjidScope, fn ($builder) => $builder->byMasjid($masjidScope))
            ->aktif()
            ->orderBy('nama_akaun')
            ->get(['id', 'nama_akaun']);

        return view('admin.hasil.index', [
            'hasil' => $hasil,
            'stats' => $stats,
            'akaunOptions' => $akaunOptions,
            'from' => $from,
            'to' => $to,
            'akaunId' => $akaunId,
            'jumaat' => $jumaat,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Hasil::class);

        return view('admin.hasil.create', $this->formData($request));
    }

    public function store(HasilStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Hasil::class);

        $hasil = $this->service->create($request->user(), $request->validated());

        return redirect()
            ->route('admin.hasil.edit', $hasil)
            ->with('status', 'Transaksi hasil berjaya direkodkan.');
    }

    public function edit(Request $request, Hasil $hasil): View
    {
        $this->authorize('update', $hasil);

        return view('admin.hasil.edit', $this->formData($request, $hasil) + [
            'hasilRecord' => $hasil,
        ]);
    }

    public function update(HasilUpdateRequest $request, Hasil $hasil): RedirectResponse
    {
        $this->authorize('update', $hasil);

        $this->service->update($hasil, $request->user(), $request->validated());

        return redirect()
            ->route('admin.hasil.edit', $hasil)
            ->with('status', 'Transaksi hasil berjaya dikemaskini.');
    }

    public function destroy(Request $request, Hasil $hasil): RedirectResponse
    {
        $this->authorize('delete', $hasil);

        $this->service->delete($hasil, $request->user());

        return redirect()
            ->route('admin.hasil.index')
            ->with('status', 'Transaksi hasil berjaya dipadamkan.');
    }

    private function formData(Request $request, ?Hasil $hasil = null): array
    {
        $selectedMasjidId = (int) old('id_masjid', $hasil?->id_masjid ?? $request->user()->id_masjid);
        $masjidScope = $request->user()->peranan === 'superadmin' ? null : $request->user()->id_masjid;

        $akaunOptions = Akaun::query()
            ->when($selectedMasjidId > 0, fn ($builder) => $builder->byMasjid($selectedMasjidId))
            ->when($selectedMasjidId <= 0 && $masjidScope, fn ($builder) => $builder->byMasjid($masjidScope))
            ->aktif()
            ->orderBy('nama_akaun')
            ->get(['id', 'nama_akaun', 'id_masjid']);

        $sumberHasilOptions = SumberHasil::query()
            ->when($selectedMasjidId > 0, fn ($builder) => $builder->byMasjid($selectedMasjidId))
            ->when($selectedMasjidId <= 0 && $masjidScope, fn ($builder) => $builder->byMasjid($masjidScope))
            ->aktif()
            ->orderBy('nama_sumber')
            ->get(['id', 'nama_sumber', 'id_masjid']);

        $tabungKhasOptions = TabungKhas::query()
            ->when($selectedMasjidId > 0, fn ($builder) => $builder->byMasjid($selectedMasjidId))
            ->when($selectedMasjidId <= 0 && $masjidScope, fn ($builder) => $builder->byMasjid($masjidScope))
            ->aktif()
            ->orderBy('nama_tabung')
            ->get(['id', 'nama_tabung', 'id_masjid']);

        return [
            'masjidOptions' => $request->user()->peranan === 'superadmin'
                ? Masjid::query()->orderBy('nama')->get(['id', 'nama'])
                : Masjid::query()->whereKey($request->user()->id_masjid)->get(['id', 'nama']),
            'akaunOptions' => $akaunOptions,
            'sumberHasilOptions' => $sumberHasilOptions,
            'tabungKhasOptions' => $tabungKhasOptions,
        ];
    }
}
