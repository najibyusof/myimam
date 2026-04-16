<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Ringkasan Tabung Khas</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @php
                $exportParams = [
                    'masjid_id' => $filters['masjid_id'] ?? null,
                    'tarikh_dari' => $filters['tarikh_dari'],
                    'tarikh_hingga' => $filters['tarikh_hingga'],
                ];
            @endphp

            <div class="rounded-xl bg-white p-5 shadow">
                <form method="GET" action="{{ route('laporan.tabung') }}" id="laporan-tabung-form" class="space-y-4">
                    @if ($is_superadmin)
                        <div class="grid grid-cols-1 md:max-w-sm">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">Masjid</label>
                                <select name="masjid_id" id="laporan-tabung-masjid" required
                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Masjid</option>
                                    @foreach ($masjid_list as $masjid)
                                        <option value="{{ $masjid->id }}" @selected((int) ($filters['masjid_id'] ?? 0) === (int) $masjid->id)>
                                            {{ $masjid->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-12 md:items-end">
                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs font-medium text-gray-600">Tarikh Dari</label>
                            <input type="date" name="tarikh_dari" value="{{ $filters['tarikh_dari'] }}"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs font-medium text-gray-600">Tarikh Hingga</label>
                            <input type="date" name="tarikh_hingga" value="{{ $filters['tarikh_hingga'] }}"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="md:col-span-6">
                            <button type="submit"
                                class="w-full rounded-lg bg-indigo-600 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                                Jana Ringkasan
                            </button>
                        </div>
                    </div>
                </form>

                @if ($requires_masjid_selection)
                    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Sila pilih masjid terlebih dahulu untuk jana Ringkasan Tabung Khas.
                    </div>
                @endif

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <a href="{{ !$requires_masjid_selection ? route('laporan.tabung.export.pdf', $exportParams) : '#' }}"
                        class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-medium text-rose-700 transition hover:bg-rose-100 {{ $requires_masjid_selection ? 'pointer-events-none opacity-50' : '' }}">
                        Eksport PDF
                    </a>
                    <a href="{{ !$requires_masjid_selection ? route('laporan.tabung.export.excel', $exportParams) : '#' }}"
                        class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700 transition hover:bg-emerald-100 {{ $requires_masjid_selection ? 'pointer-events-none opacity-50' : '' }}">
                        Eksport Excel
                    </a>
                    <span class="text-xs text-gray-500">Tempoh: {{ $tempoh_label }}</span>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow">
                <div class="border-b border-gray-100 px-4 py-3">
                    <h3 class="text-sm font-semibold text-gray-800">Carta Masuk vs Keluar Mengikut Tabung</h3>
                    <p class="mt-1 text-xs text-gray-500">Perbandingan jumlah transaksi masuk dan keluar bagi setiap
                        tabung khas.</p>
                </div>
                <div class="p-4">
                    <canvas id="tabungSummaryChart" height="120"></canvas>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow">
                <div class="border-b border-gray-100 px-4 py-3">
                    <h3 class="text-sm font-semibold text-gray-800">Ringkasan Tabung Khas</h3>
                    <p class="mt-1 text-xs text-gray-500">Ringkasan aliran masuk dan keluar mengikut tabung khas.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Nama Tabung</th>
                                <th class="px-4 py-3 text-right">Masuk Tempoh (RM)</th>
                                <th class="px-4 py-3 text-right">Keluar Tempoh (RM)</th>
                                <th class="px-4 py-3 text-right">Baki Terkumpul (RM)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($rows as $row)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-4 py-3 text-gray-800">
                                        <a href="{{ route('laporan.tabung.detail', ['tabung' => $row['id_tabung'], 'masjid_id' => $filters['masjid_id'] ?? null, 'tarikh_dari' => $filters['tarikh_dari'], 'tarikh_hingga' => $filters['tarikh_hingga']]) }}"
                                            class="font-medium text-blue-600 hover:text-blue-700 hover:underline">
                                            {{ $row['nama_tabung'] }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-emerald-700">
                                        RM {{ number_format($row['masuk_tempoh'], 2, '.', ',') }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-rose-700">
                                        RM {{ number_format($row['keluar_tempoh'], 2, '.', ',') }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right font-semibold {{ $row['baki_terkumpul'] >= 0 ? 'text-indigo-700' : 'text-rose-700' }}">
                                        RM {{ number_format($row['baki_terkumpul'], 2, '.', ',') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                                        Tiada rekod tabung khas untuk tempoh ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-indigo-50">
                                <td class="px-4 py-3 font-semibold text-gray-800">Jumlah Keseluruhan</td>
                                <td class="px-4 py-3 text-right font-semibold text-emerald-700">
                                    RM {{ number_format($total_masuk, 2, '.', ',') }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-rose-700">
                                    RM {{ number_format($total_keluar, 2, '.', ',') }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-semibold {{ $total_baki >= 0 ? 'text-indigo-700' : 'text-rose-700' }}">
                                    RM {{ number_format($total_baki, 2, '.', ',') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
        <script>
            (function() {
                @if ($is_superadmin)
                    const tabungForm = document.getElementById('laporan-tabung-form');
                    const masjidSelect = document.getElementById('laporan-tabung-masjid');
                    if (tabungForm && masjidSelect) {
                        masjidSelect.addEventListener('change', function() {
                            tabungForm.submit();
                        });
                    }
                @endif

                const chartElement = document.getElementById('tabungSummaryChart');
                if (!chartElement) {
                    return;
                }

                const labels = @json($chart['labels']);
                const masukValues = @json($chart['masuk']);
                const keluarValues = @json($chart['keluar']);

                if (!labels.length) {
                    chartElement.parentElement.innerHTML =
                        '<p class="py-8 text-center text-sm text-gray-400">Tiada data untuk dipaparkan pada carta.</p>';
                    return;
                }

                new window.Chart(chartElement, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                                label: 'Masuk',
                                data: masukValues,
                                backgroundColor: 'rgba(16, 185, 129, 0.78)',
                                borderColor: '#059669',
                                borderWidth: 1.5,
                                borderRadius: 8,
                            },
                            {
                                label: 'Keluar',
                                data: keluarValues,
                                backgroundColor: 'rgba(244, 63, 94, 0.78)',
                                borderColor: '#e11d48',
                                borderWidth: 1.5,
                                borderRadius: 8,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => 'RM ' + Number(ctx.parsed.y || 0).toLocaleString('ms-MY', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2,
                                    }),
                                },
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: (value) => 'RM ' + Number(value).toLocaleString('ms-MY'),
                                },
                            },
                        },
                    },
                });
            })();
        </script>
    @endpush
</x-app-layout>
