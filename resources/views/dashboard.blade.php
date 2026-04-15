<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-gray-400">
                    {{ __('dashboard.system_overview') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">{{ __('dashboard.title') }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('dashboard.operational_snapshot', ['context' => $contextLabel]) }}</p>
                <p class="mt-1 text-xs font-medium uppercase tracking-[0.16em] text-indigo-700">
                    {{ __('dashboard.role_focus', ['role' => $dashboardRole]) }}</p>
            </div>
            <div class="text-sm text-gray-500">
                {{ __('dashboard.last_sync', ['time' => now()->format('d M Y, h:i A')]) }}
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($stats as $stat)
                    <article
                        class="rounded-xl border border-gray-200 bg-white p-5 shadow transition hover:-translate-y-0.5 hover:shadow-md">
                        <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stat['value']) }}</p>
                        <p
                            class="mt-2 text-sm {{ $stat['tone'] === 'amber' ? 'text-amber-700' : ($stat['tone'] === 'emerald' ? 'text-emerald-700' : 'text-sky-700') }}">
                            {{ $stat['hint'] }}
                        </p>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('dashboard.activity_trend') }}</p>
                            <h3 class="mt-1 text-xl font-semibold text-gray-900">{{ __('dashboard.last_7_days') }}</h3>
                        </div>
                        <span
                            class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">{{ __('dashboard.chart_js') }}</span>
                    </div>
                    <div class="mt-5 h-72">
                        <canvas id="activityTrendChart"></canvas>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('dashboard.notification_preview') }}</p>
                            <h3 class="mt-1 text-xl font-semibold text-gray-900">
                                {{ __('dashboard.latest_notifications') }}</h3>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse ($notificationPreview as $notification)
                            <article
                                class="rounded-xl border {{ $notification['is_read'] ? 'border-gray-200 bg-gray-50' : 'border-sky-200 bg-sky-50' }} p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-medium text-gray-900">{{ $notification['title'] }}</p>
                                    <span class="text-xs text-gray-500">{{ $notification['time'] }}</span>
                                </div>
                                <p class="mt-2 text-sm text-gray-600">{{ $notification['message'] }}</p>
                            </article>
                        @empty
                            <p class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">
                                {{ __('dashboard.no_notifications_available') }}</p>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('dashboard.recent_activity_log') }}</p>
                        <h3 class="mt-1 text-xl font-semibold text-gray-900">{{ __('dashboard.latest_system_events') }}
                        </h3>
                    </div>
                </div>

                <div class="mt-6 space-y-3">
                    @forelse ($recentActivities as $activity)
                        <article class="flex items-start gap-4 rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <span
                                class="mt-1 h-3 w-3 rounded-full {{ $activity['type'] === 'success' ? 'bg-emerald-500' : ($activity['type'] === 'info' ? 'bg-sky-500' : ($activity['type'] === 'warning' ? 'bg-amber-500' : 'bg-slate-400')) }}"></span>
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900">{{ $activity['title'] }}</p>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ $activity['actor'] }}
                                    @if (!empty($activity['location']))
                                        · {{ $activity['location'] }}
                                    @endif
                                    · {{ $activity['time'] }}
                                </p>
                            </div>
                        </article>
                    @empty
                        <p class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">
                            {{ __('dashboard.no_activity_logs_available') }}</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartElement = document.getElementById('activityTrendChart');

            if (!chartElement || typeof window.Chart === 'undefined') {
                return;
            }

            const chartData = @json($activityChart);

            new window.Chart(chartElement, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: @js(__('dashboard.activities')),
                        data: chartData.counts,
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2, 132, 199, 0.14)',
                        fill: true,
                        borderWidth: 2,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#0f172a',
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.2)',
                            },
                        },
                        x: {
                            grid: {
                                display: false,
                            },
                        },
                    },
                },
            });
        });
    </script>
</x-app-layout>
