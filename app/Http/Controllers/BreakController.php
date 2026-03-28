<?php

namespace App\Http\Controllers;

use App\Services\BreakService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BreakController extends Controller
{
    public function __construct(
        protected BreakService $breakService
    ) {}

    public function dashboard(): View
    {
        $user = Auth::user();
        $activeBreak = $this->breakService->getActiveBreak($user);
        $stats = $this->breakService->getUserStats($user);
        $myHistory = $this->breakService->getUserHistory($user);

        $data = [
            'user' => $user,
            'activeBreak' => $activeBreak,
            'stats' => $stats,
            'myHistory' => $myHistory,
        ];

        $performance = [
            'compliance' => $user->getComplianceRate(),
            'daily_breaks' => $user->getDailyBreaks(),
            'weekly_total' => $user->getWeeklyStats()['total'],
            'weekly_overbreaks' => $user->getWeeklyStats()['overbreaks'],
            'avg_15m' => $user->getAverageDuration('15m'),
            'avg_60m' => $user->getAverageDuration('60m'),
            'score' => $user->getPerformanceScore(),
        ];
        $data['performance'] = $performance;

        if (!$user->isAdmin()) {
            $data['peerStats'] = $this->breakService->getPeerStats($user);
        }

        // Team Lead data
        if ($user->isTeamLead() || $user->isAdmin()) {
            $data['teamBreaks'] = $this->breakService->getTeamBreaks($user->email);
            $data['teamHistory'] = $this->breakService->getTeamHistory($user->email);
            $data['teamOverbreaks'] = $data['teamHistory']->where('over_minutes', '>', 0);
        }

        // Admin data
        if ($user->isAdmin()) {
            $data['allActiveBreaks'] = \App\Models\ActiveBreak::orderBy('started_at', 'desc')->get();
            $data['allHistory'] = \App\Models\BreakHistory::orderBy('started_at', 'desc')->limit(500)->get();
        }

        return view('dashboard', $data);
    }

    public function startBreak(Request $request): RedirectResponse
    {
        $request->validate([
            'type' => 'required|in:15m,60m',
            'break_category' => 'sometimes|in:break,lunch',
        ]);

        try {
            $this->breakService->startBreak(
                Auth::user(),
                $request->input('type'),
                $request->input('break_category', 'break')
            );
            return redirect()->back()->with('success', 'Break started successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function endBreak(): RedirectResponse
    {
        try {
            $this->breakService->endBreak(Auth::user());
            return redirect()->back()->with('success', 'Break ended successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function liveData(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $stats = $this->breakService->getUserStats($user);

        if ($user->isAdmin()) {
            $breaks = \App\Models\ActiveBreak::orderBy('started_at', 'desc')->get();
        } elseif ($user->isTeamLead()) {
            $breaks = $this->breakService->getTeamBreaks($user->email);
        } else {
            $breaks = collect([]);
        }

        return response()->json([
            'breaks' => $breaks,
            'profile' => $user,
            'stats' => $stats,
        ]);
    }

    public function history(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $filters = [
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'search' => $request->input('search'),
        ];

        if ($user->isAdmin() && $request->input('scope') === 'all') {
            $rows = \App\Models\BreakHistory::query()
                ->when($filters['from'], fn($q) => $q->whereDate('started_at', '>=', $filters['from']))
                ->when($filters['to'], fn($q) => $q->whereDate('started_at', '<=', $filters['to']))
                ->when($filters['search'], fn($q) => $q->where('user_name', 'like', '%' . $filters['search'] . '%'))
                ->orderBy('started_at', 'desc')
                ->limit(500)
                ->get();
        } elseif ($user->isTeamLead()) {
            $rows = $this->breakService->getTeamHistory($user->email, $filters);
        } else {
            $rows = $this->breakService->getUserHistory($user, $filters);
        }

        return response()->json(['rows' => $rows]);
    }

    public function resetDevice(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return redirect()->back()->with('error', 'Forbidden.');
        }

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        \App\Models\TrustedDevice::whereHas('user', function ($q) use ($request) {
            $q->where('email', $request->input('email'));
        })->delete();

        return redirect()->back()->with('success', 'Device reset for ' . $request->input('email'));
    }
}
