<?php

namespace App\Http\Controllers\Api\Finance;

use App\Models\Akaun;
use App\Models\Belanja;
use App\Models\Hasil;
use App\Models\KategoriBelanja;
use App\Models\SumberHasil;
use App\Models\TabungKhas;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReportsController extends BaseFinanceController
{
    public function bukuTunai(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_masjid' => ['nullable', 'integer', 'min:1'],
            'akaun_id' => ['required', 'integer', 'min:1'],
            'tarikh_mula' => ['nullable', 'date'],
            'tarikh_tamat' => ['nullable', 'date', 'after_or_equal:tarikh_mula'],
            'baki_awal' => ['nullable', 'numeric'],
        ]);

        $actor = $this->actor($request);
        $idMasjid = $this->resolveMasjidId($request, $actor);
        $tarikhMula = (string) ($validated['tarikh_mula'] ?? now()->startOfMonth()->toDateString());
        $tarikhTamat = (string) ($validated['tarikh_tamat'] ?? now()->toDateString());
        $bakiAwal = (float) ($validated['baki_awal'] ?? 0);
        $akaunId = (int) $validated['akaun_id'];

        $akaun = Akaun::query()
            ->withoutTenantScope()
            ->where('id_masjid', $idMasjid)
            ->whereKey($akaunId)
            ->firstOrFail(['id', 'id_masjid', 'nama_akaun']);

        $transaksiHasil = Hasil::query()
            ->withoutTenantScope()
            ->where('id_masjid', $idMasjid)
            ->where('id_akaun', $akaunId)
            ->betweenDates($tarikhMula, $tarikhTamat)
            ->orderBy('tarikh')
            ->orderBy('id')
            ->get(['id', 'tarikh', 'catatan', 'jumlah'])
            ->map(fn(Hasil $hasil): array => [
                'id' => (int) $hasil->id,
                'tarikh' => $hasil->tarikh?->toDateString(),
                'butiran' => $this->resolveButiran($hasil->catatan, 'Hasil'),
                'masuk' => (float) $hasil->jumlah,
                'keluar' => 0.0,
                'susunan' => 'hasil',
            ]);

        $transaksiBelanja = Belanja::query()
            ->withoutTenantScope()
            ->notDeleted()
            ->approved()
            ->where('id_masjid', $idMasjid)
            ->where('id_akaun', $akaunId)
            ->betweenDates($tarikhMula, $tarikhTamat)
            ->orderBy('tarikh')
            ->orderBy('id')
            ->get(['id', 'tarikh', 'catatan', 'amaun'])
            ->map(fn(Belanja $belanja): array => [
                'id' => (int) $belanja->id,
                'tarikh' => $belanja->tarikh?->toDateString(),
                'butiran' => $this->resolveButiran($belanja->catatan, 'Belanja'),
                'masuk' => 0.0,
                'keluar' => (float) $belanja->amaun,
                'susunan' => 'belanja',
            ]);

        $transaksi = $transaksiHasil
            ->concat($transaksiBelanja)
            ->sortBy([
                ['tarikh', 'asc'],
                ['susunan', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        $jumlahMasuk = 0.0;
        $jumlahKeluar = 0.0;
        $bakiSemasa = $bakiAwal;

        $rows = $transaksi->map(function (array $baris) use (&$jumlahMasuk, &$jumlahKeluar, &$bakiSemasa): array {
            $masuk = (float) $baris['masuk'];
            $keluar = (float) $baris['keluar'];

            $jumlahMasuk += $masuk;
            $jumlahKeluar += $keluar;
            $bakiSemasa = $bakiSemasa + $masuk - $keluar;

            return [
                'tarikh' => $baris['tarikh'],
                'butiran' => $baris['butiran'],
                'masuk' => $masuk,
                'keluar' => $keluar,
                'baki' => $bakiSemasa,
            ];
        });

        return response()->json([
            'data' => [
                'akaun' => [
                    'id' => (int) $akaun->id,
                    'nama_akaun' => (string) $akaun->nama_akaun,
                ],
                'tempoh' => [
                    'tarikh_mula' => $tarikhMula,
                    'tarikh_tamat' => $tarikhTamat,
                ],
                'rows' => $rows,
                'ringkasan' => [
                    'baki_awal' => $bakiAwal,
                    'jumlah_masuk' => $jumlahMasuk,
                    'jumlah_keluar' => $jumlahKeluar,
                    'baki_akhir' => $bakiSemasa,
                ],
            ],
        ]);
    }

    public function jumaat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_masjid' => ['nullable', 'integer', 'min:1'],
            'tahun' => ['nullable', 'integer', 'min:2000'],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'jenis_paparan' => ['nullable', 'in:ringkasan_bulanan,senarai_jumaat'],
        ]);

        $actor = $this->actor($request);
        $idMasjid = $this->resolveMasjidId($request, $actor);
        $tahun = (int) ($validated['tahun'] ?? now()->year);
        $bulan = isset($validated['bulan']) ? (int) $validated['bulan'] : 0;
        $jenisPaparan = (string) ($validated['jenis_paparan'] ?? 'ringkasan_bulanan');

        $start = Carbon::create($tahun, 1, 1)->startOfDay()->toDateString();
        $end = Carbon::create($tahun, 12, 31)->endOfDay()->toDateString();

        $records = Hasil::query()
            ->withoutTenantScope()
            ->jumaat()
            ->where('id_masjid', $idMasjid)
            ->whereBetween('tarikh', [$start, $end])
            ->orderBy('tarikh')
            ->orderBy('id')
            ->get(['id', 'tarikh', 'jumlah']);

        $byMonth = $records->groupBy(fn(Hasil $row): int => (int) ($row->tarikh?->format('n') ?? 0));
        $namaBulan = $this->namaBulan();

        $ringkasan = collect(range(1, 12))->map(function (int $month) use ($byMonth, $namaBulan): array {
            $bucket = $byMonth->get($month, collect());

            return [
                'bulan_no' => $month,
                'bulan' => $namaBulan[$month],
                'jumlah' => (float) $bucket->sum('jumlah'),
                'bil_rekod' => (int) $bucket->count(),
            ];
        });

        $senaraiSource = $bulan > 0
            ? $records->filter(fn(Hasil $row): bool => (int) ($row->tarikh?->format('n') ?? 0) === $bulan)
            : $records;

        $senaraiRows = $senaraiSource
            ->groupBy(fn(Hasil $row): string => (string) $row->tarikh?->toDateString())
            ->map(fn($bucket, string $tarikh): array => [
                'tarikh' => $tarikh,
                'jumlah_kutipan' => (float) $bucket->sum('jumlah'),
                'bil_rekod' => (int) $bucket->count(),
            ])
            ->values();

        return response()->json([
            'data' => [
                'filters' => [
                    'tahun' => $tahun,
                    'bulan' => $bulan > 0 ? $bulan : null,
                    'jenis_paparan' => $jenisPaparan,
                ],
                'rows' => $ringkasan,
                'senarai_rows' => $jenisPaparan === 'senarai_jumaat' ? $senaraiRows : collect(),
                'jumlah_setahun' => (float) $ringkasan->sum('jumlah'),
                'chart_labels' => $ringkasan->pluck('bulan')->values(),
                'chart_data' => $ringkasan->pluck('jumlah')->values(),
            ],
        ]);
    }

    public function derma(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_masjid' => ['nullable', 'integer', 'min:1'],
            'tarikh_dari' => ['nullable', 'date'],
            'tarikh_hingga' => ['nullable', 'date', 'after_or_equal:tarikh_dari'],
            'jenis_paparan' => ['nullable', 'in:ringkasan_sumber,ringkasan_bulan,senarai_transaksi'],
        ]);

        $actor = $this->actor($request);
        $idMasjid = $this->resolveMasjidId($request, $actor);
        $tarikhDari = (string) ($validated['tarikh_dari'] ?? now()->startOfMonth()->toDateString());
        $tarikhHingga = (string) ($validated['tarikh_hingga'] ?? now()->toDateString());
        $jenisPaparan = (string) ($validated['jenis_paparan'] ?? 'ringkasan_sumber');

        $records = Hasil::query()
            ->withoutTenantScope()
            ->with(['sumberHasil:id,nama_sumber'])
            ->where('id_masjid', $idMasjid)
            ->whereNull('jenis_jumaat')
            ->whereBetween('tarikh', [$tarikhDari, $tarikhHingga])
            ->orderBy('tarikh', 'desc')
            ->orderBy('id', 'desc')
            ->get(['id', 'tarikh', 'id_sumber_hasil', 'jumlah', 'no_resit', 'catatan']);

        $ringkasanSumber = $records
            ->groupBy(fn(Hasil $row): string => (string) ($row->id_sumber_hasil ?? 0))
            ->map(function ($bucket): array {
                $first = $bucket->first();

                return [
                    'sumber' => (string) ($first?->sumberHasil?->nama_sumber ?? 'Sumber Tidak Diketahui'),
                    'jumlah' => (float) $bucket->sum('jumlah'),
                    'bil_rekod' => (int) $bucket->count(),
                ];
            })
            ->sortBy('sumber')
            ->values();

        $ringkasanBulan = $records
            ->groupBy(fn(Hasil $row): string => (string) $row->tarikh?->format('Y-m'))
            ->map(function ($bucket, string $bulan): array {
                $bulanObj = Carbon::createFromFormat('Y-m', $bulan);

                return [
                    'bulan' => $bulanObj->format('M Y'),
                    'bulan_raw' => $bulan,
                    'jumlah' => (float) $bucket->sum('jumlah'),
                    'bil_rekod' => (int) $bucket->count(),
                ];
            })
            ->sortByDesc('bulan_raw')
            ->values();

        $senaraiRows = $records->map(function (Hasil $row): array {
            return [
                'id' => (int) $row->id,
                'tarikh' => $row->tarikh?->toDateString(),
                'sumber' => (string) ($row->sumberHasil?->nama_sumber ?? 'Sumber Tidak Diketahui'),
                'jumlah' => (float) $row->jumlah,
                'no_resit' => $row->no_resit ?: '-',
                'catatan' => $row->catatan ?: '-',
            ];
        });

        return response()->json([
            'data' => [
                'filters' => [
                    'tarikh_dari' => $tarikhDari,
                    'tarikh_hingga' => $tarikhHingga,
                    'jenis_paparan' => $jenisPaparan,
                ],
                'rows' => $ringkasanSumber,
                'ringkasan_bulan' => $jenisPaparan === 'ringkasan_bulan' ? $ringkasanBulan : collect(),
                'senarai_rows' => $jenisPaparan === 'senarai_transaksi' ? $senaraiRows : collect(),
                'jumlah_keseluruhan' => $jenisPaparan === 'ringkasan_bulan'
                    ? (float) $ringkasanBulan->sum('jumlah')
                    : ($jenisPaparan === 'senarai_transaksi'
                        ? (float) $senaraiRows->sum('jumlah')
                        : (float) $ringkasanSumber->sum('jumlah')),
            ],
        ]);
    }

    public function belanja(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_masjid' => ['nullable', 'integer', 'min:1'],
            'tarikh_dari' => ['nullable', 'date'],
            'tarikh_hingga' => ['nullable', 'date', 'after_or_equal:tarikh_dari'],
            'jenis_paparan' => ['nullable', 'in:ringkasan_kategori,ringkasan_bulan,senarai_transaksi'],
            'kategori_id' => ['nullable', 'integer', 'min:1'],
            'akaun_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:all,draf,lulus'],
        ]);

        $actor = $this->actor($request);
        $idMasjid = $this->resolveMasjidId($request, $actor);
        $tarikhDari = (string) ($validated['tarikh_dari'] ?? now()->startOfMonth()->toDateString());
        $tarikhHingga = (string) ($validated['tarikh_hingga'] ?? now()->toDateString());
        $jenisPaparan = (string) ($validated['jenis_paparan'] ?? 'ringkasan_kategori');
        $kategoriId = isset($validated['kategori_id']) ? (int) $validated['kategori_id'] : null;
        $akaunId = isset($validated['akaun_id']) ? (int) $validated['akaun_id'] : null;
        $status = (string) ($validated['status'] ?? 'all');

        $query = Belanja::query()
            ->withoutTenantScope()
            ->notDeleted()
            ->with(['kategoriBelanja:id,nama_kategori', 'akaun:id,nama_akaun'])
            ->where('id_masjid', $idMasjid)
            ->whereBetween('tarikh', [$tarikhDari, $tarikhHingga]);

        if ($kategoriId) {
            $query->where('id_kategori_belanja', $kategoriId);
        }

        if ($akaunId) {
            $query->where('id_akaun', $akaunId);
        }

        if ($status === 'draf') {
            $query->where('status', 'DRAF');
        } elseif ($status === 'lulus') {
            $query->where('status', 'LULUS');
        }

        $records = $query
            ->orderBy('tarikh', 'desc')
            ->orderBy('id', 'desc')
            ->get(['id', 'tarikh', 'id_kategori_belanja', 'id_akaun', 'penerima', 'amaun', 'status', 'catatan']);

        $ringkasanKategori = $records
            ->groupBy(fn(Belanja $row): string => (string) ($row->id_kategori_belanja ?? 0))
            ->map(function ($bucket): array {
                $first = $bucket->first();

                return [
                    'kategori' => (string) ($first?->kategoriBelanja?->nama_kategori ?? 'Kategori Tidak Diketahui'),
                    'jumlah' => (float) $bucket->sum('amaun'),
                    'bil_rekod' => (int) $bucket->count(),
                ];
            })
            ->sortBy('kategori')
            ->values();

        $ringkasanBulan = $records
            ->groupBy(fn(Belanja $row): string => (string) $row->tarikh?->format('Y-m'))
            ->map(function ($bucket, string $bulan): array {
                $bulanObj = Carbon::createFromFormat('Y-m', $bulan);

                return [
                    'bulan' => $bulanObj->format('M Y'),
                    'bulan_raw' => $bulan,
                    'jumlah' => (float) $bucket->sum('amaun'),
                    'bil_rekod' => (int) $bucket->count(),
                ];
            })
            ->sortByDesc('bulan_raw')
            ->values();

        $senaraiRows = $records->map(function (Belanja $row): array {
            return [
                'id' => (int) $row->id,
                'tarikh' => $row->tarikh?->toDateString(),
                'kategori' => (string) ($row->kategoriBelanja?->nama_kategori ?? 'Kategori Tidak Diketahui'),
                'akaun' => (string) ($row->akaun?->nama_akaun ?? 'Akaun Tidak Diketahui'),
                'penerima' => $row->penerima ?: '-',
                'amaun' => (float) $row->amaun,
                'status' => $row->status ?: 'DRAF',
                'catatan' => $row->catatan ?: '-',
            ];
        });

        return response()->json([
            'data' => [
                'filters' => [
                    'tarikh_dari' => $tarikhDari,
                    'tarikh_hingga' => $tarikhHingga,
                    'jenis_paparan' => $jenisPaparan,
                    'kategori_id' => $kategoriId,
                    'akaun_id' => $akaunId,
                    'status' => $status,
                ],
                'rows' => $ringkasanKategori,
                'ringkasan_bulan' => $jenisPaparan === 'ringkasan_bulan' ? $ringkasanBulan : collect(),
                'senarai_rows' => $jenisPaparan === 'senarai_transaksi' ? $senaraiRows : collect(),
                'jumlah_keseluruhan' => $jenisPaparan === 'ringkasan_bulan'
                    ? (float) $ringkasanBulan->sum('jumlah')
                    : ($jenisPaparan === 'senarai_transaksi'
                        ? (float) $senaraiRows->sum('amaun')
                        : (float) $ringkasanKategori->sum('jumlah')),
            ],
        ]);
    }

    public function penyata(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_masjid' => ['nullable', 'integer', 'min:1'],
            'jenis_penyata' => ['nullable', 'in:bulanan,tahunan'],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
        ]);

        $actor = $this->actor($request);
        $idMasjid = $this->resolveMasjidId($request, $actor);

        $jenisPenyata = (string) ($validated['jenis_penyata'] ?? 'bulanan');
        $tahun = (int) ($validated['tahun'] ?? now()->year);
        $bulan = (int) ($validated['bulan'] ?? now()->month);

        if ($jenisPenyata === 'tahunan') {
            $mula = Carbon::create($tahun, 1, 1)->startOfDay()->toDateString();
            $akhir = Carbon::create($tahun, 12, 31)->endOfDay()->toDateString();
            $prevMula = Carbon::create($tahun - 1, 1, 1)->startOfDay()->toDateString();
            $prevAkhir = Carbon::create($tahun - 1, 12, 31)->endOfDay()->toDateString();
            $tempohLabel = '1 Jan ' . $tahun . ' - 31 Dis ' . $tahun;
            $prevTempohLabel = '1 Jan ' . ($tahun - 1) . ' - 31 Dis ' . ($tahun - 1);
        } else {
            $mula = Carbon::create($tahun, $bulan, 1)->startOfMonth()->toDateString();
            $akhir = Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString();
            $prevDate = Carbon::create($tahun, $bulan, 1)->subMonthNoOverflow();
            $prevMula = $prevDate->copy()->startOfMonth()->toDateString();
            $prevAkhir = $prevDate->copy()->endOfMonth()->toDateString();
            $tempohLabel = ucfirst(Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y'))
                . ' (' . Carbon::parse($mula)->format('d/m/Y') . ' - ' . Carbon::parse($akhir)->format('d/m/Y') . ')';
            $prevTempohLabel = ucfirst($prevDate->translatedFormat('F Y'));
        }

        $hasilRecords = Hasil::query()
            ->withoutTenantScope()
            ->where('id_masjid', $idMasjid)
            ->whereBetween('tarikh', [$mula, $akhir])
            ->get(['id_sumber_hasil', 'jumlah']);

        $belanjaRecords = Belanja::query()
            ->withoutTenantScope()
            ->notDeleted()
            ->where('id_masjid', $idMasjid)
            ->whereBetween('tarikh', [$mula, $akhir])
            ->get(['id_kategori_belanja', 'amaun']);

        $prevHasilRecords = Hasil::query()
            ->withoutTenantScope()
            ->where('id_masjid', $idMasjid)
            ->whereBetween('tarikh', [$prevMula, $prevAkhir])
            ->get(['id_sumber_hasil', 'jumlah']);

        $prevBelanjaRecords = Belanja::query()
            ->withoutTenantScope()
            ->notDeleted()
            ->where('id_masjid', $idMasjid)
            ->whereBetween('tarikh', [$prevMula, $prevAkhir])
            ->get(['id_kategori_belanja', 'amaun']);

        $pendapatanBySource = $hasilRecords
            ->groupBy(fn(Hasil $row): string => (string) ($row->id_sumber_hasil ?? 0))
            ->map(fn($bucket): float => (float) $bucket->sum('jumlah'));

        $perbelanjaanByKategori = $belanjaRecords
            ->groupBy(fn(Belanja $row): string => (string) ($row->id_kategori_belanja ?? 0))
            ->map(fn($bucket): float => (float) $bucket->sum('amaun'));

        $prevPendapatanBySource = $prevHasilRecords
            ->groupBy(fn(Hasil $row): string => (string) ($row->id_sumber_hasil ?? 0))
            ->map(fn($bucket): float => (float) $bucket->sum('jumlah'));

        $prevPerbelanjaanByKategori = $prevBelanjaRecords
            ->groupBy(fn(Belanja $row): string => (string) ($row->id_kategori_belanja ?? 0))
            ->map(fn($bucket): float => (float) $bucket->sum('amaun'));

        $sumberIds = $pendapatanBySource->keys()->merge($prevPendapatanBySource->keys())->filter(fn(string $id): bool => $id !== '0');
        $kategoriIds = $perbelanjaanByKategori->keys()->merge($prevPerbelanjaanByKategori->keys())->filter(fn(string $id): bool => $id !== '0');

        $sumberNamaById = SumberHasil::query()
            ->withoutTenantScope()
            ->where('id_masjid', $idMasjid)
            ->whereIn('id', $sumberIds->map(fn(string $id): int => (int) $id))
            ->pluck('nama_sumber', 'id');

        $kategoriNamaById = KategoriBelanja::query()
            ->withoutTenantScope()
            ->where('id_masjid', $idMasjid)
            ->whereIn('id', $kategoriIds->map(fn(string $id): int => (int) $id))
            ->pluck('nama_kategori', 'id');

        $jumlahPendapatan = (float) $pendapatanBySource->sum();
        $jumlahPerbelanjaan = (float) $perbelanjaanByKategori->sum();
        $prevJumlahPendapatan = (float) $prevPendapatanBySource->sum();
        $prevJumlahPerbelanjaan = (float) $prevPerbelanjaanByKategori->sum();

        $pendapatanRows = $pendapatanBySource->map(function (float $jumlah, string $sourceId) use ($sumberNamaById, $jumlahPendapatan, $prevPendapatanBySource): array {
            $id = (int) $sourceId;
            $prevJumlah = (float) ($prevPendapatanBySource[$sourceId] ?? 0);

            return [
                'id' => $id,
                'butiran' => (string) ($sumberNamaById[$id] ?? 'Sumber Tidak Diketahui'),
                'jumlah' => $jumlah,
                'peratus' => $jumlahPendapatan > 0 ? round(($jumlah / $jumlahPendapatan) * 100, 1) : 0.0,
                'prev_jumlah' => $prevJumlah,
                'perubahan' => $jumlah - $prevJumlah,
                'peratus_perubahan' => $prevJumlah > 0 ? round((($jumlah - $prevJumlah) / $prevJumlah) * 100, 1) : null,
            ];
        })->values();

        $perbelanjaanRows = $perbelanjaanByKategori->map(function (float $jumlah, string $kategoriId) use ($kategoriNamaById, $jumlahPerbelanjaan, $prevPerbelanjaanByKategori): array {
            $id = (int) $kategoriId;
            $prevJumlah = (float) ($prevPerbelanjaanByKategori[$kategoriId] ?? 0);

            return [
                'id' => $id,
                'butiran' => (string) ($kategoriNamaById[$id] ?? 'Kategori Tidak Diketahui'),
                'jumlah' => $jumlah,
                'peratus' => $jumlahPerbelanjaan > 0 ? round(($jumlah / $jumlahPerbelanjaan) * 100, 1) : 0.0,
                'prev_jumlah' => $prevJumlah,
                'perubahan' => $jumlah - $prevJumlah,
                'peratus_perubahan' => $prevJumlah > 0 ? round((($jumlah - $prevJumlah) / $prevJumlah) * 100, 1) : null,
            ];
        })->values();

        return response()->json([
            'data' => [
                'filters' => [
                    'jenis_penyata' => $jenisPenyata,
                    'tahun' => $tahun,
                    'bulan' => $jenisPenyata === 'bulanan' ? $bulan : null,
                ],
                'tempoh_label' => $tempohLabel,
                'prev_tempoh_label' => $prevTempohLabel,
                'pendapatan_rows' => $pendapatanRows,
                'perbelanjaan_rows' => $perbelanjaanRows,
                'jumlah_pendapatan' => $jumlahPendapatan,
                'jumlah_perbelanjaan' => $jumlahPerbelanjaan,
                'lebihan_kurangan' => $jumlahPendapatan - $jumlahPerbelanjaan,
                'prev_jumlah_pendapatan' => $prevJumlahPendapatan,
                'prev_jumlah_perbelanjaan' => $prevJumlahPerbelanjaan,
                'prev_lebihan_kurangan' => $prevJumlahPendapatan - $prevJumlahPerbelanjaan,
            ],
        ]);
    }

    public function tabung(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_masjid' => ['nullable', 'integer', 'min:1'],
            'tarikh_dari' => ['nullable', 'date'],
            'tarikh_hingga' => ['nullable', 'date', 'after_or_equal:tarikh_dari'],
        ]);

        $actor = $this->actor($request);
        $idMasjid = $this->resolveMasjidId($request, $actor);
        $tarikhDari = (string) ($validated['tarikh_dari'] ?? now()->startOfMonth()->toDateString());
        $tarikhHingga = (string) ($validated['tarikh_hingga'] ?? now()->toDateString());

        $hasilByTabung = Hasil::query()
            ->withoutTenantScope()
            ->where('id_masjid', $idMasjid)
            ->whereNotNull('id_tabung_khas')
            ->whereBetween('tarikh', [$tarikhDari, $tarikhHingga])
            ->get(['id_tabung_khas', 'jumlah'])
            ->groupBy(fn(Hasil $row): string => (string) ($row->id_tabung_khas ?? 0))
            ->map(fn($bucket): float => (float) $bucket->sum('jumlah'));

        $belanjaByTabung = Belanja::query()
            ->withoutTenantScope()
            ->notDeleted()
            ->where('id_masjid', $idMasjid)
            ->whereNotNull('id_tabung_khas')
            ->whereBetween('tarikh', [$tarikhDari, $tarikhHingga])
            ->get(['id_tabung_khas', 'amaun'])
            ->groupBy(fn(Belanja $row): string => (string) ($row->id_tabung_khas ?? 0))
            ->map(fn($bucket): float => (float) $bucket->sum('amaun'));

        $tabungIds = $hasilByTabung->keys()
            ->merge($belanjaByTabung->keys())
            ->filter(fn(string $id): bool => $id !== '0')
            ->unique()
            ->sort()
            ->values();

        $tabungNamaById = TabungKhas::query()
            ->withoutTenantScope()
            ->where('id_masjid', $idMasjid)
            ->whereIn('id', $tabungIds->map(fn(string $id): int => (int) $id))
            ->pluck('nama_tabung', 'id');

        $rows = $tabungIds->map(function (string $tabungId) use ($hasilByTabung, $belanjaByTabung, $tabungNamaById): array {
            $id = (int) $tabungId;
            $masuk = (float) ($hasilByTabung[$tabungId] ?? 0);
            $keluar = (float) ($belanjaByTabung[$tabungId] ?? 0);

            return [
                'id_tabung' => $id,
                'nama_tabung' => (string) ($tabungNamaById[$id] ?? ('Tabung #' . $id)),
                'masuk_tempoh' => $masuk,
                'keluar_tempoh' => $keluar,
                'baki_terkumpul' => $masuk - $keluar,
            ];
        });

        return response()->json([
            'data' => [
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
            ],
        ]);
    }

    private function resolveMasjidId(Request $request, $actor): int
    {
        if ($actor->peranan === 'superadmin') {
            $idMasjid = $request->integer('id_masjid');
            if ($idMasjid <= 0) {
                throw ValidationException::withMessages([
                    'id_masjid' => ['Field id_masjid is required for superadmin requests.'],
                ]);
            }

            return $idMasjid;
        }

        if ((int) ($actor->id_masjid ?? 0) <= 0) {
            abort(403, 'Unauthorized');
        }

        return (int) $actor->id_masjid;
    }

    private function resolveButiran(?string $catatan, string $fallback): string
    {
        $nilai = trim((string) $catatan);

        return $nilai !== '' ? $nilai : $fallback;
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
}
