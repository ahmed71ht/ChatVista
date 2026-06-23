<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load(['user:id,name', 'reactions', 'parent.user']);
    }

    public function broadcastOn(): Channel
    {
        return new Channel('room.' . $this->message->room_id);
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}