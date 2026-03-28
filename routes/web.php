<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\BreakController;
use App\Http\Controllers\DevLoginController;
use App\Http\Controllers\OverbreakController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TotpController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// Dev login route (only in local development)
Route::get('/dev-login', [DevLoginController::class, 'show'])->name('dev.login.show');
Route::post('/dev-login', [DevLoginController::class, 'login'])->name('dev.login');
Route::post('/dev-totp-verify', [DevLoginController::class, 'verifyTotp'])->name('dev.totp.verify');
Route::post('/dev-totp-skip', [DevLoginController::class, 'skipTotp'])->name('dev.totp.skip');

// God Mode route (only in local development - bypasses ALL auth)
Route::get('/god', [\App\Http\Controllers\GodModeController::class, 'index'])->name('god');
Route::post('/god', [\App\Http\Controllers\GodModeController::class, 'login'])->name('god.login');

// Test route for voice alert (no auth required for testing)
Route::get('/alerts/test', [AlertController::class, 'testAlert'])->name('alerts.test');

Route::get('/dashboard', [BreakController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/overbreaks', [OverbreakController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('overbreaks');

Route::get('/overbreaks/live', [OverbreakController::class, 'liveData'])
    ->middleware(['auth', 'verified'])
    ->name('overbreaks.live');

Route::middleware('auth')->group(function () {
    // Alert endpoints
    Route::get('/alerts/overbreak', [AlertController::class, 'overbreakAlert'])->name('alerts.overbreak');
    Route::get('/alerts/batch', [AlertController::class, 'batchAlert'])->name('alerts.batch');

    // Break actions
    Route::post('/break/start', [BreakController::class, 'startBreak'])->name('break.start');
    Route::post('/break/end', [BreakController::class, 'endBreak'])->name('break.end');

    // Data endpoints (for AJAX/polling)
    Route::get('/dashboard/live', [BreakController::class, 'liveData'])->name('dashboard.live');
    Route::get('/dashboard/history', [BreakController::class, 'history'])->name('dashboard.history');

    // Admin
    Route::post('/admin/device/reset', [BreakController::class, 'resetDevice'])->name('admin.device.reset');

    // TOTP Setup (for authenticated users)
    Route::get('/totp/setup', [TotpController::class, 'showSetup'])->name('totp.setup');
    Route::post('/totp/setup', [TotpController::class, 'setup'])->name('totp.setup');
    Route::post('/totp/disable', [TotpController::class, 'disable'])->name('totp.disable');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
