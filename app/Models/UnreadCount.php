<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class UnreadCount extends Model
{
    protected $fillable = ['user_id', 'room_id', 'unread_messages', 'unread_mentions'];
    
    public function user() { return $this->belongsTo(User::class); }
    public function room() { return $this->belongsTo(ChatRoom::class, 'room_id'); }
}