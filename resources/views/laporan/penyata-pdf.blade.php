<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penyata Kewangan - {{ $tempoh_label }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9pt;
            color: #1a1a1a;
            background: #fff;
        }

        .page-shell {
            width: auto;
            margin-left: 10mm;
            margin-right: 10mm;
            padding: 0;
            overflow: hidden;
        }

        /* A4 page setup */
        @page {
            size: A4 landscape;
            margin: 18mm 22mm 18mm 22mm;
        }

        .page-header {
            text-align: center;
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .page-header .masjid-nama {
            font-size: 13pt;
            font-weight: bold;
            color: #1e3a5f;
        }

        .page-header .masjid-alamat {
            font-size: 8.5pt;
            color: #555;
            margin-top: 2px;
        }

        .page-header .tajuk-penyata {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 8px;
            color: #1a1a1a;
        }

        .page-header .tempoh {
            font-size: 9pt;
            color: #555;
            margin-top: 3px;
        }

        .meta-bar {
            display: flex;
            justify-content: space-between;
            font-size: 7.5pt;
            color: #777;
            margin-bottom: 12px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 5px;
        }

        /* Two-column layout */
        .dual-col {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .dual-col>tbody>tr>td {
            vertical-align: top;
            width: 50%;
            padding: 0 5px;
        }

        .dual-col>tbody>tr>td:first-child {
            padding-left: 0;
            border-right: 1px solid #ddd;
            padding-right: 10px;
        }

        .dual-col>tbody>tr>td:last-child {
            padding-left: 10px;
            padding-right: 0;
        }

        .section-title {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #1e3a5f;
            border-bottom: 1px solid #1e3a5f;
            padding-bottom: 3px;
            margin-bottom: 6px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            table-layout: fixed;
        }

        table.data-table th,
        table.data-table td,
        .summary-grid td {
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        table.data-table th:nth-child(1),
        table.data-table td:nth-child(1) {
            width: 31%;
        }

        table.data-table th:nth-child(2),
        table.data-table td:nth-child(2) {
            width: 17%;
        }

        table.data-table th:nth-child(3),
        table.data-table td:nth-child(3) {
            width: 10%;
        }

        table.data-table th:nth-child(4),
        table.data-table td:nth-child(4) {
            width: 19%;
        }

        table.data-table th:nth-child(5),
        table.data-table td:nth-child(5) {
            width: 23%;
        }

        table.data-table thead th {
            background: #f0f4f8;
            color: #444;
            font-weight: bold;
            padding: 5px 6px;
            text-align: left;
            font-size: 7.5pt;
            text-transform: uppercase;
            border-bottom: 1px solid #ccc;
        }

        table.data-table thead th.right {
            text-align: right;
        }

        table.data-table thead th.center {
            text-align: center;
        }

        table.data-table tbody td {
            padding: 4px 6px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        table.data-table tbody td.right {
            text-align: right;
        }

        table.data-table tbody td.center {
            text-align: center;
        }

        table.data-table tbody td.butiran {
            font-weight: 500;
        }

        table.data-table tbody tr:last-child td {
            border-bottom: none;
        }

        table.data-table tfoot td {
            padding: 5px 6px;
            font-weight: bold;
            border-top: 2px solid #1e3a5f;
            background: #eef3fb;
            color: #1e3a5f;
        }

        table.data-table tfoot td.right {
            text-align: right;
        }

        .empty-row td {
            text-align: center;
            color: #aaa;
            font-style: italic;
            padding: 10px 6px;
        }

        /* Mini progress bar */
        .bar-wrap {
            background: #e8edf4;
            border-radius: 2px;
            height: 5px;
            width: 60px;
            display: inline-block;
            vertical-align: middle;
            margin-left: 4px;
        }

        .bar-fill {
            background: #3b82f6;
            border-radius: 2px;
            height: 5px;
        }

        /* Change badge */
        .up {
            color: #16a34a;
            font-weight: bold;
        }

        .down {
            color: #dc2626;
            font-weight: bold;
        }

        .flat {
            color: #888;
        }

        /* Summary section */
        .summary-section {
            margin-top: 14px;
            border: 2px solid #1e3a5f;
            border-radius: 4px;
            padding: 10px 14px;
        }

        .summary-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-grid td {
            padding: 4px 10px;
            font-size: 9pt;
            vertical-align: middle;
        }

        .summary-grid td.label {
            font-weight: bold;
            color: #333;
            width: 35%;
        }

        .summary-grid td.value {
            text-align: right;
            font-weight: bold;
            width: 20%;
        }

        .summary-grid td.prev {
            text-align: right;
            color: #888;
            font-size: 8pt;
            width: 20%;
        }

        .summary-grid td.change {
            text-align: right;
            font-weight: bold;
            width: 25%;
        }

        .lebihan-row td {
            color: #16a34a;
            font-size: 11pt;
        }

        .kekurangan-row td {
            color: #dc2626;
            font-size: 11pt;
        }

        .separator {
            border-top: 1px solid #ddd;
        }

        .footer {
            margin-top: 18px;
            font-size: 7.5pt;
            color: #aaa;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 6px;
        }

        .comparison-note {
            font-size: 7.5pt;
            color: #888;
            font-style: italic;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="page-shell">

        {{-- Header --}}
        <div class="page-header">
            <div class="masjid-nama">{{ $masjid_nama }}</div>
            <div class="masjid-alamat">{{ $masjid_alamat }}</div>
            <div class="tajuk-penyata">Penyata Pendapatan dan Perbelanjaan</div>
            <div class="tempoh">Bagi Tempoh: <strong>{{ $tempoh_label }}</strong></div>
        </div>

        <div class="meta-bar">
            <span>Perbandingan dengan: {{ $prev_tempoh_label }}</span>
            <span>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</span>
        </div>

        {{-- Two-column: Pendapatan | Perbelanjaan --}}
        <table class="dual-col">
            <tbody>
                <tr>
                    {{-- Left: Pendapatan --}}
                    <td>
                        <div class="section-title">Pendapatan (Hasil)</div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Butiran</th>
                                    <th class="right">Jumlah (RM)</th>
                                    <th class="right">%</th>
                                    <th class="right">Tpoh Lepas</th>
                                    <th class="right">+/-</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pendapatan_rows as $row)
                                    <tr>
                                        <td class="butiran">{{ $row['butiran'] }}</td>
                                        <td class="right">{{ number_format($row['jumlah'], 2) }}</td>
                                        <td class="center">{{ $row['peratus'] }}%</td>
                                        <td class="right" style="color:#888">
                                            {{ $row['prev_jumlah'] > 0 ? number_format($row['prev_jumlah'], 2) : '-' }}
                                        </td>
                                        <td class="right">
                                            @if ($row['peratus_perubahan'] !== null)
                                                <span class="{{ $row['perubahan'] >= 0 ? 'up' : 'down' }}">
                                                    {{ $row['perubahan'] >= 0 ? '+' : '' }}{{ number_format($row['perubahan'], 2) }}
                                                    ({{ $row['peratus_perubahan'] >= 0 ? '+' : '' }}{{ $row['peratus_perubahan'] }}%)
                                                </span>
                                            @elseif ($row['jumlah'] > 0 && $row['prev_jumlah'] == 0)
                                                <span class="up">Baharu</span>
                                            @else
                                                <span class="flat">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="empty-row">
                                        <td colspan="5">Tiada rekod pendapatan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>Jumlah Pendapatan</td>
                                    <td class="right">{{ number_format($jumlah_pendapatan, 2) }}</td>
                                    <td class="right">100%</td>
                                    <td class="right" style="color:#888; font-size:8pt">
                                        {{ number_format($prev_jumlah_pendapatan, 2) }}</td>
                                    <td class="right">
                                        @php $chg = $jumlah_pendapatan - $prev_jumlah_pendapatan; @endphp
                                        <span class="{{ $chg >= 0 ? 'up' : 'down' }}">
                                            {{ $chg >= 0 ? '+' : '' }}{{ number_format($chg, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </td>

                    {{-- Right: Perbelanjaan --}}
                    <td>
                        <div class="section-title">Perbelanjaan</div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Butiran</th>
                                    <th class="right">Jumlah (RM)</th>
                                    <th class="right">%</th>
                                    <th class="right">Tpoh Lepas</th>
                                    <th class="right">+/-</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($perbelanjaan_rows as $row)
                                    <tr>
                                        <td class="butiran">{{ $row['butiran'] }}</td>
                                        <td class="right">{{ number_format($row['jumlah'], 2) }}</td>
                                        <td class="center">{{ $row['peratus'] }}%</td>
                                        <td class="right" style="color:#888">
                                            {{ $row['prev_jumlah'] > 0 ? number_format($row['prev_jumlah'], 2) : '-' }}
                                        </td>
                                        <td class="right">
                                            @if ($row['peratus_perubahan'] !== null)
                                                <span class="{{ $row['perubahan'] >= 0 ? 'down' : 'up' }}">
                                                    {{ $row['perubahan'] >= 0 ? '+' : '' }}{{ number_format($row['perubahan'], 2) }}
                                                    ({{ $row['peratus_perubahan'] >= 0 ? '+' : '' }}{{ $row['peratus_perubahan'] }}%)
                                                </span>
                                            @elseif ($row['jumlah'] > 0 && $row['prev_jumlah'] == 0)
                                                <span class="down">Baharu</span>
                                            @else
                                                <span class="flat">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="empty-row">
                                        <td colspan="5">Tiada rekod perbelanjaan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>Jumlah Perbelanjaan</td>
                                    <td class="right">{{ number_format($jumlah_perbelanjaan, 2) }}</td>
                                    <td class="right">100%</td>
                                    <td class="right" style="color:#888; font-size:8pt">
                                        {{ number_format($prev_jumlah_perbelanjaan, 2) }}</td>
                                    <td class="right">
                                        @php $chgB = $jumlah_perbelanjaan - $prev_jumlah_perbelanjaan; @endphp
                                        <span class="{{ $chgB >= 0 ? 'down' : 'up' }}">
                                            {{ $chgB >= 0 ? '+' : '' }}{{ number_format($chgB, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Summary --}}
        <div class="summary-section">
            <table class="summary-grid">
                <tbody>
                    <tr>
                        <td class="label">Jumlah Pendapatan</td>
                        <td class="value">RM {{ number_format($jumlah_pendapatan, 2) }}</td>
                        <td class="prev">RM {{ number_format($prev_jumlah_pendapatan, 2) }}</td>
                        <td class="change">
                            @php $d = $jumlah_pendapatan - $prev_jumlah_pendapatan; @endphp
                            <span class="{{ $d >= 0 ? 'up' : 'down' }}">{{ $d >= 0 ? '+' : '' }}RM
                                {{ number_format($d, 2) }}</span>
                        </td>
                    </tr>
                    <tr class="separator">
                        <td class="label">Jumlah Perbelanjaan</td>
                        <td class="value">RM {{ number_format($jumlah_perbelanjaan, 2) }}</td>
                        <td class="prev">RM {{ number_format($prev_jumlah_perbelanjaan, 2) }}</td>
                        <td class="change">
                            @php $dB = $jumlah_perbelanjaan - $prev_jumlah_perbelanjaan; @endphp
                            <span class="{{ $dB >= 0 ? 'down' : 'up' }}">{{ $dB >= 0 ? '+' : '' }}RM
                                {{ number_format($dB, 2) }}</span>
                        </td>
                    </tr>
                    <tr class="{{ $lebihan_kurangan >= 0 ? 'lebihan-row' : 'kekurangan-row' }}">
                        <td class="label" style="font-size:10pt">
                            {{ $lebihan_kurangan >= 0 ? 'Lebihan' : 'Kekurangan' }}
                        </td>
                        <td class="value" style="font-size:10pt">RM {{ number_format(abs($lebihan_kurangan), 2) }}
                        </td>
                        <td class="prev">RM {{ number_format(abs($prev_lebihan_kurangan), 2) }}</td>
                        <td class="change">
                            @php $dL = $lebihan_kurangan - $prev_lebihan_kurangan; @endphp
                            <span class="{{ $dL >= 0 ? 'up' : 'down' }}">{{ $dL >= 0 ? '+' : '' }}RM
                                {{ number_format($dL, 2) }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="footer">
            Penyata ini dijana secara automatik oleh sistem MyImam &mdash; {{ now()->format('d/m/Y H:i') }}
        </div>

    </div>

</body>

</html>
