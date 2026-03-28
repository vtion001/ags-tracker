<?php

namespace App\Events;

use App\Models\ActiveBreak;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class BreakStarted implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(
        public ActiveBreak $break
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin'),
        ];

        if ($this->break->tl_email) {
            $channels[] = new PrivateChannel('team.' . $this->break->tl_email);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'break.started';
    }
}
