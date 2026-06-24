<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JoinRequestSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

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

    public function broadcastOn()
    {
        return new Channel('room.' . $this->roomId);
    }

    public function broadcastAs()
    {
        return 'join.request.submitted';
    }
}