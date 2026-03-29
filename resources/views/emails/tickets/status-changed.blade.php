@component('mail::message')
# Ticket Status Updated

Your support ticket **"{{ $ticket->subject }}"** (Ticket #{{ $ticket->id }}) has been updated by **{{ $changedBy->name }}**.

---

## Status Change

**{{ ucfirst($oldStatus) }}** → **{{ ucfirst($newStatus) }}**

---

@component('mail::button', ['url' => url('/tickets/' . $ticket->id)])
View Ticket
@endcomponent

Thanks,<br>
AGSSupport System
@endcomponent