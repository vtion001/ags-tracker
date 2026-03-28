# Overbreak Flagging System Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add real-time overbreak monitoring for admins/TLs with ElevenLabs voice alerts when agents exceed their break time limits.

**Architecture:** Two-part system:
1. **Admin Overbreak Monitor View** (`/overbreaks`) - Real-time table of all agents with active breaks, showing overbreak status, with auto-refresh every 5 seconds
2. **ElevenLabs Voice Service** - Server-side service that generates audio alerts when agents go on overbreak, delivered via browser audio API on the admin dashboard

**Tech Stack:** Laravel 11, PHP 8.5+, ElevenLabs API, Blade templates, existing BreakService/BreakHistory/ActiveBreak models

---

## Task 1: Create ElevenLabs Configuration

**Files:**
- Create: `config/elevenlabs.php`

- [ ] **Step 1: Create ElevenLabs config file**

```php
<?php

return [
    'api_key' => env('ELEVENLABS_API_KEY'),
    'voice_id' => env('ELEVENLABS_VOICE_ID', 'pFZjBKKC4YTVvxGuTyv9'),
    'model' => env('ELEVENLABS_MODEL', 'eleven_flash_v2_5'),
    'proximity_filter' => [
        'frontend_micro_seconds' => env('ELEVENLABS_PROXIMITY_FILTER', 1000000),
    ],
];
```

- [ ] **Step 2: Add environment variables to .env**

Append to `.env`:
```
ELEVENLABS_API_KEY=
ELEVENLABS_VOICE_ID=pFZjBKKC4YTVvxGuTyv9
ELEVENLABS_MODEL=eleven_flash_v2_5
```

- [ ] **Step 3: Commit**

```bash
git add config/elevenlabs.php .env
git commit -m "config: add ElevenLabs API configuration"
```

---

## Task 2: Create ElevenLabsService

**Files:**
- Create: `app/Services/ElevenLabsService.php`
- Test: `tests/Unit/ElevenLabsServiceTest.php`

- [ ] **Step 1: Create ElevenLabsService**

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ElevenLabsService
{
    private string $apiKey;
    private string $voiceId;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('elevenlabs.api_key');
        $this->voiceId = config('elevenlabs.voice_id');
        $this->model = config('elevenlabs.model');
    }

    public function generateSpeech(string $text): ?string
    {
        if (empty($this->apiKey)) {
            Log::warning('ElevenLabs API key not configured');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'xi-api-key' => $this->apiKey,
            ])
            ->timeout(10)
            ->post("https://api.elevenlabs.io/v1/text-to-speech/{$this->voiceId}", [
                'text' => $text,
                'model_id' => $this->model,
                'voice_settings' => [
                    'stability' => 0.5,
                    'similarity_boost' => 0.75,
                    'style' => 0.0,
                    'use_speaker_boost' => true,
                ],
            ]);

            if ($response->successful()) {
                return $response->body();
            }

            Log::error('ElevenLabs API error: ' . $response->status() . ' - ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('ElevenLabsService error: ' . $e->getMessage());
            return null;
        }
    }

    public function generateOverbreakAlert(string $agentName, int $overMinutes): ?string
    {
        $text = "Alert. Agent {$agentName} has exceeded their break time by {$overMinutes} minutes. Please take action.";
        return $this->generateSpeech($text);
    }

    public function generateOverbreakBatchAlert(array $overbreakAgents): ?string
    {
        if (empty($overbreakAgents)) {
            return null;
        }

        $names = implode(', ', array_column($overbreakAgents, 'user_name'));
        $text = "Alert. The following agents are on overbreak: {$names}. Please take immediate action.";
        return $this->generateSpeech($text);
    }
}
```

- [ ] **Step 2: Register service in AppServiceProvider**

Modify `app/Providers/AppServiceProvider.php` - add to `register()` method:
```php
$this->app->singleton(ElevenLabsService::class);
```

- [ ] **Step 3: Commit**

```bash
git add app/Services/ElevenLabsService.php app/Providers/AppServiceProvider.php
git commit -m "feat: add ElevenLabs voice service for overbreak alerts"
```

---

## Task 3: Create AlertController for Audio Playback

**Files:**
- Create: `app/Http/Controllers/AlertController.php`

- [ ] **Step 1: Create AlertController**

```php
<?php

namespace App\Http\Controllers;

