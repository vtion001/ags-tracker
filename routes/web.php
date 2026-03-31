<?php

use App\Http\Controllers\AdminTicketController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\BreakController;
use App\Http\Controllers\DevLoginController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\OverbreakController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
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
Route::get('/god-bypass', [\App\Http\Controllers\GodModeController::class, 'bypass'])->name('god.bypass');

// Test route for voice alert (no auth required for testing)
Route::get('/alerts/test', [AlertController::class, 'testAlert'])->name('alerts.test');
Route::get('/alerts/slack/test', [AlertController::class, 'testSlack'])->name('alerts.slack.test');

Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
    // Refresh user from database to get latest status changes (e.g., after admin approval)
    $user = $request->user()?->fresh();
    if ($user && $user->requiresOnboarding()) {
        // Re-authenticate with fresh user data to update session
        \Illuminate\Support\Facades\Auth::setUser($user);
        return redirect()->route('onboarding.role');
    }
    return app(\App\Http\Controllers\BreakController::class)->dashboard($request);
})
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
    Route::get('/alerts/pending', [AlertController::class, 'getPendingAlerts'])->name('alerts.pending');
    Route::get('/alerts/next', [AlertController::class, 'getNextAlert'])->name('alerts.next');
    Route::post('/alerts/clear', [AlertController::class, 'clearAlerts'])->name('alerts.clear');

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

    // Admin Ticket Routes — admin and team_lead only
    Route::middleware(['auth', 'admin.or.teamlead'])->prefix('admin')->group(function () {
        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('admin.tickets.index');
        Route::get('/tickets/{id}', [AdminTicketController::class, 'show'])->name('admin.tickets.show');
        Route::post('/tickets/{id}/comment', [AdminTicketController::class, 'addComment'])->name('admin.tickets.comment');
        Route::post('/tickets/{id}/status', [AdminTicketController::class, 'updateStatus'])->name('admin.tickets.status');
        Route::post('/tickets/{id}/priority', [AdminTicketController::class, 'updatePriority'])->name('admin.tickets.priority');
    });

    // Support Tickets
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show')->where('id', '[0-9]+');
    Route::post('/tickets/{id}/comment', [TicketController::class, 'addComment'])->name('tickets.comment')->where('id', '[0-9]+');
    Route::post('/tickets/{id}/status', [TicketController::class, 'updateStatus'])->name('tickets.status')->where('id', '[0-9]+');

    // Onboarding Routes
    Route::prefix('onboarding')->middleware(['auth'])->group(function () {
        Route::get('/role-selection', [OnboardingController::class, 'showRoleSelection'])->name('onboarding.role');
        Route::post('/role-selection', [OnboardingController::class, 'storeRoleSelection'])->name('onboarding.role.store');
        Route::get('/profile', [OnboardingController::class, 'showProfile'])->name('onboarding.profile');
        Route::post('/profile', [OnboardingController::class, 'storeProfile'])->name('onboarding.profile.store');
        Route::get('/emergency', [OnboardingController::class, 'showEmergency'])->name('onboarding.emergency');
        Route::post('/emergency', [OnboardingController::class, 'storeEmergency'])->name('onboarding.emergency.store');
        Route::get('/security', [OnboardingController::class, 'showSecurity'])->name('onboarding.security');
        Route::post('/security', [OnboardingController::class, 'storeSecurity'])->name('onboarding.security.store');
        Route::post('/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');

        // Admin: Pending users management
        Route::middleware(['admin.or.teamlead'])->group(function () {
            Route::get('/pending', [OnboardingController::class, 'pendingUsers'])->name('onboarding.pending');
            Route::post('/{user}/approve', [OnboardingController::class, 'approveUser'])->name('onboarding.approve');
            Route::post('/{user}/reject', [OnboardingController::class, 'rejectUser'])->name('onboarding.reject');
        });
    });
});

require __DIR__ . '/auth.php';
