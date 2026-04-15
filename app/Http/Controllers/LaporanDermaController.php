<?php

namespace App\Http\Controllers;

use App\Exports\LaporanDermaExport;
use App\Models\Hasil;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class LaporanDermaController extends Controller
{
    public function index(Request $request): View
    {
        $data = $this->buildLaporanData($request);

        return view('laporan.derma', $data);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->buildLaporanData($request);
        $filename = 'laporan-derma-' . now()->format('Ymd_His') . '.pdf';

        return Pdf::loadView('laporan.derma-pdf', $data)->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $data = $this->buildLaporanData($request);
        $filename = 'laporan-derma-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new LaporanDermaExport($data), $filename);
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

        $hariIni = Carbon::today();
        $tarikhDari = Carbon::parse($request->query('tarikh_dari', $hariIni->copy()->startOfMonth()))->toDateString();
        $tarikhHingga = Carbon::parse($request->query('tarikh_hingga', $hariIni))->toDateString();

        // Validate date range
        if ($tarikhDari > $tarikhHingga) {
            $tarikhDari = $hariIni->copy()->startOfMonth()->toDateString();
            $tarikhHingga = $hariIni->toDateString();
        }

        $jenisPaparan = (string) $request->query('jenis_paparan', 'ringkasan_sumber');
        if (!in_array($jenisPaparan, ['ringkasan_sumber', 'ringkasan_bulan', 'senarai_transaksi'], true)) {
            $jenisPaparan = 'ringkasan_sumber';
        }

        $ringkasan = $this->buildRingkasanSumber($tarikhDari, $tarikhHingga, $idMasjid, $isSuperadmin);
        $jumlahKeseluruhan = (float) $ringkasan->sum('jumlah');

        $ringkasanBulan = collect();
        if ($jenisPaparan === 'ringkasan_bulan') {
            $ringkasanBulan = $this->buildRingkasanBulan($tarikhDari, $tarikhHingga, $idMasjid, $isSuperadmin);
            $jumlahKeseluruhan = (float) $ringkasanBulan->sum('jumlah');
        }

        $senariTransaksi = collect();
        if ($jenisPaparan === 'senarai_transaksi') {
            $senariTransaksi = $this->buildSenariTransaksi($tarikhDari, $tarikhHingga, $idMasjid, $isSuperadmin);
            $jumlahKeseluruhan = (float) $senariTransaksi->sum('jumlah');
        }

        return [
            'filters' => [
                'tarikh_dari' => $tarikhDari,
                'tarikh_hingga' => $tarikhHingga,
                'jenis_paparan' => $jenisPaparan,
            ],
            'rows' => $ringkasan,
            'ringkasan_bulan' => $ringkasanBulan,
            'senarai_rows' => $senariTransaksi,
            'jumlah_keseluruhan' => $jumlahKeseluruhan,
            'is_superadmin' => $isSuperadmin,
        ];
    }

    /**
     * Build summary grouped by source
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function buildRingkasanSumber(string $tarikhDari, string $tarikhHingga, int $idMasjid, bool $isSuperadmin): Collection
    {
        $query = Hasil::query()
            ->when($isSuperadmin, fn($builder) => $builder->withoutTenantScope())
            ->whereNull('jenis_jumaat')
            ->whereBetween('tarikh', [$tarikhDari, $tarikhHingga]);

        if (!$isSuperadmin) {
            $query->byMasjid($idMasjid);
        }

        $aggregated = $query
            ->selectRaw('id_sumber_hasil, sumber_hasil.nama_sumber, SUM(jumlah) as jumlah, COUNT(*) as bil_rekod')
            ->leftJoin('sumber_hasil', 'hasil.id_sumber_hasil', '=', 'sumber_hasil.id')
            ->groupBy('id_sumber_hasil', 'sumber_hasil.nama_sumber')
            ->orderBy('sumber_hasil.nama_sumber')
            ->get();

        return $aggregated->map(function ($row): array {
            return [
                'sumber' => $row->nama_sumber ?: 'Sumber Tidak Diketahui',
                'jumlah' => (float) $row->jumlah,
                'bil_rekod' => (int) $row->bil_rekod,
            ];
        });
    }

    /**
     * Build summary grouped by month
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function buildRingkasanBulan(string $tarikhDari, string $tarikhHingga, int $idMasjid, bool $isSuperadmin): Collection
    {
        $query = Hasil::query()
            ->when($isSuperadmin, fn($builder) => $builder->withoutTenantScope())
            ->whereNull('jenis_jumaat')
            ->whereBetween('tarikh', [$tarikhDari, $tarikhHingga]);

        if (!$isSuperadmin) {
            $query->byMasjid($idMasjid);
        }

        $aggregated = $query
            ->selectRaw('DATE_FORMAT(tarikh, "%Y-%m") as bulan, SUM(jumlah) as jumlah, COUNT(*) as bil_rekod')
            ->groupByRaw('DATE_FORMAT(tarikh, "%Y-%m")')
            ->orderByRaw('DATE_FORMAT(tarikh, "%Y-%m") DESC')
            ->get();

        return $aggregated->map(function ($row): array {
            $bulanObj = Carbon::createFromFormat('Y-m', $row->bulan);
            return [
                'bulan' => $bulanObj->format('M Y'),
                'bulan_raw' => $row->bulan,
                'jumlah' => (float) $row->jumlah,
                'bil_rekod' => (int) $row->bil_rekod,
            ];
        });
    }

    /**
     * Build transaction list ordered by date
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function buildSenariTransaksi(string $tarikhDari, string $tarikhHingga, int $idMasjid, bool $isSuperadmin): Collection
    {
        $query = Hasil::query()
            ->with(['sumberHasil:id,nama_sumber'])
            ->when($isSuperadmin, fn($builder) => $builder->withoutTenantScope())
            ->whereNull('jenis_jumaat')
            ->whereBetween('tarikh', [$tarikhDari, $tarikhHingga]);

        if (!$isSuperadmin) {
            $query->byMasjid($idMasjid);
        }

        $records = $query
            ->orderBy('tarikh', 'desc')
            ->orderBy('id', 'desc')
            ->get(['id', 'tarikh', 'id_sumber_hasil', 'jumlah', 'no_resit', 'catatan']);

        return $records->map(function ($row): array {
            return [
                'id' => $row->id,
                'tarikh' => $row->tarikh->format('Y-m-d'),
                'sumber' => optional($row->sumberHasil)->nama_sumber ?? 'Sumber Tidak Diketahui',
                'jumlah' => (float) $row->jumlah,
                'no_resit' => $row->no_resit ?: '-',
                'catatan' => $row->catatan ?: '-',
            ];
        });
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
