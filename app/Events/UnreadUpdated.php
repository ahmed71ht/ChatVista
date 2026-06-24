<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * يُبَث على القناة الخاصة بالمستخدم (user.{id}) ليحدّث عدّادات الرسائل غير المقروءة
 * عبر WebSocket فقط — دون الحاجة لـ HTTP polling/fetch.
 */
class UnreadUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $userId;
    public $unreadMessages;
    public $unreadMentions;

    public function __construct($roomId, $userId, $unreadMessages = 0, $unreadMentions = 0)
    {
        $this->roomId = $roomId;
        $this->userId = $userId;
        $this->unreadMessages = $unreadMessages;
        $this->unreadMentions = $unreadMentions;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'unread.updated';
    }
}
