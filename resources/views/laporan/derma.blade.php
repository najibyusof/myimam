<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Laporan Derma / Hasil Lain</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @php
                $exportParams = [
                    'tarikh_dari' => $filters['tarikh_dari'],
                    'tarikh_hingga' => $filters['tarikh_hingga'],
                    'jenis_paparan' => $filters['jenis_paparan'] ?? 'ringkasan_sumber',
                ];
            @endphp

            <div class="rounded-xl bg-white p-5 shadow">
                <form method="GET" action="{{ route('laporan.derma') }}"
                    class="grid grid-cols-1 gap-4 md:grid-cols-4 md:items-end">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Tarikh Dari</label>
                        <input type="date" name="tarikh_dari" value="{{ $filters['tarikh_dari'] }}"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Tarikh Hingga</label>
                        <input type="date" name="tarikh_hingga" value="{{ $filters['tarikh_hingga'] }}"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Jenis Paparan</label>
                        <select name="jenis_paparan"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="ringkasan_sumber" @selected(($filters['jenis_paparan'] ?? 'ringkasan_sumber') === 'ringkasan_sumber')>Ringkasan Mengikut Sumber
                            </option>
                            <option value="ringkasan_bulan" @selected(($filters['jenis_paparan'] ?? 'ringkasan_sumber') === 'ringkasan_bulan')>Ringkasan Mengikut Bulan
                            </option>
                            <option value="senarai_transaksi" @selected(($filters['jenis_paparan'] ?? 'ringkasan_sumber') === 'senarai_transaksi')>Senarai Transaksi
                            </option>
                        </select>
                    </div>

                    <div>
                        <button type="submit"
                            class="w-full rounded-lg bg-indigo-600 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                            Jana Laporan
                        </button>
                    </div>
                </form>
                <p class="mt-3 text-xs text-gray-500">Tidak termasuk kutipan Jumaat.</p>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <a href="{{ route('laporan.derma.export.pdf', $exportParams) }}"
                        class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-medium text-rose-700 transition hover:bg-rose-100">
                        Eksport PDF
                    </a>
                    <a href="{{ route('laporan.derma.export.excel', $exportParams) }}"
                        class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700 transition hover:bg-emerald-100">
                        Eksport Excel
                    </a>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow">
                @if (($filters['jenis_paparan'] ?? 'ringkasan_sumber') === 'ringkasan_sumber')
                    <div class="border-b border-gray-100 px-4 py-3">
                        <h3 class="text-sm font-semibold text-gray-800">Ringkasan Derma / Hasil Lain</h3>
                        <p class="mt-1 text-xs text-gray-500">Jumlah derma dan hasil lain diikuti mengikut sumber.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Sumber Hasil</th>
                                    <th class="px-4 py-3 text-right">Bil. Rekod</th>
                                    <th class="px-4 py-3 text-right">Jumlah (RM)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($rows as $row)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-4 py-3 text-gray-800">{{ $row['sumber'] }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700">{{ $row['bil_rekod'] }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-gray-800">
                                            {{ number_format($row['jumlah'], 2, '.', ',') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-gray-400">Tiada rekod derma
                                            atau hasil lain untuk tempoh ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-indigo-50">
                                    <td class="px-4 py-3 font-semibold text-gray-800">Jumlah Keseluruhan</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-700">
                                        {{ $rows->sum('bil_rekod') }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-indigo-700">
                                        {{ number_format($jumlah_keseluruhan, 2, '.', ',') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @elseif (($filters['jenis_paparan'] ?? 'ringkasan_sumber') === 'ringkasan_bulan')
                    <div class="border-b border-gray-100 px-4 py-3">
                        <h3 class="text-sm font-semibold text-gray-800">Ringkasan Derma / Hasil Lain Mengikut Bulan</h3>
                        <p class="mt-1 text-xs text-gray-500">Jumlah derma dan hasil lain diikuti mengikut bulan.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Bulan</th>
                                    <th class="px-4 py-3 text-right">Bil. Rekod</th>
                                    <th class="px-4 py-3 text-right">Jumlah (RM)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($ringkasan_bulan as $row)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-4 py-3 text-gray-800">{{ $row['bulan'] }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700">{{ $row['bil_rekod'] }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-gray-800">
                                            {{ number_format($row['jumlah'], 2, '.', ',') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-gray-400">Tiada rekod derma
                                            atau hasil lain untuk tempoh ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-indigo-50">
                                    <td class="px-4 py-3 font-semibold text-gray-800">Jumlah Keseluruhan</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-700">
                                        {{ $ringkasan_bulan->sum('bil_rekod') }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-indigo-700">
                                        {{ number_format($jumlah_keseluruhan, 2, '.', ',') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="border-b border-gray-100 px-4 py-3">
                        <h3 class="text-sm font-semibold text-gray-800">Senarai Derma / Hasil Lain</h3>
                        <p class="mt-1 text-xs text-gray-500">Semua derma dan hasil lain diurutkan mengikut tarikh. Klik
                            baris untuk lihat detail.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Tarikh</th>
                                    <th class="px-4 py-3 text-left">Sumber</th>
                                    <th class="px-4 py-3 text-left">No. Resit</th>
                                    <th class="px-4 py-3 text-right">Jumlah (RM)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($senarai_rows as $row)
                                    <tr class="cursor-pointer hover:bg-blue-50 transition"
                                        onclick="showDetail({{ json_encode($row) }})">
                                        <td class="px-4 py-3 text-gray-700">
                                            {{ \Carbon\Carbon::parse($row['tarikh'])->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-800">{{ $row['sumber'] }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ $row['no_resit'] }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-gray-800">
                                            {{ number_format($row['jumlah'], 2, '.', ',') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">Tiada rekod derma
                                            atau hasil lain untuk tempoh ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-indigo-50">
                                    <td colspan="3" class="px-4 py-3 font-semibold text-gray-800">Jumlah Keseluruhan
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-indigo-700">
                                        {{ number_format($jumlah_keseluruhan, 2, '.', ',') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50"
        onclick="closeDetail()">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative w-full max-w-md rounded-xl bg-white shadow-xl" onclick="event.stopPropagation()">
                <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Detail Transaksi</h3>
                    <button onclick="closeDetail()" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500">Tarikh</label>
                        <p class="mt-1 text-sm font-semibold text-gray-800" id="detail-tarikh">-</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500">Sumber</label>
                        <p class="mt-1 text-sm font-semibold text-gray-800" id="detail-sumber">-</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500">No. Resit</label>
                        <p class="mt-1 text-sm text-gray-700" id="detail-no-resit">-</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500">Jumlah</label>
                        <p class="mt-1 text-sm font-semibold text-indigo-600" id="detail-jumlah">RM 0.00</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500">Catatan</label>
                        <p class="mt-1 text-sm text-gray-700" id="detail-catatan">-</p>
                    </div>
                </div>

                <div class="border-t border-gray-100 px-6 py-3 text-right">
                    <button onclick="closeDetail()"
                        class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-300">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showDetail(row) {
            document.getElementById('detail-tarikh').textContent = formatDate(row.tarikh);
            document.getElementById('detail-sumber').textContent = row.sumber;
            document.getElementById('detail-no-resit').textContent = row.no_resit;
            document.getElementById('detail-jumlah').textContent = 'RM ' + parseFloat(row.jumlah).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('detail-catatan').textContent = row.catatan;
            document.getElementById('detailModal').classList.remove('hidden');
        }

        function closeDetail() {
            document.getElementById('detailModal').classList.add('hidden');
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            try {
                // Handle YYYY-MM-DD format
                const [year, month, day] = dateStr.split('-');
                const date = new Date(year, month - 1, day);
                if (isNaN(date.getTime())) {
                    return 'Invalid Date';
                }
                return date.toLocaleDateString('ms-MY', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                });
            } catch (e) {
                return 'Invalid Date';
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDetail();
            }
        });
    </script>
</x-app-layout>
