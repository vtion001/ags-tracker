@component('mail::message')
# New Support Ticket Created

**Ticket #{{ $ticket->id }}** has been submitted by **{{ $agent->name }}** ({{ $agent->department }}).

---

## Details

| Field | Value |
|-------|-------|
| **Subject** | {{ $ticket->subject }} |
| **Category** | {{ $ticket->category }} |
| **Priority** | {{ $ticket->priority }} |

## Description

{{ $ticket->description }}

---

Submitted by **{{ $agent->name }}** · {{ $agent->email }}
Team Lead: {{ $teamLeadName }}

@component('mail::button', ['url' => url('/tickets/' . $ticket->id)])
View Ticket
@endcomponent

Thanks,<br>
AGSSupport System
@endcomponent
