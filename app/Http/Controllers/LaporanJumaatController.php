<?php

namespace App\Http\Controllers;

use App\Exports\LaporanJumaatExport;
use App\Models\Hasil;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class LaporanJumaatController extends Controller
{
    public function index(Request $request): View
    {
        $data = $this->buildLaporanData($request);

        return view('laporan.jumaat', $data);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->buildLaporanData($request);
        $filename = 'laporan-kutipan-jumaat-' . now()->format('Ymd_His') . '.pdf';

        return Pdf::loadView('laporan.jumaat-pdf', $data)->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $data = $this->buildLaporanData($request);
        $filename = 'laporan-kutipan-jumaat-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new LaporanJumaatExport($data), $filename);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildLaporanData(Request $request): array
    {
        $actor = $request->user();
        $idMasjid = (int) ($actor?->id_masjid ?? 0);
        $isSuperadmin = $this->isSuperadmin($request);

        abort_if($idMasjid <= 0 && !$isSuperadmin, 403);

        $tahunSemasa = (int) now()->format('Y');
        $tahun = (int) $request->query('tahun', $tahunSemasa);
        if ($tahun < 2000 || $tahun > $tahunSemasa + 1) {
            $tahun = $tahunSemasa;
        }

        $jenisPaparan = (string) $request->query('jenis_paparan', 'ringkasan_bulanan');
        if (!in_array($jenisPaparan, ['ringkasan_bulanan', 'senarai_jumaat'], true)) {
            $jenisPaparan = 'ringkasan_bulanan';
        }

        $bulan = (int) $request->query('bulan', 0);
        if ($bulan < 1 || $bulan > 12) {
            $bulan = 0;
        }

        $ringkasan = $this->buildRingkasanBulanan($tahun, $idMasjid, $isSuperadmin);
        $jumlahSetahun = (float) $ringkasan->sum('jumlah');

        $senariJumaat = collect();
        if ($jenisPaparan === 'senarai_jumaat') {
            $senariJumaat = $this->buildSenariJumaat($tahun, $bulan, $idMasjid, $isSuperadmin);
        }

        $namaBulan = $this->namaBulan();

        return [
            'filters' => [
                'tahun' => $tahun,
                'jenis_paparan' => $jenisPaparan,
                'bulan' => $bulan,
                'bulan_nama' => $bulan > 0 ? $namaBulan[$bulan] : null,
            ],
            'rows' => $ringkasan,
            'jumlah_setahun' => $jumlahSetahun,
            'senarai_rows' => $senariJumaat,
            'chart_labels' => $ringkasan->pluck('bulan')->values(),
            'chart_data' => $ringkasan->pluck('jumlah')->values(),
            'is_superadmin' => $isSuperadmin,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function buildRingkasanBulanan(int $tahun, int $idMasjid, bool $isSuperadmin): Collection
    {
        $query = Hasil::query()
            ->when($isSuperadmin, fn($builder) => $builder->withoutTenantScope())
            ->jumaat()
            ->whereYear('tarikh', $tahun);

        if (!$isSuperadmin) {
            $query->byMasjid($idMasjid);
        }

        $aggregated = $query
            ->selectRaw('MONTH(tarikh) as bulan_no, SUM(jumlah) as jumlah, COUNT(*) as bil_rekod')
            ->groupByRaw('MONTH(tarikh)')
            ->orderByRaw('MONTH(tarikh)')
            ->get()
            ->keyBy(fn($row) => (int) $row->bulan_no);

        $namaBulan = $this->namaBulan();

        return collect(range(1, 12))->map(function (int $bulan) use ($aggregated, $namaBulan): array {
            $row = $aggregated->get($bulan);

            return [
                'bulan_no' => $bulan,
                'bulan' => $namaBulan[$bulan],
                'jumlah' => (float) ($row->jumlah ?? 0),
                'bil_rekod' => (int) ($row->bil_rekod ?? 0),
            ];
        });
    }

    /**
     * @return Collection<int, Hasil>
     */
    private function buildDetailBulanan(int $tahun, int $bulan, int $idMasjid, bool $isSuperadmin): Collection
    {
        $query = Hasil::query()
            ->with(['akaun:id,nama_akaun', 'masjid:id,nama'])
            ->when($isSuperadmin, fn($builder) => $builder->withoutTenantScope())
            ->jumaat()
            ->whereYear('tarikh', $tahun)
            ->whereMonth('tarikh', $bulan)
            ->orderBy('tarikh')
            ->orderBy('id');

        if (!$isSuperadmin) {
            $query->byMasjid($idMasjid);
        }

        return $query->get(['id', 'id_masjid', 'tarikh', 'no_resit', 'id_akaun', 'jumlah', 'catatan']);
    }

    /**
     * Build list of Friday collections grouped by date for the year
     * 
     * @return Collection<int, array<string, mixed>>
     */
    private function buildSenariJumaat(int $tahun, int $bulan, int $idMasjid, bool $isSuperadmin): Collection
    {
        $query = Hasil::query()
            ->when($isSuperadmin, fn($builder) => $builder->withoutTenantScope())
            ->jumaat()
            ->whereYear('tarikh', $tahun);

        if ($bulan > 0) {
            $query->whereMonth('tarikh', $bulan);
        }

        if (!$isSuperadmin) {
            $query->byMasjid($idMasjid);
        }

        $records = $query
            ->selectRaw('DATE(tarikh) as tarikh, SUM(jumlah) as jumlah_kutipan, COUNT(*) as bil_rekod')
            ->groupByRaw('DATE(tarikh)')
            ->orderBy('tarikh', 'asc')
            ->get();

        return $records->map(function ($row): array {
            return [
                'tarikh' => $row->tarikh,
                'jumlah_kutipan' => (float) $row->jumlah_kutipan,
                'bil_rekod' => (int) $row->bil_rekod,
            ];
        });
    }

    /**
     * @return array<int, string>
     */
    private function namaBulan(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Mac',
            4 => 'April',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Julai',
            8 => 'Ogos',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Disember',
        ];
    }

    private function isSuperadmin(Request $request): bool
    {
        $actor = $request->user();

        if (!$actor) {
            return false;
        }

        return ($actor->peranan ?? null) === 'superadmin'
            || $actor->hasRole('Superadmin')
            || $actor->hasRole('SuperAdmin')
            || $actor->hasRole('superadmin');
    }
}
