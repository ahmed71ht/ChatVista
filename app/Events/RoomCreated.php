<?php

namespace App\Events;

use App\Models\ChatRoom;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $room;

    public function __construct(ChatRoom $room)
    {
        $this->room = $room->load(['creator', 'members']);
    }

    public function broadcastOn()
    {
        return new Channel('rooms');
    }

    public function broadcastAs()
    {
        return 'room.created';
    }
}