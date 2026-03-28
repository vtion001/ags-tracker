# Agent Dashboard Enhancement Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Enhance the agent dashboard with expanded work info, personal performance metrics, peer benchmarking, and a support ticket system.

**Architecture:** Add new dashboard sections (cards) to the existing Blade template. Performance calculations live on the User model as helper methods. Peer stats extend BreakService. Ticket system is a new resource with its own controller, models, and views.

**Tech Stack:** Laravel 11, Blade templates, SQLite/Supabase, existing BreakService pattern.

---

## Task 1: User Model Performance Helpers

**Files:**
- Modify: `app/Models/User.php`

Add the following methods to the User model. Place them after the existing `disableTotp()` method, before the closing brace.

- [ ] **Step 1: Add helper methods to User model**

Open `app/Models/User.php` and add these methods after `disableTotp()`:

```php
public function getComplianceRate(int $days = 30): int
{
    $total = $this->breakHistory()
        ->where('started_at', '>=', now()->subDays($days))
        ->count();
    if ($total === 0) {
        return 100;
    }
    $onTime = $this->breakHistory()
        ->where('started_at', '>=', now()->subDays($days))
        ->where('over_minutes', 0)
        ->count();
    return (int) round(($onTime / $total) * 100);
}

public function getDailyBreaks(): int
{
    return $this->breakHistory()
        ->whereDate('started_at', today())
        ->count();
}

public function getWeeklyStats(): array
{
    $breaks = $this->breakHistory()
        ->whereBetween('started_at', [now()->startOfWeek(), now()->endOfWeek()])
        ->get();
    return [
        'total' => $breaks->count(),
        'overbreaks' => $breaks->where('over_minutes', '>', 0)->count(),
    ];
}

public function getAverageDuration(string $breakType): float
{
    $breaks = $this->breakHistory()
        ->where('break_type', $breakType)
        ->where('started_at', '>=', now()->subDays(30))
        ->whereNotNull('duration_minutes')
        ->get();
    if ($breaks->isEmpty()) {
        return 0.0;
    }
    return round($breaks->avg('duration_minutes'), 1);
}

public function getPerformanceScore(): int
{
    $compliance = $this->getComplianceRate();
    $avg15 = $this->getAverageDuration('15m');
    $avg60 = $this->getAverageDuration('60m');
    $weeklyStats = $this->getWeeklyStats();

    // avg_duration_score: 100 = perfect (15m avg = 15, 60m avg = 60), decreases as overage grows
    $avg15Score = $avg15 > 0 ? max(0, 100 - (($avg15 - 15) * 10)) : 100;
    $avg60Score = $avg60 > 0 ? max(0, 100 - (($avg60 - 60) * 5)) : 100;
    $avgDurationScore = ($avg15Score + $avg60Score) / 2;

    // overbreak_ratio: 100 = no overbreaks, decreases with each overbreak
    $weekTotal = $weeklyStats['total'] > 0 ? $weeklyStats['total'] : 1;
    $overbreakRatio = max(0, 100 - ($weeklyStats['overbreaks'] / $weekTotal * 100));

    $score = ($compliance * 0.5) + ($avgDurationScore * 0.3) + ($overbreakRatio * 0.2);
    return (int) min(100, max(0, round($score)));
}
```

- [ ] **Step 2: Verify the file still works**

Run: `php artisan tinker --execute="echo App\Models\User::first()->getPerformanceScore();"`
Expected: Integer output (e.g., "94")

- [ ] **Step 3: Commit**

```bash
git add app/Models/User.php
git commit -m "feat: add performance metric helpers to User model

Add getComplianceRate(), getDailyBreaks(), getWeeklyStats(),
getAverageDuration(), getPerformanceScore() methods.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>
```

---

## Task 2: BreakService Peer Stats

**Files:**
- Modify: `app/Services/BreakService.php`

Add peer benchmarking methods to BreakService. Place them after the existing `getUserStats()` method.

- [ ] **Step 1: Add peer stats methods to BreakService**

Open `app/Services/BreakService.php` and add after the closing brace of `getUserStats()` (before the final `}`):

```php
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
```

- [ ] **Step 2: Verify the method works**

Run: `php artisan tinker --execute="print_r(app(App\Services\BreakService::class)->getPeerStats(App\Models\User::first()));"`
Expected: Array with my, team, and department keys

- [ ] **Step 3: Commit**

```bash
git add app/Services/BreakService.php
git commit -m "feat: add peer benchmarking stats to BreakService

Add getPeerStats() method that calculates user's break stats
vs team and department averages, including compliance percentile rank.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>
```

---

## Task 3: Dashboard — Enhanced Work Info Card

**Files:**
- Modify: `resources/views/dashboard.blade.php`

The existing "User Information" card is at lines ~934-961. Replace the entire card content.

- [ ] **Step 1: Find and replace the User Information card**

Find the section starting with `<!-- User Info -->` and ending with the closing `</section>`. Replace the `.card-body` content inside it with the new 2-column layout:

```blade
<!-- User Info -->
<section class="section">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">My Information</h2>
        </div>
        <div class="card-body">
            <div class="profile-overview-grid">
                <!-- Left: Identity -->
                <div class="profile-identity">
                    <div class="profile-avatar-lg">{{ substr($user->name, 0, 1) }}</div>
                    <div class="profile-name-lg">{{ $user->name }}</div>
                    <div class="profile-role-badge">{{ ucfirst($user->role) }}</div>
                    <div class="profile-dept">{{ $user->department ?? 'No Department' }}</div>
                </div>
                <!-- Right: Work Details -->
                <div class="profile-details-grid">
                    <div class="profile-detail-item">
                        <span class="detail-label">Shift Schedule</span>
                        <span class="detail-value">{{ $user->shift_schedule ?? 'Not set' }}</span>
                    </div>
                    <div class="profile-detail-item">
                        <span class="detail-label">Work Location</span>
                        <span class="detail-value">
                            <span class="location-badge location-{{ $user->work_location ?? 'office' }}">
                                @if($user->work_location === 'wfh')
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg> WFH
                                @elseif($user->work_location === 'hybrid')
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg> Hybrid
                                @else
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg> Office
                                @endif
                            </span>
                        </span>
                    </div>
                    <div class="profile-detail-item">
                        <span class="detail-label">Team Lead</span>
                        <span class="detail-value">
                            @if($user->tl_email)
                                <a href="mailto:{{ $user->tl_email }}" class="tl-email-link">
                                    {{ $user->manager_name ?? $user->tl_email }}
                                </a>
                            @else
                                Not assigned
                            @endif
                        </span>
                    </div>
                    <div class="profile-detail-item">
                        <span class="detail-label">Hire Date</span>
                        <span class="detail-value">
                            @if($user->hire_date)
                                {{ $user->hire_date->format('M j, Y') }} ({{ $user->getTenureMonths() }} months)
                            @else
                                Not set
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
```

- [ ] **Step 2: Add CSS for the new profile overview grid**

Find the `<style>` section in dashboard.blade.php and add these styles before the closing `</style>` tag:

