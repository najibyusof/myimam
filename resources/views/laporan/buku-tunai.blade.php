<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Laporan Buku Tunai</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @php
                $bolehEksport =
                    !empty($filters['akaun_id']) && !empty($filters['tarikh_mula']) && !empty($filters['tarikh_akhir']);
                $exportParams = [
                    'akaun_id' => $filters['akaun_id'] ?? null,
                    'tarikh_mula' => $filters['tarikh_mula'] ?? null,
                    'tarikh_akhir' => $filters['tarikh_akhir'] ?? null,
                    'baki_awal' => $filters['baki_awal'] ?? 0,
                ];
            @endphp

            <div class="rounded-xl bg-white p-5 shadow">
                <form method="GET" action="{{ route('laporan.buku-tunai') }}"
                    class="grid grid-cols-1 gap-4 md:grid-cols-5 md:items-end">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Pilihan Akaun</label>
                        <select name="akaun_id" required
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Pilih Akaun</option>
                            @foreach ($akaunList as $akaun)
                                <option value="{{ $akaun->id }}" @selected((int) ($filters['akaun_id'] ?? 0) === (int) $akaun->id)>
                                    {{ $akaun->nama_akaun }}
                                </option>
                            @endforeach
                        </select>
                        @error('akaun_id')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Tarikh Mula</label>
                        <input type="date" name="tarikh_mula" required value="{{ $filters['tarikh_mula'] }}"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('tarikh_mula')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Tarikh Akhir</label>
                        <input type="date" name="tarikh_akhir" required value="{{ $filters['tarikh_akhir'] }}"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('tarikh_akhir')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Baki Awal</label>
                        <input type="number" step="0.01" name="baki_awal" value="{{ $filters['baki_awal'] }}"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('baki_awal')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <button type="submit"
                            class="w-full rounded-lg bg-indigo-600 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                            Jana Laporan
                        </button>
                    </div>

                    <div class="md:col-span-5 flex flex-wrap items-center gap-2 pt-2">
                        <a href="{{ $bolehEksport ? route('laporan.buku-tunai.export.pdf', $exportParams) : '#' }}"
                            class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-medium text-rose-700 transition hover:bg-rose-100 {{ $bolehEksport ? '' : 'pointer-events-none opacity-50' }}">
                            Eksport PDF
                        </a>
                        <a href="{{ $bolehEksport ? route('laporan.buku-tunai.export.excel', $exportParams) : '#' }}"
                            class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700 transition hover:bg-emerald-100 {{ $bolehEksport ? '' : 'pointer-events-none opacity-50' }}">
                            Eksport Excel
                        </a>
                        <a href="{{ $bolehEksport ? route('laporan.buku-tunai.print', $exportParams) : '#' }}"
                            target="_blank"
                            class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-100 {{ $bolehEksport ? '' : 'pointer-events-none opacity-50' }}">
                            Paparan Cetak
                        </a>
                    </div>
                </form>
            </div>

            @if ($laporan)
                <div class="overflow-hidden rounded-xl bg-white shadow">
                    <div class="border-b border-gray-100 px-4 py-3">
                        <h3 class="text-sm font-semibold text-gray-800">{{ $laporan['akaun']->nama_akaun }}</h3>
                        <p class="text-xs text-gray-500">
                            Tempoh:
                            {{ \Illuminate\Support\Carbon::parse($laporan['tempoh']['tarikh_mula'])->format('d/m/Y') }}
                            -
                            {{ \Illuminate\Support\Carbon::parse($laporan['tempoh']['tarikh_akhir'])->format('d/m/Y') }}
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Tarikh</th>
                                    <th class="px-4 py-3 text-left">Butiran</th>
                                    <th class="px-4 py-3 text-right">Masuk (RM)</th>
                                    <th class="px-4 py-3 text-right">Keluar (RM)</th>
                                    <th class="px-4 py-3 text-right">Baki (RM)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr class="bg-slate-50">
                                    <td class="px-4 py-3 text-gray-600">-</td>
                                    <td class="px-4 py-3 font-medium text-gray-700">Baki Awal</td>
                                    <td class="px-4 py-3 text-right text-gray-500">0.00</td>
                                    <td class="px-4 py-3 text-right text-gray-500">0.00</td>
                                    <td class="px-4 py-3 text-right font-semibold text-indigo-700">
                                        {{ number_format($laporan['ringkasan']['baki_awal'], 2, '.', ',') }}
                                    </td>
                                </tr>

                                @forelse ($laporan['rows'] as $row)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ \Illuminate\Support\Carbon::parse($row['tarikh'])->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-800">{{ $row['butiran'] }}</td>
                                        <td class="px-4 py-3 text-right text-emerald-700">
                                            {{ number_format($row['masuk'], 2, '.', ',') }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-rose-700">
                                            {{ number_format($row['keluar'], 2, '.', ',') }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-gray-800">
                                            {{ number_format($row['baki'], 2, '.', ',') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">Tiada
                                            transaksi dalam tempoh dipilih.</td>
                                    </tr>
                                @endforelse

                                <tr class="bg-indigo-50 font-semibold">
                                    <td class="px-4 py-3 text-gray-700">-</td>
                                    <td class="px-4 py-3 text-gray-800">Ringkasan Tempoh</td>
                                    <td class="px-4 py-3 text-right text-emerald-700">
                                        {{ number_format($laporan['ringkasan']['jumlah_masuk'], 2, '.', ',') }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-rose-700">
                                        {{ number_format($laporan['ringkasan']['jumlah_keluar'], 2, '.', ',') }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-indigo-700">
                                        {{ number_format($laporan['ringkasan']['baki_akhir'], 2, '.', ',') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
