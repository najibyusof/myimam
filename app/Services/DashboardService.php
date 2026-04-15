<?php

namespace App\Services;

use App\Models\Akaun;
use App\Models\BaucarBayaran;
use App\Models\Belanja;
use App\Models\Hasil;
use App\Models\Masjid;
use App\Models\TenantSubscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    // =========================================================================
    // SUPERADMIN — system-wide SaaS view
    // =========================================================================

    public function getSuperAdminData(): array
    {
        $totalMasjids        = Masjid::count();
        $activeMasjids       = Masjid::active()->count();
        $suspendedMasjids    = Masjid::suspended()->count();
        $activeSubscriptions = TenantSubscription::active()->count();
        $expiredSubscriptions = TenantSubscription::expired()->count();
        $expiringSoon        = TenantSubscription::expiringBefore(now()->addDays(7))->count();
        $totalRevenue        = (float) TenantSubscription::sum('amount_paid');

        $alerts              = $this->buildSuperAdminAlerts($expiringSoon, $suspendedMasjids);
        $tenantGrowthChart   = $this->getTenantGrowthChart();
        $subscriptionStatusChart = $this->getSubscriptionStatusChart(
            $activeSubscriptions,
            $expiredSubscriptions,
            max(0, $totalMasjids - $activeMasjids - $suspendedMasjids)
        );
        $topMasjids  = $this->getTopMasjidsByIncome(5);
        $masjidTable = $this->getMasjidTableData();

        return compact(
            'totalMasjids',
            'activeMasjids',
            'suspendedMasjids',
            'activeSubscriptions',
            'expiredSubscriptions',
            'expiringSoon',
            'totalRevenue',
            'alerts',
            'tenantGrowthChart',
            'subscriptionStatusChart',
            'topMasjids',
            'masjidTable'
        );
    }

    private function buildSuperAdminAlerts(int $expiringSoon, int $suspended): array
    {
        $alerts = [];

        $expiredCount = Masjid::where('subscription_status', 'expired')->count();
        if ($expiredCount > 0) {
            $alerts[] = [
                'type'    => 'danger',
                'message' => "{$expiredCount} tenant(s) have expired subscriptions.",
            ];
        }

        if ($suspended > 0) {
            $alerts[] = [
                'type'    => 'warning',
                'message' => "{$suspended} tenant(s) are currently suspended.",
            ];
        }

        if ($expiringSoon > 0) {
            $alerts[] = [
                'type'    => 'info',
                'message' => "{$expiringSoon} subscription(s) expiring within the next 7 days.",
            ];
        }

        return $alerts;
    }

    private function getTenantGrowthChart(): array
    {
        $labels = [];
        $counts = [];

        for ($i = 5; $i >= 0; $i--) {
            $date     = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            $counts[] = Masjid::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        return ['labels' => $labels, 'counts' => $counts];
    }

    private function getSubscriptionStatusChart(int $active, int $expired, int $other): array
    {
        return [
            'labels' => ['Active', 'Expired', 'Other'],
            'counts' => [$active, $expired, $other],
        ];
    }

    /** Uses raw DB query to bypass all Eloquent global scopes */
    private function getTopMasjidsByIncome(int $limit): array
    {
        return DB::table('hasil')
            ->join('masjid', 'hasil.id_masjid', '=', 'masjid.id')
            ->selectRaw('masjid.id, masjid.nama, SUM(hasil.jumlah) as total_income')
            ->groupBy('masjid.id', 'masjid.nama')
            ->orderByDesc('total_income')
            ->limit($limit)
            ->get()
            ->map(fn($row) => [
                'id'           => $row->id,
                'nama'         => $row->nama,
                'total_income' => round($row->total_income, 2),
            ])
            ->toArray();
    }

    private function getMasjidTableData(): array
    {
        return Masjid::with('activeSubscription')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn(Masjid $m) => [
                'id'                  => $m->id,
                'nama'                => $m->nama,
                'status'              => $m->status,
                'subscription_status' => $m->subscription_status ?? 'none',
                'subscription_expiry' => $m->subscription_expiry?->format('d M Y') ?? '—',
                'is_expiring_soon'    => $m->subscription_expiry !== null
                    && $m->subscription_expiry->isFuture()
                    && $m->subscription_expiry->diffInDays(now()) <= 7,
            ])
            ->toArray();
    }

    // =========================================================================
    // TENANT — masjid financial view
    // =========================================================================

    public function getTenantData(int $masjidId, User $user): array
    {
        $masjid            = Masjid::findOrFail($masjidId);
        $metrics           = $this->getTenantMetrics($masjidId);
        $recentTransactions = $this->getRecentTransactions($masjidId);
        $accountBreakdown  = $this->getAccountBreakdown($masjidId);
        $monthlyTrend      = $this->getMonthlyTrend($masjidId);
        $insights          = $this->generateInsights($metrics, $masjid);
        $dashboardRole     = $this->resolveTenantRole($user);
        $contextLabel      = $masjid->nama;
        $currentMonth      = Carbon::now()->format('F Y');

        return compact(
            'masjid',
            'metrics',
            'recentTransactions',
            'accountBreakdown',
            'monthlyTrend',
            'insights',
            'dashboardRole',
            'contextLabel',
            'currentMonth'
        );
    }

    private function getTenantMetrics(int $masjidId): array
    {
        // Calculate balance from transactions per active account
        $accounts = Akaun::byMasjid($masjidId)
            ->with(['hasil', 'belanja', 'pindahanKeluar', 'pindahanMasuk'])
            ->aktif()
            ->get();

        $totalBalance = 0.0;
        foreach ($accounts as $account) {
            $totalBalance += (float) $account->hasil->sum('jumlah')
                - (float) $account->belanja->sum('amaun')
                - (float) $account->pindahanKeluar->sum('amaun')
                + (float) $account->pindahanMasuk->sum('amaun');
        }

        $monthStart = now()->copy()->startOfMonth()->toDateString();
        $monthEnd   = now()->copy()->endOfMonth()->toDateString();

        $monthlyIncome  = (float) Hasil::byMasjid($masjidId)->betweenDates($monthStart, $monthEnd)->sum('jumlah');
        $monthlyExpense = (float) Belanja::byMasjid($masjidId)->notDeleted()->betweenDates($monthStart, $monthEnd)->sum('amaun');
        $jumaatCollection = (float) Hasil::byMasjid($masjidId)->jumaat()->sum('jumlah');
        $pendingVouchers  = BaucarBayaran::byMasjid($masjidId)->draft()->count();

        return [
            'totalBalance'    => round($totalBalance, 2),
            'monthlyIncome'   => round($monthlyIncome, 2),
            'monthlyExpense'  => round($monthlyExpense, 2),
            'netMonthly'      => round($monthlyIncome - $monthlyExpense, 2),
            'jumaatCollection' => round($jumaatCollection, 2),
            'pendingVouchers' => $pendingVouchers,
        ];
    }

    private function getRecentTransactions(int $masjidId): array
    {
        $hasil = Hasil::byMasjid($masjidId)
            ->with(['akaun:id,nama_akaun', 'sumberHasil:id,nama_sumber'])
            ->latest('tarikh')
            ->limit(5)
            ->get()
            ->map(fn($h) => [
                'date'        => $h->tarikh->format('d M Y'),
                'type'        => 'Income',
                'description' => $h->sumberHasil?->nama_sumber ?? 'Income',
                'amount'      => round((float) $h->jumlah, 2),
                'account'     => $h->akaun?->nama_akaun ?? '—',
                'typeClass'   => 'success',
            ]);

        $belanja = Belanja::byMasjid($masjidId)
            ->notDeleted()
            ->with(['akaun:id,nama_akaun', 'kategoriBelanja:id,nama_kategori'])
            ->latest('tarikh')
            ->limit(5)
            ->get()
            ->map(fn($b) => [
                'date'        => $b->tarikh->format('d M Y'),
                'type'        => 'Expense',
                'description' => $b->kategoriBelanja?->nama_kategori ?? 'Expense',
                'amount'      => round((float) $b->amaun, 2),
                'account'     => $b->akaun?->nama_akaun ?? '—',
                'typeClass'   => 'danger',
            ]);

        return $hasil->merge($belanja)
            ->sortByDesc(fn($t) => Carbon::createFromFormat('d M Y', $t['date']))
            ->values()
            ->take(10)
            ->toArray();
    }

    private function getAccountBreakdown(int $masjidId): array
    {
        return Akaun::byMasjid($masjidId)
            ->with(['hasil', 'belanja', 'pindahanKeluar', 'pindahanMasuk'])
            ->aktif()
            ->get()
            ->map(fn($account) => [
                'name'          => $account->nama_akaun,
                'accountNumber' => $account->no_akaun,
                'balance'       => round(
                    (float) $account->hasil->sum('jumlah')
                        - (float) $account->belanja->sum('amaun')
                        - (float) $account->pindahanKeluar->sum('amaun')
                        + (float) $account->pindahanMasuk->sum('amaun'),
                    2
                ),
                'type' => $account->jenis,
                'bank' => $account->nama_bank,
            ])
            ->toArray();
    }

    private function getMonthlyTrend(int $masjidId): array
    {
        $labels = $incomes = $expenses = [];

        for ($i = 5; $i >= 0; $i--) {
            $date     = now()->copy()->subMonths($i);
            $start    = $date->copy()->startOfMonth()->toDateString();
            $end      = $date->copy()->endOfMonth()->toDateString();
            $labels[] = $date->format('M Y');
            $incomes[]  = round((float) Hasil::byMasjid($masjidId)->betweenDates($start, $end)->sum('jumlah'), 2);
            $expenses[] = round((float) Belanja::byMasjid($masjidId)->notDeleted()->betweenDates($start, $end)->sum('amaun'), 2);
        }

        return compact('labels', 'incomes', 'expenses');
    }

    private function generateInsights(array $metrics, Masjid $masjid): array
    {
        $insights = [];

        if (!$masjid->hasActiveSubscription()) {
            $insights[] = [
                'type'    => 'warning',
                'icon'    => 'AlertTriangle',
                'title'   => 'Subscription Expired',
                'message' => 'Your subscription has expired. Please renew to continue using the system.',
            ];
        }

        if ($metrics['totalBalance'] > 0) {
            $insights[] = [
                'type'    => 'success',
                'icon'    => 'CheckCircle',
                'title'   => 'Healthy Balance',
                'message' => 'RM ' . number_format($metrics['totalBalance'], 2) . ' available across all accounts.',
            ];
        } elseif ($metrics['totalBalance'] < 0) {
            $insights[] = [
                'type'    => 'danger',
                'icon'    => 'AlertTriangle',
                'title'   => 'Negative Balance',
                'message' => 'Total account balance is negative. Review expenses and fund accounts.',
            ];
        }

        if ($metrics['monthlyExpense'] > $metrics['monthlyIncome'] && $metrics['monthlyIncome'] > 0) {
            $insights[] = [
                'type'    => 'warning',
                'icon'    => 'TrendingDown',
                'title'   => 'High Expenses This Month',
                'message' => 'Monthly expenses exceed income by RM ' . number_format(abs($metrics['netMonthly']), 2) . '.',
            ];
        }

        if ($metrics['pendingVouchers'] > 0) {
            $insights[] = [
                'type'    => 'info',
                'icon'    => 'Clock',
                'title'   => 'Pending Approvals',
                'message' => $metrics['pendingVouchers'] . ' voucher(s) awaiting approval.',
            ];
        }

        return $insights;
    }

    private function resolveTenantRole(User $user): string
    {
        $map = [
            'Bendahari'     => 'Treasurer',
            'FinanceOfficer' => 'Finance Officer',
            'Auditor'       => 'Auditor',
            'Admin'         => 'Administrator',
            'MasjidOfficer' => 'Masjid Officer',
            'Manager'       => 'Manager',
        ];

        foreach ($map as $role => $label) {
            if ($user->hasRole($role)) {
                return $label;
            }
        }

        return 'Member';
    }
}
