<x-app-layout>
<div class="page-content">
    <!-- Header -->
    <div class="page-header">
        <h1 class="page-title">Ticket Management</h1>
    </div>

    <!-- Stats Cards Row -->
    <div class="stats-cards-row" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
        <div class="stat-card" style="background: var(--white); border: 1px solid var(--gray-200); border-radius: 8px; padding: 20px;">
            <div style="font-size: 28px; font-weight: 700; color: var(--gray-900);">{{ $stats['total'] }}</div>
            <div style="font-size: 13px; color: var(--gray-500); margin-top: 4px;">Total Tickets</div>
        </div>
        <div class="stat-card" style="background: var(--white); border: 1px solid var(--gray-200); border-radius: 8px; padding: 20px;">
            <div style="font-size: 28px; font-weight: 700; color: #a16207;">{{ $stats['open'] }}</div>
            <div style="font-size: 13px; color: var(--gray-500); margin-top: 4px;">Open Tickets</div>
        </div>
        <div class="stat-card" style="background: var(--white); border: 1px solid var(--gray-200); border-radius: 8px; padding: 20px;">
            <div style="font-size: 28px; font-weight: 700; color: #15803d;">{{ $stats['resolved_this_week'] }}</div>
            <div style="font-size: 13px; color: var(--gray-500); margin-top: 4px;">Resolved This Week</div>
        </div>
        <div class="stat-card" style="background: var(--white); border: 1px solid var(--gray-200); border-radius: 8px; padding: 20px;">
            <div style="font-size: 28px; font-weight: 700; color: var(--red-600);">{{ $stats['high_priority_open'] }}</div>
            <div style="font-size: 13px; color: var(--gray-500); margin-top: 4px;">High Priority Open</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar-card" style="background: var(--white); border: 1px solid var(--gray-200); border-radius: 8px; padding: 16px; margin-bottom: 16px;">
        <form method="GET" action="{{ route('admin.tickets.index') }}" class="filter-form" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); margin-bottom: 6px;">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search subject..." style="width: 100%; padding: 8px 12px; border: 1px solid var(--gray-300); border-radius: 6px; font-size: 13px; color: var(--gray-900); background: var(--white);">
            </div>
            <div style="flex: 1; min-width: 150px;">
                <label style="display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); margin-bottom: 6px;">Status</label>
                <select name="status" style="width: 100%; padding: 8px 12px; border: 1px solid var(--gray-300); border-radius: 6px; font-size: 13px; color: var(--gray-900); background: var(--white);">
                    <option value="">All Statuses</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 150px;">
                <label style="display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); margin-bottom: 6px;">Category</label>
                <select name="category" style="width: 100%; padding: 8px 12px; border: 1px solid var(--gray-300); border-radius: 6px; font-size: 13px; color: var(--gray-900); background: var(--white);">
                    <option value="">All Categories</option>
                    <option value="bug_error" {{ request('category') === 'bug_error' ? 'selected' : '' }}>Bug/Error</option>
                    <option value="feature_request" {{ request('category') === 'feature_request' ? 'selected' : '' }}>Feature Request</option>
                    <option value="schedule_issue" {{ request('category') === 'schedule_issue' ? 'selected' : '' }}>Schedule Issue</option>
                    <option value="access_problem" {{ request('category') === 'access_problem' ? 'selected' : '' }}>Access Problem</option>
                    <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 150px;">
                <label style="display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); margin-bottom: 6px;">Priority</label>
                <select name="priority" style="width: 100%; padding: 8px 12px; border: 1px solid var(--gray-300); border-radius: 6px; font-size: 13px; color: var(--gray-900); background: var(--white);">
                    <option value="">All Priorities</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                </select>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="height: 36px; padding: 0 16px; border-radius: 6px; font-size: 13px; font-weight: 500; background: var(--navy-800); color: var(--white); border: none; cursor: pointer;">Filter</button>
                <a href="{{ route('admin.tickets.index') }}" style="height: 36px; padding: 0 16px; border-radius: 6px; font-size: 13px; font-weight: 500; background: var(--white); color: var(--gray-700); border: 1px solid var(--gray-300); text-decoration: none; display: inline-flex; align-items: center;">Clear</a>
            </div>
        </form>
    </div>

    <!-- Tickets Table Card -->
    <div class="card" style="background: var(--white); border: 1px solid var(--gray-200); border-radius: 8px;">
        <div class="card-body" style="padding: 0;">
            <div class="table-wrapper" style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); background: var(--gray-50); border-bottom: 1px solid var(--gray-200);">ID</th>
                            <th style="padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); background: var(--gray-50); border-bottom: 1px solid var(--gray-200);">Subject</th>
                            <th style="padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); background: var(--gray-50); border-bottom: 1px solid var(--gray-200);">Category</th>
                            <th style="padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); background: var(--gray-50); border-bottom: 1px solid var(--gray-200);">Priority</th>
                            <th style="padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); background: var(--gray-50); border-bottom: 1px solid var(--gray-200);">Status</th>
                            <th style="padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); background: var(--gray-50); border-bottom: 1px solid var(--gray-200);">Submitted By</th>
                            <th style="padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); background: var(--gray-50); border-bottom: 1px solid var(--gray-200);">Team</th>
                            <th style="padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); background: var(--gray-50); border-bottom: 1px solid var(--gray-200);">Created</th>
                            <th style="padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); background: var(--gray-50); border-bottom: 1px solid var(--gray-200);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        <tr style="border-bottom: 1px solid var(--gray-200);">
                            <td style="padding: 12px 16px; font-size: 13px; color: var(--gray-500);">#{{ $ticket->id }}</td>
                            <td style="padding: 12px 16px; font-size: 13px; color: var(--gray-900);" title="{{ $ticket->subject }}">{{ Str::limit($ticket->subject, 40) }}</td>
                            <td style="padding: 12px 16px;">
                                <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 500; background: var(--gray-100); color: var(--gray-600);">
                                    {{ $ticket->getCategoryLabel() }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px;">
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
                            <td style="padding: 12px 16px;">
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
                            <td style="padding: 12px 16px; font-size: 13px; color: var(--gray-700);">{{ $ticket->user->name ?? '—' }}</td>
                            <td style="padding: 12px 16px; font-size: 13px; color: var(--gray-500);">{{ $ticket->user->department ?? '—' }}</td>
                            <td style="padding: 12px 16px; font-size: 13px; color: var(--gray-500);">{{ $ticket->created_at->format('M j, Y') }}</td>
                            <td style="padding: 12px 16px;">
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
            <div class="pagination-wrapper" style="padding: 16px; border-top: 1px solid var(--gray-200);">
                {{ $tickets->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
</x-app-layout>