```css
/* Profile Overview (Enhanced User Info Card) */
.profile-overview-grid {
    display: grid;
    grid-template-columns: 140px 1fr;
    gap: 24px;
    align-items: start;
}

.profile-identity {
    text-align: center;
}

.profile-avatar-lg {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    background: var(--navy-800);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 26px;
    font-weight: 700;
    color: var(--white);
}

.profile-name-lg {
    font-size: 15px;
    font-weight: 700;
    color: var(--gray-900);
    margin-bottom: 4px;
}

.profile-role-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    background: var(--gray-100);
    color: var(--gray-600);
    margin-bottom: 6px;
}

.profile-dept {
    font-size: 12px;
    color: var(--gray-500);
}

.profile-details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px 24px;
}

.profile-detail-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.detail-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--gray-500);
}

.detail-value {
    font-size: 13px;
    font-weight: 500;
    color: var(--gray-900);
}

.location-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.location-badge.location-office {
    background: var(--gray-100);
    color: var(--gray-700);
}

.location-badge.location-wfh {
    background: var(--green-50);
    color: var(--green-600);
}

.location-badge.location-hybrid {
    background: var(--yellow-50);
    color: var(--yellow-600);
}

.tl-email-link {
    color: var(--navy-800);
    text-decoration: none;
    font-weight: 500;
}

.tl-email-link:hover {
    text-decoration: underline;
}
```

- [ ] **Step 3: Verify in browser — refresh the dashboard**

Run: `php artisan serve`
Expected: New "My Information" card with avatar, shift, location badge, TL email link, hire date

- [ ] **Step 4: Commit**

```bash
git add resources/views/dashboard.blade.php
git commit -m "feat(dashboard): replace user info card with enhanced work info

Show avatar, shift schedule, work location badge with icons,
manager TL email link, and hire date with tenure.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>
```

---

## Task 4: Dashboard — Performance Metrics Card

**Files:**
- Modify: `resources/views/dashboard.blade.php`
- Modify: `app/Http/Controllers/BreakController.php`

The Performance Metrics card goes between the Break Control section and the My History section.

- [ ] **Step 1: Add performance card HTML to dashboard**

Find the closing `</section>` of the Agent Break Control section (after the Quick Stats card). Insert the new Performance Metrics card right after:

```blade
                    <!-- Performance Metrics -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Performance</h2>
                        </div>
                        <div class="card-body">
                            <div class="performance-grid">
                                <!-- Compliance Ring -->
                                <div class="perf-ring-col">
                                    <div class="perf-ring" id="compliance-ring">
                                        <svg viewBox="0 0 36 36" class="ring-svg">
                                            <path class="ring-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                            <path class="ring-fill" stroke-dasharray="0, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                        </svg>
                                        <div class="ring-label">
                                            <span class="ring-pct" id="perf-compliance" data-value="{{ $performance['compliance'] }}">--</span>
                                            <span class="ring-sub">%</span>
                                        </div>
                                    </div>
                                    <div class="perf-ring-title">Compliance</div>
                                </div>
                                <!-- Metrics Grid -->
                                <div class="perf-metrics-grid">
                                    <div class="perf-metric">
                                        <span class="perf-metric-value" id="perf-daily">{{ $performance['daily_breaks'] }}/5</span>
                                        <span class="perf-metric-label">Today</span>
                                    </div>
                                    <div class="perf-metric">
                                        <span class="perf-metric-value" id="perf-weekly">{{ $performance['weekly_total'] }}</span>
                                        <span class="perf-metric-label">This Week</span>
                                    </div>
                                    <div class="perf-metric">
                                        <span class="perf-metric-value" id="perf-avg15">{{ $performance['avg_15m'] }}m</span>
                                        <span class="perf-metric-label">Avg 15m</span>
                                    </div>
                                    <div class="perf-metric">
                                        <span class="perf-metric-value" id="perf-avg60">{{ $performance['avg_60m'] }}m</span>
                                        <span class="perf-metric-label">Avg 1hr</span>
                                    </div>
                                </div>
                                <!-- Performance Score -->
                                <div class="perf-score-col">
                                    <div class="perf-score" id="perf-score">{{ $performance['score'] }}</div>
                                    <div class="perf-score-label">Score</div>
                                    <div class="perf-score-bar">
                                        <div class="perf-score-fill" id="perf-score-fill" style="width: {{ $performance['score'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
```

- [ ] **Step 2: Add CSS for the Performance card**

Add to the `<style>` section in dashboard.blade.php:

```css
/* Performance Metrics Card */
.performance-grid {
    display: grid;
    grid-template-columns: 90px 1fr 100px;
    gap: 20px;
    align-items: center;
}

.perf-ring-col {
    text-align: center;
}

.perf-ring {
    position: relative;
    width: 80px;
    height: 80px;
    margin: 0 auto 6px;
}

.ring-svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.ring-bg {
    fill: none;
    stroke: var(--gray-200);
    stroke-width: 3;
}

.ring-fill {
    fill: none;
    stroke: var(--green-600);
    stroke-width: 3;
    stroke-linecap: round;
    transition: stroke-dasharray 0.6s ease, stroke 0.3s ease;
}

.ring-fill.warn {
    stroke: var(--yellow-600);
}

.ring-fill.danger {
    stroke: var(--red-600);
}

.ring-label {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1px;
}

.ring-pct {
    font-size: 18px;
    font-weight: 700;
    color: var(--gray-900);
    line-height: 1;
}

.ring-sub {
    font-size: 10px;
    font-weight: 600;
    color: var(--gray-500);
    margin-top: 2px;
}

.perf-ring-title {
    font-size: 11px;
    font-weight: 600;
    color: var(--gray-500);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.perf-metrics-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.perf-metric {
    background: var(--gray-50);
    border-radius: 8px;
    padding: 10px 14px;
    text-align: center;
}

.perf-metric-value {
    display: block;
    font-size: 18px;
    font-weight: 700;
    color: var(--gray-900);
    line-height: 1;
    margin-bottom: 2px;
}

.perf-metric-label {
    font-size: 10px;
    font-weight: 600;
    color: var(--gray-500);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.perf-score-col {
    text-align: center;
    padding: 10px;
    background: var(--gray-50);
    border-radius: 10px;
}

.perf-score {
    font-size: 32px;
    font-weight: 800;
    color: var(--gray-900);
    line-height: 1;
    margin-bottom: 4px;
}

.perf-score.good { color: var(--green-600); }
.perf-score.warn { color: var(--yellow-600); }
.perf-score.danger { color: var(--red-600); }

.perf-score-label {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--gray-500);
    margin-bottom: 8px;
}

.perf-score-bar {
    height: 4px;
    background: var(--gray-200);
    border-radius: 2px;
    overflow: hidden;
}

.perf-score-fill {
    height: 100%;
    background: var(--green-600);
    border-radius: 2px;
    transition: width 0.6s ease, background 0.3s ease;
}

.perf-score-fill.warn { background: var(--yellow-600); }
.perf-score-fill.danger { background: var(--red-600); }
```

- [ ] **Step 3: Pass performance data from BreakController**

Modify `BreakController::dashboard()` to pass performance data:

Find the `$data` array in `dashboard()` method and add:

```php
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
```

Also add the `$performance` default to the `@php` block at the top of dashboard.blade.php:

```blade
@php
    $activeBreak = $activeBreak ?? null;
    $stats = $stats ?? ['count_15m' => 0, 'count_60m' => 0, 'overbreaks_count' => 0, 'total_over_minutes' => 0, 'overbreaks_15m' => 0, 'overbreaks_60m' => 0];
    $performance = $performance ?? ['compliance' => 100, 'daily_breaks' => 0, 'weekly_total' => 0, 'weekly_overbreaks' => 0, 'avg_15m' => 0, 'avg_60m' => 0, 'score' => 100];
    // ... rest of existing @php block
@endphp
```

- [ ] **Step 4: Add JavaScript to color-code the performance elements**

Find the `<script>` section at the bottom of dashboard.blade.php. Add a function and call it on DOMContentLoaded:

