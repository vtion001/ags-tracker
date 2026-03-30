# Ticket God-View & Email Notifications — Design Spec

## Overview

Implement two missing pieces of the ticket system: (1) a full admin/Team Lead "god-view" for managing all tickets, and (2) email notifications for ticket creation and status changes.

---

## Part 1: Admin/Team Lead God-View

### Route

`GET /admin/tickets` — accessible only to `admin` and `team_lead` roles.

### Controller

`AdminTicketController` at `app/Http/Controllers/AdminTicketController.php`.

**Methods:**
- `index(Request $request)` — list all tickets with filters
- `show(int $id)` — show single ticket with full details + comments thread

### Index Page — `resources/views/admin/tickets/index.blade.php`

**Layout:** Full-width page with page header "Ticket Management".

**Header row:**
- "Ticket Management" title
- Total count badges: "X Open", "X In Progress", "X Resolved"

**Filter bar:**
- Search input (subject, filters by ticket subject — GET ?q=)
- Status dropdown filter (?status=open|in_progress|resolved|closed)
- Category dropdown filter (?category=bug|feature|schedule|access|other)
- Priority dropdown filter (?priority=low|medium|high)
- "Filter" button
- "Clear" link to reset filters

**Stats row (4 metric cards):**
- Total Tickets (all)
- Open Tickets (count where status = open)
- Resolved This Week (count where status = resolved AND updated_at >= start of current week)
- High Priority Open (count where priority = high AND status != closed)

**Tickets table:**
Columns: ID | Subject | Category | Priority | Status | Submitted By | Team | Created | Actions

- **ID**: ticket->id in monospace
- **Subject**: truncated to 40 chars, full text in title attr
- **Category**: colored badge
- **Priority**: colored badge
- **Status**: colored badge
- **Submitted By**: agent's name
- **Team**: agent's department
- **Created**: "Mar 29, 2026" format
- **Actions**: "View" button link to admin ticket detail

**Row styling:** Zebra striping, hover highlight.

**Empty state:** "No tickets match your filters."

**Pagination:** Standard Laravel paginator (10 per page) — links show ?page=N preserving filter params.

### Detail Page — `resources/views/admin/tickets/show.blade.php`

Same layout as the agent `tickets/show.blade.php` but with full admin controls visible to both admin and team_lead roles (not just admin).

**Left column — Ticket Info:**
- Ticket #ID header with status badge
- Priority + Category badges
- Subject (large text)
- Description (white-space preserved)
- Submitted by: name, email, department, hire date
- Team Lead email (tl_email)
- Created at + Updated at timestamps

**Right column — Admin Actions (always visible for admin/TL):**
- Status update dropdown form (POST to route('admin.tickets.status', $ticket->id))
- Priority update dropdown form (POST to route('admin.tickets.priority', $ticket->id))
- "Assign to Me" button (future use — for now, just a placeholder)

**Comments Section:**
- Full comment thread with author avatar, name, role badge, timestamp, comment text
- Comment cards with left border color by role (admin=navy, team_lead=blue, agent=gray)

**Add Comment Form:**
- Textarea + Submit button
- POSTs to route('admin.tickets.comment', $ticket->id)

### Routes to Add

All inside the auth + admin middleware group:

```
GET  /admin/tickets              name('admin.tickets.index')
GET  /admin/tickets/{id}         name('admin.tickets.show')
POST /admin/tickets/{id}/comment  name('admin.tickets.comment')
POST /admin/tickets/{id}/status   name('admin.tickets.status')
POST /admin/tickets/{id}/priority name('admin.tickets.priority')
```

---

## Part 2: Email Notifications

### Notification Classes

**1. `app/Notifications/NewTicketCreated.php`** — sent when agent creates a ticket

