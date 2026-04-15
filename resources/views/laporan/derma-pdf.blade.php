<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Derma / Hasil Lain</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
        }

        .container {
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .meta {
            font-size: 9px;
            color: #666;
            margin-top: 5px;
        }

        .section-title {
            background-color: #f3f4f6;
            padding: 8px 10px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 10px;
            border-left: 3px solid #3b82f6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th {
            background-color: #e5e7eb;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #d1d5db;
            font-size: 10px;
        }

        td {
            padding: 8px;
            border: 1px solid #d1d5db;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .total-row {
            background-color: #e0e7ff;
            font-weight: bold;
        }

        .amount {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .footer {
            margin-top: 20px;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Laporan Derma / Hasil Lain</h1>
            <div class="meta">
                Tarikh: {{ \Carbon\Carbon::today()->format('d/m/Y') }} |
                Jenis:
                @if ($filters['jenis_paparan'] === 'ringkasan_bulan')
                    Ringkasan Mengikut Bulan
                @elseif ($filters['jenis_paparan'] === 'senarai_transaksi')
                    Senarai Transaksi
                @else
                    Ringkasan Mengikut Sumber
                @endif
                | Tempoh: {{ \Carbon\Carbon::parse($filters['tarikh_dari'])->format('d/m/Y') }} -
                {{ \Carbon\Carbon::parse($filters['tarikh_hingga'])->format('d/m/Y') }}
            </div>
        </div>

        @if ($filters['jenis_paparan'] === 'ringkasan_bulan')
            <div class="section-title">Ringkasan Mengikut Bulan</div>
            <table>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="amount">Jumlah Derma (RM)</th>
                        <th class="center">Bil. Rekod</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ringkasan_bulan as $row)
                        <tr>
                            <td>{{ $row['bulan'] }}</td>
                            <td class="amount">{{ number_format($row['jumlah'], 2, '.', ',') }}</td>
                            <td class="center">{{ $row['bil_rekod'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="center">Tiada rekod</td>
                        </tr>
                    @endforelse
                    <tr class="total-row">
                        <td>JUMLAH KESELURUHAN</td>
                        <td class="amount">{{ number_format($jumlah_keseluruhan, 2, '.', ',') }}</td>
                        <td class="center">{{ $ringkasan_bulan->sum('bil_rekod') }}</td>
                    </tr>
                </tbody>
            </table>
        @elseif ($filters['jenis_paparan'] === 'senarai_transaksi')
            <div class="section-title">Senarai Transaksi</div>
            <table>
                <thead>
                    <tr>
                        <th>Tarikh</th>
                        <th>Sumber</th>
                        <th>No. Resit</th>
                        <th class="amount">Jumlah (RM)</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($senarai_rows as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row['tarikh'])->format('d/m/Y') }}</td>
                            <td>{{ $row['sumber'] }}</td>
                            <td>{{ $row['no_resit'] }}</td>
                            <td class="amount">{{ number_format($row['jumlah'], 2, '.', ',') }}</td>
                            <td>{{ $row['catatan'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="center">Tiada rekod</td>
                        </tr>
                    @endforelse
                    <tr class="total-row">
                        <td colspan="3">JUMLAH KESELURUHAN</td>
                        <td class="amount">{{ number_format($jumlah_keseluruhan, 2, '.', ',') }}</td>
                        <td>&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        @else
            <div class="section-title">Ringkasan Mengikut Sumber</div>
            <table>
                <thead>
                    <tr>
                        <th>Sumber Hasil</th>
                        <th class="amount">Jumlah Derma (RM)</th>
                        <th class="center">Bil. Rekod</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $row['sumber'] }}</td>
                            <td class="amount">{{ number_format($row['jumlah'], 2, '.', ',') }}</td>
                            <td class="center">{{ $row['bil_rekod'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="center">Tiada rekod</td>
                        </tr>
                    @endforelse
                    <tr class="total-row">
                        <td>JUMLAH KESELURUHAN</td>
                        <td class="amount">{{ number_format($jumlah_keseluruhan, 2, '.', ',') }}</td>
                        <td class="center">{{ $rows->sum('bil_rekod') }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

        <div class="footer">
            Laporan ini dijana secara automatik dari sistem myImam pada {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>
</body>

</html>
