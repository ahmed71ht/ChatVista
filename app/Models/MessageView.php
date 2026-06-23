<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageView extends Model
{
    protected $fillable = ['message_id', 'user_id', 'viewed_at'];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}