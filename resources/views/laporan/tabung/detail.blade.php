<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Detail Tabung Khas</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @php
                $detailExportParams = [
                    'masjid_id' => $filters['masjid_id'] ?? null,
                    'tarikh_dari' => $filters['tarikh_dari'],
                    'tarikh_hingga' => $filters['tarikh_hingga'],
                ];
            @endphp

            <div class="rounded-xl bg-white p-5 shadow">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $tabung->nama_tabung }}</h3>
                        <p class="text-sm text-gray-600">Tempoh: {{ $tempoh_label }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('laporan.tabung.detail.export.pdf', ['tabung' => $tabung->id] + $detailExportParams) }}"
                            class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-100">
                            Eksport PDF
                        </a>
                        <a href="{{ route('laporan.tabung.detail.export.excel', ['tabung' => $tabung->id] + $detailExportParams) }}"
                            class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100">
                            Eksport Excel
                        </a>
                        <a href="{{ route('laporan.tabung', $filters) }}"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                            ← Kembali Ringkasan
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-600">Baki Awal</p>
                    <p class="mt-2 text-2xl font-semibold {{ $baki_awal >= 0 ? 'text-slate-700' : 'text-rose-700' }}">
                        RM {{ number_format($baki_awal, 2, '.', ',') }}
                    </p>
                </div>
                <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-emerald-700">Masuk Tempoh</p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-700">
                        RM {{ number_format($jumlah_masuk, 2, '.', ',') }}
                    </p>
                </div>
                <div class="rounded-xl border border-rose-100 bg-rose-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-rose-700">Keluar Tempoh</p>
                    <p class="mt-2 text-2xl font-semibold text-rose-700">
                        RM {{ number_format($jumlah_keluar, 2, '.', ',') }}
                    </p>
                </div>
                <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-indigo-700">Baki Akhir</p>
                    <p class="mt-2 text-2xl font-semibold {{ $baki_akhir >= 0 ? 'text-indigo-700' : 'text-rose-700' }}">
                        RM {{ number_format($baki_akhir, 2, '.', ',') }}
                    </p>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow">
                <div class="border-b border-gray-100 px-4 py-3">
                    <h3 class="text-sm font-semibold text-gray-800">Timeline Baki Berjalan</h3>
                    <p class="mt-1 text-xs text-gray-500">Susunan kronologi transaksi dengan baki selepas setiap
                        pergerakan.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Tarikh</th>
                                <th class="px-4 py-3 text-left">Jenis</th>
                                <th class="px-4 py-3 text-left">Rujukan</th>
                                <th class="px-4 py-3 text-left">Butiran</th>
                                <th class="px-4 py-3 text-right">Masuk</th>
                                <th class="px-4 py-3 text-right">Keluar</th>
                                <th class="px-4 py-3 text-right">Baki Berjalan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($timeline_rows as $row)
                                <tr
                                    class="transition hover:bg-slate-50 {{ !empty($row['is_opening']) ? 'bg-slate-50' : '' }}">
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ \Carbon\Carbon::parse($row['tarikh'])->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $row['jenis'] }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $row['rujukan'] }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $row['butiran'] }}</td>
                                    <td class="px-4 py-3 text-right text-emerald-700">
                                        @if (($row['masuk'] ?? 0) > 0)
                                            RM {{ number_format($row['masuk'], 2, '.', ',') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-rose-700">
                                        @if (($row['keluar'] ?? 0) > 0)
                                            RM {{ number_format($row['keluar'], 2, '.', ',') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right font-semibold {{ $row['baki_berjalan'] >= 0 ? 'text-indigo-700' : 'text-rose-700' }}">
                                        RM {{ number_format($row['baki_berjalan'], 2, '.', ',') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                                        Tiada data timeline untuk tempoh ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow">
                <div class="border-b border-gray-100 px-4 py-3">
                    <h3 class="text-sm font-semibold text-gray-800">Transaksi Masuk</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Tarikh</th>
                                <th class="px-4 py-3 text-left">Sumber Hasil</th>
                                <th class="px-4 py-3 text-left">Akaun</th>
                                <th class="px-4 py-3 text-left">Catatan</th>
                                <th class="px-4 py-3 text-right">Tunai</th>
                                <th class="px-4 py-3 text-right">Online</th>
                                <th class="px-4 py-3 text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($transaksi_masuk as $row)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ \Carbon\Carbon::parse($row['tarikh'])->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ $row['sumber_hasil'] }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $row['akaun'] }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $row['catatan'] }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">RM
                                        {{ number_format($row['tunai'], 2, '.', ',') }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">RM
                                        {{ number_format($row['online'], 2, '.', ',') }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-emerald-700">RM
                                        {{ number_format($row['jumlah'], 2, '.', ',') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                                        Tiada transaksi masuk untuk tempoh ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-emerald-50">
                                <td colspan="6" class="px-4 py-3 font-semibold text-gray-800">Jumlah Masuk</td>
                                <td class="px-4 py-3 text-right font-semibold text-emerald-700">RM
                                    {{ number_format($jumlah_masuk, 2, '.', ',') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow">
                <div class="border-b border-gray-100 px-4 py-3">
                    <h3 class="text-sm font-semibold text-gray-800">Transaksi Keluar</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Tarikh</th>
                                <th class="px-4 py-3 text-left">Kategori</th>
                                <th class="px-4 py-3 text-left">Penerima</th>
                                <th class="px-4 py-3 text-left">Akaun</th>
                                <th class="px-4 py-3 text-left">Catatan</th>
                                <th class="px-4 py-3 text-right">Amaun</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($transaksi_keluar as $row)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ \Carbon\Carbon::parse($row['tarikh'])->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ $row['kategori'] }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $row['penerima'] }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $row['akaun'] }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $row['catatan'] }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-rose-700">RM
                                        {{ number_format($row['amaun'], 2, '.', ',') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                        Tiada transaksi keluar untuk tempoh ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-rose-50">
                                <td colspan="5" class="px-4 py-3 font-semibold text-gray-800">Jumlah Keluar</td>
                                <td class="px-4 py-3 text-right font-semibold text-rose-700">RM
                                    {{ number_format($jumlah_keluar, 2, '.', ',') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
