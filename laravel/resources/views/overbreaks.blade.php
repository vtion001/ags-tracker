<x-app-layout>
@php
    $onBreak = $onBreak ?? collect();
    $overbreak = $overbreak ?? collect();
    $overbreakStats = $overbreakStats ?? ['total' => 0, 'agents' => 0, 'total_over_minutes' => 0];
    $departments = $departments ?? collect();
    $filters = $filters ?? ['department' => '', 'role' => '', 'status' => ''];

    // Combine onBreak and overbreak for display, with overbreak at the top
    $allBreaks = $overbreak->map(function($break) {
        $break->is_overbreak = true;
        return $break;
    })->merge($onBreak->map(function($break) {
        $break->is_overbreak = false;
        return $break;
    }));
@endphp

<style>
    :root {
        --navy-900: #0a1929;
        --navy-800: #0f2847;
        --navy-700: #143663;
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
        --white: #ffffff;
        --green-600: #16a34a;
        --green-50: #f0fdf4;
        --red-600: #dc2626;
        --red-50: #fef2f2;
        --red-100: #fee2e2;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    html { font-size: 14px; }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background: var(--gray-100);
        color: var(--gray-900);
        min-height: 100vh;
        line-height: 1.5;
        -webkit-font-smoothing: antialiased;
    }

    /* Page Content */
    .page-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        padding: 24px;
    }

    /* Top Bar */
    .topbar {
        height: 56px;
        background: var(--white);
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 24px;
        margin: -24px -24px 24px -24px;
        position: sticky;
        top: 0;
        z-index: 50;
    }

    .topbar-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .page-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--gray-900);
    }

    .topbar-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .status-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--green-600);
        font-weight: 500;
    }

    .status-indicator::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--green-600);
        animation: pulse-dot 2s infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
        gap: 16px;
    }

    .page-header-text h2 {
        font-size: 24px;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 4px;
    }

    .page-header-text p {
        font-size: 14px;
        color: var(--gray-500);
    }

    .page-header-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }

    /* Voice Alert Button */
    .btn-voice-alert {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: var(--red-600);
        color: var(--white);
        border: none;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
        box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
    }

    .btn-voice-alert:hover {
        background: #b91c1c;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
    }

    .btn-voice-alert:active {
        transform: translateY(0);
    }

    .btn-voice-alert svg {
        width: 18px;
        height: 18px;
    }

    /* Test Voice Button */
    .btn-test-voice {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: var(--navy-700);
        color: var(--white);
        border: none;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .btn-test-voice:hover {
        background: var(--navy-800);
        transform: translateY(-1px);
    }

    .btn-test-voice:active {
        transform: translateY(0);
    }

    .btn-test-voice.loading {
        opacity: 0.7;
        pointer-events: none;
    }

    .btn-test-voice svg {
        width: 18px;
        height: 18px;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: 16px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
    }

    .stat-card.on-break::before {
        background: var(--green-600);
    }

    .stat-card.on-overbreak::before {
        background: var(--red-600);
    }

    .stat-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .stat-label {
        font-size: 12px;
        font-weight: 500;
        color: var(--gray-500);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon.on-break {
        background: var(--green-50);
        color: var(--green-600);
    }

    .stat-icon.on-overbreak {
        background: var(--red-50);
        color: var(--red-600);
    }

    .stat-icon svg {
        width: 18px;
        height: 18px;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: var(--gray-900);
        line-height: 1;
    }

    .stat-card.on-break .stat-value {
        color: var(--green-600);
    }

    .stat-card.on-overbreak .stat-value {
        color: var(--red-600);
    }

    .stat-subtitle {
        font-size: 12px;
        color: var(--gray-500);
    }

    /* Filter Bar */
    .filter-bar {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: 16px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-label {
        font-size: 12px;
        font-weight: 500;
        color: var(--gray-500);
    }

    .filter-select {
        padding: 8px 32px 8px 12px;
        border: 1px solid var(--gray-300);
        border-radius: 8px;
        font-size: 13px;
        color: var(--gray-900);
        background: var(--white);
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M2.5 4.5L6 8l3.5-3.5'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        transition: border-color 0.15s;
    }

    .filter-select:hover {
        border-color: var(--gray-400);
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--navy-700);
        box-shadow: 0 0 0 3px rgba(20, 54, 99, 0.1);
    }

    .filter-actions {
        margin-left: auto;
        display: flex;
        gap: 8px;
    }

    .btn-filter {
        padding: 8px 16px;
        border: 1px solid var(--gray-300);
        border-radius: 8px;
        font-size: 12px;
        font-weight: 500;
        color: var(--gray-600);
        background: var(--white);
        cursor: pointer;
        transition: all 0.15s;
    }

    .btn-filter:hover {
        background: var(--gray-50);
        border-color: var(--gray-400);
    }

    .btn-filter.apply {
        background: var(--navy-700);
        border-color: var(--navy-700);
        color: var(--white);
    }

    .btn-filter.apply:hover {
        background: var(--navy-800);
    }

    /* Data Table */
    .data-card {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: 16px;
        overflow: hidden;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        text-align: left;
        padding: 14px 16px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--gray-500);
        background: var(--gray-50);
        border-bottom: 1px solid var(--gray-200);
    }

    .data-table td {
        padding: 14px 16px;
        font-size: 13px;
        color: var(--gray-900);
        border-bottom: 1px solid var(--gray-100);
        vertical-align: middle;
    }

    .data-table tr:last-child td {
        border-bottom: none;
    }

    .data-table tbody tr {
        transition: background 0.15s;
    }

    .data-table tbody tr:hover {
        background: var(--gray-50);
    }

    .data-table tbody tr.overbreak {
        background: var(--red-50);
    }

    .data-table tbody tr.overbreak:hover {
        background: var(--red-100);
    }

    /* Agent Cell */
    .agent-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .agent-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--navy-700);
        color: var(--white);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 600;
        flex-shrink: 0;
    }

    .agent-info {
        min-width: 0;
    }

    .agent-name {
        font-size: 13px;
        font-weight: 500;
        color: var(--gray-900);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .agent-email {
        font-size: 11px;
        color: var(--gray-500);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .status-badge.on-break {
        background: var(--green-50);
        color: var(--green-600);
    }

    .status-badge.on-break::before {
        content: '';
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--green-600);
    }

    .status-badge.on-overbreak {
        background: var(--red-100);
        color: var(--red-600);
    }

    .status-badge.on-overbreak::before {
        content: '';
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--red-600);
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* Pending Alerts Badge */
    .pending-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: var(--red-100);
        color: var(--red-600);
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        animation: pulse 1.5s infinite;
    }

    /* Time Cells */
    .time-cell {
        font-size: 13px;
        color: var(--gray-900);
        white-space: nowrap;
    }

    .time-elapsed {
        font-weight: 600;
        color: var(--gray-900);
    }

    .time-elapsed.overbreak {
        color: var(--red-600);
    }

    /* Break Type Badge */
    .break-type {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        background: var(--gray-100);
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
        color: var(--gray-600);
    }

    /* Empty State */
    .empty-state {
        padding: 48px 24px;
        text-align: center;
    }

    .empty-state svg {
        width: 48px;
        height: 48px;
        color: var(--gray-300);
        margin: 0 auto 16px;
    }

    .empty-state h3 {
        font-size: 14px;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 4px;
    }

    .empty-state p {
        font-size: 13px;
        color: var(--gray-500);
    }

    /* Toast Notification */
    .toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        padding: 12px 20px;
        background: var(--gray-900);
        color: var(--white);
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        z-index: 1000;
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
    }

    .toast.show {
        opacity: 1;
        transform: translateY(0);
    }

    .toast.success {
        background: var(--green-600);
    }

    .toast.error {
        background: var(--red-600);
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .page-wrapper {
            padding: 16px;
        }

        .topbar {
            margin: -16px -16px 16px -16px;
            padding: 0 16px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .filter-bar {
            flex-wrap: wrap;
        }

        .page-header {
            flex-direction: column;
            gap: 16px;
        }

        .page-header-actions {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Top Bar -->
    <div class="topbar">
        <div class="topbar-left">
            <h2 class="page-title">Overbreaks</h2>
        </div>
        <div class="topbar-right">
            <div class="status-indicator">
                <span id="lastUpdated">Live</span>
            </div>
            <!-- Pending Alerts Badge -->
            <div id="pendingAlertsBadge" class="pending-badge" style="display: none;">
                <span id="pendingCount">0</span> pending alert(s)
            </div>
        </div>
    </div>

    <!-- Page Header with Voice Alert -->
        <div class="page-header">
            <div class="page-header-text">
                <h2>Overbreak Monitoring</h2>
                <p>Monitor agent breaks and overbreak alerts in real-time</p>
            </div>
            <div class="page-header-actions">
                <button type="button" class="btn-test-voice" id="testVoiceBtn" onclick="testVoiceAlert()">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                    </svg>
                    Test Voice Alert
                </button>
                <button type="button" class="btn-voice-alert" onclick="triggerVoiceAlert()">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                    </svg>
                    Flag All Overbreaks
                </button>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card on-break">
                <div class="stat-card-header">
                    <span class="stat-label">On Break</span>
                    <div class="stat-icon on-break">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-value" id="statOnBreak">{{ $onBreak->count() }}</div>
                <div class="stat-subtitle">Agents currently on break</div>
            </div>

            <div class="stat-card on-overbreak">
                <div class="stat-card-header">
                    <span class="stat-label">On Overbreak</span>
                    <div class="stat-icon on-overbreak">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-value" id="statOnOverbreak">{{ $overbreak->count() }}</div>
                <div class="stat-subtitle">Breaks exceeding expected time</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-label">Agents Overbreak</span>
                    <div class="stat-icon on-overbreak">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-value" id="statAgentsOverbreak">{{ $overbreakStats['agents'] }}</div>
                <div class="stat-subtitle">Unique agents on overbreak</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-label">Total Overbreak Time</span>
                    <div class="stat-icon on-overbreak">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="stat-value" id="statTotalOverTime">{{ $overbreakStats['total_over_minutes'] }}</div>
                <div class="stat-subtitle">Total minutes in overbreak</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="filter-group">
                <label class="filter-label" for="filterDepartment">Department:</label>
                <select class="filter-select" id="filterDepartment">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department }}" {{ $filters['department'] === $department ? 'selected' : '' }}>{{ $department }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label" for="filterStatus">Status:</label>
                <select class="filter-select" id="filterStatus">
                    <option value="">All Statuses</option>
                    <option value="on_break" {{ $filters['status'] === 'on_break' ? 'selected' : '' }}>On Break</option>
                    <option value="overbreak" {{ $filters['status'] === 'overbreak' ? 'selected' : '' }}>Overbreak</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="button" class="btn-filter" onclick="clearFilters()">Clear</button>
                <button type="button" class="btn-filter apply" onclick="applyFilters()">Apply</button>
            </div>
        </div>

        <!-- Data Table -->
        <div class="data-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Department</th>
                        <th>Break Type</th>
                        <th>Start Time</th>
                        <th>Expected End</th>
                        <th>Elapsed</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="breaksTableBody">
                    @forelse($allBreaks as $break)
                        <tr class="{{ $break->is_overbreak ? 'overbreak' : '' }}" data-status="{{ $break->is_overbreak ? 'overbreak' : 'on_break' }}" data-department="{{ $break->department ?? '' }}">
                            <td>
                                <div class="agent-cell">
                                    <div class="agent-avatar">{{ strtoupper(substr($break->agent_name ?? $break->user->name ?? 'A', 0, 1)) }}</div>
                                    <div class="agent-info">
                                        <div class="agent-name">{{ $break->agent_name ?? $break->user->name ?? 'Unknown Agent' }}</div>
                                        <div class="agent-email">{{ $break->agent_email ?? $break->user->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $break->department ?? 'N/A' }}</td>
                            <td>
                                <span class="break-type">{{ $break->break_type ?? 'Break' }}</span>
                            </td>
                            <td class="time-cell">{{ $break->started_at ? $break->started_at->format('H:i:s') : 'N/A' }}</td>
                            <td class="time-cell">{{ $break->expected_end_at ? $break->expected_end_at->format('H:i:s') : 'N/A' }}</td>
                            <td class="time-elapsed {{ $break->is_overbreak ? 'overbreak' : '' }}" data-started="{{ $break->started_at?->toISOString() }}">
                                {{ $break->started_at ? $break->started_at->diffInMinutes(now()) . ' min' : 'N/A' }}
                            </td>
                            <td>
                                @if($break->is_overbreak)
                                    <span class="status-badge on-overbreak">Overbreak</span>
                                @else
                                    <span class="status-badge on-break">On Break</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <h3>No Active Breaks</h3>
                                    <p>There are currently no agents on break or overbreak.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Toast Container -->

<script>
    // Track current playing alert
    let currentAudio = null;
    let pendingAlertsData = [];
    let isPlaying = false;

    // Fetch pending alerts from server
    async function fetchPendingAlerts() {
        try {
            const response = await fetch('/alerts/pending');
            if (!response.ok) {
                console.error('Failed to fetch alerts:', response.status);
                return;
            }
            const data = await response.json();

            if (data.alerts && data.alerts.length > 0) {
                pendingAlertsData = data.alerts;
                document.getElementById('pendingCount').textContent = data.count;
                document.getElementById('pendingAlertsBadge').style.display = 'inline-flex';
            } else {
                pendingAlertsData = [];
                document.getElementById('pendingAlertsBadge').style.display = 'none';
            }
        } catch (error) {
            console.error('Failed to fetch pending alerts:', error);
        }
    }

    // Play the next pending audio alert (fetches fresh audio from server)
    async function playNextAlert() {
        if (pendingAlertsData.length === 0) {
            showToast('No more pending alerts', 'error');
            isPlaying = false;
            return;
        }

        const alertData = pendingAlertsData.shift();
        document.getElementById('pendingCount').textContent = pendingAlertsData.length;

        if (pendingAlertsData.length === 0) {
            document.getElementById('pendingAlertsBadge').style.display = 'none';
        }

        try {
            // Fetch fresh audio from server with agent info
            const params = new URLSearchParams({
                agent_name: alertData.agent_name,
                over_minutes: alertData.over_minutes || 0,
                warning_number: alertData.warning_number || 1
            });

            showToast(`Playing alert for ${alertData.agent_name} (Warning ${alertData.warning_number})...`, 'success');

            const response = await fetch(`/alerts/overbreak?${params}`);
            if (!response.ok) {
                showToast(`Failed to get audio for ${alertData.agent_name}`, 'error');
                playNextAlert(); // Try next alert
                return;
            }

            const audioBlob = await response.blob();

            if (currentAudio) {
                currentAudio.pause();
                currentAudio = null;
            }

            const audioUrl = URL.createObjectURL(audioBlob);
            currentAudio = new Audio(audioUrl);

            currentAudio.onended = function() {
                URL.revokeObjectURL(audioUrl);
                showToast(`Alert for ${alertData.agent_name} completed`, 'success');
                playNextAlert(); // Play next alert
            };
            currentAudio.onerror = function() {
                URL.revokeObjectURL(audioUrl);
                showToast(`Failed to play alert for ${alertData.agent_name}`, 'error');
                playNextAlert(); // Try next alert
            };

            await currentAudio.play();
        } catch (error) {
            showToast(`Error playing alert: ${error.message}`, 'error');
            isPlaying = false;
        }
    }

    // Voice Alert Function - plays pending ElevenLabs audio for overbreak agents
    async function triggerVoiceAlert() {
        if (isPlaying) {
            showToast('Already playing alerts', 'error');
            return;
        }

        // First fetch the latest pending alerts
        await fetchPendingAlerts();

        if (pendingAlertsData.length === 0) {
            showToast('No pending alerts. Alerts are generated automatically every minute.', 'error');
            return;
        }

        // Start playing alerts sequentially
        isPlaying = true;
        playNextAlert();
    }

    // Test Voice Alert - plays a generic test audio in-page
    async function testVoiceAlert() {
        try {
            const response = await fetch('/alerts/test');
            if (!response.ok) {
                showToast('Test audio failed', 'error');
                return;
            }
            const audioBlob = await response.blob();
            const audioUrl = URL.createObjectURL(audioBlob);

            if (currentAudio) {
                currentAudio.pause();
            }

            currentAudio = new Audio(audioUrl);
            currentAudio.onended = function() {
                URL.revokeObjectURL(audioUrl);
            };
            currentAudio.onerror = function() {
                URL.revokeObjectURL(audioUrl);
                showToast('Test audio playback failed', 'error');
            };

            showToast('Playing test alert...', 'success');
            await currentAudio.play();
        } catch (error) {
            showToast(`Error: ${error.message}`, 'error');
        }
    }

    // Toast notification helper
    function showToast(message, type = 'success') {
        // Remove existing toast
        const existing = document.querySelector('.toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);

        // Auto remove
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Filter Functions
    function applyFilters() {
        const department = document.getElementById('filterDepartment').value;
        const status = document.getElementById('filterStatus').value;
        const rows = document.querySelectorAll('#breaksTableBody tr[data-status]');

        rows.forEach(function(row) {
            let show = true;

            if (department && row.dataset.department !== department) {
                show = false;
            }

            if (status && row.dataset.status !== status) {
                show = false;
            }

            row.style.display = show ? '' : 'none';
        });
    }

    function clearFilters() {
        document.getElementById('filterDepartment').value = '';
        document.getElementById('filterStatus').value = '';

        const rows = document.querySelectorAll('#breaksTableBody tr[data-status]');
        rows.forEach(function(row) {
            row.style.display = '';
        });
    }

    // Update elapsed time display
    function updateElapsedTimes() {
        const elapsedCells = document.querySelectorAll('.time-elapsed[data-started]');
        const now = new Date();

        elapsedCells.forEach(function(cell) {
            const started = new Date(cell.dataset.started);
            const diffMs = now - started;
            const diffMins = Math.floor(diffMs / 60000);
            const diffSecs = Math.floor((diffMs % 60000) / 1000);

            const timeStr = diffMins + ' min ' + diffSecs + ' sec';
            cell.textContent = timeStr;
        });
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Refresh table data from server
    async function refreshTableData() {
        try {
            const response = await fetch('/overbreaks/live');
            if (!response.ok) return;

            const data = await response.json();
            const tbody = document.getElementById('breaksTableBody');
            if (!tbody) return;

            // Combine on_break and overbreak, sort overbreak to top
            const allBreaks = [
                ...(data.overbreak || []).map(b => ({ ...b, is_overbreak: true })),
                ...(data.on_break || []).map(b => ({ ...b, is_overbreak: false }))
            ];

            // Update stats
            const onBreakCount = data.on_break ? data.on_break.length : 0;
            const overbreakCount = data.overbreak ? data.overbreak.length : 0;
            const totalAgents = new Set([...data.overbreak, ...data.on_break].map(b => b.user_email)).size;
            const totalMinutes = (data.overbreak || []).reduce((sum, b) => sum + (b.over_minutes || 0), 0);

            document.getElementById('statOnBreak').textContent = onBreakCount;
            document.getElementById('statOnOverbreak').textContent = overbreakCount;
            document.getElementById('statAgentsOverbreak').textContent = overbreakCount > 0 ? new Set(data.overbreak.map(b => b.user_email)).size : 0;
            document.getElementById('statTotalOverTime').textContent = totalMinutes;

            // Build table rows using DOM methods for safety
            if (allBreaks.length === 0) {
                tbody.innerHTML = '';
                const tr = document.createElement('tr');
                tr.innerHTML = '<td colspan="7"><div class="empty-state"><h3>No Active Breaks</h3><p>There are currently no agents on break or overbreak.</p></div></td>';
                tbody.appendChild(tr);
                return;
            }

            tbody.innerHTML = '';
            allBreaks.forEach(function(breakData) {
                const isOverbreak = breakData.is_overbreak;
                const startedAt = new Date(breakData.started_at);
                const expectedEnd = new Date(breakData.expected_end_at);
                const initials = escapeHtml((breakData.user_name || 'A').charAt(0).toUpperCase());
                const userName = escapeHtml(breakData.user_name || 'Unknown Agent');
                const userEmail = escapeHtml(breakData.user_email || '');
                const department = escapeHtml(breakData.department || 'N/A');
                const breakType = escapeHtml(breakData.break_type || 'Break');

                const tr = document.createElement('tr');
                tr.className = isOverbreak ? 'overbreak' : '';
                tr.dataset.status = isOverbreak ? 'overbreak' : 'on_break';
                tr.dataset.department = department;

                tr.innerHTML = `
                    <td><div class="agent-cell"><div class="agent-avatar">${initials}</div><div class="agent-info"><div class="agent-name">${userName}</div><div class="agent-email">${userEmail}</div></div></div></td>
                    <td>${department}</td>
                    <td><span class="break-type">${breakType}</span></td>
                    <td class="time-cell">${startedAt.toLocaleTimeString()}</td>
                    <td class="time-cell">${expectedEnd.toLocaleTimeString()}</td>
                    <td class="time-elapsed${isOverbreak ? ' overbreak' : ''}" data-started="${breakData.started_at}">--</td>
                    <td>${isOverbreak ? '<span class="status-badge on-overbreak">Overbreak</span>' : '<span class="status-badge on-break">On Break</span>'}</td>
                `;
                tbody.appendChild(tr);
            });

            updateElapsedTimes();
        } catch (error) {
            console.error('Failed to refresh table data:', error);
        }
    }

    // Update last updated timestamp
    function updateLastUpdated() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString();
        const lastUpdatedEl = document.getElementById('lastUpdated');
        if (lastUpdatedEl) {
            lastUpdatedEl.textContent = 'Updated ' + timeStr;
        }
    }

    // Auto-refresh every 5 seconds
    setInterval(function() {
        refreshTableData();
        fetchPendingAlerts();
    }, 5000);

    // Initial update
    refreshTableData();
    fetchPendingAlerts();
</script>
</x-app-layout>
