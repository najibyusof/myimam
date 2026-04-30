<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            background: #fff;
        }

        .page {
            padding: 20px 28px;
        }

        /* Header */
        .header {
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .header-top {
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 65%;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 35%;
        }

        .masjid-name {
            font-size: 15px;
            font-weight: bold;
            color: #1e3a5f;
        }

        .masjid-addr {
            font-size: 9px;
            color: #555;
            margin-top: 2px;
        }

        .doc-title {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a5f;
            letter-spacing: 1px;
        }

        .baucar-no {
            font-size: 13px;
            font-weight: bold;
            color: #c0392b;
            margin-top: 3px;
        }

        /* Divider */
        .divider {
            border-top: 1px solid #ddd;
            margin: 10px 0;
        }

        /* Info grid - using table for dompdf compatibility */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .info-table td {
            padding: 5px 8px;
            font-size: 10px;
            vertical-align: top;
        }

        .info-table .lbl {
            font-weight: bold;
            color: #555;
            width: 130px;
        }

        .info-box {
            background: #f5f7fa;
            border: 1px solid #dde2ea;
            border-radius: 4px;
        }

        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .items-table th {
            background: #1e3a5f;
            color: #fff;
            padding: 6px 8px;
            font-size: 10px;
            text-align: left;
        }

        .items-table th.right {
            text-align: right;
        }

        .items-table td {
            padding: 7px 8px;
            font-size: 10px;
            border-bottom: 1px solid #eee;
        }

        .items-table td.right {
            text-align: right;
        }

        .items-table tr.total-row td {
            background: #f5f7fa;
            font-weight: bold;
            border-top: 2px solid #1e3a5f;
        }

        /* Status badge */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-lulus {
            background: #d4edda;
            color: #155724;
        }

        .badge-draf {
            background: #fff3cd;
            color: #856404;
        }

        /* QR + signature row */
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .bottom-table td {
            vertical-align: top;
            padding: 0 6px;
        }

        .sig-box {
            border-top: 1px solid #999;
            padding-top: 4px;
            font-size: 9px;
            color: #555;
            margin-top: 30px;
        }

        .sig-label {
            font-size: 9px;
            font-weight: bold;
            color: #333;
        }

        /* QR area */
        .qr-area {
            text-align: center;
        }

        .qr-area img {
            width: 80px;
            height: 80px;
        }

        .qr-caption {
            font-size: 8px;
            color: #888;
            margin-top: 3px;
        }

        /* Footer */
        .footer {
            border-top: 1px solid #ddd;
            margin-top: 16px;
            padding-top: 8px;
            font-size: 8px;
            color: #aaa;
            text-align: center;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 38%;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 72px;
            font-weight: bold;
            opacity: 0.07;
            transform: rotate(-35deg);
            pointer-events: none;
            z-index: 1000;
        }

        .watermark-draf {
            color: #b8860b;
        }

        .watermark-rasmi {
            color: #155724;
        }

        /* Official stamp */
        .stamp-rasmi {
            display: inline-block;
            border: 3px solid #155724;
            border-radius: 6px;
            padding: 3px 10px;
            color: #155724;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        /* Rejection notice */
        .rejection-notice {
            background: #fff5f5;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 12px;
        }

        .rejection-notice .rn-label {
            font-size: 9px;
            font-weight: bold;
            color: #721c24;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .rejection-notice .rn-text {
            font-size: 10px;
            color: #491217;
            margin-top: 3px;
        }

        .rejection-notice .rn-meta {
            font-size: 9px;
            color: #a00;
            margin-top: 2px;
        }
    </style>
</head>

<body>
    <div class="page">

        @if ($belanja->is_baucar_locked)
            <div class="watermark watermark-rasmi">RASMI</div>
        @else
            <div class="watermark watermark-draf">DRAF</div>
        @endif

        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div class="header-left">
                    <div class="masjid-name">{{ $belanja->masjid->nama ?? 'Masjid' }}</div>
                    <div class="masjid-addr">
                        {{ $belanja->masjid->alamat ?? '' }}
                        @if ($belanja->masjid->daerah ?? false)
                            , {{ $belanja->masjid->daerah }}
                        @endif
                        @if ($belanja->masjid->negeri ?? false)
                            , {{ $belanja->masjid->negeri }}
                        @endif
                    </div>
                </div>
                <div class="header-right">
                    <div class="doc-title">BAUCAR BAYARAN</div>
                    <div class="baucar-no">{{ $baucarNo }}</div>
                    @if ($belanja->is_baucar_locked)
                        <div style="margin-top:4px"><span class="stamp-rasmi">RASMI</span></div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Details Info -->
        <table class="info-table info-box">
            <tr>
                <td class="lbl">Tarikh Baucar</td>
                <td>: {{ optional($belanja->tarikh)->format('d/m/Y') ?? '-' }}</td>
                <td class="lbl">Status</td>
                <td>:
                    <span class="badge {{ $belanja->is_baucar_locked ? 'badge-lulus' : 'badge-draf' }}">
                        {{ $belanja->is_baucar_locked ? 'Diluluskan & Dikunci' : ((int) $belanja->approval_step === 1 ? 'Menunggu Pengerusi' : 'Draf') }}
                    </span>
                </td>
            </tr>
            <tr>
                <td class="lbl">Penerima</td>
                <td>: {{ $belanja->penerima ?? '-' }}</td>
                <td class="lbl">Kategori</td>
                <td>: {{ $belanja->kategoriBelanja->nama_kategori ?? '-' }}</td>
            </tr>
            <tr>
                <td class="lbl">Akaun Dikenakan</td>
                <td colspan="3">: {{ $belanja->akaun->nama_akaun ?? '-' }}</td>
            </tr>
            <tr>
                <td class="lbl">Semak Bendahari</td>
                <td>: {{ $belanja->bendahariLulusOleh->name ?? '-' }}</td>
                <td class="lbl">T.Tangan Digital</td>
                <td>: {{ $belanja->bendahari_signature ?? '-' }}</td>
            </tr>
            <tr>
                <td class="lbl">Lulus Pengerusi</td>
                <td>: {{ $belanja->pengerusiLulusOleh->name ?? '-' }}</td>
                <td class="lbl">T.Tangan Digital</td>
                <td>: {{ $belanja->pengerusi_signature ?? '-' }}</td>
            </tr>
        </table>

        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:50%">Perkara</th>
                    <th style="width:30%">Catatan</th>
                    <th class="right" style="width:20%">Amaun (RM)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Perbelanjaan – {{ $belanja->kategoriBelanja->nama_kategori ?? 'Umum' }}</td>
                    <td>{{ $belanja->catatan ?? '-' }}</td>
                    <td class="right">{{ number_format((float) $belanja->amaun, 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2" style="text-align:right">JUMLAH KESELURUHAN</td>
                    <td class="right">RM {{ number_format((float) $belanja->amaun, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="divider"></div>

        @if ($belanja->catatan_tolak)
            <!-- Rejection Notice -->
            <div class="rejection-notice">
                <div class="rn-label">Nota Penolakan</div>
                <div class="rn-text">{{ $belanja->catatan_tolak }}</div>
                <div class="rn-meta">
                    Ditolak oleh: {{ $belanja->ditolakOleh->name ?? '-' }}
                    &bull; {{ optional($belanja->tarikh_tolak)->format('d/m/Y H:i') ?? '-' }}
                </div>
            </div>
        @endif

        <!-- Signatures + QR -->
        <table class="bottom-table">
            <tr>
                <td style="width:25%">
                    <div class="sig-label">Disediakan Oleh</div>
                    <div class="sig-box">Nama &amp; Tandatangan</div>
                </td>
                <td style="width:25%">
                    <div class="sig-label">Disemak Bendahari</div>
                    @if ($belanja->bendahariLulusOleh)
                        <div style="margin-top:4px; font-size:10px; font-weight:bold; color:#1e3a5f">
                            {{ $belanja->bendahariLulusOleh->name }}</div>
                        <div style="font-size:9px; color:#555">
                            {{ optional($belanja->bendahari_lulus_pada)->format('d/m/Y') }}</div>
                        @if (!empty($signatureImages['bendahari']))
                            <div style="margin-top:3px"><img src="{{ $signatureImages['bendahari'] }}"
                                    alt="Signature Bendahari"
                                    style="height:26px; width:auto; border:1px solid #ddd; padding:2px; background:#fff;">
                            </div>
                        @endif
                        <div class="sig-box" style="border-color:#1e3a5f; color:#1e3a5f; font-weight:bold">
                            {{ $belanja->bendahari_signature }}</div>
                    @else
                        <div class="sig-box">Nama &amp; Tandatangan</div>
                    @endif
                </td>
                <td style="width:25%">
                    <div class="sig-label">Diluluskan Pengerusi</div>
                    @if ($belanja->is_baucar_locked && $belanja->pengerusiLulusOleh)
                        <div style="margin-top:4px; font-size:10px; font-weight:bold; color:#1e3a5f">
                            {{ $belanja->pengerusiLulusOleh->name }}</div>
                        <div style="font-size:9px; color:#555">
                            {{ optional($belanja->pengerusi_lulus_pada)->format('d/m/Y') }}</div>
                        @if (!empty($signatureImages['pengerusi']))
                            <div style="margin-top:3px"><img src="{{ $signatureImages['pengerusi'] }}"
                                    alt="Signature Pengerusi"
                                    style="height:26px; width:auto; border:1px solid #ddd; padding:2px; background:#fff;">
                            </div>
                        @endif
                        <div style="font-size:9px; color:#1e3a5f">{{ $belanja->pengerusi_signature }}</div>
                        <div class="sig-box" style="border-color:#155724; color:#155724; font-weight:bold">
                            &#10003; Diluluskan</div>
                    @else
                        <div class="sig-box">Nama &amp; Tandatangan</div>
                    @endif
                </td>
                <td style="width:25%; text-align:right">
                    <div class="qr-area">
                        <img src="data:image/svg+xml;base64,{{ base64_encode($qrCode) }}" alt="QR">
                        <div class="qr-caption">Imbas untuk pengesahan</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer">
            Baucar ini dijana secara automatik oleh sistem MyImam &bull;
            Rujukan: {{ $baucarNo }} &bull;
            Tarikh Cetak: {{ now()->format('d/m/Y H:i') }}
        </div>

    </div>
</body>

</html>
