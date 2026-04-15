<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $service) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($this->isSuperAdmin($user)) {
            return view('dashboard.superadmin', $this->service->getSuperAdminData());
        }

        if (!$user->id_masjid) {
            // Authenticated but not yet assigned to a masjid
            return view('dashboard.tenant', [
                'masjid'             => null,
                'metrics'            => [
                    'totalBalance'    => 0,
                    'monthlyIncome'  => 0,
                    'monthlyExpense'  => 0,
                    'netMonthly'     => 0,
                    'jumaatCollection' => 0,
                    'pendingVouchers' => 0,
                ],
                'recentTransactions' => [],
                'accountBreakdown'   => [],
                'monthlyTrend'       => ['labels' => [], 'incomes' => [], 'expenses' => []],
                'insights'           => [],
                'dashboardRole'      => 'Member',
                'contextLabel'       => 'No Masjid Assigned',
                'currentMonth'       => now()->format('F Y'),
            ]);
        }

        return view('dashboard.tenant', $this->service->getTenantData($user->id_masjid, $user));
    }

    private function isSuperAdmin($user): bool
    {
        return $user->peranan === 'superadmin'
            || $user->hasRole('Superadmin')
            || $user->hasRole('SuperAdmin')
            || $user->hasRole('superadmin');
    }
}
