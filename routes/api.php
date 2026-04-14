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
});
