<?php

namespace App\Services;

use App\Events\BreakEnded;
use App\Events\BreakOverbreakAlert;
use App\Events\BreakStarted;
use App\Models\ActiveBreak;
use App\Models\BreakHistory;
use App\Models\User;
use Illuminate\Support\Str;

class BreakService
{
    public const BREAK_TYPES = [
        '15m' => ['allowed_minutes' => 15, 'break_label' => '15-Minute Break'],
        '60m' => ['allowed_minutes' => 60, 'break_label' => '1-Hour Break'],
    ];

    public const BREAK_CATEGORIES = ['break', 'lunch'];

    public function startBreak(User $user, string $type, string $category = 'break'): ActiveBreak
    {
        if ($user->isAdmin()) {
            throw new \Exception('Admins cannot start breaks.');
        }

        if ($this->hasActiveBreak($user)) {
            throw new \Exception('You already have an active break.');
        }

        $breakType = self::BREAK_TYPES[$type] ?? null;
        if (!$breakType) {
            throw new \Exception('Invalid break type.');
        }

        $breakCategory = in_array($category, self::BREAK_CATEGORIES) ? $category : 'break';

        $breakId = 'BRK-' . Str::upper(Str::random(8));

        $activeBreak = ActiveBreak::create([
            'break_id' => $breakId,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'department' => $user->department,
            'tl_email' => $user->tl_email,
            'break_type' => $type,
            'break_category' => $breakCategory,
            'break_label' => $breakType['break_label'],
            'allowed_minutes' => $breakType['allowed_minutes'],
            'started_at' => now(),
            'expected_end_at' => now()->addMinutes($breakType['allowed_minutes']),
        ]);

        event(new BreakStarted($activeBreak));

        return $activeBreak;
    }

    public function endBreak(User $user): ?BreakHistory
    {
        $activeBreak = ActiveBreak::where('user_id', $user->id)->first();

        if (!$activeBreak) {
            throw new \Exception('No active break found.');
        }

        $endedAt = now();
        $durationSeconds = $endedAt->diffInSeconds($activeBreak->started_at);
        $durationMinutes = (int) floor($durationSeconds / 60);
        $allowedSeconds = $activeBreak->allowed_minutes * 60;
        $overSeconds = max(0, $durationSeconds - $allowedSeconds);
        $overMinutes = (int) floor($overSeconds / 60);

        $breakHistory = BreakHistory::create([
            'break_id' => $activeBreak->break_id,
            'user_id' => $activeBreak->user_id,
            'user_name' => $activeBreak->user_name,
            'user_email' => $activeBreak->user_email,
            'department' => $activeBreak->department,
            'tl_email' => $activeBreak->tl_email,
            'break_type' => $activeBreak->break_type,
            'break_category' => $activeBreak->break_category,
            'break_label' => $activeBreak->break_label,
            'allowed_minutes' => $activeBreak->allowed_minutes,
            'started_at' => $activeBreak->started_at,
            'ended_at' => $endedAt,
            'duration_minutes' => $durationMinutes,
            'duration_seconds' => $durationSeconds,
            'over_minutes' => $overMinutes,
        ]);

        event(new BreakEnded($breakHistory));

        if ($overMinutes > 0) {
            event(new BreakOverbreakAlert($breakHistory));
        }

        $activeBreak->delete();

        return $breakHistory;
    }

    public function hasActiveBreak(User $user): bool
    {
        return ActiveBreak::where('user_id', $user->id)->exists();
    }

    public function getActiveBreak(User $user): ?ActiveBreak
    {
        return ActiveBreak::where('user_id', $user->id)->first();
    }

    public function getTeamBreaks(string $tlEmail): \Illuminate\Database\Eloquent\Collection
    {
        return ActiveBreak::where('tl_email', $tlEmail)->get();
    }