```javascript
function updatePerformanceColors() {
    // Compliance ring
    var complianceEl = document.getElementById('perf-compliance');
    var compliance = parseInt(complianceEl.dataset.value || 0);
    var ring = document.querySelector('.ring-fill');
    if (ring) {
        ring.classList.remove('warn', 'danger');
        if (compliance < 70) {
            ring.classList.add('danger');
        } else if (compliance < 90) {
            ring.classList.add('warn');
        }
    }

    // Score
    var scoreEl = document.getElementById('perf-score');
    var scoreFill = document.getElementById('perf-score-fill');
    var score = parseInt(scoreEl.textContent) || 0;
    scoreEl.classList.remove('good', 'warn', 'danger');
    scoreFill.classList.remove('warn', 'danger');
    if (score < 60) {
        scoreEl.classList.add('danger');
        scoreFill.classList.add('danger');
    } else if (score < 80) {
        scoreEl.classList.add('warn');
        scoreFill.classList.add('warn');
    } else {
        scoreEl.classList.add('good');
    }
}

document.addEventListener('DOMContentLoaded', updatePerformanceColors);
```

- [ ] **Step 5: Commit**

```bash
git add resources/views/dashboard.blade.php app/Http/Controllers/BreakController.php
git commit -m "feat(dashboard): add performance metrics card

Add compliance ring, daily/weekly summary, average durations,
and composite performance score with color-coded indicators.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>
```

---

## Task 5: Dashboard — Peer Benchmarking Card

**Files:**
- Modify: `resources/views/dashboard.blade.php`
- Modify: `app/Http/Controllers/BreakController.php`

The Peer Benchmarking card goes after the Performance card.

- [ ] **Step 1: Add peer stats data to BreakController**

In `BreakController::dashboard()`, add after the `$data['performance']` line:

```php
if (!$user->isAdmin()) {
    $data['peerStats'] = $this->breakService->getPeerStats($user);
}
```

In the `@php` block at the top of dashboard.blade.php, add:

```blade
$peerStats = $peerStats ?? ['my' => ['count_15m' => 0, 'count_60m' => 0, 'overbreaks' => 0, 'compliance' => 100], 'team' => ['count_15m_avg' => 0, 'count_60m_avg' => 0, 'overbreaks_avg' => 0, 'compliance' => 100, 'percentile' => 0], 'department' => ['count_15m_avg' => 0, 'count_60m_avg' => 0, 'overbreaks_avg' => 0, 'compliance' => 100]];
```

- [ ] **Step 2: Add Peer Benchmarking card HTML**

Add after the Performance Metrics card HTML (still inside the `break-control-grid` div, after the Performance card):

```blade
                    <!-- Peer Benchmarking -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Peer Benchmarking</h2>
                            <div class="peer-toggle">
                                <button class="peer-toggle-btn active" data-scope="team" onclick="switchPeerScope('team')">My Team</button>
                                <button class="peer-toggle-btn" data-scope="dept" onclick="switchPeerScope('dept')">My Dept</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="peer-stats-table">
                                <div class="peer-row peer-header">
                                    <span>Metric</span>
                                    <span>You</span>
                                    <span id="peer-scope-label">Team Avg</span>
                                </div>
                                <div class="peer-row">
                                    <span class="peer-metric">15-min Breaks</span>
                                    <span class="peer-you">{{ $peerStats['my']['count_15m'] }}</span>
                                    <span class="peer-avg" id="peer-15m">{{ $peerStats['team']['count_15m_avg'] }}</span>
                                </div>
                                <div class="peer-row">
                                    <span class="peer-metric">1-hour Breaks</span>
                                    <span class="peer-you">{{ $peerStats['my']['count_60m'] }}</span>
                                    <span class="peer-avg" id="peer-60m">{{ $peerStats['team']['count_60m_avg'] }}</span>
                                </div>
                                <div class="peer-row">
                                    <span class="peer-metric">Overbreaks</span>
                                    <span class="peer-you">{{ $peerStats['my']['overbreaks'] }}</span>
                                    <span class="peer-avg" id="peer-over">{{ $peerStats['team']['overbreaks_avg'] }}</span>
                                </div>
                                <div class="peer-row">
                                    <span class="peer-metric">Compliance</span>
                                    <span class="peer-you">{{ $peerStats['my']['compliance'] }}%</span>
                                    <span class="peer-avg" id="peer-comp">{{ $peerStats['team']['compliance'] }}%</span>
                                </div>
                            </div>
                            <div class="peer-rank" id="peer-rank">
                                @php $pct = $peerStats['team']['percentile']; @endphp
                                @if($pct >= 70)
                                    <span class="rank-arrow up">&#8593;</span>
                                    <span>Top {{ 100 - $pct }}% of team</span>
                                @elseif($pct >= 40)
                                    <span class="rank-arrow mid">&#8594;</span>
                                    <span>Middle of team</span>
                                @else
                                    <span class="rank-arrow down">&#8595;</span>
                                    <span>Below average — improve compliance</span>
                                @endif
                            </div>
                        </div>
                    </div>
```

- [ ] **Step 3: Add CSS for Peer Benchmarking card**

Add to the `<style>` section:

```css
/* Peer Benchmarking Card */
.peer-toggle {
    display: flex;
    gap: 4px;
}

.peer-toggle-btn {
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid var(--gray-200);
    background: var(--white);
    font-size: 11px;
    font-weight: 500;
    color: var(--gray-600);
    cursor: pointer;
    transition: all 0.15s;
}

.peer-toggle-btn.active {
    background: var(--navy-800);
    color: var(--white);
    border-color: var(--navy-800);
}

.peer-stats-table {
    margin-bottom: 14px;
}

.peer-row {
    display: grid;
    grid-template-columns: 1fr 60px 70px;
    gap: 8px;
    padding: 8px 0;
    border-bottom: 1px solid var(--gray-100);
    font-size: 13px;
    align-items: center;
}

.peer-row:last-child { border-bottom: none; }

.peer-header {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--gray-500);
    border-bottom: 1px solid var(--gray-200) !important;
    padding-bottom: 8px;
}

.peer-metric {
    color: var(--gray-600);
}

.peer-you {
    font-weight: 700;
    color: var(--gray-900);
    text-align: center;
}

.peer-avg {
    color: var(--gray-500);
    text-align: right;
    font-size: 12px;
}

.peer-rank {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: var(--gray-50);
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: var(--gray-700);
}

.rank-arrow {
    font-size: 18px;
    font-weight: 700;
}

.rank-arrow.up { color: var(--green-600); }
.rank-arrow.mid { color: var(--yellow-600); }
.rank-arrow.down { color: var(--red-600); }
```

- [ ] **Step 4: Add JavaScript for peer scope toggle**

Add to the `<script>` section. Use safe DOM manipulation (textContent, setAttribute) instead of innerHTML:

```javascript
function switchPeerScope(scope) {
    // Update toggle buttons
    var btns = document.querySelectorAll('.peer-toggle-btn');
    for (var i = 0; i < btns.length; i++) {
        btns[i].classList.toggle('active', btns[i].dataset.scope === scope);
    }

    // Update label
    var labelEl = document.getElementById('peer-scope-label');
    labelEl.textContent = scope === 'team' ? 'Team Avg' : 'Dept Avg';

    // Get peer data from PHP - use safe textContent for numeric values
    var peerData = @json($peerStats);
    var data = scope === 'team' ? peerData.team : peerData.department;

    document.getElementById('peer-15m').textContent = data.count_15m_avg;
    document.getElementById('peer-60m').textContent = data.count_60m_avg;
    document.getElementById('peer-over').textContent = data.overbreaks_avg;
    document.getElementById('peer-comp').textContent = data.compliance + '%';

    // Update rank badge using safe DOM methods
    var rankEl = document.getElementById('peer-rank');
    rankEl.innerHTML = ''; // Safe here - we control the content

    var rankSpan = document.createElement('span');
    rankSpan.className = 'rank-arrow ' + (scope === 'team' && peerData.team.percentile >= 70 ? 'up' : scope === 'team' && peerData.team.percentile >= 40 ? 'mid' : 'down');
    rankSpan.textContent = scope === 'team' && peerData.team.percentile >= 70 ? '\u2191' : scope === 'team' && peerData.team.percentile >= 40 ? '\u2192' : '\u2193';

    var textNode = document.createTextNode(scope === 'team' && peerData.team.percentile >= 70
        ? 'Top ' + (100 - peerData.team.percentile) + '% of team'
        : scope === 'team' ? 'Middle of team' : 'Department comparison');

    rankEl.appendChild(rankSpan);
    rankEl.appendChild(textNode);
}
```

