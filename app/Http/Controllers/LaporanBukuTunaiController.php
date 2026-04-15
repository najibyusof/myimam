<?php

namespace App\Http\Controllers;

use App\Exports\LaporanBukuTunaiExport;
use App\Models\Akaun;
use App\Models\Belanja;
use App\Models\Hasil;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class LaporanBukuTunaiController extends Controller
{
    public function index(Request $request): View
    {
        $actor = $request->user();
        $idMasjid = (int) ($actor?->id_masjid ?? 0);
        $isSuperadmin = $this->isSuperadmin($request);

        abort_if($idMasjid <= 0 && !$isSuperadmin, 403);

        $akaunList = Akaun::query()
            ->when($isSuperadmin, fn($query) => $query->withoutTenantScope())
            ->when(!$isSuperadmin, fn($query) => $query->byMasjid($idMasjid))
            ->aktif()
            ->orderBy('nama_akaun')
            ->get(['id', 'id_masjid', 'nama_akaun']);

        $filters = [
            'akaun_id' => (int) $request->query('akaun_id', 0) > 0 ? (int) $request->query('akaun_id') : null,
            'tarikh_mula' => $request->query('tarikh_mula') ?: now()->startOfMonth()->toDateString(),
            'tarikh_akhir' => $request->query('tarikh_akhir') ?: now()->toDateString(),
            'baki_awal' => (float) $request->query('baki_awal', 0),
        ];

        $laporan = null;
        if ($request->filled(['akaun_id', 'tarikh_mula', 'tarikh_akhir'])) {
            $laporan = $this->generate($request, $idMasjid);
        }

        return view('laporan.buku-tunai', [
            'akaunList' => $akaunList,
            'filters' => $filters,
            'laporan' => $laporan,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $laporan = $this->generate($request);
        $filename = 'laporan-buku-tunai-' . now()->format('Ymd_His') . '.pdf';

        return Pdf::loadView('laporan.buku-tunai-pdf', [
            'laporan' => $laporan,
        ])->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $laporan = $this->generate($request);
        $filename = 'laporan-buku-tunai-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new LaporanBukuTunaiExport($laporan), $filename);
    }

    public function printView(Request $request): View
    {
        $laporan = $this->generate($request);

        return view('laporan.buku-tunai-print', [
            'laporan' => $laporan,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function generate(Request $request, ?int $idMasjid = null): array
    {
        $isSuperadmin = $this->isSuperadmin($request);
        $idMasjid = $idMasjid ?? (int) ($request->user()?->id_masjid ?? 0);
        abort_if($idMasjid <= 0 && !$isSuperadmin, 403);

        $validated = $this->validateFilters($request);

        $akaunId = (int) $validated['akaun_id'];
        $tarikhMula = (string) $validated['tarikh_mula'];
        $tarikhAkhir = (string) $validated['tarikh_akhir'];
        $bakiAwal = (float) ($validated['baki_awal'] ?? 0);

        if ($idMasjid <= 0 && $isSuperadmin) {
            $idMasjid = (int) Akaun::query()
                ->withoutTenantScope()
                ->whereKey($akaunId)
                ->value('id_masjid');
        }

        abort_if($idMasjid <= 0, 403);

        $akaun = Akaun::query()
            ->when($isSuperadmin, fn($query) => $query->withoutTenantScope())
            ->byMasjid($idMasjid)
            ->whereKey($akaunId)
            ->firstOrFail(['id', 'nama_akaun']);

        $transaksiHasil = Hasil::query()
            ->byMasjid($idMasjid)
            ->byAkaun($akaunId)
            ->betweenDates($tarikhMula, $tarikhAkhir)
            ->orderBy('tarikh')
            ->orderBy('id')
            ->get(['id', 'tarikh', 'catatan', 'jumlah'])
            ->map(fn(Hasil $hasil): array => [
                'id' => (int) $hasil->id,
                'tarikh' => optional($hasil->tarikh)->toDateString(),
                'butiran' => $this->resolveButiran($hasil->catatan, 'Hasil'),
                'masuk' => (float) $hasil->jumlah,
                'keluar' => 0.0,
                'susunan' => 'hasil',
            ]);

        $transaksiBelanja = Belanja::query()
            ->byMasjid($idMasjid)
            ->where('id_akaun', $akaunId)
            ->notDeleted()
            ->approved()
            ->betweenDates($tarikhMula, $tarikhAkhir)
            ->orderBy('tarikh')
            ->orderBy('id')
            ->get(['id', 'tarikh', 'catatan', 'amaun'])
            ->map(fn(Belanja $belanja): array => [
                'id' => (int) $belanja->id,
                'tarikh' => optional($belanja->tarikh)->toDateString(),
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

        $barisLaporan = $transaksi->map(function (array $baris) use (&$jumlahMasuk, &$jumlahKeluar, &$bakiSemasa): array {
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

        return [
            'akaun' => $akaun,
            'tempoh' => [
                'tarikh_mula' => $tarikhMula,
                'tarikh_akhir' => $tarikhAkhir,
            ],
            'rows' => $barisLaporan,
            'ringkasan' => [
                'baki_awal' => $bakiAwal,
                'jumlah_masuk' => $jumlahMasuk,
                'jumlah_keluar' => $jumlahKeluar,
                'baki_akhir' => $bakiSemasa,
            ],
        ];
    }

    private function resolveButiran(?string $catatan, string $fallback): string
    {
        $nilai = trim((string) $catatan);

        return $nilai !== '' ? $nilai : $fallback;
    }

    /**
     * @return array{akaun_id:int,tarikh_mula:string,tarikh_akhir:string,baki_awal?:float|int|string|null}
     */
    private function validateFilters(Request $request): array
    {
        $validator = Validator::make(
            $request->all(),
            [
                'akaun_id' => ['required', 'integer', 'exists:akaun,id'],
                'tarikh_mula' => ['required', 'date', 'before_or_equal:tarikh_akhir', 'before_or_equal:today'],
                'tarikh_akhir' => ['required', 'date', 'after_or_equal:tarikh_mula', 'before_or_equal:today'],
                'baki_awal' => ['nullable', 'numeric'],
            ],
            [
                'tarikh_mula.before_or_equal' => 'Tarikh mula mesti sama atau sebelum tarikh akhir.',
                'tarikh_akhir.after_or_equal' => 'Tarikh akhir mesti sama atau selepas tarikh mula.',
                'tarikh_akhir.before_or_equal' => 'Tarikh akhir tidak boleh melebihi tarikh hari ini.',
            ]
        );

        $validated = $validator->validate();

        $mula = Carbon::parse((string) $validated['tarikh_mula']);
        $akhir = Carbon::parse((string) $validated['tarikh_akhir']);
        if ($mula->diffInDays($akhir) > 366) {
            throw ValidationException::withMessages([
                'tarikh_akhir' => 'Julat tarikh tidak boleh melebihi 12 bulan.',
            ]);
        }

        return $validated;
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
