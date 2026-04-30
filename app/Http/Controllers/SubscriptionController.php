<?php

namespace App\Http\Controllers;

use App\Models\Masjid;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private InvoiceService $invoiceService
    )
    {
    }

    public function index(Request $request): View
    {
        $tenant = $this->resolveTenant($request);

        $plans = Plan::query()->orderBy('price')->get();

        $currentSubscription = null;
        $recentPayments = collect();

        if ($tenant) {
            $currentSubscription = Subscription::query()
                ->with('plan')
                ->where('tenant_id', $tenant->id)
                ->latest('id')
                ->first();

            $recentPayments = Payment::query()
                ->where('tenant_id', $tenant->id)
                ->latest('id')
                ->limit(5)
                ->get();
        }

        return view('subscription.index', [
            'tenant' => $tenant,
            'plans' => $plans,
            'currentSubscription' => $currentSubscription,
            'recentPayments' => $recentPayments,
        ]);
    }

    public function subscribe(int $plan_id, Request $request): RedirectResponse
    {
        $tenant = $this->resolveTenant($request);

        abort_unless($tenant !== null, 403, 'Tenant tidak dijumpai.');

        $validated = $request->validate([
            'gateway' => ['nullable', 'in:toyyibpay,billplz'],
            'auto_renew' => ['nullable', 'boolean'],
        ]);

        $plan = Plan::query()->findOrFail($plan_id);
        $gateway = $validated['gateway'] ?? null;
        $autoRenew = (bool) ($validated['auto_renew'] ?? true);

        $result = $this->paymentService->createPayment(
            $tenant,
            $plan,
            is_string($gateway) ? $gateway : null,
            [
                'auto_renew' => $autoRenew,
                'payer_email' => $request->user()?->email,
                'payer_phone' => $tenant->whatsapp_no,
            ]
        );

        if (!($result['success'] ?? false)) {
            return redirect()->route('subscription.index')
                ->with('payment_status', 'failed')
                ->with('payment_message', $result['message'] ?? 'Pembayaran gagal dimulakan.');
        }

        return redirect()->away($result['payment_url']);
    }

    public function paymentHistory(Request $request): View
    {
        $tenant = $this->resolveTenant($request);

        abort_unless($tenant !== null, 403, 'Tenant tidak dijumpai.');

        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,paid,failed'],
            'gateway' => ['nullable', 'in:toyyibpay,billplz'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $paymentsQuery = Payment::query()
            ->with(['subscription.plan'])
            ->where('tenant_id', $tenant->id);

        $this->applyPaymentFilters($paymentsQuery, $filters);

        $payments = $paymentsQuery
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('subscription.payments', [
            'tenant' => $tenant,
            'payments' => $payments,
            'filters' => $filters,
        ]);
    }

    public function startTrial(int $plan_id, Request $request): RedirectResponse
    {
        $tenant = $this->resolveTenant($request);

        abort_unless($tenant !== null, 403, 'Tenant tidak dijumpai.');

        $plan = Plan::query()->findOrFail($plan_id);
        $result = $this->paymentService->startTrial($tenant, $plan, 7);

        if (!($result['success'] ?? false)) {
            return redirect()->route('subscription.index')
                ->with('payment_message', $result['message'] ?? 'Gagal memulakan tempoh percubaan.');
        }

        return redirect()->route('subscription.index')
            ->with('payment_message', 'Tempoh percubaan 7 hari berjaya diaktifkan.');
    }

    public function paymentCallback(Request $request)
    {
        $data = $request->all();
        $result = $this->paymentService->handleCallback($data);

        if (!($result['success'] ?? false)) {
            return response()->json([
                'ok' => false,
                'message' => $result['message'] ?? 'Callback failed.',
            ], 422);
        }

        return response()->json(['ok' => true]);
    }

    public function paymentStatus(Payment $payment, Request $request): View
    {
        $tenant = $this->resolveTenant($request);

        abort_unless(
            $this->isSuperadmin() || ($tenant && (int) $payment->tenant_id === (int) $tenant->id),
            403,
            'Akses tidak dibenarkan.'
        );

        return view('subscription.status', [
            'payment' => $payment->load('subscription.plan'),
        ]);
    }

    public function downloadInvoice(Payment $payment, Request $request): StreamedResponse
    {
        $tenant = $this->resolveTenant($request);

        abort_unless(
            $this->isSuperadmin() || ($tenant && (int) $payment->tenant_id === (int) $tenant->id),
            403,
            'Akses tidak dibenarkan.'
        );

        if (!$payment->invoice_path) {
            $payment = $this->invoiceService->generateForPayment($payment);
        }

        $downloadName = ($payment->invoice_no ?: ('invoice-' . $payment->id)) . '.pdf';

        return Storage::disk('public')->download($payment->invoice_path, $downloadName);
    }

    public function superadminIndex(): View
    {
        $this->ensureSuperadmin();

        $subscriptions = Subscription::query()
            ->with(['tenant', 'plan', 'payments'])
            ->latest('id')
            ->paginate(25);

        return view('admin.subscriptions.billing-index', [
            'subscriptions' => $subscriptions,
        ]);
    }

    public function superadminPayments(Request $request): View
    {
        $this->ensureSuperadmin();

        $filters = $this->validatePaymentMonitorFilters($request);
        $paymentsQuery = $this->buildPaymentMonitorQuery($filters);

        $statsQuery = clone $paymentsQuery;

        $payments = $paymentsQuery
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'paid' => (clone $statsQuery)->where('status', 'paid')->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'failed' => (clone $statsQuery)->where('status', 'failed')->count(),
            'amount' => (float) ((clone $statsQuery)->sum('amount') ?: 0),
        ];

        return view('admin.subscriptions.payment-monitor', [
            'payments' => $payments,
            'filters' => $filters,
            'stats' => $stats,
            'tenants' => Masjid::query()->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function superadminPaymentsExport(Request $request): StreamedResponse
    {
        $this->ensureSuperadmin();

        $filters = $this->validatePaymentMonitorFilters($request);
        $query = $this->buildPaymentMonitorQuery($filters)
            ->select(['id', 'tenant_id', 'subscription_id', 'amount', 'gateway', 'status', 'reference_id', 'created_at'])
            ->orderBy('id');

        $filename = 'payment-monitor-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'payment_id',
                'datetime',
                'tenant_id',
                'tenant_name',
                'plan_name',
                'amount',
                'gateway',
                'status',
                'reference_id',
            ]);

            $query->chunkById(500, function ($payments) use ($handle) {
                foreach ($payments as $payment) {
                    fputcsv($handle, [
                        $payment->id,
                        optional($payment->created_at)->format('Y-m-d H:i:s'),
                        $payment->tenant_id,
                        $payment->tenant?->nama,
                        $payment->subscription?->plan?->name,
                        number_format((float) $payment->amount, 2, '.', ''),
                        $payment->gateway,
                        $payment->status,
                        $payment->reference_id,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function toggleTenant(Masjid $masjid, Request $request): RedirectResponse
    {
        $this->ensureSuperadmin();

        $validated = $request->validate([
            'status' => ['required', 'in:active,suspended'],
        ]);

        $masjid->update(['status' => $validated['status']]);

        return back()->with('status', 'Status tenant berjaya dikemaskini.');
    }

    public function overrideSubscription(Masjid $masjid, Request $request): RedirectResponse
    {
        $this->ensureSuperadmin();

        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'status' => ['required', 'in:active,expired,pending'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
        ]);

        $plan = Plan::query()->findOrFail($validated['plan_id']);
        $durationDays = (int) ($validated['duration_days'] ?? $plan->duration_days);

        Subscription::query()
            ->where('tenant_id', $masjid->id)
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays($durationDays);

        $subscription = Subscription::query()->create([
            'tenant_id' => $masjid->id,
            'plan_id' => $plan->id,
            'status' => $validated['status'],
            'start_date' => $validated['status'] === 'pending' ? null : $startDate,
            'end_date' => $validated['status'] === 'active' ? $endDate : null,
        ]);

        $masjid->update([
            'subscription_status' => $subscription->status,
            'subscription_expiry' => $subscription->end_date,
        ]);

        return back()->with('status', 'Subscription override berjaya.');
    }

    private function resolveTenant(Request $request): ?Masjid
    {
        $user = $request->user();

        if ($user && $user->id_masjid) {
            return Masjid::query()->find((int) $user->id_masjid);
        }

        return $request->attributes->get('current_masjid');
    }

    private function ensureSuperadmin(): void
    {
        abort_unless($this->isSuperadmin(), 403);
    }

    private function validatePaymentMonitorFilters(Request $request): array
    {
        return $request->validate([
            'status' => ['nullable', 'in:pending,paid,failed'],
            'gateway' => ['nullable', 'in:toyyibpay,billplz'],
            'tenant_id' => ['nullable', 'integer', 'exists:masjid,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
    }

    private function buildPaymentMonitorQuery(array $filters): Builder
    {
        $query = Payment::query()->with(['tenant', 'subscription.plan']);

        if (!empty($filters['tenant_id'])) {
            $query->where('tenant_id', (int) $filters['tenant_id']);
        }

        $this->applyPaymentFilters($query, $filters);

        return $query;
    }

    private function applyPaymentFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['gateway'])) {
            $query->where('gateway', $filters['gateway']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }

    private function isSuperadmin(): bool
    {
        return Auth::check() && Auth::user()->peranan === 'superadmin';
    }
}