- [ ] **Step 5: Commit**

```bash
git add resources/views/dashboard.blade.php app/Http/Controllers/BreakController.php
git commit -m "feat(dashboard): add peer benchmarking card

Show user's break stats vs team and department averages
with toggle between scopes and compliance percentile rank.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>
```

---

## Task 6: Ticket System — Database Migration

**Files:**
- Create: `database/migrations/2026_03_29_create_support_tickets_table.php`
- Create: `database/migrations/2026_03_29_create_support_ticket_comments_table.php`

- [ ] **Step 1: Create support_tickets migration**

```bash
php artisan make:migration create_support_tickets_table
```

Edit the generated migration file in `database/migrations/`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('subject', 255);
            $table->enum('category', ['bug_error', 'feature_request', 'schedule_issue', 'access_problem', 'other']);
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->text('description');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
```

- [ ] **Step 2: Create support_ticket_comments migration**

```bash
php artisan make:migration create_support_ticket_comments_table
```

Edit the generated migration file:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_ticket_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('comment');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_comments');
    }
};
```

- [ ] **Step 3: Run migrations**

```bash
php artisan migrate
```

Expected output: "Migrated: ...create_support_tickets_table" and "Migrated: ...create_support_ticket_comments_table"

- [ ] **Step 4: Commit**

```bash
git add database/migrations/
git commit -m "feat(tickets): add support ticket system migrations

Create support_tickets and support_ticket_comments tables
with foreign keys, indexes, and cascade delete.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>
```

---

## Task 7: Ticket Models

**Files:**
- Create: `app/Models/SupportTicket.php`
- Create: `app/Models/SupportTicketComment.php`

- [ ] **Step 1: Create SupportTicket model**

```bash
php artisan make:model SupportTicket
```

Edit `app/Models/SupportTicket.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'category',
        'priority',
        'description',
        'status',
    ];

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    public const CATEGORY_BUG = 'bug_error';
    public const CATEGORY_FEATURE = 'feature_request';
    public const CATEGORY_SCHEDULE = 'schedule_issue';
    public const CATEGORY_ACCESS = 'access_problem';
    public const CATEGORY_OTHER = 'other';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(SupportTicketComment::class, 'ticket_id')->orderBy('created_at', 'asc');
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function scopeForUser($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }
        if ($user->isTeamLead()) {
            return $query->whereHas('user', function ($q) use ($user) {
                $q->where('tl_email', $user->email);
            });
        }
        return $query->where('user_id', $user->id);
    }
}
```

- [ ] **Step 2: Create SupportTicketComment model**

```bash
php artisan make:model SupportTicketComment
```

Edit `app/Models/SupportTicketComment.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketComment extends Model
{
    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'comment',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

- [ ] **Step 3: Verify models work**

```bash
php artisan tinker --execute="echo App\Models\SupportTicket::class; echo App\Models\SupportTicketComment::class;"
```

Expected: No errors

- [ ] **Step 4: Commit**

```bash
git add app/Models/SupportTicket.php app/Models/SupportTicketComment.php
git commit -m "feat(tickets): add SupportTicket and SupportTicketComment models

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>
```

---

## Task 8: Ticket Controller and Routes

**Files:**
- Create: `app/Http/Controllers/TicketController.php`
- Create: `app/Http/Requests/StoreTicketRequest.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create StoreTicketRequest**

```bash
php artisan make:request StoreTicketRequest
```

Edit `app/Http/Requests/StoreTicketRequest.php`:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:bug_error,feature_request,schedule_issue,access_problem,other'],
            'priority' => ['required', 'in:low,medium,high'],
            'description' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'Please enter a subject for your ticket.',
            'category.required' => 'Please select a category.',
            'priority.required' => 'Please select a priority level.',
            'description.required' => 'Please describe your issue or feedback.',
        ];
    }
}
```

- [ ] **Step 2: Create TicketController**

```bash
php artisan make:controller TicketController
```

Edit `app/Http/Controllers/TicketController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Models\SupportTicket;
use App\Models\SupportTicketComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $tickets = SupportTicket::forUser($user)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tickets.index', [
            'tickets' => $tickets,
            'user' => $user,
        ]);
    }

    public function create(): View
    {
        return view('tickets.create');
    }

    public function store(StoreTicketRequest $request): \Illuminate\Http\RedirectResponse
    {
        SupportTicket::create([
            'user_id' => Auth::id(),
            'subject' => $request->input('subject'),
            'category' => $request->input('category'),
            'priority' => $request->input('priority'),
            'description' => $request->input('description'),
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket submitted successfully.');
    }

    public function show(int $id): View
    {
        $user = Auth::user();
        $ticket = SupportTicket::findOrFail($id);

        // Authorization check
        if (!$user->isAdmin()) {
            if ($user->isTeamLead()) {
                $ticketUser = $ticket->user;
                if ($ticketUser->tl_email !== $user->email) {
                    abort(403);
                }
            } else {
                if ($ticket->user_id !== $user->id) {
                    abort(403);
                }
            }
        }

        return view('tickets.show', [
            'ticket' => $ticket,
            'user' => $user,
        ]);
    }

    public function addComment(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'comment' => ['required', 'string', 'max:5000'],
        ]);

        $ticket = SupportTicket::findOrFail($id);
        $user = Auth::user();

        // Authorization
        if (!$user->isAdmin() && $ticket->user_id !== $user->id) {
            abort(403);
        }

        SupportTicketComment::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => $request->input('comment'),
        ]);

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Comment added.');
    }

    public function updateStatus(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        $ticket = SupportTicket::findOrFail($id);
        $user = Auth::user();

        // Only admin/TL can change status
        if (!$user->isAdmin() && !$user->isTeamLead()) {
            abort(403);
        }

        // Team leads can only update their team's tickets
        if ($user->isTeamLead() && $ticket->user->tl_email !== $user->email) {
            abort(403);
        }

        $ticket->update(['status' => $request->input('status')]);

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Ticket status updated.');
    }
}
```

- [ ] **Step 3: Add routes to web.php**

Add to `routes/web.php` inside the `auth` middleware group (around line 38-62):

```php
// Support Tickets
Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show')->where('id', '[0-9]+');
Route::post('/tickets/{id}/comment', [TicketController::class, 'addComment'])->name('tickets.comment')->where('id', '[0-9]+');
Route::post('/tickets/{id}/status', [TicketController::class, 'updateStatus'])->name('tickets.status')->where('id', '[0-9]+');
```

Also add the import at the top of routes/web.php:

```php
use App\Http\Controllers\TicketController;
```

- [ ] **Step 4: Verify routes work**

```bash
php artisan route:list --name=tickets
```

Expected: 6 ticket routes listed

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/TicketController.php app/Http/Requests/StoreTicketRequest.php routes/web.php
git commit -m "feat(tickets): add TicketController and routes

Support ticket CRUD: index, create, store, show, addComment, updateStatus.
Authorization: agents see own tickets, TLs see team tickets, admins see all.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>
```

