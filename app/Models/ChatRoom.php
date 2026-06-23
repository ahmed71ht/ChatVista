<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\JoinRequest;
use Illuminate\Support\Str;

class ChatRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'type', 'password',
        'category', 'creator_id', 'image', 'pinned_message_id'
    ];

    protected $hidden = ['password'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'room_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'room_user', 'room_id', 'user_id')
                    ->withPivot('role', 'last_read_at')
                    ->withTimestamps();
    }

    public function unreadCounts()
    {
        return $this->hasMany(UnreadCount::class, 'room_id');
    }

    public function joinRequests()
    {
        return $this->hasMany(JoinRequest::class, 'room_id');
    }

    public function pinnedMessage()
    {
        return $this->belongsTo(Message::class, 'pinned_message_id');
    }

    public function approvedMembers()
    {
        return $this->belongsToMany(User::class, 'room_user', 'room_id', 'user_id')
                    ->withPivot('role', 'last_read_at')
                    ->withTimestamps();
    }

    public function isAdmin(User $user)
    {
        return $this->members()
                    ->wherePivot('user_id', $user->id)
                    ->wherePivot('role', 'admin')
                    ->exists();
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('slug', 'like', "%{$search}%");
    }

    public function scopePublicRooms($query)
    {
        return $query->where('type', 'public');
    }


    /**
     * Boot method: auto-generate slug if not set.
     */
    protected static function booted(): void
    {
        static::creating(function (ChatRoom $room) {
            if (empty($room->slug)) {
                $room->slug = self::generateUniqueSlug($room->name);
            }
        });
    }

    /**
     * Generate a clean, unique slug (letters + hyphens only, no numbers/symbols).
     */
    public static function generateUniqueSlug(string $name): string
    {
        // Keep only letters (any language) and spaces
        $slug = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $name); // temporary allow numbers for now
        // Replace spaces with hyphens
        $slug = preg_replace('/[\s_]+/', '-', $slug);
        // Remove consecutive hyphens
        $slug = preg_replace('/-+/', '-', $slug);
        // Trim hyphens at start/end
        $slug = trim($slug, '-');
        // Remove numbers (as requested)
        $slug = preg_replace('/[0-9]+/', '', $slug);
        // Fallback if empty
        if (empty($slug)) {
            $slug = 'room';
        }
        // Ensure uniqueness
        $original = $slug;
        $counter = 1;
        while (self::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $counter;
            $counter++;
        }
        return $slug;
    }

    // Override getRouteKeyName to use slug for model binding
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}