<?php

namespace App\Services;

use App\Models\Akaun;
use App\Models\BaucarBayaran;
use App\Models\Belanja;
use App\Models\Hasil;
use App\Models\PindahanAkaun;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportingManagementService
{
    /**
     * Build all reporting datasets from one filter set.
     *
     * @param array{masjid_id:?int,akaun_id:?int,date_from:?string,date_to:?string} $filters
     */
    public function build(array $filters): array
    {
        return [
            'incomeVsExpense' => $this->incomeVsExpense($filters),
            'accountSummary'  => $this->accountSummary($filters),
            'monthlyReport'   => $this->monthlyReport($filters),
        ];
    }

    /**
     * @param array{masjid_id:?int,akaun_id:?int,date_from:?string,date_to:?string} $filters
     */
    private function incomeVsExpense(array $filters): array
    {
        $hasil = Hasil::query();
        $belanja = Belanja::query()->notDeleted()->approved();
        $baucar = BaucarBayaran::query()->approved();
        $pindahan = PindahanAkaun::query();

        $this->applyMasjidAndDate($hasil, $filters, 'tarikh');
        $this->applyMasjidAndDate($belanja, $filters, 'tarikh');
        $this->applyMasjidAndDate($baucar, $filters, 'tarikh');
        $this->applyMasjidAndDate($pindahan, $filters, 'tarikh');

        if (!empty($filters['akaun_id'])) {
            $akaunId = (int) $filters['akaun_id'];
            $hasil->where('id_akaun', $akaunId);
            $belanja->where('id_akaun', $akaunId);
            $baucar->where('id_akaun', $akaunId);
            $pindahan->forAkaun($akaunId);
        }

        $incomeTotal = (float) $hasil->sum('jumlah');
        $expenseTotal = (float) $belanja->sum('amaun');
        $voucherTotal = (float) $baucar->sum('jumlah');
        $transferTotal = (float) $pindahan->sum('amaun');

        return [
            'income_total'    => $incomeTotal,
            'expense_total'   => $expenseTotal,
            'net_balance'     => $incomeTotal - $expenseTotal,
            'voucher_total'   => $voucherTotal,
            'transfer_total'  => $transferTotal,
        ];
    }

    /**
     * @param array{masjid_id:?int,akaun_id:?int,date_from:?string,date_to:?string} $filters
     * @return Collection<int, array<string,mixed>>
     */
    private function accountSummary(array $filters): Collection
    {
        $akaun = Akaun::query()
            ->when(!empty($filters['masjid_id']), fn ($q) => $q->byMasjid((int) $filters['masjid_id']))
            ->when(!empty($filters['akaun_id']), fn ($q) => $q->where('id', (int) $filters['akaun_id']))
            ->orderBy('nama_akaun')
            ->get(['id', 'nama_akaun', 'jenis']);

        return $akaun->map(function (Akaun $item) use ($filters): array {
            $hasil = Hasil::query()->where('id_akaun', $item->id);
            $belanja = Belanja::query()->notDeleted()->approved()->where('id_akaun', $item->id);
            $baucar = BaucarBayaran::query()->approved()->where('id_akaun', $item->id);
            $transferOut = PindahanAkaun::query()->where('dari_akaun_id', $item->id);
            $transferIn = PindahanAkaun::query()->where('ke_akaun_id', $item->id);

            $this->applyMasjidAndDate($hasil, $filters, 'tarikh');
            $this->applyMasjidAndDate($belanja, $filters, 'tarikh');
            $this->applyMasjidAndDate($baucar, $filters, 'tarikh');
            $this->applyMasjidAndDate($transferOut, $filters, 'tarikh');
            $this->applyMasjidAndDate($transferIn, $filters, 'tarikh');

            $income = (float) $hasil->sum('jumlah');
            $expense = (float) $belanja->sum('amaun');
            $voucher = (float) $baucar->sum('jumlah');
            $in = (float) $transferIn->sum('amaun');
            $out = (float) $transferOut->sum('amaun');

            return [
                'akaun_id'      => $item->id,
                'akaun_nama'    => $item->nama_akaun,
                'akaun_jenis'   => $item->jenis,
                'income_total'  => $income,
                'expense_total' => $expense,
                'voucher_total' => $voucher,
                'transfer_in'   => $in,
                'transfer_out'  => $out,
                'balance'       => $income - $expense + $in - $out,
            ];
        });
    }

    /**
     * @param array{masjid_id:?int,akaun_id:?int,date_from:?string,date_to:?string} $filters
     * @return Collection<int, array<string,mixed>>
     */
    private function monthlyReport(array $filters): Collection
    {
        $hasil = Hasil::query()
            ->selectRaw("DATE_FORMAT(tarikh, '%Y-%m') as month_key")
            ->selectRaw('SUM(jumlah) as income_total')
            ->when(!empty($filters['masjid_id']), fn ($q) => $q->byMasjid((int) $filters['masjid_id']))
            ->when(!empty($filters['akaun_id']), fn ($q) => $q->where('id_akaun', (int) $filters['akaun_id']));
        $this->applyDateOnly($hasil, $filters, 'tarikh');
        $hasilRows = $hasil->groupBy('month_key')->pluck('income_total', 'month_key');

        $belanja = Belanja::query()->notDeleted()->approved()
            ->selectRaw("DATE_FORMAT(tarikh, '%Y-%m') as month_key")
            ->selectRaw('SUM(amaun) as expense_total')
            ->when(!empty($filters['masjid_id']), fn ($q) => $q->byMasjid((int) $filters['masjid_id']))
            ->when(!empty($filters['akaun_id']), fn ($q) => $q->where('id_akaun', (int) $filters['akaun_id']));
        $this->applyDateOnly($belanja, $filters, 'tarikh');
        $belanjaRows = $belanja->groupBy('month_key')->pluck('expense_total', 'month_key');

        $baucar = BaucarBayaran::query()->approved()
            ->selectRaw("DATE_FORMAT(tarikh, '%Y-%m') as month_key")
            ->selectRaw('SUM(jumlah) as voucher_total')
            ->when(!empty($filters['masjid_id']), fn ($q) => $q->byMasjid((int) $filters['masjid_id']))
            ->when(!empty($filters['akaun_id']), fn ($q) => $q->where('id_akaun', (int) $filters['akaun_id']));
        $this->applyDateOnly($baucar, $filters, 'tarikh');
        $baucarRows = $baucar->groupBy('month_key')->pluck('voucher_total', 'month_key');

        $pindahan = PindahanAkaun::query()
            ->selectRaw("DATE_FORMAT(tarikh, '%Y-%m') as month_key")
            ->selectRaw('SUM(amaun) as transfer_total')
            ->when(!empty($filters['masjid_id']), fn ($q) => $q->byMasjid((int) $filters['masjid_id']))
            ->when(!empty($filters['akaun_id']), fn ($q) => $q->forAkaun((int) $filters['akaun_id']));
        $this->applyDateOnly($pindahan, $filters, 'tarikh');
        $pindahanRows = $pindahan->groupBy('month_key')->pluck('transfer_total', 'month_key');

        $keys = collect($hasilRows->keys())
            ->merge($belanjaRows->keys())
            ->merge($baucarRows->keys())
            ->merge($pindahanRows->keys())
            ->unique()
            ->sort()
            ->values();

        return $keys->map(function (string $key) use ($hasilRows, $belanjaRows, $baucarRows, $pindahanRows): array {
            $income = (float) ($hasilRows[$key] ?? 0);
            $expense = (float) ($belanjaRows[$key] ?? 0);

            return [
                'month'          => $key,
                'income_total'   => $income,
                'expense_total'  => $expense,
                'net'            => $income - $expense,
                'voucher_total'  => (float) ($baucarRows[$key] ?? 0),
                'transfer_total' => (float) ($pindahanRows[$key] ?? 0),
            ];
        });
    }

    private function applyMasjidAndDate($query, array $filters, string $column): void
    {
        if (!empty($filters['masjid_id'])) {
            $query->byMasjid((int) $filters['masjid_id']);
        }

        $this->applyDateOnly($query, $filters, $column);
    }

    private function applyDateOnly($query, array $filters, string $column): void
    {
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween($column, [$filters['date_from'], $filters['date_to']]);
        }
    }
}