---

## Task 9: Ticket Views

**Files:**
- Create: `resources/views/tickets/index.blade.php`
- Create: `resources/views/tickets/create.blade.php`
- Create: `resources/views/tickets/show.blade.php`

- [ ] **Step 1: Create tickets directory**

```bash
mkdir -p resources/views/tickets
```

- [ ] **Step 2: Create tickets/index.blade.php**

Base it on the dashboard's style (use the same CSS variables and card patterns). This view lists all tickets for the user.

```blade
<x-app-layout>
<style>
    :root {
        --navy-800: #0f2847;
        --gray-900: #171717;
        --gray-800: #262626;
        --gray-700: #374151;
        --gray-600: #4b5563;
        --gray-500: #6b7280;
        --gray-400: #9ca3af;
        --gray-300: #d1d5db;
        --gray-200: #e5e7eb;
        --gray-100: #f3f4f6;
        --gray-50: #f9fafb;
        --white: #ffffff;
        --green-600: #16a34a;
        --green-50: #f0fdf4;
        --red-600: #dc2626;
        --red-50: #fef2f2;
        --yellow-600: #ca8a04;
        --yellow-50: #fefce8;
        --blue-600: #2563eb;
        --blue-50: #eff6ff;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html { font-size: 14px; }
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: var(--gray-100);
        color: var(--gray-900);
        min-height: 100vh;
    }
    .app-layout { display: flex; min-height: 100vh; }
    .sidebar { width: 240px; background: var(--gray-900); position: fixed; top: 0; left: 0; bottom: 0; display: flex; flex-direction: column; }
    .sidebar-brand { padding: 20px; border-bottom: 1px solid var(--gray-800); color: var(--white); font-size: 15px; font-weight: 600; display: flex; align-items: center; gap: 12px; }
    .sidebar-logo { width: 36px; height: 36px; border-radius: 8px; filter: brightness(0) invert(1); }
    .sidebar-nav { flex: 1; padding: 16px 12px; }
    .nav-section { margin-bottom: 24px; }
    .nav-section-title { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: var(--gray-500); padding: 0 12px; margin-bottom: 8px; }
    .nav-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 6px; color: var(--gray-400); text-decoration: none; font-size: 13px; font-weight: 500; transition: all 0.15s; }
    .nav-item:hover { background: var(--gray-800); color: var(--white); }
    .nav-item.active { background: var(--gray-800); color: var(--white); }
    .nav-item svg { width: 18px; height: 18px; }
    .sidebar-footer { padding: 16px; border-top: 1px solid var(--gray-800); }
    .user-card { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 6px; background: var(--gray-800); }
    .user-avatar { width: 32px; height: 32px; border-radius: 6px; background: var(--gray-700); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; color: var(--white); }
    .user-info { flex: 1; }
    .user-name { font-size: 12px; font-weight: 500; color: var(--white); }
    .user-role { font-size: 11px; color: var(--gray-500); }
    .main-content { flex: 1; margin-left: 240px; }
    .topbar { height: 56px; background: var(--white); border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center; padding: 0 24px; position: sticky; top: 0; z-index: 50; }
    .page-title { font-size: 15px; font-weight: 600; }
    .page-content { padding: 24px; max-width: 900px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .page-header h2 { font-size: 20px; font-weight: 600; color: var(--gray-900); }
    .page-header p { font-size: 13px; color: var(--gray-500); margin-top: 4px; }
    .btn { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 18px; border-radius: 999px; font-size: 13px; font-weight: 500; border: 1px solid transparent; cursor: pointer; transition: all 0.15s; text-decoration: none; justify-content: center; }
    .btn-primary { background: var(--navy-800); color: var(--white); }
    .btn-primary:hover { background: #0d2240; }
    .btn-secondary { background: var(--white); color: var(--gray-700); border-color: var(--gray-300); }
    .btn-secondary:hover { background: var(--gray-50); }
    .alert { padding: 12px 14px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; }
    .alert-success { background: var(--green-50); border: 1px solid #bbf7d0; color: var(--green-600); }
    .ticket-filters { display: flex; gap: 6px; margin-bottom: 16px; }
    .filter-tab { padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 500; border: 1px solid var(--gray-200); background: var(--white); color: var(--gray-600); cursor: pointer; text-decoration: none; }
    .filter-tab:hover { background: var(--gray-50); }
    .filter-tab.active { background: var(--navy-800); color: var(--white); border-color: var(--navy-800); }
    .ticket-table { background: var(--white); border: 1px solid var(--gray-200); border-radius: 8px; overflow: hidden; }
    .ticket-table-header { display: grid; grid-template-columns: 60px 1fr 130px 80px 90px 100px; gap: 12px; padding: 12px 16px; background: var(--gray-50); border-bottom: 1px solid var(--gray-200); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); }
    .ticket-row { display: grid; grid-template-columns: 60px 1fr 130px 80px 90px 100px; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--gray-100); align-items: center; transition: background 0.15s; cursor: pointer; text-decoration: none; color: inherit; }
    .ticket-row:last-child { border-bottom: none; }
    .ticket-row:hover { background: var(--gray-50); }
    .ticket-id { font-size: 12px; font-weight: 600; color: var(--gray-500); }
    .ticket-subject { font-size: 13px; font-weight: 500; color: var(--gray-900); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ticket-category { font-size: 11px; color: var(--gray-500); }
    .ticket-date { font-size: 12px; color: var(--gray-500); }
    .status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; }
    .status-badge.open { background: var(--yellow-50); color: var(--yellow-600); }
    .status-badge.in_progress { background: var(--blue-50); color: var(--blue-600); }
    .status-badge.resolved { background: var(--green-50); color: var(--green-600); }
    .status-badge.closed { background: var(--gray-100); color: var(--gray-500); }
    .priority-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; text-transform: uppercase; }
    .priority-badge.high { background: var(--red-50); color: var(--red-600); }
    .priority-badge.medium { background: var(--yellow-50); color: var(--yellow-600); }
    .priority-badge.low { background: var(--gray-100); color: var(--gray-500); }
    .empty-state { padding: 48px; text-align: center; color: var(--gray-500); font-size: 13px; }
    .empty-icon { width: 40px; height: 40px; margin: 0 auto 12px; opacity: 0.4; }
    @media (max-width: 768px) {
        .sidebar { display: none; }
        .main-content { margin-left: 0; }
        .page-content { padding: 16px; }
        .ticket-table-header { display: none; }
        .ticket-row { grid-template-columns: 1fr; gap: 4px; }
    }
</style>

<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('agslogo-128.png') }}" alt="AGS" class="sidebar-logo" onerror="this.style.display='none'">
            AGS Break Tracker
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Menu</div>
                <a href="{{ route('dashboard') }}" class="nav-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('profile.edit') }}" class="nav-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Profile
                </a>
                <a href="{{ route('tickets.index') }}" class="nav-item active">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>
                    Support
                </a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar">{{ substr($user->name, 0, 1) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ $user->name }}</div>
                    <div class="user-role">{{ ucfirst($user->role) }}</div>
                </div>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h1 class="page-title">Support Tickets</h1>
            <a href="{{ route('tickets.create') }}" class="btn btn-primary">+ New Ticket</a>
        </header>
        <div class="page-content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="ticket-filters">
                <a href="{{ route('tickets.index') }}" class="filter-tab {{ !request('status') ? 'active' : '' }}">All</a>
                <a href="{{ route('tickets.index', ['status' => 'open']) }}" class="filter-tab {{ request('status') == 'open' ? 'active' : '' }}">Open</a>
                <a href="{{ route('tickets.index', ['status' => 'in_progress']) }}" class="filter-tab {{ request('status') == 'in_progress' ? 'active' : '' }}">In Progress</a>
                <a href="{{ route('tickets.index', ['status' => 'resolved']) }}" class="filter-tab {{ request('status') == 'resolved' ? 'active' : '' }}">Resolved</a>
            </div>

            @if($tickets->isEmpty())
                <div class="ticket-table">
                    <div class="empty-state">
                        <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>
                        <strong>No tickets yet</strong>
                        <p style="margin-top: 4px;">Submit a ticket if you have feedback or need help.</p>
                    </div>
                </div>
            @else
                <div class="ticket-table">
                    <div class="ticket-table-header">
                        <span>ID</span>
                        <span>Subject</span>
                        <span>Category</span>
                        <span>Priority</span>
                        <span>Status</span>
                        <span>Date</span>
                    </div>
                    @foreach($tickets as $ticket)
                        <a href="{{ route('tickets.show', $ticket->id) }}" class="ticket-row">
                            <span class="ticket-id">#{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}</span>
                            <span class="ticket-subject">{{ $ticket->subject }}</span>
                            <span class="ticket-category">{{ str_replace('_', ' ', ucfirst($ticket->category)) }}</span>
                            <span class="priority-badge {{ $ticket->priority }}">{{ $ticket->priority }}</span>
                            <span class="status-badge {{ $ticket->status }}">{{ str_replace('_', ' ', ucfirst($ticket->status)) }}</span>
                            <span class="ticket-date">{{ $ticket->created_at->format('M j') }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </main>
</div>
</x-app-layout>
```