use App\Services\ElevenLabsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AlertController extends Controller
{
    public function __construct(
        protected ElevenLabsService $elevenLabs
    ) {}

    public function overbreakAlert(Request $request): Response
    {
        $request->validate([
            'agent_name' => 'required|string|max:255',
            'over_minutes' => 'required|integer|min:1',
        ]);

        $audio = $this->elevenLabs->generateOverbreakAlert(
            $request->input('agent_name'),
            $request->input('over_minutes')
        );

        if ($audio === null) {
            return response('Audio unavailable', 503);
        }

        return response($audio, 200)
            ->header('Content-Type', 'audio/mpeg')
            ->header('Content-Disposition', 'inline');
    }

    public function batchAlert(Request $request): Response
    {
        $agents = $request->input('agents', []);

        if (empty($agents)) {
            return response('No agents', 400);
        }

        $audio = $this->elevenLabs->generateOverbreakBatchAlert($agents);

        if ($audio === null) {
            return response('Audio unavailable', 503);
        }

        return response($audio, 200)
            ->header('Content-Type', 'audio/mpeg')
            ->header('Content-Disposition', 'inline');
    }
}
```

- [ ] **Step 2: Add routes**

Add to `routes/web.php` inside the `Route::middleware('auth')->group()`:
```php
Route::get('/alerts/overbreak', [AlertController::class, 'overbreakAlert'])->name('alerts.overbreak');
Route::get('/alerts/batch', [AlertController::class, 'batchAlert'])->name('alerts.batch');
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/AlertController.php routes/web.php
git commit -m "feat: add audio alert endpoints for overbreak notifications"
```

---

## Task 4: Create OverbreakController

**Files:**
- Create: `app/Http/Controllers/OverbreakController.php`

- [ ] **Step 1: Create OverbreakController**

```php
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
```

- [ ] **Step 2: Add route**

Add to `routes/web.php`:
```php
Route::get('/overbreaks', [OverbreakController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('overbreaks');

Route::get('/overbreaks/live', [OverbreakController::class, 'liveData'])
    ->middleware(['auth', 'verified'])
    ->name('overbreaks.live');
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/OverbreakController.php routes/web.php
git commit -m "feat: add overbreak monitoring controller and routes"
```

---

## Task 5: Create Overbreaks Admin View

**Files:**
- Create: `resources/views/overbreaks.blade.php`

- [ ] **Step 1: Create overbreaks view** (professional Navy theme, pill-shaped buttons)

```blade
<x-app-layout>
@php
    $onBreak = $onBreak ?? collect();
    $overbreak = $overbreak ?? collect();
    $overbreakStats = $overbreakStats ?? ['total' => 0, 'agents' => 0, 'total_over_minutes' => 0];
    $departments = $departments ?? collect();
    $filters = $filters ?? ['department' => '', 'role' => '', 'status' => 'all'];
@endphp

<style>
    :root {
        --navy-900: #0a1929;
        --navy-800: #0f2847;
        --navy-700: #143663;
        --navy-600: #1a4a8a;
        --white: #ffffff;
        --gray-950: #0a1929;
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
        --green-600: #16a34a;
        --green-500: #22c55e;
        --green-50: #f0fdf4;
        --red-600: #dc2626;
        --red-500: #ef4444;
        --red-50: #fef2f2;
        --amber-500: #f59e0b;
        --amber-50: #fffbeb;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    html { font-size: 14px; }
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: var(--gray-100);
        color: var(--gray-900);
        min-height: 100vh;
    }

    .page-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 32px 24px;
    }

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 32px;
    }

    .page-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--navy-900);
    }

    .page-subtitle {
        font-size: 14px;
        color: var(--gray-500);
        margin-top: 4px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: var(--white);
        border-radius: 16px;
        padding: 24px;
        border: 1px solid var(--gray-200);
    }

    .stat-card.overbreak {
        background: var(--red-50);
        border-color: #fecaca;
    }

    .stat-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--gray-500);
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: var(--navy-900);
    }

    .stat-card.overbreak .stat-value {
        color: var(--red-600);
    }

    .filter-bar {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .filter-select {
        padding: 10px 16px;
        border-radius: 999px;
        border: 1.5px solid var(--gray-200);
        background: var(--white);
        font-size: 14px;
        font-family: inherit;
        color: var(--gray-700);
        cursor: pointer;
        min-width: 160px;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--navy-700);
    }

    .btn-voice {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: var(--red-600);
        color: var(--white);
        border: none;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 600;
        font-family: inherit;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-left: auto;
    }

    .btn-voice:hover {
        background: var(--red-500);
        transform: translateY(-1px);
    }

    .btn-voice:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    .section {
        margin-bottom: 32px;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--gray-900);
    }

    .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 24px;
        padding: 0 8px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .badge-danger { background: var(--red-600); color: var(--white); }
    .badge-success { background: var(--green-600); color: var(--white); }

    .table-container {
        background: var(--white);
        border-radius: 16px;
        border: 1px solid var(--gray-200);
        overflow: hidden;
    }

    .table-scroll { overflow-x: auto; }

    table { width: 100%; border-collapse: collapse; }

    thead { background: var(--gray-50); }

    th {
        padding: 14px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--gray-500);
        border-bottom: 1px solid var(--gray-200);
        white-space: nowrap;
    }

    td {
        padding: 14px 16px;
        font-size: 14px;
        color: var(--gray-700);
        border-bottom: 1px solid var(--gray-100);
    }

    tr:last-child td { border-bottom: none; }
    tr:hover td { background: var(--gray-50); }

    .status-overbreak {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        background: var(--red-50);
        border: 1px solid #fecaca;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        color: var(--red-600);
    }

    .status-onbreak {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        background: var(--green-50);
        border: 1px solid #bbf7d0;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        color: var(--green-600);
    }

    .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .overbreak-row td { background: var(--red-50); }
    .overbreak-row:hover td { background: #fee2e2; }

    .empty-state {
        text-align: center;
        padding: 48px 24px;
        color: var(--gray-400);
        font-size: 14px;
    }

    .live-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--gray-500);
    }

    .live-dot {
        width: 8px;
        height: 8px;
        background: var(--green-500);
        border-radius: 50%;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    @media (max-width: 768px) {
        .page-header { flex-direction: column; align-items: flex-start; gap: 16px; }
        .btn-voice { margin-left: 0; width: 100%; justify-content: center; }
        .filter-bar { flex-direction: column; }
        .filter-select { width: 100%; }
    }
</style>

<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Overbreak Monitor</h1>
            <p class="page-subtitle">Real-time monitoring of agent break status</p>
        </div>
        <div class="live-indicator">
            <span class="live-dot"></span>
            <span>Live</span>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">On Break</div>
            <div class="stat-value">{{ $onBreak->count() }}</div>
        </div>
        <div class="stat-card overbreak">
            <div class="stat-label">On Overbreak</div>
            <div class="stat-value">{{ $overbreak->count() }}</div>
        </div>
        <div class="stat-card overbreak">
            <div class="stat-label">Agents Overbreak</div>
            <div class="stat-value">{{ $overbreak->pluck('user_name')->unique()->count() }}</div>
        </div>
        <div class="stat-card overbreak">
            <div class="stat-label">Total Overbreak Time</div>
            <div class="stat-value">{{ $overbreakStats['total_over_minutes'] }}m</div>
        </div>
    </div>

    @if($overbreak->count() > 0)
    <div style="margin-bottom: 24px;">
        <button type="button" class="btn-voice" id="voiceAlertBtn" onclick="triggerVoiceAlert()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                <line x1="12" y1="19" x2="12" y2="23"/>
                <line x1="8" y1="23" x2="16" y2="23"/>
            </svg>
            <span>Flag All Overbreaks</span>
        </button>
    </div>
    @endif

    <div class="filter-bar">
        <select class="filter-select" id="departmentFilter" onchange="applyFilters()">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ $filters['department'] == $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>
        <select class="filter-select" id="statusFilter" onchange="applyFilters()">
            <option value="all" {{ $filters['status'] == 'all' ? 'selected' : '' }}>All Status</option>
            <option value="on_break" {{ $filters['status'] == 'on_break' ? 'selected' : '' }}>On Break</option>
            <option value="overbreak" {{ $filters['status'] == 'overbreak' ? 'selected' : '' }}>Overbreak</option>
        </select>
    </div>

    <div class="section">
        <div class="section-header">
            <h2 class="section-title">All Active Breaks</h2>
            <span class="badge badge-danger">{{ $overbreak->count() }} Overbreak</span>
            <span class="badge badge-success">{{ $onBreak->count() }} On Break</span>
        </div>

        <div class="table-container">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Agent</th>
                            <th>Department</th>
                            <th>Break Type</th>
                            <th>Started</th>
                            <th>Expected End</th>
                            <th>Elapsed</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="breaksTableBody">
                        @php $now = now(); @endphp
                        @forelse($activeBreaks = ($overbreak->merge($onBreak)) as $break)
                        @php
                            $isOver = $now->greaterThan($break->expected_end_at);
                            $elapsedMins = (int) floor($now->diffInMinutes($break->started_at));
                            $overMins = $isOver ? (int) floor($now->diffInMinutes($break->expected_end_at)) : 0;
                        @endphp
                        <tr class="{{ $isOver ? 'overbreak-row' : '' }}">
                            <td>
                                <div style="font-weight: 600; color: var(--gray-900);">{{ $break->user_name }}</div>
                                <div style="font-size: 12px; color: var(--gray-400);">{{ $break->user_email }}</div>
                            </td>
                            <td>{{ $break->department ?? 'N/A' }}</td>
                            <td><span style="font-weight: 500;">{{ $break->break_label }}</span></td>
                            <td>{{ $break->started_at->format('H:i') }}</td>
                            <td>{{ $break->expected_end_at->format('H:i') }}</td>
                            <td>{{ $elapsedMins }}m</td>
                            <td>
                                @if($isOver)
                                <span class="status-overbreak">
                                    <span class="dot" style="background: var(--red-600);"></span>
                                    +{{ $overMins }}m Overbreak
                                </span>
                                @else
                                <span class="status-onbreak">
                                    <span class="dot" style="background: var(--green-500);"></span>
                                    On Break
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="empty-state">No active breaks at the moment.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<audio id="alertAudio" style="display: none;"></audio>

<script>
    function triggerVoiceAlert() {
        const btn = document.getElementById('voiceAlertBtn');
        const audio = document.getElementById('alertAudio');

        btn.disabled = true;
        btn.textContent = 'Generating alert...';

        const overbreakRows = document.querySelectorAll('.overbreak-row');
        const agents = [];

        overbreakRows.forEach(row => {
            const name = row.querySelector('td:first-child div').textContent.trim();
            const statusEl = row.querySelector('.status-overbreak');
            if (statusEl) {
                const match = statusEl.textContent.match(/\+(\d+)m/);
                if (match) {
                    agents.push({ name: name, over_minutes: parseInt(match[1]) });
                }
            }
        });

        if (agents.length === 0) {
            btn.disabled = false;
            btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/></svg> Flag All Overbreaks';
            return;
        }

        fetch('/alerts/batch?' + new URLSearchParams({ agents: JSON.stringify(agents) }), {
            method: 'GET',
            headers: { 'Accept': 'audio/mpeg' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to generate alert');
            return response.blob();
        })
        .then(blob => {
            const url = URL.createObjectURL(blob);
            audio.src = url;
            audio.play().catch(e => console.error('Audio play failed:', e));
            btn.textContent = 'Alert Sent!';
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/></svg> Flag All Overbreaks';
            }, 3000);
        })
        .catch(error => {
            console.error('Voice alert error:', error);
            btn.disabled = false;
            btn.textContent = 'Alert Failed';
            setTimeout(() => {
                btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/></svg> Flag All Overbreaks';
            }, 3000);
        });
    }

    setInterval(() => {
        fetch('/overbreaks/live')
            .then(response => response.json())
            .then(data => {
                const overbreakCount = data.overbreak.length;
                const currentCount = document.querySelectorAll('.overbreak-row').length;
                if (overbreakCount !== currentCount) {
                    location.reload();
                }
            })
            .catch(() => {});
    }, 5000);
