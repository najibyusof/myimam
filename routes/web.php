<?php

use App\Http\Controllers\Admin\MasjidManagementController;
use App\Http\Controllers\Admin\AkaunManagementController;
use App\Http\Controllers\Admin\BelanjaManagementController;
use App\Http\Controllers\Admin\PindahanAkaunManagementController;
use App\Http\Controllers\Admin\RunningNoManagementController;
use App\Http\Controllers\Admin\LogAktivitiManagementController;
use App\Http\Controllers\Admin\ReportingManagementController;
use App\Http\Controllers\Admin\HasilManagementController;
use App\Http\Controllers\Admin\KategoriBelanjaManagementController;
use App\Http\Controllers\Admin\ProgramMasjidManagementController;
use App\Http\Controllers\Admin\SumberHasilManagementController;
use App\Http\Controllers\Admin\TabungKhasManagementController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\SubscriptionManagementController;
use App\Http\Controllers\Admin\CmsLandingController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationCenterController;
use App\Http\Controllers\SidebarBadgeController;
use App\Http\Controllers\PublicLandingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicLandingController::class)
    ->middleware('resolve.tenant')
    ->name('landing');

// ── Tenant error pages (accessible to authenticated users, no tenant-check) ─
Route::middleware('auth')->group(function () {
    Route::get('/tenant-suspended', function () {
        return view('errors.tenant-suspended');
    })->name('tenant.suspended');

    Route::get('/tenant-pending', function () {
        return view('errors.tenant-pending');
    })->name('tenant.pending');

    Route::get('/subscription-expired', function () {
        return view('errors.subscription-expired');
    })->name('subscription.expired');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified', 'resolve.tenant', 'tenant.active', 'tenant.subscription'])->name('dashboard');

Route::middleware(['auth', 'resolve.tenant', 'tenant.active', 'tenant.subscription'])->group(function () {
    Route::get('/notifications', [NotificationCenterController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [NotificationCenterController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationCenterController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('/notifications/{notification}/unread', [NotificationCenterController::class, 'markAsUnread'])->name('notifications.unread');

    // Sidebar live badge counts (polled every 30s by Alpine)
    Route::get('/sidebar/badge-counts', [SidebarBadgeController::class, 'counts'])->name('sidebar.badge-counts');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role_or_permission:Admin|masjid.view|masjid.create|masjid.update|masjid.delete')->group(function () {
        Route::get('/admin/masjid', [MasjidManagementController::class, 'index'])
            ->middleware('permission:masjid.view')
            ->name('admin.masjid.index');

        Route::get('/admin/masjid/create', [MasjidManagementController::class, 'create'])
            ->middleware('permission:masjid.create')
            ->name('admin.masjid.create');

        Route::post('/admin/masjid', [MasjidManagementController::class, 'store'])
            ->middleware('permission:masjid.create')
            ->name('admin.masjid.store');

        Route::get('/admin/masjid/{masjid}/edit', [MasjidManagementController::class, 'edit'])
            ->middleware('permission:masjid.update')
            ->name('admin.masjid.edit');

        Route::put('/admin/masjid/{masjid}', [MasjidManagementController::class, 'update'])
            ->middleware('permission:masjid.update')
            ->name('admin.masjid.update');

        Route::patch('/admin/masjid/{masjid}/suspend', [MasjidManagementController::class, 'suspend'])
            ->middleware('permission:masjid.update')
            ->name('admin.masjid.suspend');

        Route::patch('/admin/masjid/{masjid}/activate', [MasjidManagementController::class, 'activate'])
            ->middleware('permission:masjid.update')
            ->name('admin.masjid.activate');

        Route::delete('/admin/masjid/{masjid}', [MasjidManagementController::class, 'destroy'])
            ->middleware('permission:masjid.delete')
            ->name('admin.masjid.destroy');
    });

    Route::middleware('role_or_permission:Admin|subscriptions.manage')->group(function () {
        Route::get('/admin/subscriptions', [SubscriptionManagementController::class, 'index'])
            ->middleware('permission:subscriptions.manage')
            ->name('admin.subscriptions.index');

        Route::get('/admin/subscriptions/plans/create', [SubscriptionManagementController::class, 'createPlan'])
            ->middleware('permission:subscriptions.manage')
            ->name('admin.subscriptions.plans.create');

        Route::post('/admin/subscriptions/plans', [SubscriptionManagementController::class, 'storePlan'])
            ->middleware('permission:subscriptions.manage')
            ->name('admin.subscriptions.plans.store');

        Route::get('/admin/subscriptions/plans/{plan}/edit', [SubscriptionManagementController::class, 'editPlan'])
            ->middleware('permission:subscriptions.manage')
            ->name('admin.subscriptions.plans.edit');

        Route::put('/admin/subscriptions/plans/{plan}', [SubscriptionManagementController::class, 'updatePlan'])
            ->middleware('permission:subscriptions.manage')
            ->name('admin.subscriptions.plans.update');

        Route::get('/admin/subscriptions/masjid/{masjid}/assign', [SubscriptionManagementController::class, 'assignForm'])
            ->middleware('permission:subscriptions.manage')
            ->name('admin.subscriptions.assign');

        Route::post('/admin/subscriptions/masjid/{masjid}/assign', [SubscriptionManagementController::class, 'assignStore'])
            ->middleware('permission:subscriptions.manage')
            ->name('admin.subscriptions.assign.store');
    });

    Route::middleware('role_or_permission:Admin|cms.manage')->group(function () {
        Route::get('/admin/cms/landing', [CmsLandingController::class, 'edit'])
            ->middleware('permission:cms.manage')
            ->name('admin.cms.landing.edit');

        Route::put('/admin/cms/landing', [CmsLandingController::class, 'update'])
            ->middleware('permission:cms.manage')
            ->name('admin.cms.landing.update');
    });

    Route::middleware('role_or_permission:Admin|akaun.view|akaun.create|akaun.update|akaun.delete')->group(function () {
        Route::get('/admin/akaun', [AkaunManagementController::class, 'index'])
            ->middleware('permission:akaun.view')
            ->name('admin.akaun.index');
        Route::get('/admin/akaun/create', [AkaunManagementController::class, 'create'])
            ->middleware('permission:akaun.create')
            ->name('admin.akaun.create');
        Route::post('/admin/akaun', [AkaunManagementController::class, 'store'])
            ->middleware('permission:akaun.create')
            ->name('admin.akaun.store');
        Route::get('/admin/akaun/{akaun}/edit', [AkaunManagementController::class, 'edit'])
            ->middleware('permission:akaun.update')
            ->name('admin.akaun.edit');
        Route::put('/admin/akaun/{akaun}', [AkaunManagementController::class, 'update'])
            ->middleware('permission:akaun.update')
            ->name('admin.akaun.update');
        Route::delete('/admin/akaun/{akaun}', [AkaunManagementController::class, 'destroy'])
            ->middleware('permission:akaun.delete')
            ->name('admin.akaun.destroy');
    });

    Route::middleware('role_or_permission:Admin|hasil.view|hasil.create|hasil.update|hasil.delete')->group(function () {
        Route::get('/admin/hasil', [HasilManagementController::class, 'index'])
            ->middleware('permission:hasil.view')
            ->name('admin.hasil.index');
        Route::get('/admin/hasil/create', [HasilManagementController::class, 'create'])
            ->middleware('permission:hasil.create')
            ->name('admin.hasil.create');
        Route::post('/admin/hasil', [HasilManagementController::class, 'store'])
            ->middleware('permission:hasil.create')
            ->name('admin.hasil.store');
        Route::get('/admin/hasil/{hasil}/edit', [HasilManagementController::class, 'edit'])
            ->middleware('permission:hasil.update')
            ->name('admin.hasil.edit');
        Route::put('/admin/hasil/{hasil}', [HasilManagementController::class, 'update'])
            ->middleware('permission:hasil.update')
            ->name('admin.hasil.update');
        Route::delete('/admin/hasil/{hasil}', [HasilManagementController::class, 'destroy'])
            ->middleware('permission:hasil.delete')
            ->name('admin.hasil.destroy');
    });

    Route::middleware('role_or_permission:Admin|belanja.view|belanja.create|belanja.update|belanja.delete')->group(function () {
        Route::get('/admin/belanja', [BelanjaManagementController::class, 'index'])
            ->middleware('permission:belanja.view')
            ->name('admin.belanja.index');
        Route::get('/admin/belanja/create', [BelanjaManagementController::class, 'create'])
            ->middleware('permission:belanja.create')
            ->name('admin.belanja.create');
        Route::post('/admin/belanja', [BelanjaManagementController::class, 'store'])
            ->middleware('permission:belanja.create')
            ->name('admin.belanja.store');
        Route::get('/admin/belanja/{belanja}/edit', [BelanjaManagementController::class, 'edit'])
            ->middleware('permission:belanja.update')
            ->name('admin.belanja.edit');
        Route::put('/admin/belanja/{belanja}', [BelanjaManagementController::class, 'update'])
            ->middleware('permission:belanja.update')
            ->name('admin.belanja.update');
        Route::delete('/admin/belanja/{belanja}', [BelanjaManagementController::class, 'destroy'])
            ->middleware('permission:belanja.delete')
            ->name('admin.belanja.destroy');
    });

    Route::middleware('role_or_permission:Admin|pindahan_akaun.view|pindahan_akaun.create|pindahan_akaun.update|pindahan_akaun.delete')->group(function () {
        Route::get('/admin/pindahan-akaun', [PindahanAkaunManagementController::class, 'index'])
            ->middleware('permission:pindahan_akaun.view')
            ->name('admin.pindahan-akaun.index');
        Route::get('/admin/pindahan-akaun/create', [PindahanAkaunManagementController::class, 'create'])
            ->middleware('permission:pindahan_akaun.create')
            ->name('admin.pindahan-akaun.create');
        Route::post('/admin/pindahan-akaun', [PindahanAkaunManagementController::class, 'store'])
            ->middleware('permission:pindahan_akaun.create')
            ->name('admin.pindahan-akaun.store');
        Route::get('/admin/pindahan-akaun/{pindahanAkaun}/edit', [PindahanAkaunManagementController::class, 'edit'])
            ->middleware('permission:pindahan_akaun.update')
            ->name('admin.pindahan-akaun.edit');
        Route::put('/admin/pindahan-akaun/{pindahanAkaun}', [PindahanAkaunManagementController::class, 'update'])
            ->middleware('permission:pindahan_akaun.update')
            ->name('admin.pindahan-akaun.update');
        Route::delete('/admin/pindahan-akaun/{pindahanAkaun}', [PindahanAkaunManagementController::class, 'destroy'])
            ->middleware('permission:pindahan_akaun.delete')
            ->name('admin.pindahan-akaun.destroy');
    });

    Route::middleware('role_or_permission:Admin|running_no.view|running_no.generate|running_no.update')->group(function () {
        Route::get('/admin/running-no', [RunningNoManagementController::class, 'index'])
            ->middleware('permission:running_no.view')
            ->name('admin.running-no.index');
        Route::get('/admin/running-no/generate', [RunningNoManagementController::class, 'generateForm'])
            ->middleware('permission:running_no.generate')
            ->name('admin.running-no.generate');
        Route::post('/admin/running-no/generate', [RunningNoManagementController::class, 'generate'])
            ->middleware('permission:running_no.generate')
            ->name('admin.running-no.generate.post');
        Route::get('/admin/running-no/{idMasjid}/{prefix}/{tahun}/{bulan}/edit', [RunningNoManagementController::class, 'edit'])
            ->middleware('permission:running_no.update')
            ->name('admin.running-no.edit');
        Route::put('/admin/running-no/{idMasjid}/{prefix}/{tahun}/{bulan}', [RunningNoManagementController::class, 'update'])
            ->middleware('permission:running_no.update')
            ->name('admin.running-no.update');
    });


    Route::middleware('role_or_permission:Admin|audit.view')->group(function () {
        Route::get('/admin/log-aktiviti', [LogAktivitiManagementController::class, 'index'])
            ->middleware('permission:audit.view')
            ->name('admin.log-aktiviti.index');
        Route::get('/admin/log-aktiviti/{logAktiviti}', [LogAktivitiManagementController::class, 'show'])
            ->middleware('permission:audit.view')
            ->name('admin.log-aktiviti.show');
    });

    Route::middleware('role_or_permission:Admin|reports.view')->group(function () {
        Route::get('/admin/reporting', [ReportingManagementController::class, 'index'])
            ->middleware('permission:reports.view')
            ->name('admin.reporting.index');
    });

    Route::middleware('role_or_permission:Admin|sumber_hasil.view|sumber_hasil.create|sumber_hasil.update|sumber_hasil.delete')->group(function () {
        Route::get('/admin/sumber-hasil', [SumberHasilManagementController::class, 'index'])
            ->middleware('permission:sumber_hasil.view')
            ->name('admin.sumber-hasil.index');
        Route::get('/admin/sumber-hasil/create', [SumberHasilManagementController::class, 'create'])
            ->middleware('permission:sumber_hasil.create')
            ->name('admin.sumber-hasil.create');
        Route::post('/admin/sumber-hasil', [SumberHasilManagementController::class, 'store'])
            ->middleware('permission:sumber_hasil.create')
            ->name('admin.sumber-hasil.store');
        Route::get('/admin/sumber-hasil/{sumberHasil}/edit', [SumberHasilManagementController::class, 'edit'])
            ->middleware('permission:sumber_hasil.update')
            ->name('admin.sumber-hasil.edit');
        Route::put('/admin/sumber-hasil/{sumberHasil}', [SumberHasilManagementController::class, 'update'])
            ->middleware('permission:sumber_hasil.update')
            ->name('admin.sumber-hasil.update');
        Route::patch('/admin/sumber-hasil/{sumberHasil}/status', [SumberHasilManagementController::class, 'toggleStatus'])
            ->middleware('permission:sumber_hasil.update')
            ->name('admin.sumber-hasil.status');
        Route::delete('/admin/sumber-hasil/{sumberHasil}', [SumberHasilManagementController::class, 'destroy'])
            ->middleware('permission:sumber_hasil.delete')
            ->name('admin.sumber-hasil.destroy');
    });

    Route::middleware('role_or_permission:Admin|kategori_belanja.view|kategori_belanja.create|kategori_belanja.update|kategori_belanja.delete')->group(function () {
        Route::get('/admin/kategori-belanja', [KategoriBelanjaManagementController::class, 'index'])
            ->middleware('permission:kategori_belanja.view')
            ->name('admin.kategori-belanja.index');
        Route::get('/admin/kategori-belanja/create', [KategoriBelanjaManagementController::class, 'create'])
            ->middleware('permission:kategori_belanja.create')
            ->name('admin.kategori-belanja.create');
        Route::post('/admin/kategori-belanja', [KategoriBelanjaManagementController::class, 'store'])
            ->middleware('permission:kategori_belanja.create')
            ->name('admin.kategori-belanja.store');
        Route::get('/admin/kategori-belanja/{kategoriBelanja}/edit', [KategoriBelanjaManagementController::class, 'edit'])
            ->middleware('permission:kategori_belanja.update')
            ->name('admin.kategori-belanja.edit');
        Route::put('/admin/kategori-belanja/{kategoriBelanja}', [KategoriBelanjaManagementController::class, 'update'])
            ->middleware('permission:kategori_belanja.update')
            ->name('admin.kategori-belanja.update');
        Route::patch('/admin/kategori-belanja/{kategoriBelanja}/status', [KategoriBelanjaManagementController::class, 'toggleStatus'])
            ->middleware('permission:kategori_belanja.update')
            ->name('admin.kategori-belanja.status');
        Route::delete('/admin/kategori-belanja/{kategoriBelanja}', [KategoriBelanjaManagementController::class, 'destroy'])
            ->middleware('permission:kategori_belanja.delete')
            ->name('admin.kategori-belanja.destroy');
    });

    Route::middleware('role_or_permission:Admin|tabung_khas.view|tabung_khas.create|tabung_khas.update|tabung_khas.delete')->group(function () {
        Route::get('/admin/tabung-khas', [TabungKhasManagementController::class, 'index'])
            ->middleware('permission:tabung_khas.view')
            ->name('admin.tabung-khas.index');
        Route::get('/admin/tabung-khas/create', [TabungKhasManagementController::class, 'create'])
            ->middleware('permission:tabung_khas.create')
            ->name('admin.tabung-khas.create');
        Route::post('/admin/tabung-khas', [TabungKhasManagementController::class, 'store'])
            ->middleware('permission:tabung_khas.create')
            ->name('admin.tabung-khas.store');
        Route::get('/admin/tabung-khas/{tabungKhas}/edit', [TabungKhasManagementController::class, 'edit'])
            ->middleware('permission:tabung_khas.update')
            ->name('admin.tabung-khas.edit');
        Route::put('/admin/tabung-khas/{tabungKhas}', [TabungKhasManagementController::class, 'update'])
            ->middleware('permission:tabung_khas.update')
            ->name('admin.tabung-khas.update');
        Route::patch('/admin/tabung-khas/{tabungKhas}/status', [TabungKhasManagementController::class, 'toggleStatus'])
            ->middleware('permission:tabung_khas.update')
            ->name('admin.tabung-khas.status');
        Route::delete('/admin/tabung-khas/{tabungKhas}', [TabungKhasManagementController::class, 'destroy'])
            ->middleware('permission:tabung_khas.delete')
            ->name('admin.tabung-khas.destroy');
    });

    Route::middleware('role_or_permission:Admin|program_masjid.view|program_masjid.create|program_masjid.update|program_masjid.delete')->group(function () {
        Route::get('/admin/program-masjid', [ProgramMasjidManagementController::class, 'index'])
            ->middleware('permission:program_masjid.view')
            ->name('admin.program-masjid.index');
        Route::get('/admin/program-masjid/create', [ProgramMasjidManagementController::class, 'create'])
            ->middleware('permission:program_masjid.create')
            ->name('admin.program-masjid.create');
        Route::post('/admin/program-masjid', [ProgramMasjidManagementController::class, 'store'])
            ->middleware('permission:program_masjid.create')
            ->name('admin.program-masjid.store');
        Route::get('/admin/program-masjid/{programMasjid}/edit', [ProgramMasjidManagementController::class, 'edit'])
            ->middleware('permission:program_masjid.update')
            ->name('admin.program-masjid.edit');
        Route::put('/admin/program-masjid/{programMasjid}', [ProgramMasjidManagementController::class, 'update'])
            ->middleware('permission:program_masjid.update')
            ->name('admin.program-masjid.update');
        Route::patch('/admin/program-masjid/{programMasjid}/status', [ProgramMasjidManagementController::class, 'toggleStatus'])
            ->middleware('permission:program_masjid.update')
            ->name('admin.program-masjid.status');
        Route::delete('/admin/program-masjid/{programMasjid}', [ProgramMasjidManagementController::class, 'destroy'])
            ->middleware('permission:program_masjid.delete')
            ->name('admin.program-masjid.destroy');
    });

    Route::middleware('role:Admin')->group(function () {
        Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create', [UserManagementController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}/edit', [UserManagementController::class, 'edit'])->name('admin.users.edit');
        Route::put('/admin/users/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');
        Route::patch('/admin/users/{user}/status', [UserManagementController::class, 'toggleStatus'])->name('admin.users.status');
        Route::post('/admin/users/{user}/reset-password', [UserManagementController::class, 'sendPasswordReset'])->name('admin.users.reset-password');
        Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
    });

    Route::middleware('role_or_permission:Admin|roles.assign')->group(function () {
        Route::get('/admin/roles', [RolePermissionController::class, 'index'])
            ->middleware('permission:roles.assign')
            ->name('admin.roles.index');
        Route::get('/admin/roles/create', [RolePermissionController::class, 'create'])
            ->middleware('permission:roles.assign')
            ->name('admin.roles.create');
        Route::post('/admin/roles', [RolePermissionController::class, 'store'])
            ->middleware('permission:roles.assign')
            ->name('admin.roles.store');
        Route::get('/admin/roles/{role}/edit', [RolePermissionController::class, 'edit'])
            ->middleware('permission:roles.assign')
            ->name('admin.roles.edit');
        Route::put('/admin/roles/{role}', [RolePermissionController::class, 'update'])
            ->middleware('permission:roles.assign')
            ->name('admin.roles.update');
    });

        Route::delete('/admin/roles/{role}', [RolePermissionController::class, 'destroy'])
            ->middleware(['permission:roles.assign', 'role_or_permission:Admin|roles.assign'])
            ->name('admin.roles.destroy');
});

require __DIR__.'/auth.php';