- [ ] **Step 3: Create tickets/create.blade.php**

```blade
<x-app-layout>
<style>
    :root { --navy-800: #0f2847; --gray-900: #171717; --gray-800: #262626; --gray-700: #374151; --gray-600: #4b5563; --gray-500: #6b7280; --gray-400: #9ca3af; --gray-300: #d1d5db; --gray-200: #e5e7eb; --gray-100: #f3f4f6; --gray-50: #f9fafb; --white: #ffffff; --green-600: #16a34a; --green-50: #f0fdf4; --red-600: #dc2626; --red-50: #fef2f2; --yellow-600: #ca8a04; --yellow-50: #fefce8; }
    * { box-sizing: border-box; margin: 0; padding: 0; } html { font-size: 14px; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--gray-100); color: var(--gray-900); min-height: 100vh; }
    .app-layout { display: flex; min-height: 100vh; } .sidebar { width: 240px; background: var(--gray-900); position: fixed; top: 0; left: 0; bottom: 0; display: flex; flex-direction: column; }
    .sidebar-brand { padding: 20px; border-bottom: 1px solid var(--gray-800); color: var(--white); font-size: 15px; font-weight: 600; display: flex; align-items: center; gap: 12px; }
    .sidebar-nav { flex: 1; padding: 16px 12px; } .nav-section { margin-bottom: 24px; }
    .nav-section-title { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: var(--gray-500); padding: 0 12px; margin-bottom: 8px; }
    .nav-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 6px; color: var(--gray-400); text-decoration: none; font-size: 13px; font-weight: 500; transition: all 0.15s; }
    .nav-item:hover { background: var(--gray-800); color: var(--white); } .nav-item.active { background: var(--gray-800); color: var(--white); }
    .nav-item svg { width: 18px; height: 18px; }
    .sidebar-footer { padding: 16px; border-top: 1px solid var(--gray-800); }
    .user-card { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 6px; background: var(--gray-800); }
    .user-avatar { width: 32px; height: 32px; border-radius: 6px; background: var(--gray-700); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; color: var(--white); }
    .user-info { flex: 1; } .user-name { font-size: 12px; font-weight: 500; color: var(--white); } .user-role { font-size: 11px; color: var(--gray-500); }
    .main-content { flex: 1; margin-left: 240px; }
    .topbar { height: 56px; background: var(--white); border-bottom: 1px solid var(--gray-200); display: flex; align-items: center; padding: 0 24px; position: sticky; top: 0; z-index: 50; }
    .page-title { font-size: 15px; font-weight: 600; }
    .page-content { padding: 24px; max-width: 640px; }
    .page-header { margin-bottom: 24px; }
    .page-header h2 { font-size: 20px; font-weight: 600; }
    .page-header p { font-size: 13px; color: var(--gray-500); margin-top: 4px; }
    .btn { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 18px; border-radius: 999px; font-size: 13px; font-weight: 500; border: 1px solid transparent; cursor: pointer; transition: all 0.15s; text-decoration: none; justify-content: center; }
    .btn-primary { background: var(--navy-800); color: var(--white); } .btn-primary:hover { background: #0d2240; }
    .btn-secondary { background: var(--white); color: var(--gray-700); border-color: var(--gray-300); }
    .btn-secondary:hover { background: var(--gray-50); }
    .card { background: var(--white); border: 1px solid var(--gray-200); border-radius: 8px; margin-bottom: 16px; }
    .card-header { padding: 14px 16px; border-bottom: 1px solid var(--gray-100); }
    .card-title { font-size: 13px; font-weight: 600; color: var(--gray-900); }
    .card-body { padding: 16px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-group label { font-size: 12px; font-weight: 500; color: var(--gray-700); }
    .form-group input, .form-group select, .form-group textarea { padding: 10px 12px; border-radius: 6px; border: 1px solid var(--gray-300); background: var(--white); color: var(--gray-900); font-size: 13px; font-family: inherit; transition: all 0.15s; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--gray-900); box-shadow: 0 0 0 2px rgba(0,0,0,0.05); }
    .form-group textarea { resize: vertical; min-height: 120px; }
    .form-group small { font-size: 11px; color: var(--gray-500); }
    .form-actions { display: flex; gap: 8px; margin-top: 16px; }
    .alert { padding: 12px 14px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; }
    .alert-error { background: var(--red-50); border: 1px solid #fecaca; color: var(--red-600); }
</style>

<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">AGS Break Tracker</div>
        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Menu</div>
                <a href="{{ route('dashboard') }}" class="nav-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('tickets.index') }}" class="nav-item active">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>
                    Support
                </a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
                </div>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Back</a>
        </header>
        <div class="page-content">
            <div class="page-header">
                <h2>Submit a Ticket</h2>
                <p>Have feedback, a bug to report, or need help? We're here for you.</p>
            </div>

            @if($errors->any())
                <div class="alert alert-error">
                    <strong>Please fix the following errors:</strong>
                    <ul style="margin-top: 6px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-header"><h3 class="card-title">Ticket Details</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tickets.store') }}">
                        @csrf
                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <input type="text" id="subject" name="subject" value="{{ old('subject') }}" placeholder="Brief summary of your issue" maxlength="255" required>
                        </div>
                        <div class="form-group">
                            <label for="category">Category *</label>
                            <select id="category" name="category" required>
                                <option value="">Select category</option>
                                <option value="bug_error" {{ old('category') == 'bug_error' ? 'selected' : '' }}>Bug / Error</option>
                                <option value="feature_request" {{ old('category') == 'feature_request' ? 'selected' : '' }}>Feature Request</option>
                                <option value="schedule_issue" {{ old('category') == 'schedule_issue' ? 'selected' : '' }}>Schedule Issue</option>
                                <option value="access_problem" {{ old('category') == 'access_problem' ? 'selected' : '' }}>Access Problem</option>
                                <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="priority">Priority *</label>
                            <select id="priority" name="priority" required>
                                <option value="">Select priority</option>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low — Minor inconvenience</option>
                                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium — Affects my work</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High — Can't work</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" placeholder="Describe your issue or feedback in detail..." required>{{ old('description') }}</textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Submit Ticket</button>
                            <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
</x-app-layout>
```

