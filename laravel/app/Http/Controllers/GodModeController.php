<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GodModeController extends Controller
{
    public function index()
    {
        // Only allow in local/development environment
        if (!app()->environment('local', 'development')) {
            abort(403, 'God Mode is only available in local/development environment.');
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
        // Only allow in local/development environment
        if (!app()->environment('local', 'development')) {
            abort(403, 'God Mode is only available in local/development environment.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        // God mode bypasses everything - no TOTP, no password, no verification
        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
