<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-gray-400">Financial Overview</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">Finance Dashboard</h2>
                <p class="mt-1 text-sm text-gray-500">Real-time financial metrics for {{ $contextLabel }} •
                    {{ $currentMonth }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs font-medium uppercase tracking-[0.16em] text-indigo-700">{{ $dashboardRole }}</p>
                <p class="mt-1 text-sm text-gray-500">Last updated: {{ now()->format('d M Y, h:i A') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- SUBSCRIPTION BANNER --}}
            @if ($masjid && !$masjid->hasActiveSubscription())
                <div class="rounded-xl border-l-4 border-red-500 bg-red-50 p-4 shadow">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h3 class="font-semibold text-red-800">Subscription Expired</h3>
                            <p class="mt-1 text-sm text-red-700">Your subscription has ended. Please renew your
                                subscription to continue using the system and access all financial features.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- TOP STATS CARDS (6 cards) --}}
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                {{-- Total Balance Card --}}
                <article
                    class="group rounded-xl border border-gray-200 bg-white p-5 shadow transition hover:shadow-lg hover:-translate-y-0.5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Balance</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">RM
                                {{ number_format($metrics['totalBalance'], 2) }}</p>
                        </div>
                        <div class="rounded-lg bg-blue-100 p-2 text-blue-600 group-hover:bg-blue-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Across all accounts</p>
                </article>

                {{-- Monthly Income Card --}}
                <article
                    class="group rounded-xl border border-gray-200 bg-white p-5 shadow transition hover:shadow-lg hover:-translate-y-0.5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Monthly Income</p>
                            <p class="mt-2 text-2xl font-bold text-emerald-600">RM
                                {{ number_format($metrics['monthlyIncome'], 2) }}</p>
                        </div>
                        <div class="rounded-lg bg-emerald-100 p-2 text-emerald-600 group-hover:bg-emerald-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8L5.257 19.793a2 2 0 00.263 3.467m0 0h8m0 0v8" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Current month</p>
                </article>

                {{-- Monthly Expense Card --}}
                <article
                    class="group rounded-xl border border-gray-200 bg-white p-5 shadow transition hover:shadow-lg hover:-translate-y-0.5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Monthly Expense</p>
                            <p class="mt-2 text-2xl font-bold text-orange-600">RM
                                {{ number_format($metrics['monthlyExpense'], 2) }}</p>
                        </div>
                        <div class="rounded-lg bg-orange-100 p-2 text-orange-600 group-hover:bg-orange-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 17h8m0 0V9m0 8L5.257 5.207a2 2 0 00-.263 3.467m0 0h8m0 0V9" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Current month</p>
                </article>

                {{-- Net Monthly Card --}}
                <article
                    class="group rounded-xl border border-gray-200 bg-white p-5 shadow transition hover:shadow-lg hover:-translate-y-0.5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Net Monthly</p>
                            <p
                                class="mt-2 text-2xl font-bold {{ $metrics['netMonthly'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                RM {{ number_format($metrics['netMonthly'], 2) }}</p>
                        </div>
                        <div
                            class="rounded-lg {{ $metrics['netMonthly'] >= 0 ? 'bg-green-100 text-green-600 group-hover:bg-green-200' : 'bg-red-100 text-red-600 group-hover:bg-red-200' }} p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Income minus expense</p>
                </article>

                {{-- Jumaat Collection Card --}}
                <article
                    class="group rounded-xl border border-gray-200 bg-white p-5 shadow transition hover:shadow-lg hover:-translate-y-0.5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Jumaat Collection</p>
                            <p class="mt-2 text-2xl font-bold text-indigo-600">RM
                                {{ number_format($metrics['jumaatCollection'], 2) }}</p>
                        </div>
                        <div class="rounded-lg bg-indigo-100 p-2 text-indigo-600 group-hover:bg-indigo-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">All periods</p>
                </article>

                {{-- Pending Vouchers Card --}}
                <article
                    class="group rounded-xl border border-gray-200 bg-white p-5 shadow transition hover:shadow-lg hover:-translate-y-0.5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pending Vouchers</p>
                            <p class="mt-2 text-2xl font-bold text-amber-600">{{ $metrics['pendingVouchers'] }}</p>
                        </div>
                        <div class="rounded-lg bg-amber-100 p-2 text-amber-600 group-hover:bg-amber-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Awaiting approval</p>
                </article>
            </section>

            {{-- CHARTS SECTION --}}
            <section class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
                {{-- Monthly Trend Chart --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">6-Month Trend</p>
                            <h3 class="mt-1 text-xl font-semibold text-gray-900">Income vs Expense</h3>
                        </div>
                        <span
                            class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Chart.js</span>
                    </div>
                    <div class="mt-6 h-80">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                {{-- Account Breakdown (Pie) --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Account Balance</p>
                            <h3 class="mt-1 text-xl font-semibold text-gray-900">Distribution</h3>
                        </div>
                    </div>
                    <div class="mt-6 h-80 flex items-center justify-center">
                        <canvas id="accountBreakdownChart"></canvas>
                    </div>
                </div>
            </section>

            {{-- INSIGHTS PANEL --}}
            @if (count($insights) > 0)
                <section class="rounded-xl border border-gray-200 bg-white p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-900">Insights & Alerts</h3>
                    <div class="mt-4 space-y-3">
                        @foreach ($insights as $insight)
                            <div
                                class="flex items-start gap-3 rounded-lg {{ match ($insight['type']) {
                                    'warning' => 'bg-amber-50 border border-amber-200',
                                    'danger' => 'bg-red-50 border border-red-200',
                                    'success' => 'bg-emerald-50 border border-emerald-200',
                                    'info' => 'bg-blue-50 border border-blue-200',
                                    default => 'bg-gray-50 border border-gray-200',
                                } }} p-4">
                                <div class="flex-shrink-0 mt-0.5">
                                    @if ($insight['icon'] === 'AlertTriangle')
                                        <svg class="h-5 w-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @elseif ($insight['icon'] === 'CheckCircle')
                                        <svg class="h-5 w-5 text-emerald-600" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @elseif ($insight['icon'] === 'TrendingDown')
                                        <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 17h8m0 0V9m0 8L5.257 5.207a2 2 0 00-.263 3.467m0 0h8m0 0V9" />
                                        </svg>
                                    @elseif ($insight['icon'] === 'TrendingUp')
                                        <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 7h8m0 0v8m0-8L5.257 19.793a2 2 0 00.263 3.467m0 0h8m0 0v8" />
                                        </svg>
                                    @elseif ($insight['icon'] === 'Clock')
                                        <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00-.293.707l-2.828 2.829a1 1 0 101.415 1.415L9 9.414V6z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <p
                                        class="font-semibold {{ match ($insight['type']) {
                                            'warning' => 'text-amber-900',
                                            'danger' => 'text-red-900',
                                            'success' => 'text-emerald-900',
                                            'info' => 'text-blue-900',
                                            default => 'text-gray-900',
                                        } }}">
                                        {{ $insight['title'] }}</p>
                                    <p
                                        class="mt-1 text-sm {{ match ($insight['type']) {
                                            'warning' => 'text-amber-800',
                                            'danger' => 'text-red-800',
                                            'success' => 'text-emerald-800',
                                            'info' => 'text-blue-800',
                                            default => 'text-gray-700',
                                        } }}">
                                        {{ $insight['message'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- RECENT TRANSACTIONS TABLE --}}
            <section class="rounded-xl border border-gray-200 bg-white shadow">
                <div class="border-b border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Transaction History</p>
                            <h3 class="mt-1 text-xl font-semibold text-gray-900">Latest 10 transactions</h3>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    Type</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    Description</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    Account</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($recentTransactions as $transaction)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $transaction['date'] }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span
                                            class="inline-flex items-center rounded-full {{ $transaction['typeClass'] === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }} px-2.5 py-0.5 text-xs font-medium">
                                            {{ $transaction['type'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $transaction['description'] }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $transaction['account'] }}</td>
                                    <td
                                        class="px-6 py-4 text-right text-sm font-semibold {{ $transaction['typeClass'] === 'success' ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ $transaction['typeClass'] === 'success' ? '+' : '-' }} RM
                                        {{ number_format($transaction['amount'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No
                                        transactions recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- ACCOUNT BREAKDOWN TABLE --}}
            <section class="rounded-xl border border-gray-200 bg-white shadow">
                <div class="border-b border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Account Details</p>
                            <h3 class="mt-1 text-xl font-semibold text-gray-900">All accounts & balances</h3>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    Account Name</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    Account Number</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    Bank</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    Type</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($accountBreakdown as $account)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $account['name'] }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 font-mono">
                                        {{ $account['accountNumber'] }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $account['bank'] ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span
                                            class="inline-flex rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                            {{ ucfirst($account['type']) }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right text-sm font-semibold {{ $account['balance'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        RM {{ number_format($account['balance'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No accounts
                                        found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

        </div>
    </div>

    {{-- Chart.js Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const trendData = @json($monthlyTrend);
            const accountData = @json($accountBreakdown);

            // ─────────────────────────────────────────────────────────
            // 6-MONTH TREND CHART (Line Chart)
            // ─────────────────────────────────────────────────────────
            const trendCtx = document.getElementById('trendChart');
            if (trendCtx && typeof window.Chart !== 'undefined') {
                new window.Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: trendData.labels,
                        datasets: [{
                                label: 'Income',
                                data: trendData.incomes,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointBackgroundColor: '#10b981',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                            },
                            {
                                label: 'Expense',
                                data: trendData.expenses,
                                borderColor: '#f97316',
                                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointBackgroundColor: '#f97316',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                            }
                        ],
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15,
                                    font: {
                                        size: 12,
                                        weight: '500'
                                    }
                                }
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'RM ' + value.toLocaleString('en-MY', {
                                            maximumFractionDigits: 0
                                        });
                                    },
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    color: 'rgba(148, 163, 184, 0.1)',
                                    drawBorder: false,
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            // ─────────────────────────────────────────────────────────
            // ACCOUNT BREAKDOWN CHART (Doughnut Chart)
            // ─────────────────────────────────────────────────────────
            const breakdownCtx = document.getElementById('accountBreakdownChart');
            if (breakdownCtx && accountData.length > 0 && typeof window.Chart !== 'undefined') {
                const accountNames = accountData.map(a => a.name);
                const accountBalances = accountData.map(a => Math.abs(a.balance)); // Use absolute for cleaner chart
                const colors = [
                    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                    '#ec4899', '#06b6d4', '#6366f1', '#14b8a6', '#f97316'
                ];

                new window.Chart(breakdownCtx, {
                    type: 'doughnut',
                    data: {
                        labels: accountNames,
                        datasets: [{
                            data: accountBalances,
                            backgroundColor: colors.slice(0, accountNames.length),
                            borderColor: '#fff',
                            borderWidth: 2,
                        }],
                    },
                    options: {
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 12,
                                    font: {
                                        size: 11
                                    },
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'RM ' + context.parsed.toLocaleString('en-MY', {
                                            maximumFractionDigits: 2
                                        });
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
