<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Akaun;
use App\Models\Masjid;
use App\Services\ReportingManagementService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportingManagementController extends Controller
{
    public function __construct(private readonly ReportingManagementService $service)
    {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('reports.view'), 403);

        $actor = $request->user();
        $admin = $actor->hasRole('Admin');

        $masjidId = $admin
            ? (int) $request->query('masjid_id', 0)
            : (int) ($actor->id_masjid ?? 0);

        $filters = [
            'masjid_id' => $masjidId > 0 ? $masjidId : null,
            'akaun_id'  => (int) $request->query('akaun_id', 0) > 0 ? (int) $request->query('akaun_id') : null,
            'date_from' => $request->query('date_from') ?: now()->startOfMonth()->toDateString(),
            'date_to'   => $request->query('date_to') ?: now()->toDateString(),
        ];

        $data = $this->service->build($filters);

        $akaunOptions = Akaun::query()
            ->when($filters['masjid_id'], fn ($q) => $q->byMasjid((int) $filters['masjid_id']))
            ->aktif()
            ->orderBy('nama_akaun')
            ->get(['id', 'nama_akaun']);

        $masjidOptions = $admin
            ? Masjid::query()->orderBy('nama')->get(['id', 'nama'])
            : collect([]);

        return view('admin.reporting.index', [
            'filters'       => $filters,
            'incomeExpense' => $data['incomeVsExpense'],
            'accountRows'   => $data['accountSummary'],
            'monthlyRows'   => $data['monthlyReport'],
            'akaunOptions'  => $akaunOptions,
            'masjidOptions' => $masjidOptions,
            'isAdmin'       => $admin,
        ]);
    }
}
