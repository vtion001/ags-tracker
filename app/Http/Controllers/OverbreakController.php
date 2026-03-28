<?php

namespace App\Http\Controllers;

use App\Models\ActiveBreak;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OverbreakController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isTeamLead()) {
            abort(403, 'Unauthorized');
        }

        $filters = [
            'department' => $request->input('department'),
            'role' => $request->input('role'),
            'status' => $request->input('status', 'all'),
        ];

        $query = ActiveBreak::query()->with('user');

        if ($user->isTeamLead()) {
            $query->where('tl_email', $user->email);
        }

        if ($filters['department']) {
            $query->where('department', $filters['department']);
        }

        $activeBreaks = $query->orderBy('started_at', 'desc')->get();

        $now = now();
        $onBreak = collect();
        $overbreak = collect();

        foreach ($activeBreaks as $break) {
            if ($now->greaterThan($break->expected_end_at)) {
                $overbreak->push($break);
            } else {
                $onBreak->push($break);
            }
        }

        $departments = User::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department')
            ->sort()
            ->values();

        $overbreakStats = [
            'total' => $overbreak->count(),
            'agents' => $overbreak->pluck('user_name')->unique()->count(),
            'total_over_minutes' => $overbreak->sum(function ($break) use ($now) {
                return (int) floor($now->diffInMinutes($break->expected_end_at));
            }),
        ];

        return view('overbreaks', [
            'user' => $user,
            'onBreak' => $onBreak,
            'overbreak' => $overbreak,
            'overbreakStats' => $overbreakStats,
            'departments' => $departments,
            'filters' => $filters,
        ]);
    }

    public function liveData(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isTeamLead()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = ActiveBreak::query();

        if ($user->isTeamLead()) {
            $query->where('tl_email', $user->email);
        }

        $activeBreaks = $query->orderBy('started_at', 'desc')->get();

        $now = now();
        $onBreak = [];
        $overbreak = [];

        foreach ($activeBreaks as $break) {
            $breakData = [
                'id' => $break->break_id,
                'user_name' => $break->user_name,
                'user_email' => $break->user_email,
                'department' => $break->department,
                'break_type' => $break->break_type,
                'break_label' => $break->break_label,
                'started_at' => $break->started_at->toISOString(),
                'expected_end_at' => $break->expected_end_at->toISOString(),
                'elapsed_minutes' => (int) floor($now->diffInMinutes($break->started_at)),
                'over_minutes' => $now->greaterThan($break->expected_end_at)
                    ? (int) floor($now->diffInMinutes($break->expected_end_at))
                    : 0,
            ];

            if ($now->greaterThan($break->expected_end_at)) {
                $overbreak[] = $breakData;
            } else {
                $onBreak[] = $breakData;
            }
        }

        return response()->json([
            'on_break' => $onBreak,
            'overbreak' => $overbreak,
            'timestamp' => $now->toISOString(),
        ]);
    }
}
