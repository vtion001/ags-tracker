<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketComment;
use App\Notifications\NewTicketCreated;
use App\Notifications\TicketStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AdminTicketController extends Controller
{
    /**
     * Display all tickets with optional filters.
     * Admin sees all. Team Lead sees only their team's tickets.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Base query
        $query = SupportTicket::with('user');

        // Team leads only see their team's tickets
        if ($user->isTeamLead()) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('tl_email', $user->email);
            });
        }
        // Admin sees all — no additional filter

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where('subject', 'like', "%{$search}%");
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(10);

        // Stats for header cards
        $statsQuery = SupportTicket::query();
        if ($user->isTeamLead()) {
            $statsQuery->whereHas('user', fn($q) => $q->where('tl_email', $user->email));
        }
        $totalCount = (clone $statsQuery)->count();
        $openCount = (clone $statsQuery)->where('status', 'open')->count();
        $resolvedThisWeek = (clone $statsQuery)
            ->where('status', 'resolved')
            ->where('updated_at', '>=', now()->startOfWeek())
            ->count();
        $highPriorityOpen = (clone $statsQuery)
            ->where('priority', 'high')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        return view('admin.tickets.index', [
            'tickets' => $tickets,
            'user' => $user,
            'stats' => [
                'total' => $totalCount,
                'open' => $openCount,
                'resolved_this_week' => $resolvedThisWeek,
                'high_priority_open' => $highPriorityOpen,
            ],
            'filters' => $request->only(['status', 'category', 'priority', 'q']),
        ]);
    }

    /**
     * Show a single ticket with comments.
     */
    public function show(int $id): View
    {
        $user = Auth::user();
        $ticket = SupportTicket::with(['user', 'comments.user'])->findOrFail($id);

        // Authorization: admin sees all, TL sees only their team
        if ($user->isTeamLead() && $ticket->user->tl_email !== $user->email) {
            abort(403);
        }

        return view('admin.tickets.show', [
            'ticket' => $ticket,
            'user' => $user,
        ]);
    }

    /**
     * Add a comment to a ticket.
     */
    public function addComment(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'comment' => ['required', 'string', 'max:5000'],
        ]);

        $ticket = SupportTicket::findOrFail($id);
        $user = Auth::user();

        // Authorization
        if ($user->isTeamLead() && $ticket->user->tl_email !== $user->email) {
            abort(403);
        }

        SupportTicketComment::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => $request->input('comment'),
        ]);

        return redirect()->route('admin.tickets.show', $ticket->id)
            ->with('success', 'Comment added.');
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        $ticket = SupportTicket::findOrFail($id);
        $user = Auth::user();

        // Authorization
        if ($user->isTeamLead() && $ticket->user->tl_email !== $user->email) {
            abort(403);
        }

        $oldStatus = $ticket->status;
        $ticket->update(['status' => $request->input('status')]);

        // Send email notification to ticket creator
        if ($ticket->user) {
            $ticket->user->notify(new TicketStatusChanged($ticket, $user, $oldStatus, $request->input('status')));
        }

        return redirect()->route('admin.tickets.show', $ticket->id)
            ->with('success', 'Ticket status updated.');
    }

    /**
     * Update ticket priority.
     */
    public function updatePriority(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'priority' => ['required', 'in:low,medium,high'],
        ]);

        $ticket = SupportTicket::findOrFail($id);
        $user = Auth::user();

        if ($user->isTeamLead() && $ticket->user->tl_email !== $user->email) {
            abort(403);
        }

        $ticket->update(['priority' => $request->input('priority')]);

        return redirect()->route('admin.tickets.show', $ticket->id)
            ->with('success', 'Ticket priority updated.');
    }
}