Mailable to: Team Lead (ticket owner's `tl_email`) OR Admin (if no TL).

Data passed to mailable:
- `$ticket` (SupportTicket model)
- `$agent` (User model — ticket creator)
- `$teamLeadName` (string)
- `$teamLeadEmail` (string)

Email subject: `[AGSSupport] New Ticket #{{ $ticket->id }} — {{ $ticket->subject }}`

Email body (Markdown):
```
# New Support Ticket Created

**Ticket #{{ $ticket->id }}** has been submitted by **{{ $agent->name }}** ({{ $agent->department }}).

## Details
- **Subject:** {{ $ticket->subject }}
- **Category:** {{ $ticket->category }}
- **Priority:** {{ $ticket->priority }}
- **Description:** {{ $ticket->description }}

[View Ticket]({{ url('/tickets/' . $ticket->id) }})
```

**2. `app/Notifications/TicketStatusChanged.php`** — sent when admin/TL changes ticket status

Mailable to: Ticket creator (agent).

Data passed:
- `$ticket` (SupportTicket model)
- `$changedBy` (User — who made the change)
- `$oldStatus`, `$newStatus`

Email subject: `[AGSSupport] Ticket #{{ $ticket->id }} Status Updated to {{ $newStatus }}`

Email body:
```
# Ticket Status Updated

Your support ticket **"{{ $ticket->subject }}"** (Ticket #{{ $ticket->id }}) has been updated.

**Status:** {{ $oldStatus }} → {{ $newStatus }}
**Updated by:** {{ $changedBy->name }}

[View Ticket]({{ url('/tickets/' . $ticket->id) }})
```

### Where to Send Emails

**NewTicketCreated:**
- Priority: Team Lead's email (from `tl_email` of ticket creator)
- If no TL_email, send to configured admin email (e.g., `admin@allianceglobalsolutions.com`)

**TicketStatusChanged:**
- Send to the ticket creator's email

### Event/Listener Pattern

Use Laravel's event system for clean separation:

**Events:**
- `TicketCreated` — fired in `TicketController::store()` after ticket is saved
- `TicketStatusUpdated` — fired in `AdminTicketController::updateStatus()` after status is updated

**Listeners:**
- `SendNewTicketNotification` — handles NewTicketCreated notification
- `SendStatusChangeNotification` — handles TicketStatusChanged notification

**Event Service Provider** (`app/Providers/EventServiceProvider.php`):
Register the events and listeners in the `$listen` array.

**Alternative (simpler):** Skip the event layer and call `Notification::send()` directly in the controller after the relevant action. Given the straightforward nature of these notifications, direct sending is acceptable here — no need for full event/listener architecture.

---

## Part 3: Navigation Updates

### Sidebar Updates

**`dashboard.blade.php` sidebar:**
- Add "Ticket Admin" nav item (only visible if `auth()->user()->isAdmin() || auth()->user()->isTeamLead()`)
- Use shield/setting icon
- Link to `route('admin.tickets.index')`
- Mark as active when `request()->routeIs('admin.tickets.*')`

**`profile/edit.blade.php` sidebar:**
- Same "Ticket Admin" nav item

**Styling:** Use the same nav item CSS classes as existing sidebar items. Active state styling.

---

## File Changes Summary

### New Files

- `app/Http/Controllers/AdminTicketController.php`
- `app/Notifications/NewTicketCreated.php`
- `app/Notifications/TicketStatusChanged.php`
- `resources/views/admin/tickets/index.blade.php`
- `resources/views/admin/tickets/show.blade.php`
- `resources/views/emails/tickets/created.blade.php` (markdown email template)
- `resources/views/emails/tickets/status-changed.blade.php` (markdown email template)

### Modified Files

- `app/Http/Controllers/TicketController.php` — fire notification after `store()`
- `routes/web.php` — add admin ticket routes
- `resources/views/dashboard.blade.php` — add admin nav item
- `resources/views/profile/edit.blade.php` — add admin nav item

### Dependencies

- Laravel Notifications (already in framework)
- Laravel Mail (check `config/mail.php` — may need `.env` SMTP settings for production)

---

## Implementation Priority

1. AdminTicketController + routes
2. Admin ticket index view
3. Admin ticket show view
4. NewTicketCreated notification + integrate into TicketController::store()
5. TicketStatusChanged notification + integrate into AdminTicketController::updateStatus()
6. Email templates (Blade Markdown)
7. Navigation updates

---

## Success Criteria

- [ ] Admin/TL can access `/admin/tickets` and see all team/global tickets
- [ ] Admin/TL can filter tickets by status, category, priority, search
- [ ] Admin/TL can view full ticket details with comment history
- [ ] Admin/TL can add comments to any ticket
- [ ] Admin/TL can change ticket status (and ticket creator gets email)
- [ ] When agent creates ticket, TL receives email notification
- [ ] Ticket Admin nav item visible only to admin/team_lead roles
- [ ] Pagination works on admin ticket list