- [ ] **Step 4: Create tickets/show.blade.php**

```blade
<x-app-layout>
<style>
    :root { --navy-800: #0f2847; --gray-900: #171717; --gray-800: #262626; --gray-700: #374151; --gray-600: #4b5563; --gray-500: #6b7280; --gray-400: #9ca3af; --gray-300: #d1d5db; --gray-200: #e5e7eb; --gray-100: #f3f4f6; --gray-50: #f9fafb; --white: #ffffff; --green-600: #16a34a; --green-50: #f0fdf4; --red-600: #dc2626; --red-50: #fef2f2; --yellow-600: #ca8a04; --yellow-50: #fefce8; --blue-600: #2563eb; --blue-50: #eff6ff; }
    * { box-sizing: border-box; margin: 0; padding: 0; } html { font-size: 14px; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--gray-100); color: var(--gray-900); min-height: 100vh; }
    .app-layout { display: flex; min-height: 100vh; } .sidebar { width: 240px; background: var(--gray-900); position: fixed; top: 0; left: 0; bottom: 0; display: flex; flex-direction: column; }
    .sidebar-brand { padding: 20px; border-bottom: 1px solid var(--gray-800); color: var(--white); font-size: 15px; font-weight: 600; }
    .sidebar-nav { flex: 1; padding: 16px 12px; } .nav-section { margin-bottom: 24px; }
    .nav-section-title { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: var(--gray-500); padding: 0 12px; margin-bottom: 8px; }
    .nav-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 6px; color: var(--gray-400); text-decoration: none; font-size: 13px; font-weight: 500; transition: all 0.15s; }
    .nav-item:hover { background: var(--gray-800); color: var(--white); } .nav-item.active { background: var(--gray-800); color: var(--white); }
    .nav-item svg { width: 18px; height: 18px; }
    .sidebar-footer { padding: 16px; border-top: 1px solid var(--gray-800); }
    .user-card { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 6px; background: var(--gray-800); }
    .user-avatar { width: 32px; height: 32px; border-radius: 6px; background: var(--gray-700); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; color: var(--white); }
    .user-info { flex: 1; } .user-name { font-size: 12px; font-weight: 500; color: var(--white); } .user-role { font-size: 11px; color: var(--gray-500); }
    .main-content { flex: 1; margin-left: 240px; }
    .topbar { height: 56px; background: var(--white); border-bottom: 1px solid var(--gray-200); display: flex; align-items: center; padding: 0 24px; position: sticky; top: 0; z-index: 50; }
    .page-title { font-size: 15px; font-weight: 600; }
    .page-content { padding: 24px; max-width: 760px; }
    .btn { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 18px; border-radius: 999px; font-size: 13px; font-weight: 500; border: 1px solid transparent; cursor: pointer; transition: all 0.15s; text-decoration: none; justify-content: center; }
    .btn-primary { background: var(--navy-800); color: var(--white); } .btn-primary:hover { background: #0d2240; }
    .btn-secondary { background: var(--white); color: var(--gray-700); border-color: var(--gray-300); }
    .btn-secondary:hover { background: var(--gray-50); }
    .btn-sm { height: 30px; padding: 0 12px; font-size: 12px; }
    .alert { padding: 12px 14px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; }
    .alert-success { background: var(--green-50); border: 1px solid #bbf7d0; color: var(--green-600); }
    .card { background: var(--white); border: 1px solid var(--gray-200); border-radius: 8px; margin-bottom: 16px; }
    .card-header { padding: 14px 16px; border-bottom: 1px solid var(--gray-100); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; }
    .card-title { font-size: 13px; font-weight: 600; color: var(--gray-900); }
    .card-body { padding: 16px; }
    .ticket-meta { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
    .meta-item { display: flex; flex-direction: column; gap: 2px; }
    .meta-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); }
    .meta-value { font-size: 13px; font-weight: 500; color: var(--gray-900); }
    .status-badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; }
    .status-badge.open { background: var(--yellow-50); color: var(--yellow-600); }
    .status-badge.in_progress { background: var(--blue-50); color: var(--blue-600); }
    .status-badge.resolved { background: var(--green-50); color: var(--green-600); }
    .status-badge.closed { background: var(--gray-100); color: var(--gray-500); }
    .priority-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; text-transform: uppercase; }
    .priority-badge.high { background: var(--red-50); color: var(--red-600); }
    .priority-badge.medium { background: var(--yellow-50); color: var(--yellow-600); }
    .priority-badge.low { background: var(--gray-100); color: var(--gray-500); }
    .ticket-desc { font-size: 14px; color: var(--gray-700); line-height: 1.6; white-space: pre-wrap; }
    .comment { display: flex; gap: 12px; padding: 14px 0; border-bottom: 1px solid var(--gray-100); }
    .comment:last-child { border-bottom: none; }
    .comment-avatar { width: 32px; height: 32px; border-radius: 6px; background: var(--gray-200); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; color: var(--gray-600); flex-shrink: 0; }
    .comment-body { flex: 1; }
    .comment-header { display: flex; gap: 8px; align-items: center; margin-bottom: 4px; }
    .comment-author { font-size: 13px; font-weight: 600; color: var(--gray-900); }
    .comment-role { font-size: 11px; color: var(--gray-500); }
    .comment-date { font-size: 11px; color: var(--gray-400); margin-left: auto; }
    .comment-text { font-size: 13px; color: var(--gray-700); line-height: 1.5; }
    .comment-form { display: flex; gap: 10px; align-items: flex-start; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--gray-200); }
    .comment-form textarea { flex: 1; padding: 10px 12px; border-radius: 6px; border: 1px solid var(--gray-300); font-size: 13px; font-family: inherit; resize: vertical; min-height: 80px; }
    .comment-form textarea:focus { outline: none; border-color: var(--gray-900); }
    .status-select { height: 30px; padding: 0 8px; border-radius: 6px; border: 1px solid var(--gray-300); font-size: 12px; }
</style>

<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">AGS Break Tracker</div>
        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Menu</div>
                <a href="{{ route('dashboard') }}" class="nav-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('tickets.index') }}" class="nav-item active">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>
                    Support
                </a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar">{{ substr($user->name, 0, 1) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ $user->name }}</div>
                    <div class="user-role">{{ ucfirst($user->role) }}</div>
                </div>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Back</a>
        </header>
        <div class="page-content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <!-- Ticket Details -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">#{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }} — {{ $ticket->subject }}</h3>
                    @if($user->isAdmin() || $user->isTeamLead())
                        <form method="POST" action="{{ route('tickets.status', $ticket->id) }}">
                            @csrf
                            <select name="status" class="status-select">
                                <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-secondary">Update</button>
                        </form>
                    @endif
                </div>
                <div class="card-body">
                    <div class="ticket-meta">
                        <div class="meta-item">
                            <span class="meta-label">Status</span>
                            <span class="status-badge {{ $ticket->status }}">{{ str_replace('_', ' ', ucfirst($ticket->status)) }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Priority</span>
                            <span class="priority-badge {{ $ticket->priority }}">{{ $ticket->priority }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Category</span>
                            <span class="meta-value">{{ str_replace('_', ' ', ucfirst($ticket->category)) }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Submitted</span>
                            <span class="meta-value">{{ $ticket->created_at->format('M j, Y g:i A') }}</span>
                        </div>
                    </div>
                    <div class="ticket-desc">{{ $ticket->description }}</div>
                </div>
            </div>

            <!-- Comments -->
            <div class="card">
                <div class="card-header"><h3 class="card-title">Comments ({{ $ticket->comments->count() }})</h3></div>
                <div class="card-body">
                    @forelse($ticket->comments as $comment)
                        <div class="comment">
                            <div class="comment-avatar">{{ substr($comment->user->name, 0, 1) }}</div>
                            <div class="comment-body">
                                <div class="comment-header">
                                    <span class="comment-author">{{ $comment->user->name }}</span>
                                    <span class="comment-role">{{ ucfirst($comment->user->role) }}</span>
                                    <span class="comment-date">{{ $comment->created_at->format('M j, g:i A') }}</span>
                                </div>
                                <div class="comment-text">{{ $comment->comment }}</div>
                            </div>
                        </div>
                    @empty
                        <p style="color: var(--gray-500); font-size: 13px; text-align: center; padding: 20px;">No comments yet.</p>
                    @endforelse

                    @if($ticket->status !== 'closed')
                        <form method="POST" action="{{ route('tickets.comment', $ticket->id) }}" class="comment-form">
                            @csrf
                            <textarea name="comment" placeholder="Add a comment..." required></textarea>
                            <button type="submit" class="btn btn-primary">Send</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>
</x-app-layout>
```

