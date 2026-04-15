<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-gray-400">
                    {{ __('dashboard.superadmin_page_label') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">
                    {{ __('dashboard.superadmin_title') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('dashboard.superadmin_subtitle') }}</p>
            </div>
            <div class="text-sm text-gray-500">
                {{ now()->format('d M Y, h:i A') }}
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- GLOBAL ALERTS --}}
            @if (count($alerts) > 0)
                <div class="space-y-2">
                    @foreach ($alerts as $alert)
                        <div
                            class="flex items-start gap-3 rounded-xl border-l-4
                            {{ $alert['type'] === 'danger' ? 'border-red-500 bg-red-50' : '' }}
                            {{ $alert['type'] === 'warning' ? 'border-amber-500 bg-amber-50' : '' }}
                            {{ $alert['type'] === 'info' ? 'border-sky-500 bg-sky-50' : '' }}
                            p-4 shadow">
                            <svg class="mt-0.5 h-5 w-5 flex-shrink-0
                                {{ $alert['type'] === 'danger' ? 'text-red-600' : '' }}
                                {{ $alert['type'] === 'warning' ? 'text-amber-600' : '' }}
                                {{ $alert['type'] === 'info' ? 'text-sky-600' : '' }}"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            <p
                                class="text-sm font-medium
                                {{ $alert['type'] === 'danger' ? 'text-red-800' : '' }}
                                {{ $alert['type'] === 'warning' ? 'text-amber-800' : '' }}
                                {{ $alert['type'] === 'info' ? 'text-sky-800' : '' }}">
                                {{ $alert['message'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- KPI CARDS --}}
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {{-- Total Tenants --}}
                <article
                    class="group rounded-xl border border-gray-200 bg-white p-5 shadow transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ __('dashboard.total_tenants') }}</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($totalMasjids) }}</p>
                        </div>
                        <div class="rounded-lg bg-indigo-100 p-2.5 text-indigo-600 group-hover:bg-indigo-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3 flex gap-3 text-xs">
                        <span
                            class="font-semibold text-emerald-600">{{ __('dashboard.active_count', ['count' => $activeMasjids]) }}</span>
                        <span class="text-gray-400">·</span>
                        <span
                            class="font-semibold text-red-500">{{ __('dashboard.suspended_count', ['count' => $suspendedMasjids]) }}</span>
                    </div>
                </article>

                {{-- Active Subscriptions --}}
                <article
                    class="group rounded-xl border border-gray-200 bg-white p-5 shadow transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ __('dashboard.active_subscriptions') }}</p>
                            <p class="mt-2 text-3xl font-bold text-emerald-600">
                                {{ number_format($activeSubscriptions) }}</p>
                        </div>
                        <div class="rounded-lg bg-emerald-100 p-2.5 text-emerald-600 group-hover:bg-emerald-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">
                        {{ __('dashboard.expired_count', ['count' => $expiredSubscriptions]) }}</p>
                </article>

                {{-- Expiring Soon --}}
                <article
                    class="group rounded-xl border border-gray-200 bg-white p-5 shadow transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ __('dashboard.expiring_soon') }}</p>
                            <p
                                class="mt-2 text-3xl font-bold {{ $expiringSoon > 0 ? 'text-amber-600' : 'text-gray-900' }}">
                                {{ number_format($expiringSoon) }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-amber-100 p-2.5 text-amber-600 group-hover:bg-amber-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">{{ __('dashboard.requires_renewal') }}</p>
                </article>

                {{-- Total Revenue --}}
                <article
                    class="group rounded-xl border border-gray-200 bg-white p-5 shadow transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ __('dashboard.total_revenue') }}</p>
                            <p class="mt-2 text-3xl font-bold text-sky-700">RM {{ number_format($totalRevenue, 2) }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-sky-100 p-2.5 text-sky-600 group-hover:bg-sky-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">{{ __('dashboard.all_time_payments') }}</p>
                </article>
            </section>

            {{-- CHARTS --}}
            <section class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
                {{-- Tenant Growth Bar Chart --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('dashboard.last_6_months') }}</p>
                            <h3 class="mt-1 text-xl font-semibold text-gray-900">{{ __('dashboard.tenant_growth') }}
                            </h3>
                        </div>
                        <span
                            class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Chart.js</span>
                    </div>
                    <div class="mt-6 h-72">
                        <canvas id="tenantGrowthChart"></canvas>
                    </div>
                </div>

                {{-- Subscription Status Doughnut --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('dashboard.all_tenants') }}</p>
                            <h3 class="mt-1 text-xl font-semibold text-gray-900">
                                {{ __('dashboard.subscription_status_title') }}</h3>
                        </div>
                    </div>
                    <div class="mt-6 flex h-72 items-center justify-center">
                        <canvas id="subscriptionStatusChart"></canvas>
                    </div>
                </div>
            </section>

            {{-- TOP MASJID BY INCOME --}}
            @if (count($topMasjids) > 0)
                <section class="rounded-xl border border-gray-200 bg-white p-6 shadow">
                    <div class="mb-5">
                        <p class="text-sm font-medium text-gray-500">{{ __('dashboard.rankings') }}</p>
                        <h3 class="mt-1 text-xl font-semibold text-gray-900">{{ __('dashboard.top_masjid_income') }}
                        </h3>
                    </div>
                    <div class="space-y-3">
                        @foreach ($topMasjids as $index => $masjid)
                            <div class="flex items-center gap-4 rounded-lg border border-gray-100 bg-gray-50 px-4 py-3">
                                <span
                                    class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full
                                    {{ $index === 0 ? 'bg-yellow-100 text-yellow-700' : ($index === 1 ? 'bg-gray-200 text-gray-600' : ($index === 2 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500')) }}
                                    text-sm font-bold">
                                    {{ $index + 1 }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium text-gray-900">{{ $masjid['nama'] }}</p>
                                </div>
                                <span class="flex-shrink-0 font-semibold text-emerald-700">
                                    RM {{ number_format($masjid['total_income'], 2) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- MASJID TABLE --}}
            <section class="rounded-xl border border-gray-200 bg-white shadow">
                <div class="border-b border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('dashboard.tenant_registry') }}</p>
                            <h3 class="mt-1 text-xl font-semibold text-gray-900">{{ __('dashboard.all_masjid') }}</h3>
                        </div>
                        @can('masjid.create')
                            <a href="{{ route('admin.masjid.create') }}"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                {{ __('dashboard.new_masjid') }}
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('dashboard.col_masjid') }}</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('dashboard.col_status') }}</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('dashboard.col_subscription') }}</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">
                                    {{ __('dashboard.col_expiry') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($masjidTable as $row)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $row['nama'] }}</td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                            {{ $row['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                            {{ $row['status'] === 'suspended' ? 'bg-red-100 text-red-700' : '' }}
                                            {{ !in_array($row['status'], ['active', 'suspended']) ? 'bg-gray-100 text-gray-600' : '' }}">
                                            {{ __('dashboard.status_' . $row['status'], [], null) ?? ucfirst($row['status']) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                            {{ $row['subscription_status'] === 'active' ? 'bg-sky-100 text-sky-700' : '' }}
                                            {{ $row['subscription_status'] === 'expired' ? 'bg-red-100 text-red-700' : '' }}
                                            {{ !in_array($row['subscription_status'], ['active', 'expired']) ? 'bg-gray-100 text-gray-600' : '' }}">
                                            {{ __('dashboard.status_' . $row['subscription_status'], [], null) ?? ucfirst($row['subscription_status']) }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm
                                        {{ $row['is_expiring_soon'] ? 'font-semibold text-amber-600' : 'text-gray-600' }}">
                                        {{ $row['subscription_expiry'] }}
                                        @if ($row['is_expiring_soon'])
                                            <span
                                                class="ml-1 rounded bg-amber-100 px-1.5 py-0.5 text-xs font-bold text-amber-700">{{ __('dashboard.expiring_soon_badge') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                                        {{ __('dashboard.no_masjid_registered') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (count($masjidTable) >= 20)
                    <div class="border-t border-gray-200 p-4 text-center">
                        <a href="{{ route('admin.subscriptions.index') }}"
                            class="text-sm font-medium text-indigo-600 hover:underline">
                            {{ __('dashboard.view_all_tenants') }}
                        </a>
                    </div>
                @endif
            </section>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.Chart === 'undefined') return;

            const growthEl = document.getElementById('tenantGrowthChart');
            const statusEl = document.getElementById('subscriptionStatusChart');
            const growthData = @json($tenantGrowthChart);
            const statusData = @json($subscriptionStatusChart);

            if (growthEl) {
                new window.Chart(growthEl, {
                    type: 'bar',
                    data: {
                        labels: growthData.labels,
                        datasets: [{
                            label: @js(__('dashboard.new_tenants_chart_label')),
                            data: growthData.counts,
                            backgroundColor: 'rgba(99, 102, 241, 0.75)',
                            borderColor: 'rgba(99, 102, 241, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },
                                grid: {
                                    color: 'rgba(148,163,184,0.2)'
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

            if (statusEl) {
                new window.Chart(statusEl, {
                    type: 'doughnut',
                    data: {
                        labels: statusData.labels,
                        datasets: [{
                            data: statusData.counts,
                            backgroundColor: ['#10b981', '#ef4444', '#94a3b8'],
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
                                        size: 12
                                    }
                                }
                            },
                        },
                        cutout: '65%',
                    },
                });
            }
        });
    </script>
</x-app-layout>
