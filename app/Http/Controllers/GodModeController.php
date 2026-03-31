<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GodModeController extends Controller
{
    public function index(Request $request)
    {
        // Verify God Mode token
        $token = $request->query('token') ?? $request->header('X-God-Mode-Token');

        if ($token !== config('app.god_mode_token')) {
            abort(403, 'Invalid God Mode token.');
        }

        $users = User::orderBy('role')->orderBy('name')->get();

        $groupedUsers = [
            'admin' => $users->where('role', 'admin')->values(),
            'team_lead' => $users->where('role', 'tl')->values(),
            'agent' => $users->where('role', 'agent')->values(),
        ];

        return view('auth.god-mode', compact('groupedUsers'));
    }

    public function login(Request $request)
    {
        // Verify God Mode token
        $token = $request->query('token') ?? $request->header('X-God-Mode-Token');

        if ($token !== config('app.god_mode_token')) {
            abort(403, 'Invalid God Mode token.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        // God mode bypasses everything - no TOTP, no password, no verification
        Auth::login($user);

        return redirect()->route('dashboard');
    }

    /**
     * Simple email-based God Mode bypass for quick testing.
     * Access: /god-bypass?token=YOUR_TOKEN&email=user@example.com
     */
    public function bypass(Request $request)
    {
        $token = $request->query('token') ?? $request->header('X-God-Mode-Token');

        if ($token !== config('app.god_mode_token')) {
            abort(403, 'Invalid God Mode token.');
        }

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        // God mode bypasses everything - no TOTP, no password, no verification
        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
