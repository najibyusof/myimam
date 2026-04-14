<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RunningNoGenerateRequest;
use App\Http\Requests\Admin\RunningNoUpdateRequest;
use App\Models\Masjid;
use App\Models\RunningNo;
use App\Services\RunningNoManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RunningNoManagementController extends Controller
{
    public function __construct(private readonly RunningNoManagementService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', RunningNo::class);

        $actor       = $request->user();
        $masjidScope = $actor->peranan === 'superadmin' ? null : $actor->id_masjid;

        $prefix = (string) $request->query('prefix', '');
        $tahun  = (int)    $request->query('tahun', 0);
        $bulan  = (int)    $request->query('bulan', 0);

        $query = RunningNo::query()
            ->when($masjidScope, fn ($q) => $q->where('id_masjid', $masjidScope))
            ->when($prefix !== '', fn ($q) => $q->where('prefix', $prefix))
            ->when($tahun > 0, fn ($q) => $q->where('tahun', $tahun))
            ->when($bulan > 0 && $bulan <= 12, fn ($q) => $q->where('bulan', $bulan))
            ->with('masjid:id,nama')
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->orderBy('prefix');

        $records = $query->paginate(20)->withQueryString();

        $prefixes = RunningNo::query()
            ->when($masjidScope, fn ($q) => $q->where('id_masjid', $masjidScope))
            ->distinct()
            ->orderBy('prefix')
            ->pluck('prefix');

        return view('admin.running-no.index', [
            'records'  => $records,
            'prefixes' => $prefixes,
            'prefix'   => $prefix,
            'tahun'    => $tahun,
            'bulan'    => $bulan,
        ]);
    }

    public function generateForm(Request $request): View
    {
        $this->authorize('generate', RunningNo::class);

        $actor = $request->user();
        $now   = now();

        $masjidOptions = $actor->peranan === 'superadmin'
            ? Masjid::query()->orderBy('nama')->get(['id', 'nama'])
            : collect([$actor->masjid]);

        return view('admin.running-no.generate', [
            'isAdmin'       => $actor->peranan === 'superadmin',
            'masjidOptions' => $masjidOptions,
            'defaultTahun'  => $now->year,
            'defaultBulan'  => $now->month,
            'nomborRujukan' => null,
        ]);
    }

    public function generate(RunningNoGenerateRequest $request): View
    {
        $this->authorize('generate', RunningNo::class);

        $actor    = $request->user();
        $idMasjid = $actor->peranan === 'superadmin'
            ? (int) $request->validated('id_masjid')
            : (int) $actor->id_masjid;

        $prefix = strtoupper((string) $request->validated('prefix'));
        $tahun  = (int) $request->validated('tahun');
        $bulan  = (int) $request->validated('bulan');

        $nomborRujukan = $this->service->generate($idMasjid, $prefix, $tahun, $bulan);

        $masjidOptions = $actor->peranan === 'superadmin'
            ? Masjid::query()->orderBy('nama')->get(['id', 'nama'])
            : collect([$actor->masjid]);

        return view('admin.running-no.generate', [
            'isAdmin'        => $actor->peranan === 'superadmin',
            'masjidOptions'  => $masjidOptions,
            'defaultTahun'   => $tahun,
            'defaultBulan'   => $bulan,
            'nomborRujukan'  => $nomborRujukan,
            'lastPrefix'     => $prefix,
            'lastIdMasjid'   => $idMasjid,
        ]);
    }

    public function edit(int $idMasjid, string $prefix, int $tahun, int $bulan): View
    {
        $record = RunningNo::query()
            ->forPeriod($idMasjid, $prefix, $tahun, $bulan)
            ->with('masjid:id,nama')
            ->firstOrFail();

        $this->authorize('update', $record);

        return view('admin.running-no.edit', ['record' => $record]);
    }

    public function update(
        RunningNoUpdateRequest $request,
        int $idMasjid,
        string $prefix,
        int $tahun,
        int $bulan
    ): RedirectResponse {
        $record = RunningNo::query()
            ->forPeriod($idMasjid, $prefix, $tahun, $bulan)
            ->firstOrFail();

        $this->authorize('update', $record);

        $this->service->resetCounter($record, (int) $request->validated('last_no'));

        return redirect()->route('admin.running-no.index')
            ->with('success', "Kaunter [{$prefix}] berjaya dikemaskini.");
    }
}
