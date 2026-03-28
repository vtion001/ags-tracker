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
        --blue-600: #2563eb;
        --blue-50: #eff6ff;
        --yellow-600: #ca8a04;
        --yellow-50: #fef9c3;
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

    /* Cards */
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

    /* Ticket Detail Grid */
    .ticket-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 20px;
    }

    /* Ticket Info */
    .ticket-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .ticket-id {
        font-size: 13px;
        font-weight: 600;
        color: var(--gray-500);
    }

    .ticket-subject {
        font-size: 20px;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 16px;
    }

    .ticket-meta {
        display: flex;
        gap: 24px;
        margin-bottom: 16px;
    }

    .ticket-meta-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .ticket-meta-label {
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--gray-500);
    }

    .ticket-meta-value {
        font-size: 13px;
        color: var(--gray-900);
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
    }

    .badge-open { background: var(--yellow-50); color: var(--yellow-600); }
    .badge-in-progress { background: var(--blue-50); color: var(--blue-600); }
    .badge-resolved { background: var(--green-50); color: var(--green-600); }
    .badge-closed { background: var(--gray-100); color: var(--gray-500); }
    .badge-low { background: var(--gray-100); color: var(--gray-600); }
    .badge-medium { background: var(--yellow-50); color: var(--yellow-600); }
    .badge-high { background: var(--red-50); color: var(--red-600); }
    .badge-category { background: var(--gray-100); color: var(--gray-600); }

    /* Ticket Description */
    .ticket-description {
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: 6px;
        padding: 16px;
        font-size: 13px;
        line-height: 1.6;
        white-space: pre-wrap;
        color: var(--gray-700);
    }

    .ticket-created {
        margin-top: 16px;
        font-size: 12px;
        color: var(--gray-500);
    }

    /* Status Update Form */
    .status-form {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .status-form select {
        padding: 10px 12px;
        border-radius: 6px;
        border: 1px solid var(--gray-300);
        background: var(--white);
        font-size: 13px;
        font-family: inherit;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
    }

    .status-form select:focus {
        outline: none;
        border-color: var(--black);
        box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
    }

    /* Comments */
    .comments-section {
        margin-top: 24px;
    }

    .comments-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
    }

    .comments-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--gray-900);
    }

    .comments-count {
        background: var(--gray-100);
        color: var(--gray-600);
        font-size: 11px;
        font-weight: 500;
        padding: 2px 8px;
        border-radius: 10px;
    }

    .comment {
        display: flex;
        gap: 12px;
        padding: 16px;
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-left: 3px solid var(--navy-800);
        border-radius: 6px;
        margin-bottom: 12px;
    }

    .comment-avatar {
        width: 36px;
        height: 36px;
        border-radius: 6px;
        background: var(--gray-800);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 600;
        color: var(--white);
        flex-shrink: 0;
    }

    .comment-content {
        flex: 1;
        min-width: 0;
    }

    .comment-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }

    .comment-author {
        font-size: 13px;
        font-weight: 600;
        color: var(--gray-900);
    }

    .comment-time {
        font-size: 12px;
        color: var(--gray-500);
    }

    .comment-text {
        font-size: 13px;
        color: var(--gray-700);
        line-height: 1.5;
        white-space: pre-wrap;
    }

    .no-comments {
        text-align: center;
        padding: 32px;
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: 6px;
        color: var(--gray-500);
        font-size: 13px;
    }

    /* Add Comment Form */
    .add-comment-form {
        margin-top: 24px;
    }

    .add-comment-form textarea {
        width: 100%;
        padding: 12px;
        border-radius: 6px;
        border: 1px solid var(--gray-300);
        background: var(--white);
        font-size: 13px;
        font-family: inherit;
        resize: vertical;
        min-height: 100px;
        margin-bottom: 12px;
    }

    .add-comment-form textarea:focus {
        outline: none;
        border-color: var(--black);
        box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
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

    /* Back link */
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--gray-500);
        text-decoration: none;
        margin-bottom: 16px;
    }

    .back-link:hover {
        color: var(--gray-700);
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
        .ticket-grid { grid-template-columns: 1fr; }
    }
</style>

