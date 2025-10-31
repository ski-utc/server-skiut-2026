<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    protected $fillable = [
        'id',
        'title',
        'description',
        'general',
        'display',
        'sender_id',
        'type',
        'target_users',
        'target_rooms',
        'push_sent',
        'scheduled_at',
        'firebase_response'
    ];

    protected $casts = [
        'general' => 'boolean',
        'display' => 'boolean',
        'push_sent' => 'boolean',
        'target_users' => 'array',
        'target_rooms' => 'array',
        'scheduled_at' => 'datetime',
        'firebase_response' => 'array'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    public function recipients()
    {
        return $this->belongsToMany(User::class, 'user_notifications')
                   ->withPivot(['read', 'read_at'])
                   ->withTimestamps();
    }
}
