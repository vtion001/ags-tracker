<?php

namespace App\Events;

use App\Models\BreakHistory;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class BreakOverbreakAlert implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(
        public BreakHistory $breakHistory
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin'),
        ];

        if ($this->breakHistory->tl_email) {
            $channels[] = new PrivateChannel('team.' . $this->breakHistory->tl_email);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'break.overbreak';
    }
}
