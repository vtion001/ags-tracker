<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class DevLoginController extends Controller
{
    public function show()
    {
        // Only allow in local/development environment
        if (!app()->environment('local', 'development')) {
            abort(403, 'Dev login is only available in local/development environment.');
        }

        $users = User::orderBy('role')->orderBy('name')->get();
        return view('auth.dev-login', compact('users'));
    }

    public function login(Request $request)
    {
        // Only allow in local/development environment
        if (!app()->environment('local', 'development')) {
            abort(403, 'Dev login is only available in local/development environment.');
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
        if (!app()->environment('local', 'development')) {
            abort(403, 'Dev login is only available in local/development environment.');
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
        session()->forget(['totp_user_id', 'totp_pending']);

        return redirect()->route('dev.login.show');
    }
}
