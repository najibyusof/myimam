<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\MasjidController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Finance\AkaunController;
use App\Http\Controllers\Api\Finance\BaucarApiController;
use App\Http\Controllers\Api\Finance\BelanjaController;
use App\Http\Controllers\Api\Finance\HasilController;
use App\Http\Controllers\Api\Finance\KategoriBelanjaController;
use App\Http\Controllers\Api\Finance\PindahanAkaunController;
use App\Http\Controllers\Api\Finance\ProgramMasjidController;
use App\Http\Controllers\Api\Finance\ReportsController;
use App\Http\Controllers\Api\Finance\RunningNoController;
use App\Http\Controllers\Api\Finance\SumberHasilController;
use App\Http\Controllers\Api\Finance\TabungKhasController;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0',
    ]);
});

// Public Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('register', RegisterController::class)->name('api.auth.register');
    Route::post('login', LoginController::class)->name('api.auth.login');
    Route::post('forgot-password', [LoginController::class, 'forgotPassword'])->name('api.auth.forgot-password');
});

// Protected Routes (Requires Sanctum Authentication)
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth Routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', LogoutController::class)->name('api.auth.logout');
        Route::post('refresh', [LoginController::class, 'refresh'])->name('api.auth.refresh');
        Route::get('me', [ProfileController::class, 'me'])->name('api.auth.me');
        Route::patch('profile', [ProfileController::class, 'updateProfile'])->name('api.auth.profile.update');
        Route::post('profile/signature', [ProfileController::class, 'uploadSignature'])->middleware('throttle:3,1')->name('api.auth.profile.signature.upload');
        Route::delete('profile/signature', [ProfileController::class, 'removeSignature'])->middleware('throttle:5,1')->name('api.auth.profile.signature.remove');
        Route::post('change-password', [ProfileController::class, 'changePassword'])->name('api.auth.change-password');
    });

    // User Management (Admin Only)
    Route::middleware('permission:users.manage')->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('api.users.index');
            Route::post('/', [UserController::class, 'store'])->name('api.users.store');
            Route::get('{user}', [UserController::class, 'show'])->name('api.users.show');
            Route::patch('{user}', [UserController::class, 'update'])->name('api.users.update');
            Route::delete('{user}', [UserController::class, 'destroy'])->name('api.users.destroy');
            Route::patch('{user}/status', [UserController::class, 'toggleStatus'])->name('api.users.toggle-status');
            Route::post('{user}/roles', [UserController::class, 'assignRoles'])->name('api.users.assign-roles');
            Route::get('{user}/permissions', [UserController::class, 'getUserPermissions'])->name('api.users.permissions');
        });
    });

    // Masjid Management (Admin Only)
    Route::middleware('permission:masjid.manage')->group(function () {
        Route::prefix('masjids')->group(function () {
            Route::get('/', [MasjidController::class, 'index'])->name('api.masjids.index');
            Route::post('/', [MasjidController::class, 'store'])->name('api.masjids.store');
            Route::get('{masjid}', [MasjidController::class, 'show'])->name('api.masjids.show');
            Route::patch('{masjid}', [MasjidController::class, 'update'])->name('api.masjids.update');
            Route::delete('{masjid}', [MasjidController::class, 'destroy'])->name('api.masjids.destroy');
            Route::get('{masjid}/programs', [MasjidController::class, 'getPrograms'])->name('api.masjids.programs');
            Route::get('{masjid}/members', [MasjidController::class, 'getMembers'])->name('api.masjids.members');
        });
    });

    // Notifications (Accessible by all authenticated users)
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('api.notifications.index');
        Route::get('unread', [NotificationController::class, 'unread'])->name('api.notifications.unread');
        Route::patch('{notification}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.mark-read');
        Route::patch('read-all', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.mark-all-read');
        Route::delete('{notification}', [NotificationController::class, 'destroy'])->name('api.notifications.destroy');
        Route::delete('/', [NotificationController::class, 'deleteAll'])->name('api.notifications.delete-all');
        Route::get('preferences', [NotificationController::class, 'getPreferences'])->name('api.notifications.preferences');
        Route::patch('preferences', [NotificationController::class, 'updatePreferences'])->name('api.notifications.preferences.update');
    });

    // Finance Module (Phase A scaffold)
    Route::prefix('finance')->group(function () {
        // Accounts (akaun)
        Route::prefix('akaun')->group(function () {
            Route::get('/', [AkaunController::class, 'index'])->middleware('permission:akaun.view')->name('api.finance.akaun.index');
            Route::post('/', [AkaunController::class, 'store'])->middleware('permission:akaun.create')->name('api.finance.akaun.store');
            Route::get('{id}', [AkaunController::class, 'show'])->middleware('permission:akaun.view')->name('api.finance.akaun.show');
            Route::patch('{id}', [AkaunController::class, 'update'])->middleware('permission:akaun.update')->name('api.finance.akaun.update');
            Route::delete('{id}', [AkaunController::class, 'destroy'])->middleware('permission:akaun.delete')->name('api.finance.akaun.destroy');
        });

        // Income (hasil)
        Route::prefix('hasil')->group(function () {
            Route::get('/', [HasilController::class, 'index'])->middleware('permission:hasil.view')->name('api.finance.hasil.index');
            Route::post('/', [HasilController::class, 'store'])->middleware('permission:hasil.create')->name('api.finance.hasil.store');
            Route::get('{id}', [HasilController::class, 'show'])->middleware('permission:hasil.view')->name('api.finance.hasil.show');
            Route::patch('{id}', [HasilController::class, 'update'])->middleware('permission:hasil.update')->name('api.finance.hasil.update');
            Route::delete('{id}', [HasilController::class, 'destroy'])->middleware('permission:hasil.delete')->name('api.finance.hasil.destroy');
            Route::get('{id}/receipt', [HasilController::class, 'receipt'])->middleware('permission:hasil.view')->name('api.finance.hasil.receipt');
        });

        // Expenses (belanja)
        Route::prefix('belanja')->group(function () {
            Route::get('/', [BelanjaController::class, 'index'])->middleware('permission:belanja.view')->name('api.finance.belanja.index');
            Route::post('/', [BelanjaController::class, 'store'])->middleware('permission:belanja.create')->name('api.finance.belanja.store');
            Route::get('{id}', [BelanjaController::class, 'show'])->middleware('permission:belanja.view')->name('api.finance.belanja.show');
            Route::patch('{id}', [BelanjaController::class, 'update'])->middleware('permission:belanja.update')->name('api.finance.belanja.update');
            Route::delete('{id}', [BelanjaController::class, 'destroy'])->middleware('permission:belanja.delete')->name('api.finance.belanja.destroy');
            Route::patch('{id}/approve', [BelanjaController::class, 'approve'])->middleware('permission:finance.approve')->name('api.finance.belanja.approve');
        });

        // Baucar (read-only view of belanja as official payment vouchers)
        Route::prefix('baucar')->group(function () {
            Route::get('/', [BaucarApiController::class, 'index'])->middleware('permission:belanja.view')->name('api.finance.baucar.index');
            Route::get('{id}', [BaucarApiController::class, 'show'])->middleware('permission:belanja.view')->name('api.finance.baucar.show');
            Route::get('{id}/pdf', [BaucarApiController::class, 'pdf'])->middleware('permission:belanja.view')->name('api.finance.baucar.pdf');
        });

        // Account transfers (pindahan-akaun)
        Route::prefix('pindahan-akaun')->group(function () {
            Route::get('/', [PindahanAkaunController::class, 'index'])->middleware('permission:pindahan_akaun.view')->name('api.finance.pindahan-akaun.index');
            Route::post('/', [PindahanAkaunController::class, 'store'])->middleware('permission:pindahan_akaun.create')->name('api.finance.pindahan-akaun.store');
            Route::get('{id}', [PindahanAkaunController::class, 'show'])->middleware('permission:pindahan_akaun.view')->name('api.finance.pindahan-akaun.show');
            Route::patch('{id}', [PindahanAkaunController::class, 'update'])->middleware('permission:pindahan_akaun.update')->name('api.finance.pindahan-akaun.update');
            Route::delete('{id}', [PindahanAkaunController::class, 'destroy'])->middleware('permission:pindahan_akaun.delete')->name('api.finance.pindahan-akaun.destroy');
        });

        // Master data: sumber-hasil
        Route::prefix('sumber-hasil')->group(function () {
            Route::get('/', [SumberHasilController::class, 'index'])->middleware('permission:sumber_hasil.view')->name('api.finance.sumber-hasil.index');
            Route::post('/', [SumberHasilController::class, 'store'])->middleware('permission:sumber_hasil.create')->name('api.finance.sumber-hasil.store');
            Route::get('{id}', [SumberHasilController::class, 'show'])->middleware('permission:sumber_hasil.view')->name('api.finance.sumber-hasil.show');
            Route::patch('{id}', [SumberHasilController::class, 'update'])->middleware('permission:sumber_hasil.update')->name('api.finance.sumber-hasil.update');
            Route::delete('{id}', [SumberHasilController::class, 'destroy'])->middleware('permission:sumber_hasil.delete')->name('api.finance.sumber-hasil.destroy');
            Route::patch('{id}/status', [SumberHasilController::class, 'toggleStatus'])->middleware('permission:sumber_hasil.update')->name('api.finance.sumber-hasil.status');
        });

        // Master data: kategori-belanja
        Route::prefix('kategori-belanja')->group(function () {
            Route::get('/', [KategoriBelanjaController::class, 'index'])->middleware('permission:kategori_belanja.view')->name('api.finance.kategori-belanja.index');
            Route::post('/', [KategoriBelanjaController::class, 'store'])->middleware('permission:kategori_belanja.create')->name('api.finance.kategori-belanja.store');
            Route::get('{id}', [KategoriBelanjaController::class, 'show'])->middleware('permission:kategori_belanja.view')->name('api.finance.kategori-belanja.show');
            Route::patch('{id}', [KategoriBelanjaController::class, 'update'])->middleware('permission:kategori_belanja.update')->name('api.finance.kategori-belanja.update');
            Route::delete('{id}', [KategoriBelanjaController::class, 'destroy'])->middleware('permission:kategori_belanja.delete')->name('api.finance.kategori-belanja.destroy');
            Route::patch('{id}/status', [KategoriBelanjaController::class, 'toggleStatus'])->middleware('permission:kategori_belanja.update')->name('api.finance.kategori-belanja.status');
        });

        // Master data: tabung-khas
        Route::prefix('tabung-khas')->group(function () {
            Route::get('/', [TabungKhasController::class, 'index'])->middleware('permission:tabung_khas.view')->name('api.finance.tabung-khas.index');
            Route::post('/', [TabungKhasController::class, 'store'])->middleware('permission:tabung_khas.create')->name('api.finance.tabung-khas.store');
            Route::get('{id}', [TabungKhasController::class, 'show'])->middleware('permission:tabung_khas.view')->name('api.finance.tabung-khas.show');
            Route::patch('{id}', [TabungKhasController::class, 'update'])->middleware('permission:tabung_khas.update')->name('api.finance.tabung-khas.update');
            Route::delete('{id}', [TabungKhasController::class, 'destroy'])->middleware('permission:tabung_khas.delete')->name('api.finance.tabung-khas.destroy');
            Route::patch('{id}/status', [TabungKhasController::class, 'toggleStatus'])->middleware('permission:tabung_khas.update')->name('api.finance.tabung-khas.status');
        });

        // Master data: program-masjid
        Route::prefix('program-masjid')->group(function () {
            Route::get('/', [ProgramMasjidController::class, 'index'])->middleware('permission:program_masjid.view')->name('api.finance.program-masjid.index');
            Route::post('/', [ProgramMasjidController::class, 'store'])->middleware('permission:program_masjid.create')->name('api.finance.program-masjid.store');
            Route::get('{id}', [ProgramMasjidController::class, 'show'])->middleware('permission:program_masjid.view')->name('api.finance.program-masjid.show');
            Route::patch('{id}', [ProgramMasjidController::class, 'update'])->middleware('permission:program_masjid.update')->name('api.finance.program-masjid.update');
            Route::delete('{id}', [ProgramMasjidController::class, 'destroy'])->middleware('permission:program_masjid.delete')->name('api.finance.program-masjid.destroy');
            Route::patch('{id}/status', [ProgramMasjidController::class, 'toggleStatus'])->middleware('permission:program_masjid.update')->name('api.finance.program-masjid.status');
        });

        // Running number
        Route::prefix('running-no')->group(function () {
            Route::get('/', [RunningNoController::class, 'index'])->middleware('permission:running_no.view')->name('api.finance.running-no.index');
            Route::post('generate', [RunningNoController::class, 'generate'])->middleware('permission:running_no.generate')->name('api.finance.running-no.generate');
            Route::patch('{idMasjid}/{prefix}/{tahun}/{bulan}', [RunningNoController::class, 'update'])->middleware('permission:running_no.update')->name('api.finance.running-no.update');
        });

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('buku-tunai', [ReportsController::class, 'bukuTunai'])->middleware('permission:reports.view')->name('api.finance.reports.buku-tunai');
            Route::get('jumaat', [ReportsController::class, 'jumaat'])->middleware('permission:reports.view')->name('api.finance.reports.jumaat');
            Route::get('derma', [ReportsController::class, 'derma'])->middleware('permission:reports.view')->name('api.finance.reports.derma');
            Route::get('belanja', [ReportsController::class, 'belanja'])->middleware('permission:reports.view')->name('api.finance.reports.belanja');
            Route::get('penyata', [ReportsController::class, 'penyata'])->middleware('permission:reports.view')->name('api.finance.reports.penyata');
            Route::get('tabung', [ReportsController::class, 'tabung'])->middleware('permission:reports.view')->name('api.finance.reports.tabung');
        });
    });
});
