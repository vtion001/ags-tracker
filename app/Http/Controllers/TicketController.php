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
