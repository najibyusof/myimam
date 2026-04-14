<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubscriptionPlanStoreRequest;
use App\Http\Requests\Admin\SubscriptionPlanUpdateRequest;
use App\Http\Requests\Admin\TenantSubscriptionAssignRequest;
use App\Models\Masjid;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionManagementService;
use Illuminate\Support\Facades\Auth;

class SubscriptionManagementController extends Controller
{
    public function __construct(private SubscriptionManagementService $service)
    {
    }

    public function index()
    {
        $this->ensureSuperAdmin();

        $plans = SubscriptionPlan::query()
            ->withCount('tenantSubscriptions')
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        $masjids = Masjid::query()
            ->with(['activeSubscription.plan'])
            ->orderBy('nama')
            ->paginate(20);

        return view('admin.subscriptions.index', [
            'plans' => $plans,
            'masjids' => $masjids,
        ]);
    }

    public function createPlan()
    {
        $this->ensureSuperAdmin();

        return view('admin.subscriptions.plan-create');
    }

    public function storePlan(SubscriptionPlanStoreRequest $request)
    {
        $this->ensureSuperAdmin();

        $this->service->createPlan($request->validated());

        return redirect()->route('admin.subscriptions.index')
            ->with('status', 'Pelan langganan berjaya dicipta.');
    }

    public function editPlan(SubscriptionPlan $plan)
    {
        $this->ensureSuperAdmin();

        return view('admin.subscriptions.plan-edit', [
            'plan' => $plan,
        ]);
    }

    public function updatePlan(SubscriptionPlanUpdateRequest $request, SubscriptionPlan $plan)
    {
        $this->ensureSuperAdmin();

        $this->service->updatePlan($plan, $request->validated());

        return redirect()->route('admin.subscriptions.index')
            ->with('status', 'Pelan langganan berjaya dikemaskini.');
    }

    public function assignForm(Masjid $masjid)
    {
        $this->ensureSuperAdmin();

        $plans = SubscriptionPlan::query()->active()->ordered()->get();

        return view('admin.subscriptions.assign', [
            'masjid' => $masjid,
            'plans' => $plans,
        ]);
    }

    public function assignStore(TenantSubscriptionAssignRequest $request, Masjid $masjid)
    {
        $this->ensureSuperAdmin();

        $this->service->assignTenantSubscription(
            $masjid,
            $request->validated(),
            $request->user()
        );

        return redirect()->route('admin.subscriptions.index')
            ->with('status', 'Langganan tenant berjaya dikemaskini.');
    }

    private function ensureSuperAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->peranan === 'superadmin', 403);
    }
}
