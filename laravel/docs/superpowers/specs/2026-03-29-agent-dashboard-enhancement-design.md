# Agent Dashboard Enhancement — Design Spec

## Overview

Enhance the agent dashboard view to be more useful by adding: (1) expanded work info, (2) personal performance metrics, (3) peer benchmarking, and (4) a support ticket system. Delivered incrementally — dashboard improvements first, ticket system as a separate page.

---

## Section 1: Enhanced Work Info Card

Replaces the current basic "User Information" card.

### Contents
- **Avatar + Name + Role** — display initials avatar, full name, role badge (Agent/Team Lead/Admin), department
- **Shift Schedule** — schedule name (e.g., "Morning Shift"), time range (e.g., "6:00 AM - 3:00 PM") from `shift_schedule` field
- **Manager / Team Lead** — name from `manager_name`, clickable email button linking to `tl_email` (mailto:)
- **Work Location** — badge: "Office", "WFH", or "Hybrid" with icon, from `work_location` field
- **Hire Date / Tenure** — "Hired: Jan 15, 2024 (14 months)" from `hire_date`, calculated via `getTenureMonths()`

### Layout
- 2-column grid inside the card
- Left: avatar, name, role, department
- Right: shift schedule, manager, work location, tenure

### Data
- All fields from authenticated user's `User` model
- Tenure calculation: `getTenureMonths()` already exists on User model

---

## Section 2: Performance Metrics Card

New card added below the Quick Stats card.

### Contents
- **Compliance Rate** — percentage of breaks returned on time (breaks where `over_minutes == 0`)
  - Visual: circular progress ring
  - Color: green (≥90%), yellow (70-89%), red (<70%)
- **Daily Summary** — "Today: 3/5 breaks" — breaks taken today vs. allowed (assumes 5/day max)
  - Allowable breaks per day: 5 total (configurable)
- **Weekly Summary** — "This week: 12 breaks, 2 overbreaks" — scoped to current week
- **Average Duration** — "Avg: 14.2 min" for 15-min breaks, "Avg: 58.4 min" for 1-hour breaks
  - Calculated from `break_history` scoped to user, last 30 days
- **Performance Score** — composite 0-100 score
  - Formula: `(compliance_rate * 0.5) + (avg_duration_score * 0.3) + (overbreak_ratio * 0.2)`
  - `avg_duration_score` = how close avg duration is to the allowed time (100 = perfect, decreases as overages grow)
  - Displayed as large number with color badge: green (80+), yellow (60-79), red (<60)

### Data Queries
```php
// Compliance rate — last 30 days
$total = BreakHistory::where('user_id', $user->id)->where('started_at', '>=', now()->subDays(30))->count();
$onTime = BreakHistory::where('user_id', $user->id)->where('started_at', '>=', now()->subDays(30))->where('over_minutes', 0)->count();
$complianceRate = $total > 0 ? round(($onTime / $total) * 100) : 100;

// Daily — today
$todayBreaks = BreakHistory::where('user_id', $user->id)->whereDate('started_at', today())->count();

// Weekly — current week
$weekBreaks = BreakHistory::where('user_id', $user->id)->whereBetween('started_at', [now()->startOfWeek(), now()->endOfWeek()])->get();
$weekOverbreaks = $weekBreaks->where('over_minutes', '>', 0)->count();
```

---

## Section 3: Peer Benchmarking Card

New card showing how the agent compares to peers.

### Contents
- **Stats Comparison Table** — side-by-side:
  - Metric | You | Team Avg | Dept Avg
  - 15-min breaks | 4 | 3.2 | 2.8
  - 1-hour breaks | 1 | 0.8 | 0.6
  - Overbreaks | 0 | 0.5 | 0.3
  - Compliance | 94% | 91% | 89%
- **Compliance Rank Badge** — "Top 30% of team" or "Below average"
  - Calculated by ranking user's compliance rate against all team members
- **Trend Arrow** — ↑ (green) if above average, ↓ (red) if below, — (yellow) if at par

