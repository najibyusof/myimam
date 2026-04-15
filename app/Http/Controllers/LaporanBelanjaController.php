<?php

namespace App\Http\Controllers;

use App\Exports\LaporanBelanjaExport;
use App\Models\Akaun;
use App\Models\Belanja;
use App\Models\KategoriBelanja;
use App\Models\Masjid;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class LaporanBelanjaController extends Controller
{
    public function index(Request $request): View
    {
        $data = $this->buildLaporanData($request);

        return view('laporan.belanja', $data);
    }

    public function exportPdf(Request $request)
    {
        abort_if($this->isSuperadmin($request) && !(int) $request->query('masjid_id', 0), 403);

        $data = $this->buildLaporanData($request);
        $filename = 'laporan-belanja-' . now()->format('Ymd_His') . '.pdf';

        return Pdf::loadView('laporan.belanja-pdf', $data)->download($filename);
    }

    public function exportExcel(Request $request)
    {
        abort_if($this->isSuperadmin($request) && !(int) $request->query('masjid_id', 0), 403);

        $data = $this->buildLaporanData($request);
        $filename = 'laporan-belanja-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new LaporanBelanjaExport($data), $filename);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildLaporanData(Request $request): array
    {
        $actor = $request->user();
        $isSuperadmin = $this->isSuperadmin($request);
        $masjidContext = $this->resolveMasjidContext($request, $isSuperadmin);
        $idMasjid = $masjidContext['id'];

        abort_if($idMasjid <= 0 && !$isSuperadmin, 403);

        $hariIni = Carbon::today();
        $tarikhDari = Carbon::parse($request->query('tarikh_dari', $hariIni->copy()->startOfMonth()))->toDateString();
        $tarikhHingga = Carbon::parse($request->query('tarikh_hingga', $hariIni))->toDateString();

        // Validate date range
        if ($tarikhDari > $tarikhHingga) {
            $tarikhDari = $hariIni->copy()->startOfMonth()->toDateString();
            $tarikhHingga = $hariIni->toDateString();
        }

        $jenisPaparan = (string) $request->query('jenis_paparan', 'ringkasan_kategori');
        if (!in_array($jenisPaparan, ['ringkasan_kategori', 'ringkasan_bulan', 'senarai_transaksi'], true)) {
            $jenisPaparan = 'ringkasan_kategori';
        }

        $kategoriId = $request->query('kategori_id');
        $akaunId = $request->query('akaun_id');
        $status = strtolower((string) $request->query('status', 'all'));

        if (!in_array($status, ['all', 'draf', 'lulus'], true)) {
            $status = 'all';
        }

        $requiresMasjidSelection = $isSuperadmin && !$masjidContext['selected_id'];

        $ringkasan = $requiresMasjidSelection ? collect() : $this->buildRingkasanKategori(
            $tarikhDari,
            $tarikhHingga,
            $idMasjid,
            $isSuperadmin,
            $kategoriId,
            $akaunId,
            $status
        );
        $jumlahKeseluruhan = (float) $ringkasan->sum('jumlah');

        $ringkasanBulan = collect();
        if ($jenisPaparan === 'ringkasan_bulan' && !$requiresMasjidSelection) {
            $ringkasanBulan = $this->buildRingkasanBulan(
                $tarikhDari,
                $tarikhHingga,
                $idMasjid,
                $isSuperadmin,
                $kategoriId,
                $akaunId,
                $status
            );
            $jumlahKeseluruhan = (float) $ringkasanBulan->sum('jumlah');
        }

        $senariTransaksi = collect();
        if ($jenisPaparan === 'senarai_transaksi' && !$requiresMasjidSelection) {
            $senariTransaksi = $this->buildSenariTransaksi(
                $tarikhDari,
                $tarikhHingga,
                $idMasjid,
                $isSuperadmin,
                $kategoriId,
                $akaunId,
                $status
            );
            $jumlahKeseluruhan = (float) $senariTransaksi->sum('amaun');
        }

        // Get categories for filter dropdown
        $akaunList = $requiresMasjidSelection ? collect() : $this->getAkaunList($idMasjid, $isSuperadmin);
        $kategoriList = $requiresMasjidSelection ? collect() : $this->getKategoriList($idMasjid, $isSuperadmin);

        return [
            'filters' => [
                'tarikh_dari' => $tarikhDari,
                'tarikh_hingga' => $tarikhHingga,
                'jenis_paparan' => $jenisPaparan,
                'kategori_id' => $kategoriId,
                'akaun_id' => $akaunId,
                'status' => $status,
                'masjid_id' => $masjidContext['selected_id'],
            ],
            'rows' => $ringkasan,
            'ringkasan_bulan' => $ringkasanBulan,
            'senarai_rows' => $senariTransaksi,
            'jumlah_keseluruhan' => $jumlahKeseluruhan,
            'is_superadmin' => $isSuperadmin,
            'kategori_list' => $kategoriList,
            'akaun_list' => $akaunList,
            'masjid_list' => $masjidContext['options'],
            'selected_masjid' => $masjidContext['masjid'],
            'requires_masjid_selection' => $requiresMasjidSelection,
        ];
    }

    /**
     * Build summary grouped by category
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function buildRingkasanKategori(
        string $tarikhDari,
        string $tarikhHingga,
        int $idMasjid,
        bool $isSuperadmin,
        ?string $kategoriId,
        ?string $akaunId,
        string $status
    ): Collection {
        $query = Belanja::query();

        $this->applyCommonFilters($query, $tarikhDari, $tarikhHingga, $idMasjid, $isSuperadmin, $kategoriId, $akaunId, $status);

        $aggregated = $query
            ->selectRaw('id_kategori_belanja, kategori_belanja.nama_kategori, SUM(amaun) as jumlah, COUNT(*) as bil_rekod')
            ->leftJoin('kategori_belanja', 'belanja.id_kategori_belanja', '=', 'kategori_belanja.id')
            ->groupBy('id_kategori_belanja', 'kategori_belanja.nama_kategori')
            ->orderBy('kategori_belanja.nama_kategori')
            ->get();

        return $aggregated->map(function ($row): array {
            return [
                'kategori' => $row->nama_kategori ?: 'Kategori Tidak Diketahui',
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
    private function buildRingkasanBulan(
        string $tarikhDari,
        string $tarikhHingga,
        int $idMasjid,
        bool $isSuperadmin,
        ?string $kategoriId,
        ?string $akaunId,
        string $status
    ): Collection {
        $query = Belanja::query();

        $this->applyCommonFilters($query, $tarikhDari, $tarikhHingga, $idMasjid, $isSuperadmin, $kategoriId, $akaunId, $status);

        $aggregated = $query
            ->selectRaw('DATE_FORMAT(tarikh, "%Y-%m") as bulan, SUM(amaun) as jumlah, COUNT(*) as bil_rekod')
            ->groupByRaw('DATE_FORMAT(tarikh, "%Y-%m")')
            ->orderByRaw('DATE_FORMAT(tarikh, "%Y-%m") DESC')
            ->get();

        return $aggregated->map(function ($row): array {
            $bulanObj = Carbon::createFromFormat('Y-m', $row->bulan);
            return [
                'bulan' => $bulanObj->translatedFormat('M Y'),
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
    private function buildSenariTransaksi(
        string $tarikhDari,
        string $tarikhHingga,
        int $idMasjid,
        bool $isSuperadmin,
        ?string $kategoriId,
        ?string $akaunId,
        string $status
    ): Collection {
        $query = Belanja::query()
            ->with(['kategoriBelanja:id,nama_kategori', 'akaun:id,nama_akaun']);

        $this->applyCommonFilters($query, $tarikhDari, $tarikhHingga, $idMasjid, $isSuperadmin, $kategoriId, $akaunId, $status);

        $records = $query
            ->orderBy('tarikh', 'desc')
            ->orderBy('id', 'desc')
            ->get(['id', 'tarikh', 'id_kategori_belanja', 'id_akaun', 'penerima', 'amaun', 'status', 'catatan']);

        return $records->map(function ($row): array {
            return [
                'id' => $row->id,
                'tarikh' => $row->tarikh->format('Y-m-d'),
                'kategori' => optional($row->kategoriBelanja)->nama_kategori ?? 'Kategori Tidak Diketahui',
                'akaun' => optional($row->akaun)->nama_akaun ?? 'Akaun Tidak Diketahui',
                'penerima' => $row->penerima ?: '-',
                'amaun' => (float) $row->amaun,
                'status' => $row->status ?: 'DRAF',
                'catatan' => $row->catatan ?: '-',
                'edit_url' => route('admin.belanja.edit', $row->id),
            ];
        });
    }

    private function applyCommonFilters(
        Builder $query,
        string $tarikhDari,
        string $tarikhHingga,
        int $idMasjid,
        bool $isSuperadmin,
        ?string $kategoriId,
        ?string $akaunId,
        string $status
    ): void {
        $query->notDeleted()->whereBetween('tarikh', [$tarikhDari, $tarikhHingga]);
        $query->withoutTenantScope()->where('id_masjid', $idMasjid);

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
    }

    /**
     * Get list of categories for dropdown
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function getKategoriList(int $idMasjid, bool $isSuperadmin): Collection
    {
        $query = KategoriBelanja::query()
            ->withoutTenantScope()
            ->aktif()
            ->where('id_masjid', $idMasjid);

        return $query->orderBy('nama_kategori')->get(['id', 'nama_kategori'])->map(function ($row) {
            return ['id' => $row->id, 'name' => $row->nama_kategori];
        });
    }

    /**
     * Get list of accounts for dropdown
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function getAkaunList(int $idMasjid, bool $isSuperadmin): Collection
    {
        $query = Akaun::query()
            ->withoutTenantScope()
            ->aktif()
            ->where('id_masjid', $idMasjid);

        return $query->orderBy('nama_akaun')->get(['id', 'nama_akaun'])->map(function ($row) {
            return ['id' => $row->id, 'name' => $row->nama_akaun];
        });
    }

    /**
     * @return array{id:int,selected_id:?int,masjid:?Masjid,options:Collection<int, array{id:int,name:string}>}
     */
    private function resolveMasjidContext(Request $request, bool $isSuperadmin): array
    {
        $actor = $request->user();
        $selectedId = $isSuperadmin ? (int) $request->query('masjid_id', 0) : (int) ($actor?->id_masjid ?? 0);
        $selectedId = $selectedId > 0 ? $selectedId : null;

        $options = Masjid::query()
            ->orderBy('nama')
            ->get(['id', 'nama'])
            ->map(fn(Masjid $row): array => ['id' => (int) $row->id, 'name' => $row->nama]);

        $masjid = $selectedId
            ? Masjid::query()->whereKey($selectedId)->first(['id', 'nama'])
            : null;

        return [
            'id' => $selectedId ?? 0,
            'selected_id' => $selectedId,
            'masjid' => $masjid,
            'options' => $options,
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