<!-- App Layout -->
<div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <img src="{{ asset('agslogo-128.png') }}" alt="AGS" class="sidebar-logo" onerror="this.style.display='none'">
                <h1>AGS Break Tracker</h1>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Menu</div>
                <a href="{{ route('dashboard') }}" class="nav-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('tickets.index') }}" class="nav-item active">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.355 0-2.697-.056-4.024-.166-1.133-.093-1.98-1.057-1.98-2.193v-4.286c0-.968.616-1.813 1.5-2.097V4.5a2.25 2.25 0 013-2.25h6a2.25 2.25 0 012.25 2.25v2.511z" />
                    </svg>
                    <span>Support Tickets</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="nav-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span>Profile</span>
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

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <header class="topbar">
            <div class="topbar-left">
                <h1 class="page-title">Ticket Details</h1>
            </div>
            <div class="topbar-right">
                <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Back to Tickets</a>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content">
            <a href="{{ route('tickets.index') }}" class="back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Tickets
            </a>

            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="ticket-grid">
                <!-- Left Column - Ticket Info -->
                <div class="card">
                    <div class="card-header">
                        <div class="ticket-header">
                            <span class="ticket-id">Ticket #{{ $ticket->id }}</span>
                            <span class="badge badge-{{ $ticket->status }}">{{ $ticket->getStatusLabel() }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="ticket-subject">{{ $ticket->subject }}</div>

                        <div class="ticket-meta">
                            <div class="ticket-meta-item">
                                <span class="ticket-meta-label">Category</span>
                                <span class="badge badge-category">{{ $ticket->getCategoryLabel() }}</span>
                            </div>
                            <div class="ticket-meta-item">
                                <span class="ticket-meta-label">Priority</span>
                                <span class="badge badge-{{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span>
                            </div>
                            <div class="ticket-meta-item">
                                <span class="ticket-meta-label">Submitted By</span>
                                <span class="ticket-meta-value">{{ $ticket->user->name }}</span>
                            </div>
                        </div>

                        <div class="ticket-description">{{ $ticket->description }}</div>

                        <div class="ticket-created">
                            Created {{ $ticket->created_at->format('M j, Y \a\t g:i A') }}
                        </div>
                    </div>
                </div>

                <!-- Right Column - Actions (Admin/TL only) -->
                @if($user->isAdmin() || $user->isTeamLead())
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Actions</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('tickets.status', $ticket->id) }}" class="status-form">
                            @csrf
                            <div>
                                <label for="status" style="display: block; font-size: 12px; font-weight: 500; color: var(--gray-700); margin-bottom: 6px;">Update Status</label>
                                <select id="status" name="status" required>
                                    <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Update Status</button>
                        </form>
                    </div>
                </div>
                @endif
            </div>

            <!-- Comments Section -->
            <div class="comments-section">
                <div class="comments-header">
                    <h3 class="comments-title">Comments</h3>
                    <span class="comments-count">{{ $ticket->comments->count() }}</span>
                </div>

                @if($ticket->comments->isEmpty())
                    <div class="no-comments">No comments yet.</div>
                @else
                    @foreach($ticket->comments as $comment)
                        <div class="comment">
                            <div class="comment-avatar" style="background: {{ ['#0f2847', '#16a34a', '#ca8a04', '#dc2626', '#2563eb'][strlen($comment->user->name) % 5] }}">
                                {{ substr($comment->user->name, 0, 1) }}
                            </div>
                            <div class="comment-content">
                                <div class="comment-header">
                                    <span class="comment-author">{{ $comment->user->name }}</span>
                                    <span class="comment-time">{{ $comment->created_at->format('M j, Y \a\t g:i A') }}</span>
                                </div>
                                <div class="comment-text">{{ $comment->comment }}</div>
                            </div>
                        </div>
                    @endforeach
                @endif

                <!-- Add Comment Form -->
                <div class="card add-comment-form">
                    <div class="card-header">
                        <h3 class="card-title">Add Comment</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('tickets.comment', $ticket->id) }}">
                            @csrf
                            <textarea name="comment" placeholder="Write a comment..." required>{{ old('comment') }}</textarea>
                            @error('comment')
                                <div style="color: var(--red-600); font-size: 12px; margin-bottom: 12px;">{{ $message }}</div>
                            @enderror
                            <button type="submit" class="btn btn-primary">Post Comment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</x-app-layout>
