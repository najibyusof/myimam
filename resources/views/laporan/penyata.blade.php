<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Penyata Pendapatan &amp; Perbelanjaan</h2>
    </x-slot>

    {{-- ===================== PRINT STYLES (A4 landscape, mirrors PDF) ===================== --}}
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 18mm 22mm;
            }

            /* Hide all app/layout content by default */
            body * {
                visibility: hidden !important;
            }

            /* Force hide of screen report and controls */
            #penyata-print-area,
            #penyata-print-area * {
                display: none !important;
                visibility: hidden !important;
            }

            .no-print {
                display: none !important;
            }

            /* Reveal only the dedicated PDF-like print view */
            #penyata-print-view {
                display: block !important;
                visibility: visible !important;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                width: auto;
                margin-left: 10mm;
                margin-right: 10mm;
                overflow: hidden;
            }

            #penyata-print-view * {
                visibility: visible !important;
            }

            /* ---- Base ---- */
            #penyata-print-view,
            #penyata-print-view * {
                box-sizing: border-box;
            }

            #penyata-print-view {
                font-family: DejaVu Sans, Arial, sans-serif;
                font-size: 9pt;
                color: #1a1a1a;
                background: #fff;
                padding: 0;
                line-height: 1.35;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* ---- Page header ---- */
            #pv-header {
                text-align: center;
                border-bottom: 2px solid #1e3a5f;
                padding-bottom: 10px;
                margin-bottom: 14px;
            }

            #pv-header .pv-masjid {
                font-size: 13pt;
                font-weight: bold;
                color: #1e3a5f;
            }

            #pv-header .pv-alamat {
                font-size: 8.5pt;
                color: #555;
                margin-top: 2px;
            }

            #pv-header .pv-tajuk {
                font-size: 11pt;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-top: 8px;
            }

            #pv-header .pv-tempoh {
                font-size: 9pt;
                color: #555;
                margin-top: 3px;
            }

            /* ---- Meta bar ---- */
            #pv-meta {
                display: flex;
                justify-content: space-between;
                font-size: 7.5pt;
                color: #777;
                margin-bottom: 12px;
                border-bottom: 1px solid #e0e0e0;
                padding-bottom: 5px;
            }

            /* ---- Two-column layout ---- */
            .pv-dual {
                display: flex !important;
                width: 100%;
                gap: 0;
            }

            .pv-col {
                display: block !important;
                width: 50%;
                vertical-align: top;
            }

            .pv-col:first-child {
                padding-right: 10px;
                border-right: 1px solid #ddd;
            }

            .pv-col:last-child {
                padding-left: 10px;
            }

            /* ---- Section title ---- */
            .pv-section-title {
                font-size: 9pt;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: #1e3a5f;
                border-bottom: 1px solid #1e3a5f;
                padding-bottom: 3px;
                margin-bottom: 6px;
            }

            /* ---- Data table: force table display (Tailwind preflight resets these) ---- */
            .pv-dt {
                display: table !important;
                width: 100%;
                border-collapse: collapse;
                font-size: 8.5pt;
                table-layout: fixed;
            }

            .pv-dt thead {
                display: table-header-group !important;
            }

            .pv-dt tbody {
                display: table-row-group !important;
            }

            .pv-dt tfoot {
                display: table-footer-group !important;
            }

            .pv-dt tr {
                display: table-row !important;
            }

            .pv-dt th,
            .pv-dt td {
                display: table-cell !important;
                overflow-wrap: break-word;
                word-wrap: break-word;
            }

            .pv-dt th:nth-child(1),
            .pv-dt td:nth-child(1) {
                width: 31%;
            }

            .pv-dt th:nth-child(2),
            .pv-dt td:nth-child(2) {
                width: 17%;
            }

            .pv-dt th:nth-child(3),
            .pv-dt td:nth-child(3) {
                width: 10%;
            }

            .pv-dt th:nth-child(4),
            .pv-dt td:nth-child(4) {
                width: 19%;
            }

            .pv-dt th:nth-child(5),
            .pv-dt td:nth-child(5) {
                width: 23%;
            }

            .pv-dt thead th {
                background: #f0f4f8;
                color: #444;
                font-weight: bold;
                padding: 5px 6px;
                text-align: left;
                font-size: 7.5pt;
                text-transform: uppercase;
                border-bottom: 1px solid #ccc;
            }

            .pv-dt thead th.r {
                text-align: right;
            }

            .pv-dt thead th.c {
                text-align: center;
            }

            .pv-dt tbody td {
                padding: 4px 6px;
                border-bottom: 1px solid #eee;
                vertical-align: middle;
            }

            .pv-dt tbody td.r {
                text-align: right;
            }

            .pv-dt tbody td.c {
                text-align: center;
            }

            .pv-dt tbody td.b {
                font-weight: 500;
            }

            .pv-dt tbody tr:last-child td {
                border-bottom: none;
            }

            .pv-dt tfoot td {
                padding: 5px 6px;
                font-weight: bold;
                border-top: 2px solid #1e3a5f;
                background: #eef3fb;
                color: #1e3a5f;
            }

            .pv-dt tfoot td.r {
                text-align: right;
            }

            .pv-empty-row td {
                text-align: center;
                color: #aaa;
                font-style: italic;
                padding: 10px 6px;
            }

            /* ---- Colours ---- */
            .pv-up {
                color: #16a34a;
                font-weight: bold;
            }

            .pv-down {
                color: #dc2626;
                font-weight: bold;
            }

            .pv-flat {
                color: #888;
            }

            /* ---- Summary table ---- */
            .pv-sgrid {
                display: table !important;
                width: 100%;
                border-collapse: collapse;
            }

            .pv-sgrid tbody {
                display: table-row-group !important;
            }

            .pv-sgrid tr {
                display: table-row !important;
            }

            .pv-sgrid td {
                display: table-cell !important;
                padding: 4px 10px;
                font-size: 9pt;
                vertical-align: middle;
            }

            .pv-sgrid td.lbl {
                font-weight: bold;
                color: #333;
                width: 35%;
            }

            .pv-sgrid td.val {
                text-align: right;
                font-weight: bold;
                width: 20%;
            }

            .pv-sgrid td.prev {
                text-align: right;
                color: #888;
                font-size: 8pt;
                width: 20%;
            }

            .pv-sgrid td.chg {
                text-align: right;
                font-weight: bold;
                width: 25%;
            }

            .pv-sgrid tr.sep {
                border-top: 1px solid #ddd;
            }

            .pv-sgrid tr.pv-lebihan td {
                color: #16a34a;
                font-size: 11pt;
            }

            .pv-sgrid tr.pv-kekurangan td {
                color: #dc2626;
                font-size: 11pt;
            }

            /* ---- Footer ---- */
            .pv-footer {
                margin-top: 18px;
                font-size: 7.5pt;
                color: #aaa;
                text-align: center;
                border-top: 1px solid #eee;
                padding-top: 6px;
            }

            /* ---- Comparison toggle ---- */
            #penyata-print-view.hide-comparison .pv-cmp {
                display: none !important;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>

    <div class="py-8" id="penyata-print-area">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- ===== HEADER CARD ===== --}}
            <div class="rounded-xl bg-white p-6 shadow">
                <div class="text-center">
                    <p class="text-lg font-semibold text-gray-900">{{ $masjid_nama }}</p>
                    <p class="text-sm text-gray-600">{{ $masjid_alamat }}</p>
                    <p class="mt-4 text-xl font-bold uppercase tracking-wide text-gray-900">Penyata Pendapatan dan
                        Perbelanjaan</p>
                    <p class="mt-1 text-sm font-medium text-gray-700">Tempoh: {{ $tempoh_label }}</p>
                    <p class="comparison-only mt-0.5 text-xs text-gray-400">Perbandingan dengan:
                        {{ $prev_tempoh_label }}</p>
                </div>

                {{-- Filter form --}}
                <div class="no-print mt-6 border-t border-gray-100 pt-5">
                    <form method="GET" action="{{ route('laporan.penyata') }}"
                        class="grid grid-cols-1 gap-4 md:grid-cols-5 md:items-end">
                        @if ($is_superadmin)
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">Masjid</label>
                                <select name="masjid_id"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Masjid</option>
                                    @foreach ($masjid_list as $masjid)
                                        <option value="{{ $masjid['id'] }}" @selected((string) ($filters['masjid_id'] ?? '') === (string) $masjid['id'])>
                                            {{ $masjid['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Jenis Penyata</label>
                            <select name="jenis_penyata" onchange="this.form.submit()"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="bulanan" @selected(($filters['jenis_penyata'] ?? 'bulanan') === 'bulanan')>Bulanan</option>
                                <option value="tahunan" @selected(($filters['jenis_penyata'] ?? 'bulanan') === 'tahunan')>Tahunan</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Tahun</label>
                            <select name="tahun"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($tahun_opsyen as $tahun)
                                    <option value="{{ $tahun }}" @selected((int) ($filters['tahun'] ?? now()->year) === (int) $tahun)>{{ $tahun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Bulan</label>
                            <select name="bulan" @disabled(($filters['jenis_penyata'] ?? 'bulanan') !== 'bulanan')
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:cursor-not-allowed disabled:bg-gray-100">
                                @foreach ($bulan_opsyen as $bulan)
                                    <option value="{{ $bulan['id'] }}" @selected((int) ($filters['bulan'] ?? now()->month) === (int) $bulan['id'])>
                                        {{ ucfirst($bulan['nama']) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <button type="submit"
                                class="w-full rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 sm:w-auto">
                                Jana Penyata
                            </button>
                            <div
                                class="flex items-center gap-2 border-t border-gray-100 pt-2 sm:border-l sm:border-t-0 sm:pl-4 sm:pt-0">
                                <button type="button" id="comparisonToggle" aria-pressed="false"
                                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                                    title="Sembunyi perbandingan tempoh lepas">
                                    Sembunyi Perbandingan
                                </button>
                                <a id="pdfLink"
                                    href="{{ !empty($filters['masjid_id']) || !$is_superadmin ? route('laporan.penyata.export.pdf', $filters) : '#' }}"
                                    data-base-url="{{ !empty($filters['masjid_id']) || !$is_superadmin ? route('laporan.penyata.export.pdf', $filters) : '' }}"
                                    class="rounded-lg border border-rose-300 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-100 {{ $is_superadmin && empty($filters['masjid_id']) ? 'pointer-events-none opacity-50' : '' }}"
                                    title="Muat turun PDF">
                                    PDF
                                </a>
                                <button type="button" onclick="window.print()"
                                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                                    title="Cetak halaman">
                                    Cetak
                                </button>
                            </div>
                        </div>
                    </form>

                    @if ($is_superadmin && empty($filters['masjid_id']))
                        <div
                            class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            Superadmin perlu memilih masjid terlebih dahulu untuk jana penyata bagi masjid yang dipilih.
                        </div>
                    @endif
                </div>
            </div>

            {{-- ===== TWO-COLUMN TABLES ===== --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                {{-- PENDAPATAN --}}
                <div class="overflow-hidden rounded-xl bg-white shadow">
                    <div class="border-b border-gray-100 bg-emerald-50 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Pendapatan
                                (Hasil)</h3>
                            <span class="comparison-only text-xs text-gray-500">vs {{ $prev_tempoh_label }}</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-4 py-2.5 text-left">Butiran</th>
                                    <th class="px-3 py-2.5 text-right">Jumlah (RM)</th>
                                    <th class="comparison-only px-3 py-2.5 text-right">%</th>
                                    <th class="comparison-only px-3 py-2.5 text-right no-print">Tempoh Lepas</th>
                                    <th class="comparison-only px-3 py-2.5 text-right no-print">+/-</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($pendapatan_rows as $row)
                                    <tr class="transition hover:bg-slate-50">
                                        <td class="px-4 py-2.5">
                                            <a href="{{ $row['detail_url'] }}"
                                                class="font-medium text-blue-600 hover:text-blue-800 hover:underline">
                                                {{ $row['butiran'] }}
                                            </a>
                                            <div class="mt-1 h-1.5 w-full rounded-full bg-gray-100">
                                                <div class="h-1.5 rounded-full bg-emerald-400 transition-all"
                                                    style="width: {{ min($row['peratus'], 100) }}%"></div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2.5 text-right font-medium tabular-nums text-gray-800">
                                            {{ number_format($row['jumlah'], 2, '.', ',') }}
                                        </td>
                                        <td class="comparison-only px-3 py-2.5 text-right tabular-nums">
                                            <span
                                                class="rounded bg-emerald-50 px-1.5 py-0.5 text-xs font-semibold text-emerald-700">
                                                {{ $row['peratus'] }}%
                                            </span>
                                        </td>
                                        <td
                                            class="comparison-only px-3 py-2.5 text-right text-xs tabular-nums text-gray-400 no-print">
                                            {{ $row['prev_jumlah'] > 0 ? number_format($row['prev_jumlah'], 2, '.', ',') : chr(8212) }}
                                        </td>
                                        <td
                                            class="comparison-only px-3 py-2.5 text-right text-xs tabular-nums no-print">
                                            @if ($row['peratus_perubahan'] !== null)
                                                <span
                                                    class="{{ $row['perubahan'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-semibold">
                                                    {{ $row['perubahan'] >= 0 ? chr(9650) : chr(9660) }}
                                                    {{ abs($row['peratus_perubahan']) }}%
                                                </span>
                                            @elseif ($row['jumlah'] > 0 && $row['prev_jumlah'] == 0)
                                                <span class="text-xs font-medium text-blue-500">Baharu</span>
                                            @else
                                                <span class="text-gray-300">{{ chr(8212) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                            {{ $is_superadmin && empty($filters['masjid_id']) ? 'Pilih masjid untuk melihat rekod pendapatan.' : 'Tiada rekod pendapatan.' }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-emerald-50">
                                    <td class="px-4 py-3 font-semibold text-gray-800">Jumlah Pendapatan</td>
                                    <td class="px-3 py-3 text-right font-bold tabular-nums text-emerald-700">
                                        {{ number_format($jumlah_pendapatan, 2, '.', ',') }}
                                    </td>
                                    <td
                                        class="comparison-only px-3 py-3 text-right text-xs font-semibold text-gray-500">
                                        100%</td>
                                    <td
                                        class="comparison-only px-3 py-3 text-right text-xs tabular-nums text-gray-400 no-print">
                                        {{ number_format($prev_jumlah_pendapatan, 2, '.', ',') }}
                                    </td>
                                    <td class="comparison-only px-3 py-3 text-right text-xs tabular-nums no-print">
                                        @php $chgP = $jumlah_pendapatan - $prev_jumlah_pendapatan; @endphp
                                        <span
                                            class="{{ $chgP >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-semibold">
                                            {{ $chgP >= 0 ? '+' : '' }}{{ number_format($chgP, 2, '.', ',') }}
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- PERBELANJAAN --}}
                <div class="overflow-hidden rounded-xl bg-white shadow">
                    <div class="border-b border-gray-100 bg-rose-50 px-4 py-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-rose-800">Perbelanjaan</h3>
                            <span class="comparison-only text-xs text-gray-500">vs {{ $prev_tempoh_label }}</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-4 py-2.5 text-left">Butiran</th>
                                    <th class="px-3 py-2.5 text-right">Jumlah (RM)</th>
                                    <th class="comparison-only px-3 py-2.5 text-right">%</th>
                                    <th class="comparison-only px-3 py-2.5 text-right no-print">Tempoh Lepas</th>
                                    <th class="comparison-only px-3 py-2.5 text-right no-print">+/-</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($perbelanjaan_rows as $row)
                                    <tr class="transition hover:bg-slate-50">
                                        <td class="px-4 py-2.5">
                                            <a href="{{ $row['detail_url'] }}"
                                                class="font-medium text-blue-600 hover:text-blue-800 hover:underline">
                                                {{ $row['butiran'] }}
                                            </a>
                                            <div class="mt-1 h-1.5 w-full rounded-full bg-gray-100">
                                                <div class="h-1.5 rounded-full bg-rose-400 transition-all"
                                                    style="width: {{ min($row['peratus'], 100) }}%"></div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2.5 text-right font-medium tabular-nums text-gray-800">
                                            {{ number_format($row['jumlah'], 2, '.', ',') }}
                                        </td>
                                        <td class="comparison-only px-3 py-2.5 text-right tabular-nums">
                                            <span
                                                class="rounded bg-rose-50 px-1.5 py-0.5 text-xs font-semibold text-rose-700">
                                                {{ $row['peratus'] }}%
                                            </span>
                                        </td>
                                        <td
                                            class="comparison-only px-3 py-2.5 text-right text-xs tabular-nums text-gray-400 no-print">
                                            {{ $row['prev_jumlah'] > 0 ? number_format($row['prev_jumlah'], 2, '.', ',') : chr(8212) }}
                                        </td>
                                        <td
                                            class="comparison-only px-3 py-2.5 text-right text-xs tabular-nums no-print">
                                            @if ($row['peratus_perubahan'] !== null)
                                                <span
                                                    class="{{ $row['perubahan'] >= 0 ? 'text-rose-600' : 'text-emerald-600' }} font-semibold">
                                                    {{ $row['perubahan'] >= 0 ? chr(9650) : chr(9660) }}
                                                    {{ abs($row['peratus_perubahan']) }}%
                                                </span>
                                            @elseif ($row['jumlah'] > 0 && $row['prev_jumlah'] == 0)
                                                <span class="text-xs font-medium text-blue-500">Baharu</span>
                                            @else
                                                <span class="text-gray-300">{{ chr(8212) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                            {{ $is_superadmin && empty($filters['masjid_id']) ? 'Pilih masjid untuk melihat rekod perbelanjaan.' : 'Tiada rekod perbelanjaan.' }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-rose-50">
                                    <td class="px-4 py-3 font-semibold text-gray-800">Jumlah Perbelanjaan</td>
                                    <td class="px-3 py-3 text-right font-bold tabular-nums text-rose-700">
                                        {{ number_format($jumlah_perbelanjaan, 2, '.', ',') }}
                                    </td>
                                    <td
                                        class="comparison-only px-3 py-3 text-right text-xs font-semibold text-gray-500">
                                        100%</td>
                                    <td
                                        class="comparison-only px-3 py-3 text-right text-xs tabular-nums text-gray-400 no-print">
                                        {{ number_format($prev_jumlah_perbelanjaan, 2, '.', ',') }}
                                    </td>
                                    <td class="comparison-only px-3 py-3 text-right text-xs tabular-nums no-print">
                                        @php $chgB = $jumlah_perbelanjaan - $prev_jumlah_perbelanjaan; @endphp
                                        <span
                                            class="{{ $chgB >= 0 ? 'text-rose-600' : 'text-emerald-600' }} font-semibold">
                                            {{ $chgB >= 0 ? '+' : '' }}{{ number_format($chgB, 2, '.', ',') }}
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ===== LEBIHAN / KEKURANGAN SUMMARY ===== --}}
            <div
                class="rounded-xl border bg-white px-6 py-5 shadow
                {{ $lebihan_kurangan >= 0 ? 'border-emerald-200' : 'border-rose-200' }}">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            {{ $lebihan_kurangan >= 0 ? 'Lebihan' : 'Kekurangan' }} Bersih
                        </p>
                        <p
                            class="mt-1 text-2xl font-bold {{ $lebihan_kurangan >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                            RM {{ number_format($lebihan_kurangan, 2, '.', ',') }}
                        </p>
                    </div>
                    <div
                        class="comparison-only no-print flex flex-col items-end gap-1 text-right text-xs text-gray-500">
                        <span>Tempoh Lepas ({{ $prev_tempoh_label }}):
                            <span
                                class="{{ $prev_lebihan_kurangan >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-semibold">
                                RM {{ number_format($prev_lebihan_kurangan, 2, '.', ',') }}
                            </span>
                        </span>
                        @php $chgL = $lebihan_kurangan - $prev_lebihan_kurangan; @endphp
                        <span>Perubahan:
                            <span class="{{ $chgL >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-semibold">
                                {{ $chgL >= 0 ? '+' : '' }}RM {{ number_format($chgL, 2, '.', ',') }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- ===== CHARTS SECTION ===== --}}
            <div class="chart-section no-print grid grid-cols-1 gap-6 lg:grid-cols-3">

                {{-- Bar chart: Current vs Previous --}}
                <div class="overflow-hidden rounded-xl bg-white shadow">
                    <div class="border-b border-gray-100 px-4 py-3">
                        <h3 class="text-sm font-semibold text-gray-700">Pendapatan vs Perbelanjaan</h3>
                        <p class="comparison-only text-xs text-gray-400">Tempoh ini berbanding tempoh lepas</p>
                    </div>
                    <div class="p-4">
                        <canvas id="chartBar" height="220"></canvas>
                    </div>
                </div>

                {{-- Doughnut: Income breakdown --}}
                <div class="overflow-hidden rounded-xl bg-white shadow">
                    <div class="border-b border-gray-100 px-4 py-3">
                        <h3 class="text-sm font-semibold text-emerald-700">Agihan Pendapatan</h3>
                        <p class="text-xs text-gray-400">Mengikut sumber hasil</p>
                    </div>
                    <div class="p-4">
                        <canvas id="chartHasil" height="220"></canvas>
                    </div>
                </div>

                {{-- Doughnut: Expense breakdown --}}
                <div class="overflow-hidden rounded-xl bg-white shadow">
                    <div class="border-b border-gray-100 px-4 py-3">
                        <h3 class="text-sm font-semibold text-rose-700">Agihan Perbelanjaan</h3>
                        <p class="text-xs text-gray-400">Mengikut kategori</p>
                    </div>
                    <div class="p-4">
                        <canvas id="chartBelanja" height="220"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
        <script>
            (function() {
                const emeraldPalette = [
                    '#10b981', '#34d399', '#059669', '#6ee7b7', '#047857',
                    '#a7f3d0', '#065f46', '#d1fae5', '#ecfdf5', '#00b37d',
                ];
                const rosePalette = [
                    '#f43f5e', '#fb7185', '#e11d48', '#fda4af', '#be123c',
                    '#fecdd3', '#9f1239', '#fff1f2', '#4c0519', '#ff6b81',
                ];

                const pendapatanLabels = @json($pendapatan_rows->pluck('butiran')->values());
                const pendapatanValues = @json($pendapatan_rows->pluck('jumlah')->values());
                const belanjaLabels = @json($perbelanjaan_rows->pluck('butiran')->values());
                const belanjaValues = @json($perbelanjaan_rows->pluck('jumlah')->values());
                const jumlahPendapatan = {{ $jumlah_pendapatan }};
                const jumlahPerbelanjaan = {{ $jumlah_perbelanjaan }};
                const prevJumlahPendapatan = {{ $prev_jumlah_pendapatan }};
                const prevJumlahPerbelanjaan = {{ $prev_jumlah_perbelanjaan }};
                const prevLabel = @json($prev_tempoh_label);
                const thisLabel = @json($tempoh_label);
                const comparisonToggle = document.getElementById('comparisonToggle');
                const comparisonElements = document.querySelectorAll('.comparison-only');
                let isComparisonHidden = false;
                let barChart = null;

                const baseCurrentDataset = {
                    label: thisLabel.substring(0, 22),
                    data: [jumlahPendapatan, jumlahPerbelanjaan],
                    backgroundColor: ['rgba(16,185,129,0.8)', 'rgba(244,63,94,0.8)'],
                    borderColor: ['#059669', '#e11d48'],
                    borderWidth: 1.5,
                    borderRadius: 6,
                };

                const basePrevDataset = {
                    label: prevLabel.substring(0, 22) + ' (lepas)',
                    data: [prevJumlahPendapatan, prevJumlahPerbelanjaan],
                    backgroundColor: ['rgba(16,185,129,0.25)', 'rgba(244,63,94,0.25)'],
                    borderColor: ['#059669', '#e11d48'],
                    borderWidth: 1.5,
                    borderRadius: 6,
                };

                function setComparisonMode(hidden) {
                    isComparisonHidden = hidden;

                    comparisonElements.forEach((element) => {
                        element.classList.toggle('hidden', hidden);
                    });

                    if (comparisonToggle) {
                        comparisonToggle.setAttribute('aria-pressed', hidden ? 'true' : 'false');
                        comparisonToggle.textContent = hidden ? 'Tunjuk Perbandingan' : 'Sembunyi Perbandingan';
                        comparisonToggle.title = hidden ?
                            'Tunjuk semula perbandingan tempoh lepas' :
                            'Sembunyi perbandingan tempoh lepas';
                    }

                    if (barChart) {
                        barChart.data.datasets = hidden ? [baseCurrentDataset] : [baseCurrentDataset, basePrevDataset];
                        barChart.options.plugins.legend.display = !hidden;
                        barChart.update();
                    }

                    try {
                        localStorage.setItem('penyata.hideComparison', hidden ? '1' : '0');
                    } catch (error) {
                        // Ignore localStorage access issues (private mode/restricted browser)
                    }

                    const printView = document.getElementById('penyata-print-view');
                    if (printView) {
                        printView.classList.toggle('hide-comparison', hidden);
                    }
                }

                // 1. Grouped Bar: this period vs prev period
                const ctxBar = document.getElementById('chartBar');
                if (ctxBar) {
                    barChart = new Chart(ctxBar, {
                        type: 'bar',
                        data: {
                            labels: ['Pendapatan', 'Perbelanjaan'],
                            datasets: [baseCurrentDataset, basePrevDataset],
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: {
                                            size: 10
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => ' RM ' + ctx.parsed.y.toLocaleString('ms-MY', {
                                            minimumFractionDigits: 2
                                        })
                                    }
                                },
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: (v) => 'RM ' + v.toLocaleString(),
                                        font: {
                                            size: 10
                                        }
                                    }
                                },
                                x: {
                                    ticks: {
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                            },
                        },
                    });
                }

                const pdfLink = document.getElementById('pdfLink');

                function updatePdfLink(hidden) {
                    if (!pdfLink) return;
                    const base = pdfLink.dataset.baseUrl;
                    if (!base) return;
                    const separator = base.includes('?') ? '&' : '?';
                    pdfLink.href = hidden ? base + separator + 'hide_comparison=1' : base;
                }

                const originalSetComparisonMode = setComparisonMode;
                setComparisonMode = function(hidden) {
                    originalSetComparisonMode(hidden);
                    updatePdfLink(hidden);
                };

                if (comparisonToggle) {
                    comparisonToggle.addEventListener('click', function() {
                        setComparisonMode(!isComparisonHidden);
                    });
                }

                let savedPreference = false;
                try {
                    savedPreference = localStorage.getItem('penyata.hideComparison') === '1';
                } catch (error) {
                    savedPreference = false;
                }
                setComparisonMode(savedPreference);

                // 2. Doughnut: income breakdown
                const ctxHasil = document.getElementById('chartHasil');
                if (ctxHasil && pendapatanValues.length > 0) {
                    new Chart(ctxHasil, {
                        type: 'doughnut',
                        data: {
                            labels: pendapatanLabels,
                            datasets: [{
                                data: pendapatanValues,
                                backgroundColor: emeraldPalette.slice(0, pendapatanValues.length),
                                borderWidth: 1.5
                            }],
                        },
                        options: {
                            responsive: true,
                            cutout: '62%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: {
                                            size: 10
                                        },
                                        boxWidth: 12
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => {
                                            const t = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                            const p = t > 0 ? ((ctx.parsed / t) * 100).toFixed(1) : 0;
                                            return ' RM ' + ctx.parsed.toLocaleString('ms-MY', {
                                                minimumFractionDigits: 2
                                            }) + ' (' + p + '%)';
                                        }
                                    }
                                },
                            },
                        },
                    });
                } else if (ctxHasil) {
                    ctxHasil.parentElement.innerHTML =
                        '<p class="py-8 text-center text-sm text-gray-400">Tiada data pendapatan.</p>';
                }

                // 3. Doughnut: expense breakdown
                const ctxBelanja = document.getElementById('chartBelanja');
                if (ctxBelanja && belanjaValues.length > 0) {
                    new Chart(ctxBelanja, {
                        type: 'doughnut',
                        data: {
                            labels: belanjaLabels,
                            datasets: [{
                                data: belanjaValues,
                                backgroundColor: rosePalette.slice(0, belanjaValues.length),
                                borderWidth: 1.5
                            }],
                        },
                        options: {
                            responsive: true,
                            cutout: '62%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: {
                                            size: 10
                                        },
                                        boxWidth: 12
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => {
                                            const t = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                            const p = t > 0 ? ((ctx.parsed / t) * 100).toFixed(1) : 0;
                                            return ' RM ' + ctx.parsed.toLocaleString('ms-MY', {
                                                minimumFractionDigits: 2
                                            }) + ' (' + p + '%)';
                                        }
                                    }
                                },
                            },
                        },
                    });
                } else if (ctxBelanja) {
                    ctxBelanja.parentElement.innerHTML =
                        '<p class="py-8 text-center text-sm text-gray-400">Tiada data perbelanjaan.</p>';
                }
            })();
        </script>
    @endpush

    {{-- ===================== PRINT VIEW (mirrors PDF layout) ===================== --}}
    <div id="penyata-print-view" style="display:none">

        <div id="pv-header">
            <div class="pv-masjid">{{ $masjid_nama }}</div>
            <div class="pv-alamat">{{ $masjid_alamat }}</div>
            <div class="pv-tajuk">Penyata Pendapatan dan Perbelanjaan</div>
            <div class="pv-tempoh">Bagi Tempoh: <strong>{{ $tempoh_label }}</strong></div>
        </div>

        <div id="pv-meta">
            <span class="pv-cmp">Perbandingan dengan: {{ $prev_tempoh_label }}</span>
            <span>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</span>
        </div>

        <div class="pv-dual">
            {{-- Pendapatan --}}
            <div class="pv-col">
                <div class="pv-section-title">Pendapatan (Hasil)</div>
                <table class="pv-dt">
                    <thead>
                        <tr>
                            <th>Butiran</th>
                            <th class="r">Jumlah (RM)</th>
                            <th class="pv-cmp r">%</th>
                            <th class="pv-cmp r">Tpoh Lepas</th>
                            <th class="pv-cmp r">+/-</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendapatan_rows as $row)
                            <tr>
                                <td class="b">{{ $row['butiran'] }}</td>
                                <td class="r">{{ number_format($row['jumlah'], 2) }}</td>
                                <td class="pv-cmp c">{{ $row['peratus'] }}%</td>
                                <td class="pv-cmp r" style="color:#888">
                                    {{ $row['prev_jumlah'] > 0 ? number_format($row['prev_jumlah'], 2) : '-' }}</td>
                                <td class="pv-cmp r">
                                    @if ($row['peratus_perubahan'] !== null)
                                        <span
                                            class="{{ $row['perubahan'] >= 0 ? 'pv-up' : 'pv-down' }}">{{ $row['perubahan'] >= 0 ? '+' : '' }}{{ number_format($row['perubahan'], 2) }}
                                            ({{ $row['peratus_perubahan'] >= 0 ? '+' : '' }}{{ $row['peratus_perubahan'] }}%)</span>
                                    @elseif ($row['jumlah'] > 0 && $row['prev_jumlah'] == 0)
                                        <span class="pv-up">Baharu</span>
                                    @else
                                        <span class="pv-flat">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr class="pv-empty-row">
                                <td colspan="5">Tiada rekod pendapatan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Jumlah Pendapatan</td>
                            <td class="r">{{ number_format($jumlah_pendapatan, 2) }}</td>
                            <td class="pv-cmp r">100%</td>
                            <td class="pv-cmp r" style="color:#888;font-size:8pt">
                                {{ number_format($prev_jumlah_pendapatan, 2) }}</td>
                            <td class="pv-cmp r">
                                @php $chg = $jumlah_pendapatan - $prev_jumlah_pendapatan; @endphp
                                <span
                                    class="{{ $chg >= 0 ? 'pv-up' : 'pv-down' }}">{{ $chg >= 0 ? '+' : '' }}{{ number_format($chg, 2) }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Perbelanjaan --}}
            <div class="pv-col">
                <div class="pv-section-title">Perbelanjaan</div>
                <table class="pv-dt">
                    <thead>
                        <tr>
                            <th>Butiran</th>
                            <th class="r">Jumlah (RM)</th>
                            <th class="pv-cmp r">%</th>
                            <th class="pv-cmp r">Tpoh Lepas</th>
                            <th class="pv-cmp r">+/-</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($perbelanjaan_rows as $row)
                            <tr>
                                <td class="b">{{ $row['butiran'] }}</td>
                                <td class="r">{{ number_format($row['jumlah'], 2) }}</td>
                                <td class="pv-cmp c">{{ $row['peratus'] }}%</td>
                                <td class="pv-cmp r" style="color:#888">
                                    {{ $row['prev_jumlah'] > 0 ? number_format($row['prev_jumlah'], 2) : '-' }}</td>
                                <td class="pv-cmp r">
                                    @if ($row['peratus_perubahan'] !== null)
                                        <span
                                            class="{{ $row['perubahan'] >= 0 ? 'pv-down' : 'pv-up' }}">{{ $row['perubahan'] >= 0 ? '+' : '' }}{{ number_format($row['perubahan'], 2) }}
                                            ({{ $row['peratus_perubahan'] >= 0 ? '+' : '' }}{{ $row['peratus_perubahan'] }}%)</span>
                                    @elseif ($row['jumlah'] > 0 && $row['prev_jumlah'] == 0)
                                        <span class="pv-down">Baharu</span>
                                    @else
                                        <span class="pv-flat">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr class="pv-empty-row">
                                <td colspan="5">Tiada rekod perbelanjaan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Jumlah Perbelanjaan</td>
                            <td class="r">{{ number_format($jumlah_perbelanjaan, 2) }}</td>
                            <td class="pv-cmp r">100%</td>
                            <td class="pv-cmp r" style="color:#888;font-size:8pt">
                                {{ number_format($prev_jumlah_perbelanjaan, 2) }}</td>
                            <td class="pv-cmp r">
                                @php $chgB = $jumlah_perbelanjaan - $prev_jumlah_perbelanjaan; @endphp
                                <span
                                    class="{{ $chgB >= 0 ? 'pv-down' : 'pv-up' }}">{{ $chgB >= 0 ? '+' : '' }}{{ number_format($chgB, 2) }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="pv-summary">
            <table class="pv-sgrid">
                <tbody>
                    <tr>
                        <td class="lbl">Jumlah Pendapatan</td>
                        <td class="val">RM {{ number_format($jumlah_pendapatan, 2) }}</td>
                        <td class="pv-cmp prev">RM {{ number_format($prev_jumlah_pendapatan, 2) }}</td>
                        <td class="pv-cmp chg">
                            @php $d = $jumlah_pendapatan - $prev_jumlah_pendapatan; @endphp
                            <span class="{{ $d >= 0 ? 'pv-up' : 'pv-down' }}">{{ $d >= 0 ? '+' : '' }}RM
                                {{ number_format($d, 2) }}</span>
                        </td>
                    </tr>
                    <tr class="sep">
                        <td class="lbl">Jumlah Perbelanjaan</td>
                        <td class="val">RM {{ number_format($jumlah_perbelanjaan, 2) }}</td>
                        <td class="pv-cmp prev">RM {{ number_format($prev_jumlah_perbelanjaan, 2) }}</td>
                        <td class="pv-cmp chg">
                            @php $dB = $jumlah_perbelanjaan - $prev_jumlah_perbelanjaan; @endphp
                            <span class="{{ $dB >= 0 ? 'pv-down' : 'pv-up' }}">{{ $dB >= 0 ? '+' : '' }}RM
                                {{ number_format($dB, 2) }}</span>
                        </td>
                    </tr>
                    <tr class="sep {{ $lebihan_kurangan >= 0 ? 'pv-lebihan' : 'pv-kekurangan' }}">
                        <td class="lbl" style="font-size:10pt">
                            {{ $lebihan_kurangan >= 0 ? 'Lebihan' : 'Kekurangan' }}</td>
                        <td class="val" style="font-size:10pt">RM {{ number_format(abs($lebihan_kurangan), 2) }}
                        </td>
                        <td class="pv-cmp prev">RM {{ number_format(abs($prev_lebihan_kurangan), 2) }}</td>
                        <td class="pv-cmp chg">
                            @php $dL = $lebihan_kurangan - $prev_lebihan_kurangan; @endphp
                            <span class="{{ $dL >= 0 ? 'pv-up' : 'pv-down' }}">{{ $dL >= 0 ? '+' : '' }}RM
                                {{ number_format($dL, 2) }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="pv-footer">
            Penyata ini dijana secara automatik oleh sistem MyImam &mdash; {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

</x-app-layout>
