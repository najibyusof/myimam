<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Detail Perbelanjaan</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl bg-white p-5 shadow">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Kategori: {{ $kategori_nama }}</h3>
                        <p class="text-sm text-gray-600">Tempoh: {{ $tempoh_label }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('laporan.penyata', $filters) }}"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                            Kembali
                        </a>
                        <button type="button" onclick="window.print()"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                            Cetak
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Tarikh</th>
                                <th class="px-4 py-3 text-left">Penerima</th>
                                <th class="px-4 py-3 text-left">Catatan</th>
                                <th class="px-4 py-3 text-left">Bukti</th>
                                <th class="px-4 py-3 text-right">Jumlah (RM)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($records as $row)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ \Carbon\Carbon::parse($row['tarikh'])->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ $row['penerima'] }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $row['catatan'] }}</td>
                                    <td class="px-4 py-3">
                                        @if ($row['bukti_url'])
                                            <a href="{{ $row['bukti_url'] }}" target="_blank" rel="noopener"
                                                class="text-blue-600 hover:text-blue-800 hover:underline">
                                                Lihat Bukti
                                            </a>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-800">
                                        {{ number_format($row['amaun'], 2, '.', ',') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">Tiada rekod untuk
                                        kategori ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-indigo-50">
                                <td colspan="4" class="px-4 py-3 font-semibold text-gray-800">Jumlah</td>
                                <td class="px-4 py-3 text-right font-semibold text-indigo-700">
                                    {{ number_format($jumlah, 2, '.', ',') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