    public function getTeamHistory(string $tlEmail, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = BreakHistory::where('tl_email', $tlEmail);

        if (!empty($filters['from'])) {
            $query->whereDate('started_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->whereDate('started_at', '<=', $filters['to']);
        }
        if (!empty($filters['search'])) {
            $query->where('user_name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('started_at', 'desc')->get();
    }

    public function getUserHistory(User $user, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = BreakHistory::where('user_id', $user->id);

        if (!empty($filters['from'])) {
            $query->whereDate('started_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->whereDate('started_at', '<=', $filters['to']);
        }

        return $query->orderBy('started_at', 'desc')->get();
    }

    public function getUserStats(User $user, ?string $from = null, ?string $to = null): array
    {
        $query = BreakHistory::where('user_id', $user->id);

        if ($from) {
            $query->whereDate('started_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('started_at', '<=', $to);
        }

        $breaks = $query->get();

        $stats15 = $breaks->where('break_type', '15m');
        $stats60 = $breaks->where('break_type', '60m');

        return [
            'count_15m' => $stats15->count(),
            'count_60m' => $stats60->count(),
            'overbreaks_count' => $breaks->where('over_minutes', '>', 0)->count(),
            'overbreaks_15m' => $stats15->where('over_minutes', '>', 0)->count(),
            'overbreaks_60m' => $stats60->where('over_minutes', '>', 0)->count(),
            'total_over_minutes' => $breaks->sum('over_minutes'),
        ];
    }

    public function getPeerStats(User $user): array
    {
        $teamUsers = User::where('tl_email', $user->tl_email)
            ->where('id', '!=', $user->id)
            ->pluck('id');

        $deptUsers = User::where('department', $user->department)
            ->where('id', '!=', $user->id)
            ->pluck('id');

        $teamHistory = BreakHistory::whereIn('user_id', $teamUsers)
            ->where('started_at', '>=', now()->subDays(30))
            ->get();

        $deptHistory = BreakHistory::whereIn('user_id', $deptUsers)
            ->where('started_at', '>=', now()->subDays(30))
            ->get();

        $myHistory = $user->breakHistory()
            ->where('started_at', '>=', now()->subDays(30))
            ->get();

        // Per-type break counts
        $my15m = $myHistory->where('break_type', '15m')->count();
        $my60m = $myHistory->where('break_type', '60m')->count();
        $myOverbreaks = $myHistory->where('over_minutes', '>', 0)->count();
        $myCompliance = $user->getComplianceRate();

        $team15mAvg = $teamHistory->where('break_type', '15m')->count() / max(1, $teamUsers->count());
        $team60mAvg = $teamHistory->where('break_type', '60m')->count() / max(1, $teamUsers->count());
        $teamOverbreaksAvg = $teamHistory->where('over_minutes', '>', 0)->count() / max(1, $teamUsers->count());

        $dept15mAvg = $deptHistory->where('break_type', '15m')->count() / max(1, $deptUsers->count());
        $dept60mAvg = $deptHistory->where('break_type', '60m')->count() / max(1, $deptUsers->count());
        $deptOverbreaksAvg = $deptHistory->where('over_minutes', '>', 0)->count() / max(1, $deptUsers->count());

        // Team compliance calculation
        $teamTotal = $teamHistory->count();
        $teamOnTime = $teamHistory->where('over_minutes', 0)->count();
        $teamCompliance = $teamTotal > 0 ? round(($teamOnTime / $teamTotal) * 100) : 100;

        $deptTotal = $deptHistory->count();
        $deptOnTime = $deptHistory->where('over_minutes', 0)->count();
        $deptCompliance = $deptTotal > 0 ? round(($deptOnTime / $deptTotal) * 100) : 100;

        // Compliance rank within team (percentile)
        $allTeamUsers = User::where('tl_email', $user->tl_email)->pluck('id');
        $rank = 1;
        foreach ($allTeamUsers as $uid) {
            $otherCompliance = (new User())->find($uid)->getComplianceRate();
            if ($otherCompliance > $myCompliance) {
                $rank++;
            }
        }
        $teamSize = $allTeamUsers->count();
        $percentile = $teamSize > 1 ? round((1 - ($rank - 1) / ($teamSize - 1)) * 100) : 100;

        return [
            'my' => [
                'count_15m' => $my15m,
                'count_60m' => $my60m,
                'overbreaks' => $myOverbreaks,
                'compliance' => $myCompliance,
            ],
            'team' => [
                'count_15m_avg' => round($team15mAvg, 1),
                'count_60m_avg' => round($team60mAvg, 1),
                'overbreaks_avg' => round($teamOverbreaksAvg, 1),
                'compliance' => $teamCompliance,
                'percentile' => $percentile,
            ],
            'department' => [
                'count_15m_avg' => round($dept15mAvg, 1),
                'count_60m_avg' => round($dept60mAvg, 1),
                'overbreaks_avg' => round($deptOverbreaksAvg, 1),
                'compliance' => $deptCompliance,
            ],
        ];
    }
}
