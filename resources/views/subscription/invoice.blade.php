<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoiceNo }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 12px;
        }

        .container {
            padding: 24px;
        }

        .header {
            margin-bottom: 20px;
        }

        .title {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .muted {
            color: #6b7280;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #f3f4f6;
        }

        .right {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1 class="title">Invoice</h1>
            <p class="muted">Invoice No: {{ $invoiceNo }}</p>
            <p class="muted">Date: {{ now()->format('d M Y H:i') }}</p>
        </div>

        <p><strong>Tenant:</strong> {{ $tenant?->nama ?? '-' }}</p>
        <p><strong>Plan:</strong> {{ $plan?->name ?? '-' }}</p>
        <p><strong>Payment Gateway:</strong> {{ strtoupper($payment->gateway) }}</p>
        <p><strong>Reference:</strong> {{ $payment->reference_id ?? '-' }}</p>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="right">Amount (RM)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Subscription fee - {{ $plan?->name ?? 'Plan' }}</td>
                    <td class="right">{{ number_format((float) $payment->amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="right"><strong>Total</strong></td>
                    <td class="right"><strong>{{ number_format((float) $payment->amount, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
