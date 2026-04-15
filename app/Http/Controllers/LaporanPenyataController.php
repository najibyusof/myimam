<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use App\Models\Hasil;
use App\Models\KategoriBelanja;
use App\Models\Masjid;
use App\Models\SumberHasil;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class LaporanPenyataController extends Controller
{
    public function index(Request $request): View
    {
        return view('laporan.penyata', $this->buildReportData($request));
    }

    public function exportPdf(Request $request): Response
    {
        $data = $this->buildReportData($request);
        $filename = 'penyata-kewangan-' . str_replace(['/', ' ', '(', ')'], ['-', '-', '', ''], strtolower($data['tempoh_label'])) . '.pdf';

        return Pdf::loadView('laporan.penyata-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }

    private function buildReportData(Request $request): array
    {
        $actor = $request->user();
        $isSuperadmin = $this->isSuperadmin($request);
        $idMasjid = (int) ($actor?->id_masjid ?? 0);

        abort_if($idMasjid <= 0 && !$isSuperadmin, 403);

        [$jenisPenyata, $tahun, $bulan, $mula, $akhir] = $this->resolveFilters($request);
        [$prevMula, $prevAkhir] = $this->resolvePrevPeriod($jenisPenyata, $tahun, $bulan);

        // Current period queries
        $hasilQuery = Hasil::query()
            ->when($isSuperadmin, fn(Builder $b) => $b->withoutTenantScope())
            ->whereBetween('tarikh', [$mula, $akhir]);
        if (!$isSuperadmin) {
            $hasilQuery->byMasjid($idMasjid);
        }

        $belanjaQuery = Belanja::query()
            ->notDeleted()
            ->when($isSuperadmin, fn(Builder $b) => $b->withoutTenantScope())
            ->whereBetween('tarikh', [$mula, $akhir]);
        if (!$isSuperadmin) {
            $belanjaQuery->byMasjid($idMasjid);
        }

        // Previous period queries (for comparison)
        $prevHasilQ = Hasil::query()
            ->when($isSuperadmin, fn(Builder $b) => $b->withoutTenantScope())
            ->whereBetween('tarikh', [$prevMula, $prevAkhir]);
        if (!$isSuperadmin) {
            $prevHasilQ->byMasjid($idMasjid);
        }

        $prevBelanjaQ = Belanja::query()
            ->notDeleted()
            ->when($isSuperadmin, fn(Builder $b) => $b->withoutTenantScope())
            ->whereBetween('tarikh', [$prevMula, $prevAkhir]);
        if (!$isSuperadmin) {
            $prevBelanjaQ->byMasjid($idMasjid);
        }

        $prevPendapatan = $prevHasilQ
            ->selectRaw('id_sumber_hasil, SUM(jumlah) as jumlah')
            ->groupBy('id_sumber_hasil')
            ->pluck('jumlah', 'id_sumber_hasil');

        $prevPerbelanjaan = $prevBelanjaQ
            ->selectRaw('id_kategori_belanja, SUM(amaun) as jumlah')
            ->groupBy('id_kategori_belanja')
            ->pluck('jumlah', 'id_kategori_belanja');

        // Build current rows with percentages + comparison
        $rawHasil = $hasilQuery
            ->selectRaw('id_sumber_hasil, sumber_hasil.nama_sumber, SUM(jumlah) as jumlah')
            ->leftJoin('sumber_hasil', 'hasil.id_sumber_hasil', '=', 'sumber_hasil.id')
            ->groupBy('id_sumber_hasil', 'sumber_hasil.nama_sumber')
            ->orderBy('sumber_hasil.nama_sumber')
            ->get();

        $jumlahPendapatan = (float) $rawHasil->sum('jumlah');

        $pendapatanRows = $rawHasil->map(function ($row) use ($jenisPenyata, $tahun, $bulan, $jumlahPendapatan, $prevPendapatan): array {
            $jumlah     = (float) $row->jumlah;
            $prevJumlah = (float) ($prevPendapatan[(int) $row->id_sumber_hasil] ?? 0);
            return [
                'id'                => (int) $row->id_sumber_hasil,
                'butiran'           => $row->nama_sumber ?: 'Sumber Tidak Diketahui',
                'jumlah'            => $jumlah,
                'peratus'           => $jumlahPendapatan > 0 ? round($jumlah / $jumlahPendapatan * 100, 1) : 0.0,
                'prev_jumlah'       => $prevJumlah,
                'perubahan'         => $jumlah - $prevJumlah,
                'peratus_perubahan' => $prevJumlah > 0 ? round(($jumlah - $prevJumlah) / $prevJumlah * 100, 1) : null,
                'detail_url'        => route('laporan.penyata.detail.hasil', [
                    'sumber'        => (int) $row->id_sumber_hasil,
                    'jenis_penyata' => $jenisPenyata,
                    'tahun'         => $tahun,
                    'bulan'         => $bulan,
                ]),
            ];
        });

        $rawBelanja = $belanjaQuery
            ->selectRaw('id_kategori_belanja, kategori_belanja.nama_kategori, SUM(amaun) as jumlah')
            ->leftJoin('kategori_belanja', 'belanja.id_kategori_belanja', '=', 'kategori_belanja.id')
            ->groupBy('id_kategori_belanja', 'kategori_belanja.nama_kategori')
            ->orderBy('kategori_belanja.nama_kategori')
            ->get();

        $jumlahPerbelanjaan = (float) $rawBelanja->sum('jumlah');

        $perbelanjaanRows = $rawBelanja->map(function ($row) use ($jenisPenyata, $tahun, $bulan, $jumlahPerbelanjaan, $prevPerbelanjaan): array {
            $jumlah     = (float) $row->jumlah;
            $prevJumlah = (float) ($prevPerbelanjaan[(int) $row->id_kategori_belanja] ?? 0);
            return [
                'id'                => (int) $row->id_kategori_belanja,
                'butiran'           => $row->nama_kategori ?: 'Kategori Tidak Diketahui',
                'jumlah'            => $jumlah,
                'peratus'           => $jumlahPerbelanjaan > 0 ? round($jumlah / $jumlahPerbelanjaan * 100, 1) : 0.0,
                'prev_jumlah'       => $prevJumlah,
                'perubahan'         => $jumlah - $prevJumlah,
                'peratus_perubahan' => $prevJumlah > 0 ? round(($jumlah - $prevJumlah) / $prevJumlah * 100, 1) : null,
                'detail_url'        => route('laporan.penyata.detail.belanja', [
                    'kategori'      => (int) $row->id_kategori_belanja,
                    'jenis_penyata' => $jenisPenyata,
                    'tahun'         => $tahun,
                    'bulan'         => $bulan,
                ]),
            ];
        });

        $lebihanKekurangan      = $jumlahPendapatan - $jumlahPerbelanjaan;
        $prevJumlahPendapatan   = (float) $prevPendapatan->sum();
        $prevJumlahPerbelanjaan = (float) $prevPerbelanjaan->sum();
        $prevLebihanKekurangan  = $prevJumlahPendapatan - $prevJumlahPerbelanjaan;

        $masjid = $isSuperadmin ? null : $actor?->masjid;

        return [
            'filters' => [
                'jenis_penyata' => $jenisPenyata,
                'tahun'         => $tahun,
                'bulan'         => $bulan,
            ],
            'tempoh_label'              => $this->buildTempohLabel($jenisPenyata, $tahun, $bulan, $mula, $akhir),
            'prev_tempoh_label'         => $this->buildPrevTempohLabel($jenisPenyata, $tahun, $bulan),
            'pendapatan_rows'           => $pendapatanRows,
            'perbelanjaan_rows'         => $perbelanjaanRows,
            'jumlah_pendapatan'         => $jumlahPendapatan,
            'jumlah_perbelanjaan'       => $jumlahPerbelanjaan,
            'lebihan_kurangan'          => $lebihanKekurangan,
            'prev_jumlah_pendapatan'    => $prevJumlahPendapatan,
            'prev_jumlah_perbelanjaan'  => $prevJumlahPerbelanjaan,
            'prev_lebihan_kurangan'     => $prevLebihanKekurangan,
            'masjid_nama'               => $masjid?->nama ?? 'Semua Masjid',
            'masjid_alamat'             => $masjid?->alamat ?? 'Paparan semua masjid (SuperAdmin)',
            'tahun_opsyen'              => $this->buildTahunOpsyen(),
            'bulan_opsyen'              => $this->buildBulanOpsyen(),
        ];
    }

    public function detailHasil(Request $request, int $sumber): View
    {
        $actor = $request->user();
        $isSuperadmin = $this->isSuperadmin($request);
        $idMasjid = (int) ($actor?->id_masjid ?? 0);

        abort_if($idMasjid <= 0 && !$isSuperadmin, 403);

        [$jenisPenyata, $tahun, $bulan, $mula, $akhir] = $this->resolveFilters($request);

        $query = Hasil::query()
            ->with(['sumberHasil:id,nama_sumber'])
            ->when($isSuperadmin, fn(Builder $builder) => $builder->withoutTenantScope())
            ->where('id_sumber_hasil', $sumber)
            ->whereBetween('tarikh', [$mula, $akhir]);

        if (!$isSuperadmin) {
            $query->byMasjid($idMasjid);
        }

        $records = $query
            ->orderBy('tarikh', 'desc')
            ->orderBy('id', 'desc')
            ->get(['id', 'tarikh', 'catatan', 'jumlah'])
            ->map(function ($row): array {
                return [
                    'tarikh' => $row->tarikh?->toDateString(),
                    'catatan' => $row->catatan ?: '-',
                    'jumlah' => (float) $row->jumlah,
                ];
            });

        $sumberNama = SumberHasil::query()->whereKey($sumber)->value('nama_sumber') ?: 'Sumber Tidak Diketahui';

        return view('laporan.penyata-detail-hasil', [
            'records' => $records,
            'sumber_nama' => $sumberNama,
            'tempoh_label' => $this->buildTempohLabel($jenisPenyata, $tahun, $bulan, $mula, $akhir),
            'filters' => [
                'jenis_penyata' => $jenisPenyata,
                'tahun' => $tahun,
                'bulan' => $bulan,
            ],
            'jumlah' => (float) $records->sum('jumlah'),
        ]);
    }

    public function detailBelanja(Request $request, int $kategori): View
    {
        $actor = $request->user();
        $isSuperadmin = $this->isSuperadmin($request);
        $idMasjid = (int) ($actor?->id_masjid ?? 0);

        abort_if($idMasjid <= 0 && !$isSuperadmin, 403);

        [$jenisPenyata, $tahun, $bulan, $mula, $akhir] = $this->resolveFilters($request);

        $query = Belanja::query()
            ->notDeleted()
            ->when($isSuperadmin, fn(Builder $builder) => $builder->withoutTenantScope())
            ->where('id_kategori_belanja', $kategori)
            ->whereBetween('tarikh', [$mula, $akhir]);

        if (!$isSuperadmin) {
            $query->byMasjid($idMasjid);
        }

        $records = $query
            ->orderBy('tarikh', 'desc')
            ->orderBy('id', 'desc')
            ->get(['id', 'tarikh', 'penerima', 'catatan', 'bukti_fail', 'amaun'])
            ->map(function ($row): array {
                $buktiUrl = null;
                if (!empty($row->bukti_fail)) {
                    $buktiUrl = str_starts_with((string) $row->bukti_fail, 'http')
                        ? $row->bukti_fail
                        : asset('storage/' . ltrim((string) $row->bukti_fail, '/'));
                }

                return [
                    'tarikh' => $row->tarikh?->toDateString(),
                    'penerima' => $row->penerima ?: '-',
                    'catatan' => $row->catatan ?: '-',
                    'bukti_url' => $buktiUrl,
                    'amaun' => (float) $row->amaun,
                ];
            });

        $kategoriNama = KategoriBelanja::query()->whereKey($kategori)->value('nama_kategori') ?: 'Kategori Tidak Diketahui';

        return view('laporan.penyata-detail-belanja', [
            'records' => $records,
            'kategori_nama' => $kategoriNama,
            'tempoh_label' => $this->buildTempohLabel($jenisPenyata, $tahun, $bulan, $mula, $akhir),
            'filters' => [
                'jenis_penyata' => $jenisPenyata,
                'tahun' => $tahun,
                'bulan' => $bulan,
            ],
            'jumlah' => (float) $records->sum('amaun'),
        ]);
    }

    /**
     * @return array{0:string,1:string}
     */
    private function resolvePrevPeriod(string $jenisPenyata, int $tahun, int $bulan): array
    {
        if ($jenisPenyata === 'tahunan') {
            $prevYear = $tahun - 1;
            return [
                Carbon::create($prevYear, 1, 1)->startOfDay()->toDateString(),
                Carbon::create($prevYear, 12, 31)->endOfDay()->toDateString(),
            ];
        }

        $prevDate = Carbon::create($tahun, $bulan, 1)->subMonthNoOverflow();
        return [
            $prevDate->copy()->startOfMonth()->toDateString(),
            $prevDate->copy()->endOfMonth()->toDateString(),
        ];
    }

    private function buildPrevTempohLabel(string $jenisPenyata, int $tahun, int $bulan): string
    {
        if ($jenisPenyata === 'tahunan') {
            return '1 Jan ' . ($tahun - 1) . ' - 31 Dis ' . ($tahun - 1);
        }

        $prevDate = Carbon::create($tahun, $bulan, 1)->subMonthNoOverflow();
        return ucfirst($prevDate->translatedFormat('F Y'));
    }

    /**
     * @return array{0:string,1:int,2:int,3:string,4:string}
     */
    private function resolveFilters(Request $request): array
    {
        $jenisPenyata = (string) $request->query('jenis_penyata', 'bulanan');
        if (!in_array($jenisPenyata, ['bulanan', 'tahunan'], true)) {
            $jenisPenyata = 'bulanan';
        }

        $tahun = (int) $request->query('tahun', now()->year);
        if ($tahun < 2000 || $tahun > 2100) {
            $tahun = now()->year;
        }

        $bulan = (int) $request->query('bulan', now()->month);
        if ($bulan < 1 || $bulan > 12) {
            $bulan = now()->month;
        }

        if ($jenisPenyata === 'tahunan') {
            $mula = Carbon::create($tahun, 1, 1)->startOfDay()->toDateString();
            $akhir = Carbon::create($tahun, 12, 31)->endOfDay()->toDateString();
        } else {
            $mula = Carbon::create($tahun, $bulan, 1)->startOfMonth()->toDateString();
            $akhir = Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString();
        }

        return [$jenisPenyata, $tahun, $bulan, $mula, $akhir];
    }

    private function buildTempohLabel(string $jenisPenyata, int $tahun, int $bulan, string $mula, string $akhir): string
    {
        if ($jenisPenyata === 'tahunan') {
            return '1 Jan ' . $tahun . ' - 31 Dis ' . $tahun;
        }

        $bulanLabel = Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y');

        return ucfirst($bulanLabel) . ' (' . Carbon::parse($mula)->format('d/m/Y') . ' - ' . Carbon::parse($akhir)->format('d/m/Y') . ')';
    }

    /**
     * @return Collection<int, int>
     */
    private function buildTahunOpsyen(): Collection
    {
        $tahunSemasa = now()->year;

        return collect(range($tahunSemasa - 5, $tahunSemasa + 1));
    }

    /**
     * @return Collection<int, array{id:int,nama:string}>
     */
    private function buildBulanOpsyen(): Collection
    {
        return collect(range(1, 12))->map(function (int $bulan): array {
            return [
                'id' => $bulan,
                'nama' => Carbon::create(null, $bulan, 1)->translatedFormat('F'),
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
