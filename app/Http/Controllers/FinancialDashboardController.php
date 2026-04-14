<?php

namespace App\Http\Controllers;

use App\Models\Akaun;
use App\Models\BaucarBayaran;
use App\Models\Belanja;
use App\Models\Hasil;
use App\Models\Masjid;
use App\Models\PindahanAkaun;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->authorize('view', Masjid::class);

        $user = $request->user();
        $masjidId = $user->peranan === 'superadmin' ? null : $user->id_masjid;

        // Multi-tenant safety: get masjid instance
        $masjid = $masjidId ? Masjid::findOrFail($masjidId) : null;

        // Financial metrics
        $metrics = $this->getFinancialMetrics($masjidId);

        // Recent transactions
        $recentTransactions = $this->getRecentTransactions($masjidId);

        // Account breakdown
        $accountBreakdown = $this->getAccountBreakdown($masjidId);

        // Monthly trend
        $monthlyTrend = $this->getMonthlyTrend($masjidId);

        // Insights
        $insights = $this->generateInsights($metrics, $masjid);

        $dashboardRole = $this->resolveDashboardRole($user);

        return view('financial-dashboard', [
            'metrics' => $metrics,
            'recentTransactions' => $recentTransactions,
            'accountBreakdown' => $accountBreakdown,
            'monthlyTrend' => $monthlyTrend,
            'insights' => $insights,
            'masjid' => $masjid,
            'dashboardRole' => $dashboardRole,
            'contextLabel' => $masjid?->nama ?? 'All Masjids',
            'currentMonth' => Carbon::now()->format('F Y'),
        ]);
    }

    /**
     * Get core financial metrics.
     */
    private function getFinancialMetrics(?int $masjidId): array
    {
        // Total Balance: Sum of all account balances
        $totalBalanceQuery = Akaun::query();
        if ($masjidId) {
            $totalBalanceQuery->byMasjid($masjidId);
        }

        // Calculate balance from transactions
        $accounts = $totalBalanceQuery->with(['hasil', 'belanja', 'pindahanKeluar', 'pindahanMasuk'])
            ->active()
            ->get();

        $totalBalance = 0;
        foreach ($accounts as $account) {
            $income = $account->hasil?->sum('jumlah') ?? 0;
            $expense = $account->belanja?->sum('amaun') ?? 0;
            $outgoing = $account->pindahanKeluar?->sum('amaun') ?? 0;
            $incoming = $account->pindahanMasuk?->sum('amaun') ?? 0;
            $totalBalance += ($income - $expense - $outgoing + $incoming);
        }

        // Current month dates
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        // Total Income (Current Month)
        $totalIncomeQuery = Hasil::query()
            ->betweenDates($monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d'));

        if ($masjidId) {
            $totalIncomeQuery->byMasjid($masjidId);
        }

        $totalIncome = $totalIncomeQuery->sum('jumlah');

        // Total Expense (Current Month)
        $totalExpenseQuery = Belanja::query()
            ->notDeleted()
            ->betweenDates($monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d'));

        if ($masjidId) {
            $totalExpenseQuery->byMasjid($masjidId);
        }

        $totalExpense = $totalExpenseQuery->sum('amaun');

        // Jumaat Collection
        $jumaatQuery = Hasil::query()->jumaat();
        if ($masjidId) {
            $jumaatQuery->byMasjid($masjidId);
        }

        $jumaatCollection = $jumaatQuery->sum('jumlah');

        // Pending Vouchers (Draft)
        $pendingVouchersQuery = BaucarBayaran::query()->draft();
        if ($masjidId) {
            $pendingVouchersQuery->byMasjid($masjidId);
        }

        $pendingVouchers = $pendingVouchersQuery->count();

        return [
            'totalBalance' => round($totalBalance, 2),
            'monthlyIncome' => round($totalIncome, 2),
            'monthlyExpense' => round($totalExpense, 2),
            'netMonthly' => round($totalIncome - $totalExpense, 2),
            'jumaatCollection' => round($jumaatCollection, 2),
            'pendingVouchers' => $pendingVouchers,
        ];
    }

    /**
     * Get recent transactions (latest 10 from Hasil + Belanja).
     */
    private function getRecentTransactions(?int $masjidId): array
    {
        $hasil = Hasil::query()
            ->with(['akaun:id,nama_akaun', 'sumberHasil:id,nama'])
            ->latest('tarikh');

        if ($masjidId) {
            $hasil->byMasjid($masjidId);
        }

        $hasilData = $hasil->limit(5)->get()->map(fn($h) => [
            'date' => $h->tarikh->format('d M Y'),
            'type' => 'Income',
            'description' => $h->sumberHasil?->nama ?? 'Income',
            'amount' => round($h->jumlah, 2),
            'account' => $h->akaun?->nama_akaun ?? '-',
            'typeClass' => 'success',
        ])->toArray();

        $belanja = Belanja::query()
            ->notDeleted()
            ->with(['akaun:id,nama_akaun', 'kategoriBelanja:id,nama'])
            ->latest('tarikh');

        if ($masjidId) {
            $belanja->byMasjid($masjidId);
        }

        $belanjaData = $belanja->limit(5)->get()->map(fn($b) => [
            'date' => $b->tarikh->format('d M Y'),
            'type' => 'Expense',
            'description' => $b->kategoriBelanja?->nama ?? 'Expense',
            'amount' => round($b->amaun, 2),
            'account' => $b->akaun?->nama_akaun ?? '-',
            'typeClass' => 'danger',
        ])->toArray();

        // Merge and sort by date (latest first)
        $transactions = collect($hasilData)
            ->merge($belanjaData)
            ->sortByDesc(fn($t) => Carbon::createFromFormat('d M Y', $t['date']))
            ->values()
            ->take(10)
            ->toArray();

        return $transactions;
    }

    /**
     * Get account breakdown (balance per account).
     */
    private function getAccountBreakdown(?int $masjidId): array
    {
        $query = Akaun::query()
            ->with(['hasil', 'belanja', 'pindahanKeluar', 'pindahanMasuk'])
            ->active();

        if ($masjidId) {
            $query->byMasjid($masjidId);
        }

        $accounts = $query->get();

        return $accounts->map(function ($account) {
            $income = $account->hasil?->sum('jumlah') ?? 0;
            $expense = $account->belanja?->sum('amaun') ?? 0;
            $outgoing = $account->pindahanKeluar?->sum('amaun') ?? 0;
            $incoming = $account->pindahanMasuk?->sum('amaun') ?? 0;
            $balance = round($income - $expense - $outgoing + $incoming, 2);

            return [
                'name' => $account->nama_akaun,
                'accountNumber' => $account->no_akaun,
                'balance' => $balance,
                'type' => $account->jenis,
                'bank' => $account->nama_bank,
            ];
        })->toArray();
    }

    /**
     * Get monthly trend data (last 6 months: income vs expense).
     */
    private function getMonthlyTrend(?int $masjidId): array
    {
        $months = [];
        $incomes = [];
        $expenses = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->startOfMonth();
            $monthEnd = $date->endOfMonth();
            $labels[] = $date->format('M Y');

            // Income for this month
            $incomeQuery = Hasil::query()
                ->betweenDates($monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d'));

            if ($masjidId) {
                $incomeQuery->byMasjid($masjidId);
            }

            $income = $incomeQuery->sum('jumlah');
            $incomes[] = round($income, 2);

            // Expense for this month
            $expenseQuery = Belanja::query()
                ->notDeleted()
                ->betweenDates($monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d'));

            if ($masjidId) {
                $expenseQuery->byMasjid($masjidId);
            }

            $expense = $expenseQuery->sum('amaun');
            $expenses[] = round($expense, 2);
        }

        return [
            'labels' => $labels,
            'incomes' => $incomes,
            'expenses' => $expenses,
        ];
    }

    /**
     * Generate dynamic insights based on financial data.
     */
    private function generateInsights(array $metrics, ?Masjid $masjid): array
    {
        $insights = [];

        // Subscription check
        if ($masjid && !$masjid->hasActiveSubscription()) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'AlertTriangle',
                'title' => 'Subscription Expired',
                'message' => 'Your subscription has expired. Please renew to continue using the system.',
            ];
        }

        // Balance health check
        if ($metrics['totalBalance'] > 0) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'CheckCircle',
                'title' => 'Healthy Balance',
                'message' => 'RM ' . number_format($metrics['totalBalance'], 2) . ' available across all accounts.',
            ];
        } elseif ($metrics['totalBalance'] < 0) {
            $insights[] = [
                'type' => 'danger',
                'icon' => 'AlertTriangle',
                'title' => 'Negative Balance',
                'message' => 'Total account balance is negative. Review expenses and fund accounts.',
            ];
        }

        // Monthly expense check
        if ($metrics['monthlyExpense'] > $metrics['monthlyIncome']) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'TrendingDown',
                'title' => 'High Expenses This Month',
                'message' => 'Monthly expenses exceed income. Balance: RM ' . number_format($metrics['netMonthly'], 2),
            ];
        }

        // Pending approvals
        if ($metrics['pendingVouchers'] > 0) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'Clock',
                'title' => 'Pending Approvals',
                'message' => $metrics['pendingVouchers'] . ' voucher(s) awaiting approval.',
            ];
        }

        // Jumaat collection insight
        if ($metrics['jumaatCollection'] > 0) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'TrendingUp',
                'title' => 'Jumaat Collection',
                'message' => 'RM ' . number_format($metrics['jumaatCollection'], 2) . ' collected from Jumaat prayers.',
            ];
        }

        return $insights;
    }

    /**
     * Resolve user's dashboard role.
     */
    private function resolveDashboardRole($user): string
    {
        $roleMap = [
            'Bendahari' => 'Treasurer',
            'FinanceOfficer' => 'Finance Officer',
            'Auditor' => 'Auditor',
            'Admin' => 'Administrator',
            'MasjidOfficer' => 'Masjid Officer',
            'superadmin' => 'Super Administrator',
        ];

        foreach (['Bendahari', 'FinanceOfficer', 'Auditor', 'Admin', 'MasjidOfficer'] as $role) {
            if ($user->hasRole($role)) {
                return $roleMap[$role] ?? $role;
            }
        }

        if ($user->peranan === 'superadmin') {
            return 'Super Administrator';
        }

        return 'Finance User';
    }
}
