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

    /* Detail Grid */
    .detail-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 16px;
    }

    /* Meta Badges */
    .meta-badges {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
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
    .badge-in_progress { background: var(--blue-50); color: var(--blue-600); }
    .badge-resolved { background: var(--green-50); color: var(--green-600); }
    .badge-closed { background: var(--gray-100); color: var(--gray-500); }
    .badge-low { background: var(--gray-100); color: var(--gray-600); }
    .badge-medium { background: var(--yellow-50); color: var(--yellow-600); }
    .badge-high { background: var(--red-50); color: var(--red-600); }

    /* Ticket Subject */
    .ticket-subject {
        font-size: 20px;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 16px;
    }

    /* Ticket Description */
    .ticket-description {
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: 6px;
        padding: 16px;
        font-size: 13px;
        line-height: 1.6;
        white-space: pre-wrap;
        color: var(--gray-500);
        margin-bottom: 20px;
    }

    /* Submitter Info */
    .submitter-info {
        margin-bottom: 20px;
    }

    .submitter-info h4 {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--gray-500);
        margin-bottom: 12px;
    }

    .submitter-info p {
        font-size: 13px;
        color: var(--gray-700);
        margin-bottom: 6px;
    }

    .submitter-info strong {
        color: var(--gray-900);
        font-weight: 500;
    }

    /* Timestamps */
    .timestamps {
        padding-top: 16px;
        border-top: 1px solid var(--gray-100);
    }

    .timestamps p {
        font-size: 12px;
        color: var(--gray-500);
        margin-bottom: 4px;
    }

    .timestamps strong {
        font-weight: 500;
    }

    /* Action Form */
    .action-form {
        margin-bottom: 16px;
    }

    .action-form:last-child {
        margin-bottom: 0;
    }

    .action-form label {
        display: block;
        font-size: 12px;
        font-weight: 500;
        color: var(--gray-700);
        margin-bottom: 6px;
    }

    .form-select {
        width: 100%;
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
        margin-bottom: 8px;
    }

    .form-select:focus {
        outline: none;
        border-color: var(--black);
        box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
    }

    .form-textarea {
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

    .form-textarea:focus {
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

    /* Comments */
    .comments-card {
        margin-top: 16px;
    }

    .comment-item {
        border-left: 3px solid var(--navy-800);
        padding-left: 12px;
        margin-bottom: 16px;
    }

    .comment-admin {
        border-left-color: var(--navy-800);
    }

    .comment-team_lead {
        border-left-color: var(--blue-600);
    }

    .comment-agent {
        border-left-color: var(--gray-400);
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

    .role-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 500;
    }

    .role-badge.role-admin {
        background: var(--navy-800);
        color: var(--white);
    }

    .role-badge.role-team_lead {
        background: var(--blue-50);
        color: var(--blue-600);
    }

    .role-badge.role-agent {
        background: var(--gray-100);
        color: var(--gray-600);
    }

    .comment-time {
        font-size: 12px;
        color: var(--gray-500);
        margin-left: auto;
    }

    .comment-body {
        font-size: 13px;
        color: var(--gray-700);
        line-height: 1.5;
        white-space: pre-wrap;
    }

    .empty-comments {
        text-align: center;
        padding: 24px;
        color: var(--gray-500);
        font-size: 13px;
    }

    /* Error Text */
    .error-text {
        color: var(--red-600);
        font-size: 12px;
        display: block;
        margin-bottom: 8px;
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
        .detail-grid { grid-template-columns: 1fr; }
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
                <h1 class="page-title">Ticket Details</h1>
            </div>
            <div class="topbar-right">
                <a href="{{ route('admin.tickets.index') }}" class="btn btn-secondary">Back to Tickets</a>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content">
            <!-- Back link -->
            <a href="{{ route('admin.tickets.index') }}" class="back-link">← Back to Tickets</a>

            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <!-- Two-column grid -->
            <div class="detail-grid">
                <!-- Left: Ticket Info -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Ticket #{{ $ticket->id }}</h2>
                        <span class="badge badge-{{ $ticket->status }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                    </div>
                    <div class="card-body">
                        <!-- Priority + Category badges -->
                        <div class="meta-badges">
                            <span class="badge badge-{{ $ticket->priority }}">Priority: {{ ucfirst($ticket->priority) }}</span>
                            <span class="badge badge-low">Category: {{ $ticket->category ?? '—' }}</span>
                        </div>

                        <!-- Subject -->
                        <h3 class="ticket-subject">{{ $ticket->subject }}</h3>

                        <!-- Description -->
                        <div class="ticket-description">
                            <p>{{ $ticket->description }}</p>
                        </div>

                        <!-- Submitter Info -->
                        <div class="submitter-info">
                            <h4>Submitted By</h4>
                            <p><strong>Name:</strong> {{ $ticket->user->name ?? '—' }}</p>
                            <p><strong>Email:</strong> {{ $ticket->user->email ?? '—' }}</p>
                            <p><strong>Department:</strong> {{ $ticket->user->department ?? '—' }}</p>
                            <p><strong>Team Lead:</strong> {{ $ticket->user->manager_name ?? '—' }} ({{ $ticket->user->tl_email ?? '—' }})</p>
                            <p><strong>Hire Date:</strong> {{ $ticket->user->hire_date ? \Carbon\Carbon::parse($ticket->user->hire_date)->format('M j, Y') : '—' }}</p>
                        </div>

                        <!-- Timestamps -->
                        <div class="timestamps">
                            <p><strong>Created:</strong> {{ $ticket->created_at->format('M j, Y g:i A') }}</p>
                            <p><strong>Last Updated:</strong> {{ $ticket->updated_at->format('M j, Y g:i A') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right: Admin Actions -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Admin Actions</h2>
                    </div>
                    <div class="card-body">
                        <!-- Status Update Form -->
                        <form method="POST" action="{{ route('admin.tickets.status', $ticket->id) }}" class="action-form">
                            @csrf
                            <label>Update Status</label>
                            <select name="status" class="form-select">
                                <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </form>

                        <!-- Priority Update Form -->
                        <form method="POST" action="{{ route('admin.tickets.priority', $ticket->id) }}" class="action-form">
                            @csrf
                            <label>Update Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low" {{ $ticket->priority == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $ticket->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $ticket->priority == 'high' ? 'selected' : '' }}>High</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Update Priority</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="card comments-card">
                <div class="card-header">
                    <h2 class="card-title">Comments ({{ $ticket->comments->count() }})</h2>
                </div>
                <div class="card-body">
                    @forelse($ticket->comments as $comment)
                    <div class="comment-item comment-{{ $comment->user->role ?? 'agent' }}">
                        <div class="comment-header">
                            <span class="comment-author">{{ $comment->user->name ?? 'Unknown' }}</span>
                            @if(isset($comment->user->role))
                                <span class="role-badge role-{{ $comment->user->role }}">{{ str_replace('_', ' ', $comment->user->role) }}</span>
                            @endif
                            <span class="comment-time">{{ $comment->created_at->format('M j, Y g:i A') }}</span>
                        </div>
                        <div class="comment-body">{{ $comment->comment }}</div>
                    </div>
                    @empty
                    <p class="empty-comments">No comments yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Add Comment Form -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Add Comment</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.tickets.comment', $ticket->id) }}">
                        @csrf
                        <textarea name="comment" rows="3" class="form-textarea" placeholder="Write your comment here..." required>{{ old('comment') }}</textarea>
                        @error('comment')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                        <button type="submit" class="btn btn-primary">Post Comment</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
</x-app-layout>
