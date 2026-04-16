<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Ringkasan Tabung Khas</title>
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
            <h1>Ringkasan Tabung Khas</h1>
            <div class="meta">
                Tarikh Jana: {{ now()->format('d/m/Y H:i') }} |
                Tempoh: {{ \Carbon\Carbon::parse($filters['tarikh_dari'])->format('d/m/Y') }} -
                {{ \Carbon\Carbon::parse($filters['tarikh_hingga'])->format('d/m/Y') }}
            </div>
        </div>

        <div class="section-title">Ringkasan Masuk dan Keluar Mengikut Tabung</div>
        <table>
            <thead>
                <tr>
                    <th>Nama Tabung</th>
                    <th class="amount">Masuk Tempoh (RM)</th>
                    <th class="amount">Keluar Tempoh (RM)</th>
                    <th class="amount">Baki Terkumpul (RM)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row['nama_tabung'] }}</td>
                        <td class="amount">{{ number_format($row['masuk_tempoh'], 2, '.', ',') }}</td>
                        <td class="amount">{{ number_format($row['keluar_tempoh'], 2, '.', ',') }}</td>
                        <td class="amount">{{ number_format($row['baki_terkumpul'], 2, '.', ',') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center;">Tiada rekod tabung khas untuk tempoh ini.</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td>JUMLAH KESELURUHAN</td>
                    <td class="amount">{{ number_format($total_masuk, 2, '.', ',') }}</td>
                    <td class="amount">{{ number_format($total_keluar, 2, '.', ',') }}</td>
                    <td class="amount">{{ number_format($total_baki, 2, '.', ',') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            Laporan ini dijana secara automatik dari sistem myImam pada {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>
</body>

</html>