### Data Queries
- Team = users with same `tl_email`
- Department = users with same `department`
- Team/dept stats = aggregate queries on `break_history` join to `users`

### Filter Toggle
- Pill selector: "My Team" / "My Department"
- Default: "My Team"
- Updates comparison data on selection

### Layout
- Toggle pills at top of card
- Stats table below
- Rank badge at bottom

---

## Section 4: Support Ticket System

Separate page at `/tickets`, accessible from sidebar navigation.

### Database Schema

**Table: `support_tickets`**
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to users |
| subject | string(255) | Ticket subject |
| category | enum | Bug/Error, Feature Request, Schedule Issue, Access Problem, Other |
| priority | enum | low, medium, high |
| description | text | Full description |
| status | enum | open, in_progress, resolved, closed |
| created_at | timestamp | |
| updated_at | timestamp | |

**Table: `support_ticket_comments`**
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| ticket_id | bigint | Foreign key to support_tickets |
| user_id | bigint | Foreign key to users (admin/agent) |
| comment | text | Comment text |
| created_at | timestamp | |

### Agent Ticket List (`/tickets`)
- Table: ID, Subject, Category, Priority, Status, Created
- Status badges: Open (yellow), In Progress (blue), Resolved (green), Closed (gray)
- Click row to expand and view full details + comments
- Filter tabs: All | Open | In Progress | Resolved
- "New Ticket" button → opens submission form

### Ticket Submission Form
- Subject (required, max 255)
- Category (required, dropdown)
- Priority (required, dropdown)
- Description (required, textarea)
- Submit → creates ticket with status "open"

### Ticket Detail View
- Full ticket info at top
- Comments thread below
- Admin/Agent can add comments
- Admin can change status via dropdown

### Notifications
- When ticket is created → admin/TL receives email (optional, via existing ElevenLabs or mail)
- When ticket status changes → agent receives email notification

### Access Control
- Agents can only see/edit their own tickets
- Admins/TLs can see all tickets in their team/department
- Admins can see all tickets globally

---

## Implementation Priority

1. **Work Info Card** — easiest, highest visual impact
2. **Performance Metrics Card** — new calculations, new card
3. **Peer Benchmarking Card** — aggregate queries, ranking logic
4. **Ticket System** — new models, controller, views, migration

---

## File Changes Summary

### New Files
- `app/Models/SupportTicket.php`
- `app/Models/SupportTicketComment.php`
- `app/Http/Controllers/TicketController.php`
- `app/Http/Requests/StoreTicketRequest.php`
- `database/migrations/2026_03_29_create_support_tickets_table.php`
- `database/migrations/2026_03_29_create_support_ticket_comments_table.php`
- `resources/views/tickets/index.blade.php`
- `resources/views/tickets/show.blade.php`
- `resources/views/tickets/create.blade.php`
- `resources/views/components/ticket-card.blade.php`
- `resources/views/components/performance-ring.blade.php`
- `routes/web.php` — add ticket routes

### Modified Files
- `resources/views/dashboard.blade.php` — add Work Info, Performance, Benchmarking cards
- `app/Http/Controllers/BreakController.php` — add peer stats to dashboard data
- `app/Models/User.php` — add `getComplianceRate()`, `getDailyBreaks()`, `getWeeklyBreaks()`, `getAverageDuration()`, `getPerformanceScore()`, `getPeerStats()` helper methods
- `resources/views/layouts/navigation.blade.php` or sidebar — add Tickets nav item
- `database/migrations/2026_03_29_add_profile_fields_to_users.php` — already exists, ensure it ran

---

## Success Criteria

- [ ] Agent sees all 4 sections on dashboard: Work Info, Quick Stats, Performance, Benchmarking
- [ ] Performance metrics update via polling alongside existing break data
- [ ] Peer benchmarking shows team/dept toggle and accurate comparisons
- [ ] Ticket system: agent can create ticket, view list, see status, read comments
- [ ] Admin/TL can view all team tickets, add comments, update status
- [ ] No new deprecation errors or console warnings
- [ ] Dashboard remains functional on mobile (responsive)