- [ ] **Step 5: Add sidebar nav item to dashboard and profile views**

In `resources/views/dashboard.blade.php`, find the sidebar nav section and add after the Profile nav item:

```blade
<a href="{{ route('tickets.index') }}" class="nav-item">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
        <rect x="9" y="3" width="6" height="4" rx="2"/>
        <line x1="9" y1="12" x2="15" y2="12"/>
        <line x1="9" y1="16" x2="13" y2="16"/>
    </svg>
    <span>Support</span>
</a>
```

Do the same in `resources/views/profile/edit.blade.php`.

- [ ] **Step 6: Verify the ticket system works**

Run: `php artisan serve`
Navigate to: http://localhost:8080/tickets/create
Expected: Ticket submission form renders correctly

Submit a test ticket.
Navigate to: http://localhost:8080/tickets
Expected: Ticket appears in the list.

- [ ] **Step 7: Commit**

```bash
git add resources/views/tickets/ routes/web.php app/Http/Controllers/TicketController.php app/Http/Requests/StoreTicketRequest.php app/Models/SupportTicket.php app/Models/SupportTicketComment.php
git commit -m "feat(tickets): implement support ticket system

Add TicketController, SupportTicket/SupportTicketComment models,
and three views: index, create, show.
Tickets support: create, list with filters, detail view,
comments, and admin/TL status updates.
Nav item added to dashboard and profile sidebars.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>
```

---

## Task 10: Live Polling for Performance & Peer Stats

**Files:**
- Modify: `app/Http/Controllers/BreakController.php`
- Modify: `resources/views/dashboard.blade.php`

Update the existing `liveData()` endpoint to return performance and peer stats.

- [ ] **Step 1: Update BreakController::liveData()**

Find the `liveData()` method in `BreakController.php` and add performance and peer stats to the returned JSON:

```php
public function liveData(): \Illuminate\Http\JsonResponse
{
    $user = Auth::user();
    $activeBreak = $this->breakService->getActiveBreak($user);
    $stats = $this->breakService->getUserStats($user);

    $data = [
        'active_break' => $activeBreak ? [
            'type' => $activeBreak->break_type,
            'category' => $activeBreak->break_category,
            'started_at' => $activeBreak->started_at->toISOString(),
            'expected_end_at' => $activeBreak->expected_end_at->toISOString(),
            'elapsed_minutes' => (int) $activeBreak->started_at->diffInMinutes(now()),
            'over_minutes' => $activeBreak->over_minutes ?? 0,
        ] : null,
        'stats' => $stats,
        'performance' => [
            'compliance' => $user->getComplianceRate(),
            'daily_breaks' => $user->getDailyBreaks(),
            'weekly_total' => $user->getWeeklyStats()['total'],
            'weekly_overbreaks' => $user->getWeeklyStats()['overbreaks'],
            'avg_15m' => $user->getAverageDuration('15m'),
            'avg_60m' => $user->getAverageDuration('60m'),
            'score' => $user->getPerformanceScore(),
        ],
        'timestamp' => now()->toISOString(),
    ];

    if (!$user->isAdmin()) {
        $data['peer_stats'] = $this->breakService->getPeerStats($user);
    }

    return response()->json($data);
}
```

- [ ] **Step 2: Update the JavaScript polling to refresh performance data**

Find the `setInterval` in the `<script>` section of dashboard.blade.php. In the `fetch('/dashboard/live')` success callback, add performance/peer stat updates. Use safe DOM methods (textContent, setAttribute):

```javascript
// Update performance
if (data.performance) {
    var p = data.performance;
    var complianceEl = document.getElementById('perf-compliance');
    complianceEl.textContent = p.compliance;
    complianceEl.dataset.value = p.compliance;
    document.getElementById('perf-daily').textContent = p.daily_breaks + '/5';
    document.getElementById('perf-weekly').textContent = p.weekly_total;
    document.getElementById('perf-avg15').textContent = p.avg_15m + 'm';
    document.getElementById('perf-avg60').textContent = p.avg_60m + 'm';
    document.getElementById('perf-score').textContent = p.score;
    document.getElementById('perf-score-fill').style.width = p.score + '%';

    // Update ring
    var ring = document.querySelector('.ring-fill');
    ring.setAttribute('stroke-dasharray', p.compliance + ', 100');
    ring.classList.remove('warn', 'danger');
    if (p.compliance < 70) ring.classList.add('danger');
    else if (p.compliance < 90) ring.classList.add('warn');

    // Update score color
    var scoreEl = document.getElementById('perf-score');
    var scoreFill = document.getElementById('perf-score-fill');
    scoreEl.classList.remove('good', 'warn', 'danger');
    scoreFill.classList.remove('warn', 'danger');
    if (p.score < 60) { scoreEl.classList.add('danger'); scoreFill.classList.add('danger'); }
    else if (p.score < 80) { scoreEl.classList.add('warn'); scoreFill.classList.add('warn'); }
    else { scoreEl.classList.add('good'); }
}

// Update peer stats
if (data.peer_stats) {
    var peer = data.peer_stats;
    var activeBtn = document.querySelector('.peer-toggle-btn.active');
    var scope = activeBtn ? activeBtn.dataset.scope : 'team';
    var peerData = scope === 'team' ? peer.team : peer.department;
    document.getElementById('peer-15m').textContent = peerData.count_15m_avg;
    document.getElementById('peer-60m').textContent = peerData.count_60m_avg;
    document.getElementById('peer-over').textContent = peerData.overbreaks_avg;
    document.getElementById('peer-comp').textContent = peerData.compliance + '%';
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/BreakController.php resources/views/dashboard.blade.php
git commit -m "refactor(dashboard): include performance and peer stats in live polling

Performance metrics and peer stats now update via the existing
5-second polling interval without full page refresh.

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>
```

---

## Spec Coverage Check

- [x] Section 1 (Work Info Card) — Tasks 3
- [x] Section 2 (Performance Metrics Card) — Tasks 4, 10
- [x] Section 3 (Peer Benchmarking Card) — Tasks 2, 5, 10
- [x] Section 4 (Ticket System) — Tasks 6, 7, 8, 9
- [x] Live polling update — Task 10
- [x] Sidebar navigation — Task 9, Step 5
