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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HasilManagementController extends Controller
{
    public function __construct(private readonly HasilManagementService $service) {}

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
            ->when($masjidScope, fn($builder) => $builder->byMasjid($masjidScope))
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

        $baseStats = Hasil::query()->when($masjidScope, fn($builder) => $builder->byMasjid($masjidScope));

        $stats = [
            'total' => (clone $baseStats)->count(),
            'jumlah' => (float) ((clone $baseStats)->sum('jumlah') ?: 0),
            'jumaat' => (clone $baseStats)->jumaat()->count(),
        ];

        $akaunOptions = Akaun::query()
            ->when($masjidScope, fn($builder) => $builder->byMasjid($masjidScope))
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

        return view('admin.hasil.create', $this->formData($request) + [
            'formMode' => 'regular',
        ]);
    }

    public function store(HasilStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Hasil::class);

        $data = $request->validated();
        $data['is_jumaat'] = false;

        $hasil = $this->service->create($request->user(), $data);

        return redirect()
            ->route('admin.hasil.edit', $hasil)
            ->with('status', 'Transaksi hasil berjaya direkodkan.');
    }

    public function createJumaat(Request $request): View|RedirectResponse
    {
        $permissionRedirect = $this->guardJumaatPermission($request, 'hasil.create');
        if ($permissionRedirect) {
            return $permissionRedirect;
        }

        $this->authorize('create', Hasil::class);

        return view('admin.hasil.create-jumaat', $this->formData($request) + [
            'formMode' => 'jumaat',
        ]);
    }

    public function storeJumaat(HasilStoreRequest $request): RedirectResponse
    {
        $permissionRedirect = $this->guardJumaatPermission($request, 'hasil.create');
        if ($permissionRedirect) {
            return $permissionRedirect;
        }

        $this->authorize('create', Hasil::class);

        $data = $request->validated();
        $data = $this->prepareJumaatData($request, $data);
        $data['is_jumaat'] = true;

        $hasil = $this->service->create($request->user(), $data);

        return redirect()
            ->route('admin.hasil.jumaat.edit', $hasil)
            ->with('status', 'Transaksi kutipan Jumaat berjaya direkodkan.');
    }

    public function edit(Request $request, Hasil $hasil): View|RedirectResponse
    {
        $this->authorize('update', $hasil);

        if ($hasil->jenis_jumaat !== null) {
            return redirect()
                ->route('admin.hasil.jumaat.edit', $hasil)
                ->with('error', __('hasil.guard.use_jumaat_edit'));
        }

        return view('admin.hasil.edit', $this->formData($request, $hasil) + [
            'hasilRecord' => $hasil,
            'formMode' => 'regular',
        ]);
    }

    public function update(HasilUpdateRequest $request, Hasil $hasil): RedirectResponse
    {
        $this->authorize('update', $hasil);

        if ($hasil->jenis_jumaat !== null) {
            return redirect()
                ->route('admin.hasil.jumaat.edit', $hasil)
                ->with('error', __('hasil.guard.use_jumaat_edit'));
        }

        $data = $request->validated();
        $data['is_jumaat'] = false;

        $this->service->update($hasil, $request->user(), $data);

        return redirect()
            ->route('admin.hasil.edit', $hasil)
            ->with('status', 'Transaksi hasil berjaya dikemaskini.');
    }

    public function editJumaat(Request $request, Hasil $hasil): View|RedirectResponse
    {
        $permissionRedirect = $this->guardJumaatPermission($request, 'hasil.update');
        if ($permissionRedirect) {
            return $permissionRedirect;
        }

        $this->authorize('update', $hasil);

        if ($hasil->jenis_jumaat === null) {
            return redirect()
                ->route('admin.hasil.edit', $hasil)
                ->with('error', __('hasil.guard.not_jumaat_record'));
        }

        return view('admin.hasil.edit-jumaat', $this->formData($request, $hasil) + [
            'hasilRecord' => $hasil,
            'formMode' => 'jumaat',
        ]);
    }

    public function updateJumaat(HasilUpdateRequest $request, Hasil $hasil): RedirectResponse
    {
        $permissionRedirect = $this->guardJumaatPermission($request, 'hasil.update');
        if ($permissionRedirect) {
            return $permissionRedirect;
        }

        $this->authorize('update', $hasil);

        if ($hasil->jenis_jumaat === null) {
            return redirect()
                ->route('admin.hasil.edit', $hasil)
                ->with('error', __('hasil.guard.not_jumaat_record'));
        }

        $data = $request->validated();
        $data = $this->prepareJumaatData($request, $data, $hasil);
        $data['is_jumaat'] = true;

        $this->service->update($hasil, $request->user(), $data);

        return redirect()
            ->route('admin.hasil.jumaat.edit', $hasil)
            ->with('status', 'Transaksi kutipan Jumaat berjaya dikemaskini.');
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
            ->when($selectedMasjidId > 0, fn($builder) => $builder->byMasjid($selectedMasjidId))
            ->when($selectedMasjidId <= 0 && $masjidScope, fn($builder) => $builder->byMasjid($masjidScope))
            ->aktif()
            ->orderBy('nama_akaun')
            ->get(['id', 'nama_akaun', 'id_masjid']);

        $sumberHasilOptions = SumberHasil::query()
            ->when($selectedMasjidId > 0, fn($builder) => $builder->byMasjid($selectedMasjidId))
            ->when($selectedMasjidId <= 0 && $masjidScope, fn($builder) => $builder->byMasjid($masjidScope))
            ->aktif()
            ->orderBy('nama_sumber')
            ->get(['id', 'nama_sumber', 'id_masjid']);

        $tabungKhasOptions = TabungKhas::query()
            ->when($selectedMasjidId > 0, fn($builder) => $builder->byMasjid($selectedMasjidId))
            ->when($selectedMasjidId <= 0 && $masjidScope, fn($builder) => $builder->byMasjid($masjidScope))
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

    private function guardJumaatPermission(Request $request, string $permission): ?RedirectResponse
    {
        $actor = $request->user();

        if (!$actor) {
            return redirect()
                ->route('admin.hasil.index')
                ->with('error', __('hasil.guard.permission_denied_generic'));
        }

        if ($actor->hasRole('Admin') || $actor->can($permission)) {
            return null;
        }

        $message = $permission === 'hasil.create'
            ? __('hasil.guard.permission_denied_create_jumaat')
            : __('hasil.guard.permission_denied_update_jumaat');

        return redirect()
            ->route('admin.hasil.index')
            ->with('error', $message);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function prepareJumaatData(Request $request, array $data, ?Hasil $hasil = null): array
    {
        $data['id_tabung_khas'] = null;

        if ($hasil !== null && !empty($hasil->id_sumber_hasil)) {
            $data['id_sumber_hasil'] = (int) $hasil->id_sumber_hasil;

            return $data;
        }

        $actor = $request->user();
        $masjidId = $actor->peranan === 'superadmin'
            ? (int) ($data['id_masjid'] ?? 0)
            : (int) ($actor->id_masjid ?? 0);

        if ($masjidId <= 0) {
            throw ValidationException::withMessages([
                'id_masjid' => __('hasil.guard.missing_masjid_for_jumaat'),
            ]);
        }

        $sumberId = SumberHasil::query()
            ->where('id_masjid', $masjidId)
            ->where('aktif', true)
            ->orderByRaw("CASE WHEN LOWER(nama_sumber) LIKE '%jumaat%' OR LOWER(kod) LIKE '%jmt%' OR LOWER(jenis) LIKE '%jumaat%' THEN 0 ELSE 1 END")
            ->orderBy('nama_sumber')
            ->value('id');

        if (!$sumberId) {
            throw ValidationException::withMessages([
                'id_sumber_hasil' => __('hasil.guard.missing_jumaat_source'),
            ]);
        }

        $data['id_sumber_hasil'] = (int) $sumberId;

        return $data;
    }
}
