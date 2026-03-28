<tr style="cursor: pointer;" onclick="window.location='{{ route('tickets.show', $ticket->id) }}'">
    <td style="padding: 12px 16px; border-bottom: 1px solid var(--gray-200); font-size: 13px; color: var(--gray-500);">#{{ $ticket->id }}</td>
    <td style="padding: 12px 16px; border-bottom: 1px solid var(--gray-200); font-size: 13px; color: var(--gray-900);">
        {{ Str::limit($ticket->subject, 50) }}
    </td>
    <td style="padding: 12px 16px; border-bottom: 1px solid var(--gray-200);">
        <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 500; background: var(--gray-100); color: var(--gray-600);">
            {{ $ticket->getCategoryLabel() }}
        </span>
    </td>
    <td style="padding: 12px 16px; border-bottom: 1px solid var(--gray-200);">
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
    <td style="padding: 12px 16px; border-bottom: 1px solid var(--gray-200);">
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
    <td style="padding: 12px 16px; border-bottom: 1px solid var(--gray-200); font-size: 13px; color: var(--gray-500);">
        {{ $ticket->created_at->format('M j, Y') }}
    </td>
</tr>
