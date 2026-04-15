<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-gray-400">
                    {{ __('dashboard.financial_overview') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">
                    {{ $masjid ? $masjid->nama : 'Dashboard' }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('dashboard.financial_subtitle', ['month' => $currentMonth]) }}
                </p>
                <p class="mt-1 text-xs font-medium uppercase tracking-[0.16em] text-indigo-700">
                    {{ $dashboardRole }}
                </p>
            </div>
            <div class="text-sm text-gray-500">
                Last updated: {{ now()->format('d M Y, h:i A') }}
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- SUBSCRIPTION BANNER --}}
            @if ($masjid && !$masjid->hasActiveSubscription())
                <div class="flex items-start gap-3 rounded-xl border-l-4 border-red-500 bg-red-50 p-4 shadow">
                    <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h3 class="font-semibold text-red-800">{{ __('dashboard.subscription_expired') }}</h3>
                        <p class="mt-1 text-sm text-red-700">{{ __('dashboard.subscription_expired_msg') }}</p>
                    </div>
                </div>
            @elseif (!$masjid)
                <div class="flex items-start gap-3 rounded-xl border-l-4 border-amber-500 bg-amber-50 p-4 shadow">
                    <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h3 class="font-semibold text-amber-900">{{ __('dashboard.no_masjid_assigned') }}</h3>
                        <p class="mt-1 text-sm text-amber-800">{{ __('dashboard.no_masjid_assigned_msg') }}</p>
                    </div>
                </div>
            @endif

            @if ($masjid)

                {{-- KPI STAT CARDS --}}
                <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    {{-- Total Balance --}}
                    <article
                        class="group col-span-1 rounded-xl border border-gray-200 bg-white p-5 shadow transition hover:-translate-y-0.5 hover:shadow-lg">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ __('dashboard.total_balance') }}</p>
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
                        <p class="mt-2 text-xs text-gray-500">{{ __('dashboard.across_all_accounts') }}</p>
                    </article>

                    {{-- Monthly Income --}}
                    <article
                        class="group rounded-xl border border-gray-200 bg-white p-5 shadow transition hover:-translate-y-0.5 hover:shadow-lg">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ __('dashboard.monthly_income') }}</p>
                                </p>
                                <p class="mt-2 text-2xl font-bold text-emerald-600">RM
                                    {{ number_format($metrics['monthlyIncome'], 2) }}</p>
                            </div>
                            <div class="rounded-lg bg-emerald-100 p-2 text-emerald-600 group-hover:bg-emerald-200">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">{{ now()->format('F Y') }}</p>
                    </article>

                    {{-- Monthly Expense --}}
                    <article
                        class="group rounded-xl border border-gray-200 bg-white p-5 shadow transition hover:-translate-y-0.5 hover:shadow-lg">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ __('dashboard.monthly_expense') }}</p>
                                </p>
                                <p class="mt-2 text-2xl font-bold text-orange-600">RM
                                    {{ number_format($metrics['monthlyExpense'], 2) }}</p>
                            </div>
                            <div class="rounded-lg bg-orange-100 p-2 text-orange-600 group-hover:bg-orange-200">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 17h8m0 0V9m0 8L5 5" />
                                </svg>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">{{ now()->format('F Y') }}</p>
                    </article>

                    {{-- Net Monthly --}}
                    <article
                        class="group rounded-xl border border-gray-200 bg-white p-5 shadow transition hover:-translate-y-0.5 hover:shadow-lg">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ __('dashboard.net_monthly') }}</p>
                                <p
                                    class="mt-2 text-2xl font-bold {{ $metrics['netMonthly'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    RM {{ number_format($metrics['netMonthly'], 2) }}
                                </p>
                            </div>
                            <div
                                class="rounded-lg p-2 {{ $metrics['netMonthly'] >= 0 ? 'bg-green-100 text-green-600 group-hover:bg-green-200' : 'bg-red-100 text-red-600 group-hover:bg-red-200' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">{{ __('dashboard.income_minus_expense') }}</p>
                    </article>

                    {{-- Jumaat Collection --}}
                    <article
                        class="group rounded-xl border border-gray-200 bg-white p-5 shadow transition hover:-translate-y-0.5 hover:shadow-lg">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ __('dashboard.jumaat_collection') }}</p>
                                </p>
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
                        <p class="mt-2 text-xs text-gray-500">{{ __('dashboard.all_periods') }}</p>
                    </article>

                    {{-- Pending Vouchers --}}
                    <article
                        class="group rounded-xl border border-gray-200 bg-white p-5 shadow transition hover:-translate-y-0.5 hover:shadow-lg">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ __('dashboard.pending_vouchers') }}</p>
                                </p>
                                <p class="mt-2 text-2xl font-bold text-amber-600">{{ $metrics['pendingVouchers'] }}</p>
                            </div>
                            <div class="rounded-lg bg-amber-100 p-2 text-amber-600 group-hover:bg-amber-200">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">{{ __('dashboard.awaiting_approval') }}</p>
                    </article>
                </section>

                {{-- CHARTS --}}
                <section class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
                    {{-- 6-Month Income vs Expense --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ __('dashboard.trend_subtitle') }}</p>
                                <h3 class="mt-1 text-xl font-semibold text-gray-900">
                                    {{ __('dashboard.income_vs_expense') }}</h3>
                            </div>
                            <span
                                class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Chart.js</span>
                        </div>
                        <div class="mt-6 h-80">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>

                    {{-- Account Distribution Doughnut --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow">
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500">{{ __('dashboard.account_balance_title') }}
                            </p>
                            <h3 class="mt-1 text-xl font-semibold text-gray-900">{{ __('dashboard.distribution') }}
                            </h3>
                        </div>
                        <div class="flex h-80 items-center justify-center">
                            @if (count($accountBreakdown) > 0)
                                <canvas id="accountChart"></canvas>
                            @else
                                <p class="text-sm text-gray-400">{{ __('dashboard.no_active_accounts') }}</p>
                            @endif
                        </div>
                    </div>
                </section>

                {{-- INSIGHTS --}}
                @if (count($insights) > 0)
                    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('dashboard.insights_alerts') }}</h3>
                        <div class="mt-4 space-y-3">
                            @foreach ($insights as $insight)
                                <div
                                    class="flex items-start gap-3 rounded-lg p-4 border
                                    {{ match ($insight['type']) {
                                        'warning' => 'bg-amber-50 border-amber-200',
                                        'danger' => 'bg-red-50 border-red-200',
                                        'success' => 'bg-emerald-50 border-emerald-200',
                                        'info' => 'bg-blue-50 border-blue-200',
                                        default => 'bg-gray-50 border-gray-200',
                                    } }}">
                                    <div class="mt-0.5 flex-shrink-0">
                                        @if ($insight['icon'] === 'AlertTriangle')
                                            <svg class="h-5 w-5 {{ $insight['type'] === 'danger' ? 'text-red-600' : 'text-amber-600' }}"
                                                fill="currentColor" viewBox="0 0 20 20">
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
                                                    d="M13 17h8m0 0V9m0 8L5 5" />
                                            </svg>
                                        @elseif ($insight['icon'] === 'Clock')
                                            <svg class="h-5 w-5 text-blue-600" fill="currentColor"
                                                viewBox="0 0 20 20">
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

                {{-- RECENT TRANSACTIONS --}}
                <section class="rounded-xl border border-gray-200 bg-white shadow">
                    <div class="border-b border-gray-200 p-6">
                        <p class="text-sm font-medium text-gray-500">{{ __('dashboard.transaction_history') }}</p>
                        <h3 class="mt-1 text-xl font-semibold text-gray-900">{{ __('dashboard.latest_transactions') }}
                        </h3>
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
                                        Amount (RM)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($recentTransactions as $txn)
                                    <tr class="transition hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $txn['date'] }}</td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                                {{ $txn['typeClass'] === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $txn['type'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700">{{ $txn['description'] }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $txn['account'] }}</td>
                                        <td
                                            class="px-6 py-4 text-right text-sm font-semibold
                                            {{ $txn['typeClass'] === 'success' ? 'text-emerald-600' : 'text-red-600' }}">
                                            {{ number_format($txn['amount'], 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                            {{ __('dashboard.no_transactions') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

            @endif {{-- end $masjid check --}}

        </div>
    </div>

    @if ($masjid && count($accountBreakdown) > 0)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof window.Chart === 'undefined') return;

                const trendEl = document.getElementById('trendChart');
                const accountEl = document.getElementById('accountChart');
                const trendData = @json($monthlyTrend);
                const accountData = @json($accountBreakdown);

                if (trendEl) {
                    new window.Chart(trendEl, {
                        type: 'bar',
                        data: {
                            labels: trendData.labels,
                            datasets: [{
                                    label: @js(__('dashboard.income_label')),
                                    data: trendData.incomes,
                                    backgroundColor: 'rgba(16, 185, 129, 0.75)',
                                    borderColor: 'rgba(16, 185, 129, 1)',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                },
                                {
                                    label: @js(__('dashboard.expense_label')),
                                    data: trendData.expenses,
                                    backgroundColor: 'rgba(249, 115, 22, 0.75)',
                                    borderColor: 'rgba(249, 115, 22, 1)',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                },
                            ],
                        },
                        options: {
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        padding: 12,
                                        font: {
                                            size: 12
                                        }
                                    }
                                },
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(148,163,184,0.2)'
                                    },
                                    ticks: {
                                        callback: v => 'RM ' + v.toLocaleString(),
                                    },
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                },
                            },
                        },
                    });
                }

                if (accountEl && accountData.length > 0) {
                    const positiveAccounts = accountData.filter(a => a.balance > 0);
                    new window.Chart(accountEl, {
                        type: 'doughnut',
                        data: {
                            labels: positiveAccounts.map(a => a.name),
                            datasets: [{
                                data: positiveAccounts.map(a => a.balance),
                                backgroundColor: [
                                    '#6366f1', '#10b981', '#f59e0b', '#3b82f6',
                                    '#ec4899', '#14b8a6', '#f97316', '#8b5cf6',
                                ],
                                borderWidth: 2,
                                borderColor: '#ffffff',
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 16,
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => ctx.label + ': RM ' + ctx.parsed.toFixed(2),
                                    },
                                },
                            },
                            cutout: '60%',
                        },
                    });
                }
            });
        </script>
    @endif

</x-app-layout>
