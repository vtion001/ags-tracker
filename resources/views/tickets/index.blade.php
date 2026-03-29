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

    /* Alert */
    .alert {
        padding: 12px 14px;
        border-radius: 6px;
        margin-bottom: 16px;
        font-size: 13px;
    }

    .alert-success {
        background: var(--green-50);
        border: 1px solid #bbf7d0;
        color: var(--green-600);
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

    /* Filter Tabs */
    .filter-tabs {
        display: flex;
        gap: 4px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--gray-200);
        background: var(--gray-50);
        border-radius: 8px 8px 0 0;
    }

    .filter-tab {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        color: var(--gray-500);
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .filter-tab:hover {
        background: var(--gray-200);
        color: var(--gray-700);
    }

    .filter-tab.active {
        background: var(--white);
        color: var(--gray-900);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
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

    tbody tr:hover {
        background: var(--gray-50);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 48px 24px;
    }

    .empty-state svg {
        width: 48px;
        height: 48px;
        color: var(--gray-300);
        margin-bottom: 16px;
    }

    .empty-state h3 {
        font-size: 15px;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 8px;
    }

    .empty-state p {
        font-size: 13px;
        color: var(--gray-500);
        margin-bottom: 16px;
    }

    /* Button */
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

    /* Responsive */
    @media (max-width: 1024px) {
        .sidebar { width: 72px; }
        .sidebar-brand h1, .nav-item span, .nav-section-title, .user-info { display: none; }
        .sidebar-header { padding: 16px 12px; }
        .nav-item { justify-content: center; padding: 12px; }
        .user-card { justify-content: center; }
        .main-content { margin-left: 72px; }
    }

    @media (max-width: 768px) {
        .sidebar { display: none; }
        .main-content { margin-left: 0; }
        .topbar { padding: 0 16px; }
        .page-content { padding: 16px; }
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
                <h1 class="page-title">Support Tickets</h1>
            </div>
            <div class="topbar-right">
                <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    New Ticket
                </a>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content">
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <!-- Tickets Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Support Tickets</h3>
                </div>

                <!-- Filter Tabs -->
                <div class="filter-tabs">
                    <a href="{{ route('tickets.index') }}" class="filter-tab {{ !request('status') ? 'active' : '' }}">All</a>
                    <a href="{{ route('tickets.index', ['status' => 'open']) }}" class="filter-tab {{ request('status') === 'open' ? 'active' : '' }}">Open</a>
                    <a href="{{ route('tickets.index', ['status' => 'in_progress']) }}" class="filter-tab {{ request('status') === 'in_progress' ? 'active' : '' }}">In Progress</a>
                    <a href="{{ route('tickets.index', ['status' => 'resolved']) }}" class="filter-tab {{ request('status') === 'resolved' ? 'active' : '' }}">Resolved</a>
                </div>

                <div class="table-wrapper">
                    @if($tickets->isEmpty())
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.355 0-2.697-.056-4.024-.166-1.133-.093-1.98-1.057-1.98-2.193v-4.286c0-.968.616-1.813 1.5-2.097V4.5a2.25 2.25 0 013-2.25h6a2.25 2.25 0 012.25 2.25v2.511z" />
                            </svg>
                            <h3>No support tickets yet</h3>
                            <p>Need help? Submit a support ticket and our team will assist you.</p>
                            <a href="{{ route('tickets.create') }}" class="btn btn-primary">Submit a Ticket</a>
                        </div>
                    @else
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Subject</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tickets as $ticket)
                                    @include('tickets._ticket-row', ['ticket' => $ticket])
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>
</x-app-layout>
