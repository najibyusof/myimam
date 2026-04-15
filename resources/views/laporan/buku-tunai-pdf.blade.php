<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="utf-8">
    <title>Laporan Buku Tunai</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 8px;
        }

        .meta {
            margin-bottom: 12px;
        }

        .meta p {
            margin: 2px 0;
            font-size: 11px;
            color: #374151;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 6px;
        }

        th {
            background: #f3f4f6;
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .summary {
            background: #eef2ff;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h1>Laporan Buku Tunai</h1>
    <div class="meta">
        <p>Akaun: {{ $laporan['akaun']->nama_akaun }}</p>
        <p>Tempoh: {{ \Illuminate\Support\Carbon::parse($laporan['tempoh']['tarikh_mula'])->format('d/m/Y') }} -
            {{ \Illuminate\Support\Carbon::parse($laporan['tempoh']['tarikh_akhir'])->format('d/m/Y') }}</p>
        <p>Dijana pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tarikh</th>
                <th>Butiran</th>
                <th class="right">Masuk (RM)</th>
                <th class="right">Keluar (RM)</th>
                <th class="right">Baki (RM)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>-</td>
                <td>Baki Awal</td>
                <td class="right">0.00</td>
                <td class="right">0.00</td>
                <td class="right">{{ number_format($laporan['ringkasan']['baki_awal'], 2, '.', ',') }}</td>
            </tr>

            @foreach ($laporan['rows'] as $row)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($row['tarikh'])->format('d/m/Y') }}</td>
                    <td>{{ $row['butiran'] }}</td>
                    <td class="right">{{ number_format($row['masuk'], 2, '.', ',') }}</td>
                    <td class="right">{{ number_format($row['keluar'], 2, '.', ',') }}</td>
                    <td class="right">{{ number_format($row['baki'], 2, '.', ',') }}</td>
                </tr>
            @endforeach

            <tr class="summary">
                <td>-</td>
                <td>Ringkasan Tempoh</td>
                <td class="right">{{ number_format($laporan['ringkasan']['jumlah_masuk'], 2, '.', ',') }}</td>
                <td class="right">{{ number_format($laporan['ringkasan']['jumlah_keluar'], 2, '.', ',') }}</td>
                <td class="right">{{ number_format($laporan['ringkasan']['baki_akhir'], 2, '.', ',') }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
