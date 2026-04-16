<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Detail Tabung Khas</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.45;
            color: #333;
        }

        .container {
            margin: 16px;
        }

        .header {
            text-align: center;
            margin-bottom: 16px;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .meta {
            font-size: 9px;
            color: #666;
            margin-top: 4px;
        }

        .section-title {
            background-color: #f3f4f6;
            padding: 6px 8px;
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 8px;
            border-left: 3px solid #3b82f6;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .summary-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
        }

        .summary-table td:first-child {
            width: 35%;
            background-color: #f9fafb;
            font-weight: bold;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            table-layout: fixed;
        }

        table.data-table th {
            background-color: #e5e7eb;
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
        }

        table.data-table td {
            border: 1px solid #d1d5db;
            padding: 6px;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .total-row {
            background-color: #e0e7ff;
            font-weight: bold;
        }

        .opening-row {
            background-color: #f8fafc;
            font-weight: bold;
        }

        .footer {
            margin-top: 16px;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #d1d5db;
            padding-top: 8px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Detail Tabung Khas</h1>
            <div class="meta">
                Nama Tabung: {{ $tabung->nama_tabung }} |
                Tempoh: {{ $tempoh_label }} |
                Tarikh Jana: {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>

        <div class="section-title">Ringkasan</div>
        <table class="summary-table">
            <tr>
                <td>Baki Awal</td>
                <td>RM {{ number_format($baki_awal, 2, '.', ',') }}</td>
            </tr>
            <tr>
                <td>Jumlah Masuk</td>
                <td>RM {{ number_format($jumlah_masuk, 2, '.', ',') }}</td>
            </tr>
            <tr>
                <td>Jumlah Keluar</td>
                <td>RM {{ number_format($jumlah_keluar, 2, '.', ',') }}</td>
            </tr>
            <tr>
                <td>Baki Akhir</td>
                <td>RM {{ number_format($baki_akhir, 2, '.', ',') }}</td>
            </tr>
        </table>

        <div class="section-title">Timeline Baki Berjalan</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Tarikh</th>
                    <th style="width: 10%;">Jenis</th>
                    <th style="width: 16%;">Rujukan</th>
                    <th style="width: 28%;">Butiran</th>
                    <th style="width: 11%;" class="text-right">Masuk</th>
                    <th style="width: 11%;" class="text-right">Keluar</th>
                    <th style="width: 12%;" class="text-right">Baki</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($timeline_rows as $row)
                    <tr class="{{ !empty($row['is_opening']) ? 'opening-row' : '' }}">
                        <td>{{ \Carbon\Carbon::parse($row['tarikh'])->format('d/m/Y') }}</td>
                        <td>{{ $row['jenis'] }}</td>
                        <td>{{ $row['rujukan'] }}</td>
                        <td>{{ $row['butiran'] }}</td>
                        <td class="text-right">
                            @if (($row['masuk'] ?? 0) > 0)
                                {{ number_format($row['masuk'], 2, '.', ',') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right">
                            @if (($row['keluar'] ?? 0) > 0)
                                {{ number_format($row['keluar'], 2, '.', ',') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($row['baki_berjalan'], 2, '.', ',') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center;">Tiada data timeline untuk tempoh ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Transaksi Masuk</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Tarikh</th>
                    <th style="width: 18%;">Sumber Hasil</th>
                    <th style="width: 16%;">Akaun</th>
                    <th style="width: 24%;">Catatan</th>
                    <th style="width: 10%;" class="text-right">Tunai</th>
                    <th style="width: 10%;" class="text-right">Online</th>
                    <th style="width: 10%;" class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transaksi_masuk as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row['tarikh'])->format('d/m/Y') }}</td>
                        <td>{{ $row['sumber_hasil'] }}</td>
                        <td>{{ $row['akaun'] }}</td>
                        <td>{{ $row['catatan'] }}</td>
                        <td class="text-right">{{ number_format($row['tunai'], 2, '.', ',') }}</td>
                        <td class="text-right">{{ number_format($row['online'], 2, '.', ',') }}</td>
                        <td class="text-right">{{ number_format($row['jumlah'], 2, '.', ',') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center;">Tiada transaksi masuk untuk tempoh ini.</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="6">Jumlah Masuk</td>
                    <td class="text-right">{{ number_format($jumlah_masuk, 2, '.', ',') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="section-title">Transaksi Keluar</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Tarikh</th>
                    <th style="width: 16%;">Kategori</th>
                    <th style="width: 16%;">Penerima</th>
                    <th style="width: 16%;">Akaun</th>
                    <th style="width: 26%;">Catatan</th>
                    <th style="width: 14%;" class="text-right">Amaun</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transaksi_keluar as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row['tarikh'])->format('d/m/Y') }}</td>
                        <td>{{ $row['kategori'] }}</td>
                        <td>{{ $row['penerima'] }}</td>
                        <td>{{ $row['akaun'] }}</td>
                        <td>{{ $row['catatan'] }}</td>
                        <td class="text-right">{{ number_format($row['amaun'], 2, '.', ',') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">Tiada transaksi keluar untuk tempoh ini.</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="5">Jumlah Keluar</td>
                    <td class="text-right">{{ number_format($jumlah_keluar, 2, '.', ',') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            Laporan ini dijana secara automatik dari sistem myImam pada {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>
</body>

</html>
