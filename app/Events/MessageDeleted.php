<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MessageDeleted implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public $messageId;
    public $roomId;

    public function __construct($messageId, $roomId)
    {
        $this->messageId = $messageId;
        $this->roomId = $roomId;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('room.' . $this->roomId);
    }

    public function broadcastAs(): string
    {
        return 'message.deleted';
    }
}