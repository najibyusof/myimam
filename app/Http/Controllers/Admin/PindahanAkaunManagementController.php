<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PindahanAkaunStoreRequest;
use App\Http\Requests\Admin\PindahanAkaunUpdateRequest;
use App\Models\Akaun;
use App\Models\PindahanAkaun;
use App\Services\PindahanAkaunManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PindahanAkaunManagementController extends Controller
{
    public function __construct(private readonly PindahanAkaunManagementService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PindahanAkaun::class);

        $actor = $request->user();
        $masjidScope = $actor->hasRole('Admin') ? null : $actor->id_masjid;

        $akaunId  = (int) $request->query('akaun_id', 0);
        $dateFrom = (string) $request->query('date_from', '');
        $dateTo   = (string) $request->query('date_to', '');

        $query = PindahanAkaun::query()
            ->when($masjidScope, fn ($q) => $q->byMasjid($masjidScope))
            ->with(['dariAkaun:id,nama_akaun', 'keAkaun:id,nama_akaun'])
            ->latest('tarikh')
            ->latest('id');

        if ($akaunId > 0) {
            $query->forAkaun($akaunId);
        }

        if ($dateFrom !== '' && $dateTo !== '') {
            $query->betweenDates($dateFrom, $dateTo);
        }

        $records = $query->paginate(15)->withQueryString();

        $akaunOptions = Akaun::query()
            ->when($masjidScope, fn ($q) => $q->byMasjid($masjidScope))
            ->aktif()
            ->orderBy('nama_akaun')
            ->get(['id', 'nama_akaun']);

        $stats = [
            'total'  => PindahanAkaun::query()
                ->when($masjidScope, fn ($q) => $q->byMasjid($masjidScope))
                ->count(),
            'jumlah' => PindahanAkaun::query()
                ->when($masjidScope, fn ($q) => $q->byMasjid($masjidScope))
                ->sum('amaun'),
        ];

        return view('admin.pindahan-akaun.index', [
            'records'      => $records,
            'stats'        => $stats,
            'akaunOptions' => $akaunOptions,
            'akaunId'      => $akaunId,
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', PindahanAkaun::class);

        return view('admin.pindahan-akaun.create', $this->formData($request));
    }

    public function store(PindahanAkaunStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', PindahanAkaun::class);

        $record = $this->service->create($request->user(), $request->validated());

        return redirect()
            ->route('admin.pindahan-akaun.edit', $record)
            ->with('status', 'Pindahan akaun berjaya disimpan.');
    }

    public function edit(Request $request, PindahanAkaun $pindahanAkaun): View
    {
        $this->authorize('update', $pindahanAkaun);

        return view('admin.pindahan-akaun.edit', $this->formData($request) + [
            'record' => $pindahanAkaun->load(['dariAkaun', 'keAkaun']),
        ]);
    }

    public function update(PindahanAkaunUpdateRequest $request, PindahanAkaun $pindahanAkaun): RedirectResponse
    {
        $this->authorize('update', $pindahanAkaun);

        $this->service->update($pindahanAkaun, $request->user(), $request->validated());

        return redirect()
            ->route('admin.pindahan-akaun.edit', $pindahanAkaun)
            ->with('status', 'Pindahan akaun berjaya dikemaskini.');
    }

    public function destroy(Request $request, PindahanAkaun $pindahanAkaun): RedirectResponse
    {
        $this->authorize('delete', $pindahanAkaun);

        $this->service->delete($pindahanAkaun, $request->user());

        return redirect()
            ->route('admin.pindahan-akaun.index')
            ->with('status', 'Pindahan akaun berjaya dipadam.');
    }

    private function formData(Request $request): array
    {
        $actor = $request->user();
        $masjidScope = $actor->hasRole('Admin') ? null : $actor->id_masjid;

        $akaunOptions = Akaun::query()
            ->when($masjidScope, fn ($q) => $q->byMasjid($masjidScope))
            ->aktif()
            ->orderBy('nama_akaun')
            ->get(['id', 'nama_akaun']);

        return ['akaunOptions' => $akaunOptions];
    }
}
