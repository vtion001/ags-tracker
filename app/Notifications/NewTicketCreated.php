<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketCreated extends Notification
{
    use Queueable;

    public function __construct(
        public SupportTicket $ticket,
        public User $agent,
        public string $teamLeadName,
        public string $teamLeadEmail,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[AGSSupport] New Ticket #{$this->ticket->id} — {$this->ticket->subject}")
            ->markdown('emails.tickets.created', [
                'ticket' => $this->ticket,
                'agent' => $this->agent,
                'teamLeadName' => $this->teamLeadName,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'subject' => $this->ticket->subject,
            'agent_name' => $this->agent->name,
        ];
    }
}
