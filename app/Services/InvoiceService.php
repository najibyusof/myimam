<?php

namespace App\Services;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function generateForPayment(Payment $payment): Payment
    {
        if ($payment->invoice_no && $payment->invoice_path) {
            return $payment;
        }

        $payment->loadMissing(['subscription.plan', 'tenant']);

        $invoiceNo = $this->buildInvoiceNo($payment->id);
        $fileName = $invoiceNo . '.pdf';
        $relativePath = 'invoices/' . $fileName;

        $pdf = Pdf::loadView('subscription.invoice', [
            'payment' => $payment,
            'subscription' => $payment->subscription,
            'tenant' => $payment->tenant,
            'plan' => $payment->subscription?->plan,
            'invoiceNo' => $invoiceNo,
        ]);

        Storage::disk('public')->put($relativePath, $pdf->output());

        $payment->update([
            'invoice_no' => $invoiceNo,
            'invoice_path' => $relativePath,
        ]);

        return $payment->fresh();
    }

    private function buildInvoiceNo(int $paymentId): string
    {
        return 'INV-' . now()->format('Ym') . '-' . str_pad((string) $paymentId, 6, '0', STR_PAD_LEFT);
    }
}
