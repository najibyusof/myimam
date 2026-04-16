<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Laporan Belanja</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @php
                $exportParams = [
                    'tarikh_dari' => $filters['tarikh_dari'],
                    'tarikh_hingga' => $filters['tarikh_hingga'],
                    'jenis_paparan' => $filters['jenis_paparan'] ?? 'ringkasan_kategori',
                    'kategori_id' => $filters['kategori_id'] ?? null,
                    'akaun_id' => $filters['akaun_id'] ?? null,
                    'status' => $filters['status'] ?? 'all',
                    'masjid_id' => $filters['masjid_id'] ?? null,
                ];
            @endphp

            <div class="rounded-xl bg-white p-5 shadow">
                <form method="GET" action="{{ route('laporan.belanja') }}" id="laporan-belanja-form" class="space-y-4">
                    @if ($is_superadmin)
                        <div class="grid grid-cols-1 md:max-w-sm">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">Masjid</label>
                                <select name="masjid_id" id="laporan-belanja-masjid" required
                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Masjid</option>
                                    @foreach ($masjid_list as $masjid)
                                        <option value="{{ $masjid['id'] }}" @selected((string) ($filters['masjid_id'] ?? '') === (string) $masjid['id'])>
                                            {{ $masjid['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-12">

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

                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs font-medium text-gray-600">Kategori</label>
                            <select name="kategori_id" id="laporan-belanja-kategori"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Semua Kategori</option>
                                @foreach ($kategori_list as $kategori)
                                    <option value="{{ $kategori['id'] }}" @selected((string) ($filters['kategori_id'] ?? '') === (string) $kategori['id'])>
                                        {{ $kategori['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs font-medium text-gray-600">Akaun</label>
                            <select name="akaun_id" id="laporan-belanja-akaun"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Semua Akaun</option>
                                @foreach ($akaun_list as $akaun)
                                    <option value="{{ $akaun['id'] }}" @selected((string) ($filters['akaun_id'] ?? '') === (string) $akaun['id'])>
                                        {{ $akaun['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-12 md:items-end">
                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs font-medium text-gray-600">Status</label>
                            <select name="status"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Semua</option>
                                <option value="draf" @selected(($filters['status'] ?? 'all') === 'draf')>DRAF</option>
                                <option value="lulus" @selected(($filters['status'] ?? 'all') === 'lulus')>LULUS</option>
                            </select>
                        </div>

                        <div class="md:col-span-3">
                            <label class="mb-1 block text-xs font-medium text-gray-600">Jenis Paparan</label>
                            <select name="jenis_paparan"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="ringkasan_kategori" @selected(($filters['jenis_paparan'] ?? 'ringkasan_kategori') === 'ringkasan_kategori')>Ringkasan Mengikut
                                    Kategori</option>
                                <option value="ringkasan_bulan" @selected(($filters['jenis_paparan'] ?? 'ringkasan_kategori') === 'ringkasan_bulan')>Ringkasan Mengikut Bulan
                                </option>
                                <option value="senarai_transaksi" @selected(($filters['jenis_paparan'] ?? 'ringkasan_kategori') === 'senarai_transaksi')>Senarai Transaksi
                                </option>
                            </select>
                        </div>

                        <div class="md:col-span-6">
                            <button type="submit"
                                class="w-full rounded-lg bg-indigo-600 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                                Jana Laporan
                            </button>
                        </div>
                    </div>
                </form>

                @if ($is_superadmin && empty($filters['masjid_id']))
                    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Sila pilih masjid untuk jana laporan belanja yang tepat bagi masjid tersebut.
                    </div>
                @endif

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <a href="{{ !empty($filters['masjid_id']) || !$is_superadmin ? route('laporan.belanja.export.pdf', $exportParams) : '#' }}"
                        class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-medium text-rose-700 transition hover:bg-rose-100 {{ $is_superadmin && empty($filters['masjid_id']) ? 'pointer-events-none opacity-50' : '' }}">
                        Eksport PDF
                    </a>
                    <a href="{{ !empty($filters['masjid_id']) || !$is_superadmin ? route('laporan.belanja.export.excel', $exportParams) : '#' }}"
                        class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700 transition hover:bg-emerald-100 {{ $is_superadmin && empty($filters['masjid_id']) ? 'pointer-events-none opacity-50' : '' }}">
                        Eksport Excel
                    </a>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow">
                @if (($filters['jenis_paparan'] ?? 'ringkasan_kategori') === 'ringkasan_kategori')
                    <div class="border-b border-gray-100 px-4 py-3">
                        <h3 class="text-sm font-semibold text-gray-800">Ringkasan Belanja Mengikut Kategori</h3>
                        <p class="mt-1 text-xs text-gray-500">Jumlah belanja mengikut kategori.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Kategori</th>
                                    <th class="px-4 py-3 text-right">Bil. Rekod</th>
                                    <th class="px-4 py-3 text-right">Jumlah (RM)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($rows as $row)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-4 py-3 text-gray-800">{{ $row['kategori'] }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700">{{ $row['bil_rekod'] }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-gray-800">
                                            {{ number_format($row['jumlah'], 2, '.', ',') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-gray-400">
                                            {{ $requires_masjid_selection ? 'Pilih masjid untuk melihat ringkasan belanja.' : 'Tiada rekod belanja untuk tempoh ini.' }}
                                        </td>
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
                @elseif (($filters['jenis_paparan'] ?? 'ringkasan_kategori') === 'ringkasan_bulan')
                    <div class="border-b border-gray-100 px-4 py-3">
                        <h3 class="text-sm font-semibold text-gray-800">Ringkasan Belanja Mengikut Bulan</h3>
                        <p class="mt-1 text-xs text-gray-500">Jumlah belanja mengikut bulan.</p>
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
                                        <td colspan="3" class="px-4 py-8 text-center text-gray-400">
                                            {{ $requires_masjid_selection ? 'Pilih masjid untuk melihat ringkasan belanja.' : 'Tiada rekod belanja untuk tempoh ini.' }}
                                        </td>
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
                        <h3 class="text-sm font-semibold text-gray-800">Senarai Belanja</h3>
                        <p class="mt-1 text-xs text-gray-500">Klik baris untuk buka dan edit rekod belanja.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Tarikh</th>
                                    <th class="px-4 py-3 text-left">Kategori</th>
                                    <th class="px-4 py-3 text-left">Akaun</th>
                                    <th class="px-4 py-3 text-left">Penerima</th>
                                    <th class="px-4 py-3 text-right">Amaun (RM)</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-left">Catatan</th>
                                    <th class="px-4 py-3 text-center">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($senarai_rows as $row)
                                    <tr class="cursor-pointer hover:bg-blue-50 transition"
                                        onclick="window.location='{{ $row['edit_url'] }}'">
                                        <td class="px-4 py-3 text-gray-700">
                                            {{ \Carbon\Carbon::parse($row['tarikh'])->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-gray-800">{{ $row['kategori'] }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ $row['akaun'] }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ $row['penerima'] }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-gray-800">
                                            {{ number_format($row['amaun'], 2, '.', ',') }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if ($row['status'] === 'LULUS')
                                                <span
                                                    class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">LULUS</span>
                                            @else
                                                <span
                                                    class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-800">DRAF</span>
                                            @endif
                                        </td>
                                        <td class="max-w-xs truncate px-4 py-3 text-gray-700"
                                            title="{{ $row['catatan'] }}">{{ $row['catatan'] }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <a href="{{ $row['edit_url'] }}"
                                                class="rounded-md border border-indigo-200 bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100"
                                                onclick="event.stopPropagation()">
                                                Buka / Edit
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-8 text-center text-gray-400">Tiada rekod
                                            belanja untuk tempoh ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-indigo-50">
                                    <td colspan="4" class="px-4 py-3 font-semibold text-gray-800">Jumlah
                                        Keseluruhan</td>
                                    <td class="px-4 py-3 text-right font-semibold text-indigo-700">
                                        {{ number_format($jumlah_keseluruhan, 2, '.', ',') }}</td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($is_superadmin)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('laporan-belanja-form');
                const masjidSelect = document.getElementById('laporan-belanja-masjid');
                const kategoriSelect = document.getElementById('laporan-belanja-kategori');
                const akaunSelect = document.getElementById('laporan-belanja-akaun');

                if (!form || !masjidSelect || !kategoriSelect || !akaunSelect) {
                    return;
                }

                masjidSelect.addEventListener('change', function() {
                    kategoriSelect.value = '';
                    akaunSelect.value = '';
                    form.submit();
                });
            });
        </script>
    @endif
</x-app-layout>
