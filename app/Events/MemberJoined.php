<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MemberJoined implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $roomId;
    public $userId;
    public $userName;
    public $roomName;

    public function __construct($roomId, $userId, $userName, $roomName)
    {
        $this->roomId = $roomId;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->roomName = $roomName;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('room.' . $this->roomId);
    }

    public function broadcastAs(): string
    {
        return 'member.joined';
    }
}