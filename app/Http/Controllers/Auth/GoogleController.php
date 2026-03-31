<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        // First check if user exists with this Google ID
        $user = User::where('google_id', $googleUser->id)->first();

        if (!$user) {
            // No Google ID match - check if email exists without Google ID
            // Only link if email exists AND has no google_id AND no password (never logged in locally)
            $user = User::where('email', $googleUser->email)
                ->whereNull('google_id')
                ->whereNotNull('password')
                ->first();

            if ($user) {
                // Email exists with local password - user must login locally first
                // Redirect to login with error message
                return redirect()->route('login')->withErrors([
                    'email' => 'An account with this email already exists. Please login with your password first, then link your Google account from your profile settings.',
                ]);
            }

            // No match at all - create new user
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'password' => Hash::make(bin2hex(random_bytes(16))),
                'role' => 'agent',
                'status' => 'pending', // Requires onboarding approval
            ]);
        }

        Auth::login($user);

        // Check if user needs to complete onboarding
        if ($user->requiresOnboarding() || ($user->isPending() && !$user->hasCompletedOnboarding())) {
            return redirect()->route('onboarding.role');
        }

        return redirect()->intended(route('dashboard'));
    }
}