</script>
</x-app-layout>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/overbreaks.blade.php
git commit -m "feat: add admin overbreak monitoring view with ElevenLabs voice alerts"
```

---

## Task 6: Add Overbreaks Navigation Link

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

- [ ] **Step 1: Add navigation link**

Find the sidebar navigation and add:
```blade
@if(Auth::user()->isAdmin() || Auth::user()->isTeamLead())
    <a href="{{ route('overbreaks') }}" class="nav-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        <span>Overbreaks</span>
    </a>
@endif
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/layouts/app.blade.php
git commit -m "feat: add overbreaks link to navigation for admin/TL"
```

---

## Task 7: Add Real-time Overbreak Alerts to Dashboard

**Files:**
- Modify: `resources/views/dashboard.blade.php`

- [ ] **Step 1: Add overbreak alert banner**

Add this near the agent-alert section in dashboard:
```blade
@if(($user->isAdmin() || $user->isTeamLead()) && $teamBreaks->count() > 0)
    @php
        $overbreakAgents = $teamBreaks->filter(fn($b) => now()->greaterThan($b->expected_end_at));
    @endphp
    @if($overbreakAgents->count() > 0)
    <div id="team-overbreak-alert" class="agent-alert" style="background: var(--red-50); border: 1px solid var(--red-600);">
        <div class="alert-icon" style="background: var(--red-600);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <div class="alert-content">
            <strong style="color: var(--red-600);">{{ $overbreakAgents->count() }} Agent(s) on Overbreak</strong>
            <p style="color: var(--gray-600);">{{ $overbreakAgents->pluck('user_name')->implode(', ') }}</p>
        </div>
        <button type="button" class="alert-dismiss" onclick="dismissAlert('team')" style="margin-left: auto;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>
    @endif
