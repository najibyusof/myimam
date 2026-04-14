<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktiviti;
use App\Models\User;
use App\Services\LogAktivitiService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogAktivitiManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', LogAktiviti::class);

        $actor       = $request->user();
        $masjidScope = $actor->hasRole('Admin') ? null : $actor->id_masjid;

        $jenis     = (string) $request->query('jenis', '');
        $modul     = (string) $request->query('modul', '');
        $userId    = (int)    $request->query('user_id', 0);
        $dateFrom  = (string) $request->query('date_from', '');
        $dateTo    = (string) $request->query('date_to', '');

        $query = LogAktiviti::query()
            ->when($masjidScope, fn ($q) => $q->byMasjid($masjidScope))
            ->with(['user:id,name,email'])
            ->when($jenis !== '', fn ($q) => $q->jenis($jenis))
            ->when($modul !== '', fn ($q) => $q->where('modul', $modul))
            ->when($userId > 0, fn ($q) => $q->where('id_user', $userId))
            ->when(
                $dateFrom !== '' && $dateTo !== '',
                fn ($q) => $q->betweenCreatedAt($dateFrom . ' 00:00:00', $dateTo . ' 23:59:59')
            )
            ->latest('created_at')
            ->latest('id');

        $records = $query->paginate(30)->withQueryString();

        $jenisOptions = LogAktivitiService::allJenis();

        $modulOptions = LogAktiviti::query()
            ->when($masjidScope, fn ($q) => $q->byMasjid($masjidScope))
            ->whereNotNull('modul')
            ->distinct()
            ->orderBy('modul')
            ->pluck('modul');

        $userOptions = User::query()
            ->when($masjidScope, fn ($q) => $q->byMasjid($masjidScope))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $stats = [
            'total'     => LogAktiviti::query()
                ->when($masjidScope, fn ($q) => $q->byMasjid($masjidScope))
                ->count(),
            'hari_ini'  => LogAktiviti::query()
                ->when($masjidScope, fn ($q) => $q->byMasjid($masjidScope))
                ->where('created_at', '>=', now()->startOfDay())
                ->count(),
            'login_ok'  => LogAktiviti::query()
                ->when($masjidScope, fn ($q) => $q->byMasjid($masjidScope))
                ->jenis(LogAktivitiService::JENIS_LOGIN_OK)
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'login_fail' => LogAktiviti::query()
                ->when($masjidScope, fn ($q) => $q->byMasjid($masjidScope))
                ->jenis(LogAktivitiService::JENIS_LOGIN_FAIL)
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
        ];

        return view('admin.log-aktiviti.index', [
            'records'      => $records,
            'stats'        => $stats,
            'jenisOptions' => $jenisOptions,
            'modulOptions' => $modulOptions,
            'userOptions'  => $userOptions,
            'jenis'        => $jenis,
            'modul'        => $modul,
            'userId'       => $userId,
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
        ]);
    }

    public function show(Request $request, LogAktiviti $logAktiviti): View
    {
        $this->authorize('view', $logAktiviti);

        $logAktiviti->load(['user:id,name,email', 'masjid:id,nama']);

        return view('admin.log-aktiviti.show', ['record' => $logAktiviti]);
    }
}
