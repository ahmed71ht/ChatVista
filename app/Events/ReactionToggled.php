<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ReactionToggled implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public $messageId;
    public $reactions;
    public $roomId;

    public function __construct(Message $message)
    {
        $this->messageId = $message->id;
        $this->reactions = $message->reactions()->get()->toArray();
        $this->roomId = $message->room_id;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('room.' . $this->roomId);
    }

    public function broadcastAs(): string
    {
        return 'reaction.toggled';
    }
}