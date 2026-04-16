<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('hasil.receipt.title') }} - {{ $hasil->masjid->nama ?? 'N/A' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .receipt-container {
            background-color: white;
            max-width: 600px;
            margin: 0 auto;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            text-align: left;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }

        .receipt-header-main {
            flex: 1;
        }

        .masjid-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .masjid-address {
            font-size: 13px;
            line-height: 1.6;
            color: #333;
        }

        .receipt-right {
            text-align: right;
            border: 2px dashed #999;
            padding: 15px 20px;
            min-width: 200px;
        }

        .receipt-amount-title {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .receipt-amount {
            font-size: 28px;
            font-weight: bold;
            color: #000;
            margin-bottom: 10px;
        }

        .receipt-no-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 3px;
        }

        .receipt-no {
            font-size: 13px;
            font-weight: 600;
            color: #000;
        }

        .divider {
            border-bottom: 1px solid #000;
            margin: 20px 0;
        }

        .details-section {
            margin-bottom: 30px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .detail-label {
            font-weight: 600;
            color: #333;
            min-width: 160px;
            padding-right: 20px;
        }

        .detail-value {
            text-align: left;
            color: #555;
            flex: 1;
            word-break: break-word;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-yes {
            background-color: #fef08a;
            color: #854d0e;
        }

        .status-no {
            background-color: #e5e7eb;
            color: #374151;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 11px;
            color: #999;
        }

        @media print {
            body {
                background-color: white;
                padding: 0;
            }

            .receipt-header {
                align-items: flex-start;
            }

            .receipt-container {
                box-shadow: none;
                max-width: 100%;
                margin: 0;
                padding: 0;
            }

            .print-button {
                display: none;
            }
        }

        .print-button {
            display: block;
            margin: 20px auto;
            padding: 10px 30px;
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .print-button:hover {
            background-color: #4338ca;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #4f46e5;
            text-decoration: none;
            font-size: 13px;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <a href="{{ route('admin.hasil.index') }}" class="back-link">← {{ __('hasil.receipt.back') }}</a>

    <div class="receipt-container">
        <!-- Header -->
        <div class="receipt-header">
            <div class="receipt-header-main">
                <div class="masjid-name">{{ $hasil->masjid->nama ?? 'N/A' }}</div>
                <div class="masjid-address">
                    @if ($hasil->masjid)
                        @if ($hasil->masjid->alamat || $hasil->masjid->daerah || $hasil->masjid->negeri)
                            {{ $hasil->masjid->alamat ?? '' }}{{ $hasil->masjid->daerah ? ', ' . $hasil->masjid->daerah : '' }}{{ $hasil->masjid->negeri ? ', ' . $hasil->masjid->negeri : '' }}<br>
                        @endif
                        @if ($hasil->masjid->no_pendaftaran)
                            {{ __('hasil.receipt.registration') }}: {{ $hasil->masjid->no_pendaftaran }}<br>
                        @endif
                    @endif
                </div>
            </div>
            <div class="receipt-right">
                <div class="receipt-amount-title">{{ __('hasil.receipt.total_received') }}</div>
                <div class="receipt-amount">RM {{ number_format($hasil->jumlah, 2) }}</div>
                <div class="receipt-no-label">{{ __('hasil.receipt.receipt_no') }}:</div>
                <div class="receipt-no">{{ $hasil->no_resit ?? 'N/A' }}</div>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="details-section">
            <div class="detail-row">
                <div class="detail-label">{{ __('hasil.receipt.date') }}</div>
                <div class="detail-value">{{ optional($hasil->tarikh)->format('d/m/Y') ?? 'N/A' }}</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">{{ __('hasil.receipt.type') }}</div>
                <div class="detail-value">{{ $hasil->sumberHasil->nama_sumber ?? 'N/A' }}</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">{{ __('hasil.receipt.account') }}</div>
                <div class="detail-value">{{ $hasil->akaun->nama_akaun ?? 'N/A' }}</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">{{ __('hasil.receipt.fund') }}</div>
                <div class="detail-value">
                    @if ($hasil->tabungKhas)
                        {{ __('hasil.receipt.fund_type') }}: {{ $hasil->tabungKhas->nama_tabung }}
                    @else
                        -
                    @endif
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">{{ __('hasil.receipt.amount') }}</div>
                <div class="detail-value"><strong>RM {{ number_format($hasil->jumlah, 2) }}</strong></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">{{ __('hasil.receipt.notes') }}</div>
                <div class="detail-value">{{ $hasil->catatan ?? '-' }}</div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="footer">
            {{ __('hasil.receipt.footer') }}
        </div>
    </div>

    <button type="button" class="print-button" onclick="window.print()">
        {{ __('hasil.receipt.print') }}
    </button>

    <script>
        // Auto-trigger print dialog on page load for direct printing
        document.addEventListener('DOMContentLoaded', function() {
            // Uncomment the line below to auto-print when page loads
            // window.print();
        });
    </script>
</body>

</html>
