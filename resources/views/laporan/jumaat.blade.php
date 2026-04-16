<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Laporan Kutipan Jumaat</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @php
                $exportParams = [
                    'tahun' => $filters['tahun'],
                    'jenis_paparan' => $filters['jenis_paparan'] ?? 'ringkasan_bulanan',
                ];

                if (($filters['bulan'] ?? 0) > 0) {
                    $exportParams['bulan'] = $filters['bulan'];
                }
            @endphp

            <div class="rounded-xl bg-white p-5 shadow">
                <form method="GET" action="{{ route('laporan.jumaat') }}"
                    class="grid grid-cols-1 gap-4 md:grid-cols-3 md:items-end">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Tahun</label>
                        <select name="tahun"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @php
                                $tahunSemasa = (int) now()->format('Y');
                            @endphp
                            @for ($tahun = $tahunSemasa; $tahun >= $tahunSemasa - 10; $tahun--)
                                <option value="{{ $tahun }}" @selected((int) $filters['tahun'] === $tahun)>
                                    {{ $tahun }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Jenis Paparan</label>
                        <select name="jenis_paparan"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="ringkasan_bulanan" @selected(($filters['jenis_paparan'] ?? 'ringkasan_bulanan') === 'ringkasan_bulanan')>Ringkasan Bulanan</option>
                            <option value="senarai_jumaat" @selected(($filters['jenis_paparan'] ?? 'ringkasan_bulanan') === 'senarai_jumaat')>Senarai Setiap Jumaat</option>
                        </select>
                    </div>

                    <div>
                        <button type="submit"
                            class="w-full rounded-lg bg-indigo-600 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                            Jana Laporan
                        </button>
                    </div>
                </form>
                <p class="mt-3 text-xs text-gray-500">Sumber: transaksi yang direkod sebagai 'Kutipan Jumaat'.</p>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <a href="{{ route('laporan.jumaat.export.pdf', $exportParams) }}"
                        class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-medium text-rose-700 transition hover:bg-rose-100">
                        Eksport PDF
                    </a>
                    <a href="{{ route('laporan.jumaat.export.excel', $exportParams) }}"
                        class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700 transition hover:bg-emerald-100">
                        Eksport Excel
                    </a>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow">
                <div class="mb-3">
                    <h3 class="text-sm font-semibold text-gray-800">Trend Kutipan Jumaat Bulanan
                        ({{ $filters['tahun'] }})</h3>
                </div>
                <div class="h-72 w-full">
                    <canvas id="jumaatTrendChart"></canvas>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow">
                @if (($filters['jenis_paparan'] ?? 'ringkasan_bulanan') === 'ringkasan_bulanan')
                    <div class="border-b border-gray-100 px-4 py-3">
                        <h3 class="text-sm font-semibold text-gray-800">Ringkasan Bulanan Tahun {{ $filters['tahun'] }}
                        </h3>
                        <p class="mt-1 text-xs text-gray-500">Jumlah kutipan Jumaat diikuti mengikut bulan.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Bulan</th>
                                    <th class="px-4 py-3 text-right">Jumlah Kutipan (RM)</th>
                                    <th class="px-4 py-3 text-right">Bil. Rekod</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($rows as $row)
                                    <tr class="cursor-pointer hover:bg-slate-50 transition"
                                        data-href="{{ route('laporan.jumaat', ['tahun' => $filters['tahun'], 'jenis_paparan' => 'senarai_jumaat', 'bulan' => $row['bulan_no']]) }}"
                                        tabindex="0" role="link" aria-label="Lihat kutipan {{ $row['bulan'] }}">
                                        <td class="px-4 py-3">
                                            <span class="font-medium text-gray-800">{{ $row['bulan'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right text-gray-800">
                                            {{ number_format($row['jumlah'], 2, '.', ',') }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700">{{ $row['bil_rekod'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-gray-400">Tiada rekod
                                            kutipan Jumaat untuk tahun ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-indigo-50">
                                    <td class="px-4 py-3 font-semibold text-gray-800">Jumlah Setahun</td>
                                    <td class="px-4 py-3 text-right font-semibold text-indigo-700">
                                        {{ number_format($jumlah_setahun, 2, '.', ',') }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-700">
                                        {{ $rows->sum('bil_rekod') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="border-b border-gray-100 px-4 py-3">
                        <h3 class="text-sm font-semibold text-gray-800">Senarai Kutipan Mengikut Tarikh (Tahun
                            {{ $filters['tahun'] }})</h3>
                        @if (($filters['bulan'] ?? 0) > 0)
                            <p class="mt-1 text-xs text-gray-500">Paparan bulan {{ $filters['bulan_nama'] }}. Semua
                                kutipan Jumaat diurutkan mengikut tarikh.</p>
                            <a href="{{ route('laporan.jumaat', ['tahun' => $filters['tahun'], 'jenis_paparan' => 'senarai_jumaat']) }}"
                                class="mt-2 inline-flex text-xs font-medium text-indigo-600 hover:text-indigo-700">
                                Tunjuk semua bulan
                            </a>
                        @else
                            <p class="mt-1 text-xs text-gray-500">Semua kutipan Jumaat diurutkan mengikut tarikh.</p>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Tarikh</th>
                                    <th class="px-4 py-3 text-right">Jumlah Kutipan (RM)</th>
                                    <th class="px-4 py-3 text-right">Bil. Rekod</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($senarai_rows as $row)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-4 py-3 text-gray-700">
                                            {{ \Carbon\Carbon::parse($row['tarikh'])->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-gray-800">
                                            {{ number_format($row['jumlah_kutipan'], 2, '.', ',') }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700">{{ $row['bil_rekod'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-gray-400">Tiada rekod
                                            kutipan Jumaat untuk tahun ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-indigo-50">
                                    <td class="px-4 py-3 font-semibold text-gray-800">Jumlah Setahun</td>
                                    <td class="px-4 py-3 text-right font-semibold text-indigo-700">
                                        {{ number_format($senarai_rows->sum('jumlah_kutipan'), 2, '.', ',') }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-700">
                                        {{ $senarai_rows->sum('bil_rekod') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tahunFilter = @json((int) $filters['tahun']);
            const senaraiBaseUrl = @json(route('laporan.jumaat'));

            function redirectToBulan(bulanNo) {
                if (!Number.isInteger(bulanNo) || bulanNo < 1 || bulanNo > 12) {
                    return;
                }

                const params = new URLSearchParams({
                    tahun: String(tahunFilter),
                    jenis_paparan: 'senarai_jumaat',
                    bulan: String(bulanNo),
                });

                window.location.href = senaraiBaseUrl + '?' + params.toString();
            }

            document.querySelectorAll('tr[data-href]').forEach(function(row) {
                row.addEventListener('click', function() {
                    window.location.href = row.dataset.href;
                });

                row.addEventListener('keydown', function(event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        window.location.href = row.dataset.href;
                    }
                });
            });

            const ctx = document.getElementById('jumaatTrendChart');
            if (!ctx || !window.Chart) {
                return;
            }

            new window.Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chart_labels),
                    datasets: [{
                        label: 'Jumlah Kutipan (RM)',
                        data: @json($chart_data),
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.15)',
                        tension: 0.3,
                        fill: true,
                        borderWidth: 2,
                        pointRadius: 3,
                    }]
                },
                options: {
                    onClick: function(event, elements) {
                        if (!elements.length) {
                            return;
                        }

                        const dataIndex = elements[0].index;
                        const bulanNo = dataIndex + 1;
                        redirectToBulan(bulanNo);
                    },
                    responsive: true,
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
                                callback: function(value) {
                                    return 'RM ' + Number(value).toLocaleString('en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
