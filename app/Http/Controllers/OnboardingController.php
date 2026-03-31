<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * Show the onboarding selection step (role + team).
     */
    public function showRoleSelection(): View|RedirectResponse
    {
        $user = Auth::user();

        // If already completed onboarding or not pending, redirect to dashboard
        if ($user->hasCompletedOnboarding() || !$user->isPending()) {
            return redirect()->route('dashboard');
        }

        $teams = Team::where('is_active', true)->orderBy('name')->get();

        return view('onboarding.role-selection', [
            'teams' => $teams,
            'user' => $user,
        ]);
    }

    /**
     * Process role and team selection.
     */
    public function storeRoleSelection(Request $request): RedirectResponse
    {
        $request->validate([
            'role' => ['required', Rule::in(['agent', 'tl'])],
            'team_id' => ['required_if:role,agent', 'nullable|exists:teams,id'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        // Agents can select their own team and start with pending status
        // Team leads are auto-approved
        $user->update([
            'role' => $request->role,
            'team_id' => $request->role === 'agent' ? $request->team_id : null,
            'department' => $request->department,
            'status' => $request->role === 'tl' ? 'active' : 'pending',
        ]);

        // If team lead, redirect to profile setup
        if ($user->isTeamLead()) {
            return redirect()->route('onboarding.profile')->with('status', 'As a team lead, your account is now active!');
        }

        return redirect()->route('onboarding.profile');
    }

    /**
     * Show the profile setup step.
     */
    public function showProfile(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasCompletedOnboarding()) {
            return redirect()->route('dashboard');
        }

        $teams = Team::where('is_active', true)->orderBy('name')->get();

        return view('onboarding.profile', [
            'user' => $user,
            'teams' => $teams,
        ]);
    }

    /**
     * Process profile setup.
     */
    public function storeProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'position' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'shift_schedule' => ['nullable', 'string', 'max:255'],
            'hire_date' => ['nullable', 'date'],
            'work_location' => ['nullable', Rule::in(['office', 'wfh', 'hybrid'])],
            'manager_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        $user->update($request->only([
            'position',
            'contact_number',
            'shift_schedule',
            'hire_date',
            'work_location',
            'manager_name',
        ]));

        return redirect()->route('onboarding.emergency');
    }

    /**
     * Show the emergency contact step.
     */
    public function showEmergency(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasCompletedOnboarding()) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.emergency', [
            'user' => $user,
        ]);
    }

    /**
     * Process emergency contact setup.
     */
    public function storeEmergency(Request $request): RedirectResponse
    {
        $request->validate([
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
        ]);

        $user = Auth::user();

        $user->update($request->only([
            'emergency_contact_name',
            'emergency_contact_phone',
            'emergency_contact_relationship',
        ]));

        return redirect()->route('onboarding.security');
    }

    /**
     * Show the security setup step.
     */
    public function showSecurity(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasCompletedOnboarding()) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.security', [
            'user' => $user,
        ]);
    }

    /**
     * Process security setup and complete onboarding.
     */
    public function storeSecurity(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Mark onboarding as completed
        $user->update([
            'onboarding_completed' => true,
            'status' => 'active',
        ]);

        // Refresh the user in the session to avoid cached stale data
        Auth::setUser($user->fresh());

        return redirect()->route('dashboard')->with('status', 'Welcome to AGS Break Tracker! Your onboarding is complete.');
    }

    /**
     * Skip onboarding and go to dashboard (for agents who want to skip optional steps).
     */
    public function skip(): RedirectResponse
    {
        $user = Auth::user();

        $user->update([
            'onboarding_completed' => true,
            'status' => 'active',
        ]);

        // Refresh the user in the session to avoid cached stale data
        Auth::setUser($user->fresh());

        return redirect()->route('dashboard');
    }

    /**
     * Admin: Show pending users for approval.
     */
    public function pendingUsers(): View
    {
        $pendingUsers = User::where('status', 'pending')
            ->where('role', 'agent')
            ->with('team')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('onboarding.pending-users', [
            'pendingUsers' => $pendingUsers,
        ]);
    }

    /**
     * Admin: Approve a pending user.
     */
    public function approveUser(User $user): RedirectResponse
    {
        if (!$user->isPending()) {
            return redirect()->back()->with('error', 'User is not pending approval.');
        }

        $user->update(['status' => 'active']);

        return redirect()->back()->with('status', "User {$user->name} has been approved.");
    }

    /**
     * Admin: Reject a pending user.
     */
    public function rejectUser(Request $request, User $user): RedirectResponse
    {
        if (!$user->isPending()) {
            return redirect()->back()->with('error', 'User is not pending approval.');
        }

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        // Soft delete or just change status
        $user->update(['status' => 'rejected']);

        return redirect()->back()->with('status', "User {$user->name} has been rejected.");
    }
}
