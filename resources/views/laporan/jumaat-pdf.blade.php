<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="utf-8">
    <title>Laporan Kutipan Jumaat</title>
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
            margin-bottom: 12px;
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
    <h1>Laporan Kutipan Jumaat</h1>
    <div class="meta">
        <p>Tahun: {{ $filters['tahun'] }}</p>
        <p>Jenis Paparan: {{ $filters['jenis_paparan'] === 'detail' ? 'Detail' : 'Ringkasan Bulanan' }}</p>
        <p>Dijana pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th class="right">Jumlah Kutipan (RM)</th>
                <th class="right">Bil. Rekod</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['bulan'] }}</td>
                    <td class="right">{{ number_format($row['jumlah'], 2, '.', ',') }}</td>
                    <td class="right">{{ $row['bil_rekod'] }}</td>
                </tr>
            @endforeach
            <tr class="summary">
                <td>Jumlah Setahun</td>
                <td class="right">{{ number_format($jumlah_setahun, 2, '.', ',') }}</td>
                <td class="right">{{ $rows->sum('bil_rekod') }}</td>
            </tr>
        </tbody>
    </table>

    @if (($filters['jenis_paparan'] ?? 'ringkasan_bulanan') === 'detail' && ($detail_rows ?? collect())->isNotEmpty())
        <h1 style="font-size:16px; margin-top: 16px;">Detail Bulan {{ $detail_bulan_nama }} {{ $filters['tahun'] }}</h1>
        <table>
            <thead>
                <tr>
                    <th>Tarikh</th>
                    <th>No Resit</th>
                    <th>Akaun</th>
                    <th class="right">Jumlah (RM)</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detail_rows as $row)
                    <tr>
                        <td>{{ optional($row->tarikh)->format('d/m/Y') }}</td>
                        <td>{{ $row->no_resit ?: '-' }}</td>
                        <td>{{ optional($row->akaun)->nama_akaun ?: '-' }}</td>
                        <td class="right">{{ number_format((float) $row->jumlah, 2, '.', ',') }}</td>
                        <td>{{ $row->catatan ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>

</html>
