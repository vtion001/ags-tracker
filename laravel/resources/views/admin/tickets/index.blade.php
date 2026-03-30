<x-app-layout>
<style>
    :root {
        --black: #000000;
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

    /* Layout */
    .app-layout {
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
        width: 240px;
        background: var(--gray-950);
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        display: flex;
        flex-direction: column;
    }

    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid var(--gray-800);
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sidebar-logo {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        object-fit: contain;
        filter: brightness(0) invert(1);
    }

    .sidebar-brand h1 {
        font-size: 15px;
        font-weight: 600;
        color: var(--white);
        letter-spacing: -0.01em;
    }

    .sidebar-nav {
        flex: 1;
        padding: 16px 12px;
    }

    .nav-section {
        margin-bottom: 24px;
    }

    .nav-section-title {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--gray-500);
        padding: 0 12px;
        margin-bottom: 8px;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 6px;
        color: var(--gray-400);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.15s ease;
        margin-bottom: 2px;
    }

    .nav-item:hover {
        background: var(--gray-800);
        color: var(--white);
    }

    .nav-item.active {
        background: var(--gray-800);
        color: var(--white);
    }

    .nav-item svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        opacity: 0.7;
    }

    .nav-item:hover svg,
    .nav-item.active svg {
        opacity: 1;
    }

    .sidebar-footer {
        padding: 16px;
        border-top: 1px solid var(--gray-800);
    }

    .user-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px;
        border-radius: 6px;
        background: var(--gray-900);
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        background: var(--gray-700);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 600;
        color: var(--white);
    }

    .user-info {
        flex: 1;
        min-width: 0;
    }

    .user-name {
        font-size: 12px;
        font-weight: 500;
        color: var(--white);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-role {
        font-size: 11px;
        color: var(--gray-500);
    }

    /* Main Content */
    .main-content {
        flex: 1;
        margin-left: 240px;
        display: flex;
        flex-direction: column;
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

    /* Page Content */
    .page-content {
        flex: 1;
        padding: 24px;
    }

    /* Card */
    .card {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        margin-bottom: 16px;
    }

    .card-header {
        padding: 14px 16px;
        border-bottom: 1px solid var(--gray-100);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--gray-900);
    }

    .card-body {
        padding: 16px;
    }

    /* Stat Cards */
    .stats-cards-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        padding: 20px;
    }

    /* Filter Bar */
    .filter-bar-card {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
    }

    .filter-form > div {
        flex: 1;
        min-width: 150px;
    }

    .filter-form label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--gray-500);
        margin-bottom: 6px;
    }

    .filter-form input,
    .filter-form select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid var(--gray-300);
        border-radius: 6px;
        font-size: 13px;
        color: var(--gray-900);
        background: var(--white);
    }

    .filter-form input:focus,
    .filter-form select:focus {
        outline: none;
        border-color: var(--black);
        box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
    }

    .filter-form input::placeholder {
        color: var(--gray-400);
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        height: 36px;
        padding: 0 18px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 500;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.15s ease;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--navy-800);
        color: var(--white);
        border-color: var(--navy-800);
    }

    .btn-primary:hover {
        background: var(--navy-700);
    }

    .btn-secondary {
        background: var(--white);
        color: var(--gray-700);
        border-color: var(--gray-300);
    }

    .btn-secondary:hover {
        background: var(--gray-50);
    }

    /* Table */
    .table-wrapper {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead th {
        padding: 12px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--gray-500);
        background: var(--gray-50);
        border-bottom: 1px solid var(--gray-200);
    }

    tbody tr {
        border-bottom: 1px solid var(--gray-200);
    }

    tbody td {
        padding: 12px 16px;
        font-size: 13px;
        color: var(--gray-700);
    }

    /* Pagination */
    .pagination-wrapper {
        padding: 16px;
        border-top: 1px solid var(--gray-200);
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .sidebar { width: 72px; }
        .sidebar-brand h1, .nav-item span, .nav-section-title, .user-info { display: none; }
        .sidebar-header { padding: 16px 12px; }
        .nav-item { justify-content: center; padding: 12px; }
        .user-card { justify-content: center; }
        .main-content { margin-left: 72px; }
        .stats-cards-row { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
        .sidebar { display: none; }
        .main-content { margin-left: 0; }
        .topbar { padding: 0 16px; }
        .page-content { padding: 16px; }
        .filter-form { flex-direction: column; }
        .stats-cards-row { grid-template-columns: 1fr; }
    }
</style>

<!-- App Layout -->
<div class="app-layout">
    <!-- Sidebar -->
    @include('components.sidebar')

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <header class="topbar">
            <div class="topbar-left">
                <h1 class="page-title">Ticket Management</h1>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content">

            <!-- Stats Cards Row -->
            <div class="stats-cards-row">
                <div class="stat-card">
                    <div style="font-size: 28px; font-weight: 700; color: var(--gray-900);">{{ $stats['total'] }}</div>
                    <div style="font-size: 13px; color: var(--gray-500); margin-top: 4px;">Total Tickets</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 28px; font-weight: 700; color: #a16207;">{{ $stats['open'] }}</div>
                    <div style="font-size: 13px; color: var(--gray-500); margin-top: 4px;">Open Tickets</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 28px; font-weight: 700; color: #15803d;">{{ $stats['resolved_this_week'] }}</div>
                    <div style="font-size: 13px; color: var(--gray-500); margin-top: 4px;">Resolved This Week</div>
                </div>
                <div class="stat-card">
                    <div style="font-size: 28px; font-weight: 700; color: var(--red-600);">{{ $stats['high_priority_open'] }}</div>
                    <div style="font-size: 13px; color: var(--gray-500); margin-top: 4px;">High Priority Open</div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar-card">
                <form method="GET" action="{{ route('admin.tickets.index') }}" class="filter-form">
                    <div style="min-width: 200px;">
                        <label>Search</label>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search subject...">
                    </div>
                    <div>
                        <label>Status</label>
                        <select name="status">
                            <option value="">All Statuses</option>
                            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div>
                        <label>Category</label>
                        <select name="category">
                            <option value="">All Categories</option>
                            <option value="bug_error" {{ request('category') === 'bug_error' ? 'selected' : '' }}>Bug/Error</option>
                            <option value="feature_request" {{ request('category') === 'feature_request' ? 'selected' : '' }}>Feature Request</option>
                            <option value="schedule_issue" {{ request('category') === 'schedule_issue' ? 'selected' : '' }}>Schedule Issue</option>
                            <option value="access_problem" {{ request('category') === 'access_problem' ? 'selected' : '' }}>Access Problem</option>
                            <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div>
                        <label>Priority</label>
                        <select name="priority">
                            <option value="">All Priorities</option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 8px; flex: 0;">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.tickets.index') }}" class="btn btn-secondary">Clear</a>
                    </div>
                </form>
            </div>

            <!-- Tickets Table Card -->
            <div class="card">
                <div class="card-body" style="padding: 0;">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Subject</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Submitted By</th>
                                    <th>Team</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tickets as $ticket)
                                <tr>
                                    <td>#{{ $ticket->id }}</td>
                                    <td title="{{ $ticket->subject }}">{{ Str::limit($ticket->subject, 40) }}</td>
                                    <td>
                                        <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 500; background: var(--gray-100); color: var(--gray-600);">
                                            {{ $ticket->getCategoryLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $priorityClasses = [
                                                'low' => 'background: var(--gray-100); color: var(--gray-600);',
                                                'medium' => 'background: #fef9c3; color: #a16207;',
                                                'high' => 'background: var(--red-50); color: var(--red-600);',
                                            ];
                                            $priorityStyle = $priorityClasses[$ticket->priority] ?? $priorityClasses['low'];
                                        @endphp
                                        <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 500; {{ $priorityStyle }}">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClasses = [
                                                'open' => 'background: #fef9c3; color: #a16207;',
                                                'in_progress' => 'background: #dbeafe; color: #1d4ed8;',
                                                'resolved' => 'background: #dcfce7; color: #15803d;',
                                                'closed' => 'background: var(--gray-100); color: var(--gray-500);',
                                            ];
                                            $statusLabels = [
                                                'open' => '● Open',
                                                'in_progress' => '◐ In Progress',
                                                'resolved' => '✓ Resolved',
                                                'closed' => '○ Closed',
                                            ];
                                            $statusStyle = $statusClasses[$ticket->status] ?? $statusClasses['open'];
                                            $statusLabel = $statusLabels[$ticket->status] ?? $statusLabels['open'];
                                        @endphp
                                        <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 500; {{ $statusStyle }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td>{{ $ticket->user->name ?? '—' }}</td>
                                    <td>{{ $ticket->user->department ?? '—' }}</td>
                                    <td>{{ $ticket->created_at->format('M j, Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.tickets.show', $ticket->id) }}" style="display: inline-block; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500; background: var(--navy-800); color: var(--white); text-decoration: none;">View</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 48px 24px; color: var(--gray-500);">No tickets match your filters.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($tickets->hasPages())
                    <div class="pagination-wrapper">
                        {{ $tickets->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </main>
</div>
</x-app-layout>
