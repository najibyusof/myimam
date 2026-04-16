<?php

namespace App\Http\Controllers;

use App\Exports\LaporanTabungExport;
use App\Exports\LaporanTabungDetailExport;
use App\Models\Belanja;
use App\Models\Hasil;
use App\Models\TabungKhas;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class LaporanTabungController extends Controller
{
    public function index(Request $request): View
    {
        return view('laporan.tabung.index', $this->buildIndexData($request));
    }

    public function exportPdf(Request $request)
    {
        $data = $this->buildIndexData($request);
        $filename = 'laporan-tabung-khas-' . now()->format('Ymd_His') . '.pdf';

        return Pdf::loadView('laporan.tabung.pdf', $data)->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $data = $this->buildIndexData($request);
        $filename = 'laporan-tabung-khas-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new LaporanTabungExport($data), $filename);
    }

    public function detail(Request $request, int $tabung): View
    {
        return view('laporan.tabung.detail', $this->buildDetailData($request, $tabung));
    }

    public function exportDetailExcel(Request $request, int $tabung)
    {
        $data = $this->buildDetailData($request, $tabung);
        $filename = 'laporan-tabung-khas-detail-' . $tabung . '-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new LaporanTabungDetailExport($data), $filename);
    }

    public function exportDetailPdf(Request $request, int $tabung)
    {
        $data = $this->buildDetailData($request, $tabung);
        $filename = 'laporan-tabung-khas-detail-' . $tabung . '-' . now()->format('Ymd_His') . '.pdf';

        return Pdf::loadView('laporan.tabung.detail-pdf', $data)->download($filename);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildIndexData(Request $request): array
    {
        [$tarikhDari, $tarikhHingga] = $this->resolveDateRange($request);

        $actor = $request->user();
        $isSuperadmin = (bool) $actor?->hasRole('Superadmin');
        $idMasjid = (int) ($actor?->id_masjid ?? 0);

        if (!$isSuperadmin && $idMasjid <= 0) {
            abort(403);
        }

        $hasilQuery = Hasil::query()
            ->whereNotNull('id_tabung_khas')
            ->whereBetween('tarikh', [$tarikhDari, $tarikhHingga]);

        $belanjaQuery = Belanja::query()
            ->whereNotNull('id_tabung_khas')
            ->whereBetween('tarikh', [$tarikhDari, $tarikhHingga]);

        if (!$isSuperadmin) {
            $hasilQuery->byMasjid($idMasjid);
            $belanjaQuery->byMasjid($idMasjid);
        }

        $hasilByTabung = $hasilQuery
            ->selectRaw('id_tabung_khas, SUM(jumlah) as masuk_tempoh')
            ->groupBy('id_tabung_khas')
            ->pluck('masuk_tempoh', 'id_tabung_khas');

        $belanjaByTabung = $belanjaQuery
            ->selectRaw('id_tabung_khas, SUM(amaun) as keluar_tempoh')
            ->groupBy('id_tabung_khas')
            ->pluck('keluar_tempoh', 'id_tabung_khas');

        $tabungIds = collect($hasilByTabung->keys())
            ->merge($belanjaByTabung->keys())
            ->map(fn($id): int => (int) $id)
            ->unique()
            ->sort()
            ->values();

        $namaTabungById = TabungKhas::query()
            ->when(!$isSuperadmin, fn($q) => $q->byMasjid($idMasjid))
            ->whereIn('id', $tabungIds)
            ->pluck('nama_tabung', 'id');

        $rows = $tabungIds->map(function (int $tabungId) use ($hasilByTabung, $belanjaByTabung, $namaTabungById): array {
            $masuk = (float) ($hasilByTabung[$tabungId] ?? 0);
            $keluar = (float) ($belanjaByTabung[$tabungId] ?? 0);

            return [
                'id_tabung' => $tabungId,
                'nama_tabung' => (string) ($namaTabungById[$tabungId] ?? ('Tabung #' . $tabungId)),
                'masuk_tempoh' => $masuk,
                'keluar_tempoh' => $keluar,
                'baki_terkumpul' => $masuk - $keluar,
            ];
        })->values();

        return [
            'tempoh_label' => $this->buildTempohLabel($tarikhDari, $tarikhHingga),
            'filters' => [
                'tarikh_dari' => $tarikhDari,
                'tarikh_hingga' => $tarikhHingga,
            ],
            'rows' => $rows,
            'total_masuk' => (float) $rows->sum('masuk_tempoh'),
            'total_keluar' => (float) $rows->sum('keluar_tempoh'),
            'total_baki' => (float) $rows->sum('baki_terkumpul'),
            'chart' => [
                'labels' => $rows->pluck('nama_tabung')->values(),
                'masuk' => $rows->pluck('masuk_tempoh')->values(),
                'keluar' => $rows->pluck('keluar_tempoh')->values(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDetailData(Request $request, int $tabung): array
    {
        [$tarikhDari, $tarikhHingga] = $this->resolveDateRange($request);

        $actor = $request->user();
        $isSuperadmin = (bool) $actor?->hasRole('Superadmin');
        $idMasjid = (int) ($actor?->id_masjid ?? 0);

        if (!$isSuperadmin && $idMasjid <= 0) {
            abort(403);
        }

        $tabungQuery = TabungKhas::query()->whereKey($tabung);
        if (!$isSuperadmin) {
            $tabungQuery->byMasjid($idMasjid);
        }

        $tabungModel = $tabungQuery->firstOrFail(['id', 'nama_tabung']);

        $hasilQuery = Hasil::query()
            ->with(['sumberHasil:id,nama_sumber', 'akaun:id,nama_akaun'])
            ->where('id_tabung_khas', $tabungModel->id)
            ->whereBetween('tarikh', [$tarikhDari, $tarikhHingga]);

        $belanjaQuery = Belanja::query()
            ->with(['kategoriBelanja:id,nama_kategori', 'akaun:id,nama_akaun'])
            ->where('id_tabung_khas', $tabungModel->id)
            ->whereBetween('tarikh', [$tarikhDari, $tarikhHingga]);

        $openingHasilQuery = Hasil::query()
            ->where('id_tabung_khas', $tabungModel->id)
            ->where('tarikh', '<', $tarikhDari);

        $openingBelanjaQuery = Belanja::query()
            ->where('id_tabung_khas', $tabungModel->id)
            ->where('tarikh', '<', $tarikhDari);

        if (!$isSuperadmin) {
            $hasilQuery->byMasjid($idMasjid);
            $belanjaQuery->byMasjid($idMasjid);
            $openingHasilQuery->byMasjid($idMasjid);
            $openingBelanjaQuery->byMasjid($idMasjid);
        }

        $transaksiMasuk = $hasilQuery
            ->orderBy('tarikh', 'desc')
            ->orderBy('id', 'desc')
            ->get(['id', 'tarikh', 'id_sumber_hasil', 'id_akaun', 'catatan', 'amaun_tunai', 'amaun_online', 'jumlah'])
            ->map(function (Hasil $row): array {
                return [
                    'id' => (int) $row->id,
                    'tarikh' => $row->tarikh?->toDateString(),
                    'sumber_hasil' => $row->sumberHasil?->nama_sumber ?? '-',
                    'akaun' => $row->akaun?->nama_akaun ?? '-',
                    'catatan' => $row->catatan ?: '-',
                    'tunai' => (float) $row->amaun_tunai,
                    'online' => (float) $row->amaun_online,
                    'jumlah' => (float) $row->jumlah,
                ];
            });

        $transaksiKeluar = $belanjaQuery
            ->orderBy('tarikh', 'desc')
            ->orderBy('id', 'desc')
            ->get(['id', 'tarikh', 'id_kategori_belanja', 'penerima', 'id_akaun', 'catatan', 'amaun'])
            ->map(function (Belanja $row): array {
                return [
                    'id' => (int) $row->id,
                    'tarikh' => $row->tarikh?->toDateString(),
                    'kategori' => $row->kategoriBelanja?->nama_kategori ?? '-',
                    'penerima' => $row->penerima ?: '-',
                    'akaun' => $row->akaun?->nama_akaun ?? '-',
                    'catatan' => $row->catatan ?: '-',
                    'amaun' => (float) $row->amaun,
                ];
            });

        $bakiAwal = (float) $openingHasilQuery->sum('jumlah') - (float) $openingBelanjaQuery->sum('amaun');
        $timeline = $this->buildTimeline($transaksiMasuk, $transaksiKeluar, $bakiAwal, $tarikhDari);

        $jumlahMasuk = (float) $transaksiMasuk->sum('jumlah');
        $jumlahKeluar = (float) $transaksiKeluar->sum('amaun');

        return [
            'tabung' => $tabungModel,
            'filters' => [
                'tarikh_dari' => $tarikhDari,
                'tarikh_hingga' => $tarikhHingga,
            ],
            'tempoh_label' => $this->buildTempohLabel($tarikhDari, $tarikhHingga),
            'transaksi_masuk' => $transaksiMasuk,
            'transaksi_keluar' => $transaksiKeluar,
            'timeline_rows' => $timeline,
            'baki_awal' => $bakiAwal,
            'jumlah_masuk' => $jumlahMasuk,
            'jumlah_keluar' => $jumlahKeluar,
            'baki' => $jumlahMasuk - $jumlahKeluar,
            'baki_akhir' => $bakiAwal + $jumlahMasuk - $jumlahKeluar,
        ];
    }

    private function buildTimeline($transaksiMasuk, $transaksiKeluar, float $bakiAwal, string $tarikhDari)
    {
        $masukRows = collect($transaksiMasuk->all());
        $keluarRows = collect($transaksiKeluar->all());

        $masukTimeline = $masukRows->map(function (array $row): array {
            return [
                'tarikh' => $row['tarikh'],
                'jenis' => 'Masuk',
                'rujukan' => $row['sumber_hasil'],
                'butiran' => $row['catatan'],
                'masuk' => $row['jumlah'],
                'keluar' => 0.0,
                'sort_order' => 1,
                'record_id' => $row['id'],
            ];
        });

        $keluarTimeline = $keluarRows->map(function (array $row): array {
            return [
                'tarikh' => $row['tarikh'],
                'jenis' => 'Keluar',
                'rujukan' => $row['kategori'],
                'butiran' => trim($row['penerima'] . ' | ' . $row['catatan'], ' |'),
                'masuk' => 0.0,
                'keluar' => $row['amaun'],
                'sort_order' => 2,
                'record_id' => $row['id'],
            ];
        });

        $rows = $masukTimeline
            ->merge($keluarTimeline)
            ->sort(function (array $left, array $right): int {
                $leftKey = [$left['tarikh'], $left['sort_order'], $left['record_id']];
                $rightKey = [$right['tarikh'], $right['sort_order'], $right['record_id']];

                return $leftKey <=> $rightKey;
            })
            ->values();

        $runningBalance = $bakiAwal;

        $timeline = $rows->map(function (array $row) use (&$runningBalance): array {
            $runningBalance += (float) $row['masuk'] - (float) $row['keluar'];
            $row['baki_berjalan'] = $runningBalance;

            return $row;
        });

        return collect([
            [
                'tarikh' => $tarikhDari,
                'jenis' => 'Baki Awal',
                'rujukan' => '-',
                'butiran' => 'Baki dibawa sebelum tempoh laporan',
                'masuk' => 0.0,
                'keluar' => 0.0,
                'baki_berjalan' => $bakiAwal,
                'is_opening' => true,
            ],
        ])->merge($timeline);
    }

    /**
     * @return array{0:string,1:string}
     */
    private function resolveDateRange(Request $request): array
    {
        $hariIni = Carbon::today();
        $defaultDari = $hariIni->copy()->startOfMonth()->toDateString();
        $defaultHingga = $hariIni->toDateString();

        $tarikhDari = (string) $request->query('tarikh_dari', $defaultDari);
        $tarikhHingga = (string) $request->query('tarikh_hingga', $defaultHingga);

        try {
            $tarikhDari = Carbon::parse($tarikhDari)->toDateString();
            $tarikhHingga = Carbon::parse($tarikhHingga)->toDateString();
        } catch (\Throwable) {
            return [$defaultDari, $defaultHingga];
        }

        if ($tarikhDari > $tarikhHingga) {
            return [$defaultDari, $defaultHingga];
        }

        return [$tarikhDari, $tarikhHingga];
    }

    private function buildTempohLabel(string $tarikhDari, string $tarikhHingga): string
    {
        return Carbon::parse($tarikhDari)->format('d/m/Y') . ' - ' . Carbon::parse($tarikhHingga)->format('d/m/Y');
    }
}
