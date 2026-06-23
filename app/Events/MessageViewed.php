<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MessageViewed implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public $messageId;
    public $userId;
    public $userName;
    public $roomId;

    public function __construct($messageId, $userId, $userName, $roomId)
    {
        $this->messageId = $messageId;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->roomId = $roomId;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('room.' . $this->roomId);
    }

    public function broadcastAs(): string
    {
        return 'message.viewed';
    }
}