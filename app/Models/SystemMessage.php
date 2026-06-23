<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id', 'type', 'content', 'data'
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function room()
    {
        return $this->belongsTo(ChatRoom::class);
    }
}