@endif
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/dashboard.blade.php
git commit -m "feat: add real-time overbreak alert banner to dashboard"
```

---

## Task 8: Integration Testing

**Files:**
- Create: `tests/Feature/OverbreakMonitoringTest.php`

- [ ] **Step 1: Create integration test**

```php
<?php

namespace Tests\Feature;

use App\Models\ActiveBreak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OverbreakMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_overbreaks_page_accessible_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/overbreaks');

        $response->assertStatus(200);
        $response->assertSee('Overbreak Monitor');
    }

    public function test_overbreaks_page_accessible_for_team_lead(): void
    {
        $tl = User::factory()->create(['role' => 'tl']);

        $response = $this->actingAs($tl)->get('/overbreaks');

        $response->assertStatus(200);
    }

    public function test_overbreaks_page_forbidden_for_agent(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);

        $response = $this->actingAs($agent)->get('/overbreaks');

        $response->assertStatus(403);
    }

    public function test_overbreaks_live_data_returns_json(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $agent = User::factory()->create(['role' => 'agent', 'tl_email' => $admin->email]);

        ActiveBreak::create([
            'break_id' => 'BRK-TEST123',
            'user_id' => $agent->id,
            'user_name' => $agent->name,
            'user_email' => $agent->email,
            'department' => 'Test',
            'tl_email' => $admin->email,
            'break_type' => '15m',
            'break_label' => '15-Minute Break',
            'allowed_minutes' => 15,
            'started_at' => now()->subMinutes(20),
            'expected_end_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($admin)->get('/overbreaks/live');

        $response->assertStatus(200);
        $response->assertJsonStructure(['on_break', 'overbreak', 'timestamp']);
    }

    public function test_overbreaks_separates_on_break_and_overbreak(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $agent1 = User::factory()->create(['role' => 'agent']);
        $agent2 = User::factory()->create(['role' => 'agent']);

        // Agent 1: on break (not over)
        ActiveBreak::create([
            'break_id' => 'BRK-ONBREAK',
            'user_id' => $agent1->id,
            'user_name' => $agent1->name,
            'user_email' => $agent1->email,
            'tl_email' => 'tl@test.com',
            'break_type' => '15m',
            'break_label' => '15-Minute Break',
            'allowed_minutes' => 15,
            'started_at' => now()->subMinutes(5),
            'expected_end_at' => now()->addMinutes(10),
        ]);

        // Agent 2: overbreak
        ActiveBreak::create([
            'break_id' => 'BRK-OVERBRK',
            'user_id' => $agent2->id,
            'user_name' => $agent2->name,
            'user_email' => $agent2->email,
            'tl_email' => 'tl@test.com',
            'break_type' => '15m',
            'break_label' => '15-Minute Break',
            'allowed_minutes' => 15,
            'started_at' => now()->subMinutes(20),
            'expected_end_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($admin)->get('/overbreaks/live');

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertCount(1, $data['on_break']);
        $this->assertCount(1, $data['overbreak']);
    }
}
```

- [ ] **Step 2: Run tests**

```bash
php artisan test --filter=OverbreakMonitoringTest
```

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/OverbreakMonitoringTest.php
git commit -m "test: add overbreak monitoring feature tests"
```

---

## Summary

| Task | Description |
|------|-------------|
| 1 | Create ElevenLabs config |
| 2 | Create ElevenLabsService |
| 3 | Create AlertController for audio playback |
| 4 | Create OverbreakController for monitoring |
| 5 | Create overbreaks admin view |
| 6 | Add navigation link |
| 7 | Add real-time alerts to dashboard |
| 8 | Integration testing |

---

## Self-Review Checklist

- [ ] All routes registered in web.php
- [ ] All controllers imported in routes file
- [ ] ElevenLabsService registered as singleton
- [ ] Auth middleware applied to overbreaks routes
- [ ] Admin/TL-only access enforced in controller
- [ ] View uses Navy theme consistent with dashboard
- [ ] Voice alert button visible only when overbreaks exist
- [ ] Auto-refresh every 5 seconds works
- [ ] All file paths are absolute and correct
