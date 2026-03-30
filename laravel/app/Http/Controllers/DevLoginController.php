<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class DevLoginController extends Controller
{
    private function isDevMode(): bool
    {
        $token = request()->query('token') ?? request()->header('X-God-Mode-Token');
        return app()->environment('local', 'development') && $token === config('app.god_mode_token');
    }

    public function show()
    {
        if (!$this->isDevMode()) {
            abort(403, 'Dev login is not available.');
        }

        $users = User::orderBy('role')->orderBy('name')->get();
        return view('auth.dev-login', compact('users'));
    }

    public function login(Request $request)
    {
        if (!$this->isDevMode()) {
            abort(403, 'Dev login is not available.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        // Check if user has TOTP enabled
        if ($user->totp_enabled && $user->totp_secret) {
            // Store user ID in session and show TOTP verification
            session(['totp_user_id' => $user->id, 'totp_pending' => true]);
            return view('auth.totp-verify', compact('user'));
        }

        // No TOTP enabled, login directly
        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function verifyTotp(Request $request)
    {
        if (!$this->isDevMode()) {
            abort(403, 'Dev login is not available.');
        }

        $request->validate([
            'code' => 'required|digits:6',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found']);
        }

        $google2fa = new Google2FA();

        if ($google2fa->verifyKey($user->totp_secret, $request->code)) {
            // Clear TOTP session and login
            session()->forget(['totp_user_id', 'totp_pending']);
            Auth::login($user);

            return response()->json([
                'success' => true,
                'redirect' => route('dashboard')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid verification code. Please try again.'
        ]);
    }

    public function skipTotp(Request $request)
    {
        if (!$this->isDevMode()) {
            abort(403, 'Dev login is not available.');
        }

        session()->forget(['totp_user_id', 'totp_pending']);

        return redirect()->route('dev.login.show');
    }
}
