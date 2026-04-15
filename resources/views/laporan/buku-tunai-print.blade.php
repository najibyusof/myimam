<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Buku Tunai</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 24px;
            color: #1f2937;
        }

        .meta {
            margin-bottom: 16px;
        }

        .meta h1 {
            margin: 0 0 8px;
            font-size: 20px;
        }

        .meta p {
            margin: 2px 0;
            font-size: 12px;
            color: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 8px;
        }

        th {
            background: #f3f4f6;
            text-align: left;
        }

        td.right,
        th.right {
            text-align: right;
        }

        .row-highlight {
            background: #eef2ff;
            font-weight: 600;
        }

        .actions {
            margin-bottom: 12px;
        }

        @media print {
            .actions {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="actions">
        <button type="button" onclick="window.print()">Cetak</button>
    </div>

    <div class="meta">
        <h1>Laporan Buku Tunai</h1>
        <p>Akaun: {{ $laporan['akaun']->nama_akaun }}</p>
        <p>Tempoh: {{ \Illuminate\Support\Carbon::parse($laporan['tempoh']['tarikh_mula'])->format('d/m/Y') }} -
            {{ \Illuminate\Support\Carbon::parse($laporan['tempoh']['tarikh_akhir'])->format('d/m/Y') }}</p>
        <p>Tarikh Cetakan: {{ now()->format('d/m/Y H:i') }}</p>
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

            <tr class="row-highlight">
                <td>-</td>
                <td>Ringkasan Tempoh</td>
                <td class="right">{{ number_format($laporan['ringkasan']['jumlah_masuk'], 2, '.', ',') }}</td>
                <td class="right">{{ number_format($laporan['ringkasan']['jumlah_keluar'], 2, '.', ',') }}</td>
                <td class="right">{{ number_format($laporan['ringkasan']['baki_akhir'], 2, '.', ',') }}</td>
            </tr>
        </tbody>
    </table>

    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>

</html>
