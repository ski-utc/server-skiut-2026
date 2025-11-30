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

    /**
     * Get the user that sent the notification.
     *
     * @return BelongsTo
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the user notifications that belong to the notification.
     *
     * @return HasMany
     */
    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    /**
     * Create a notification with user notifications for recipients.
     *
     * @param array $data
     * @param array $recipientIds
     * @return self
     */
    public static function createWithRecipients($data, $recipientIds)
    {
        $notification = self::create($data);

        foreach ($recipientIds as $recipientId) {
            UserNotification::create([
                'user_id' => $recipientId,
                'notification_id' => $notification->id,
                'read' => false
            ]);
        }

        return $notification;
    }

    /**
     * Check if notification is read by a specific user.
     *
     * @param int $user_id
     * @return bool
     */
    public function isReadBy($user_id)
    {
        $userNotification = $this->userNotifications()
            ->where('user_id', $user_id)
            ->first();

        return $userNotification ? $userNotification->read : false;
    }

    /**
     * Mark notification as read by a specific user.
     *
     * @param int $user_id
     * @return bool
     */
    public function markAsReadBy($user_id)
    {
        UserNotification::create([
            'user_id' => $user_id,
            'notification_id' => $this->id,
            'read' => true,
            'read_at' => now()
        ]);

        return true;
    }

    /**
     * Scope a query to only include displayed notifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDisplayed($query)
    {
        return $query->where('display', true);
    }

    /**
     * Scope a query for global notifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGlobal($query)
    {
        return $query->where(function ($q) {
            $q->where('type', 'global')->orWhere('general', true);
        });
    }

    /**
     * Scope a query for targeted notifications to specific user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $user_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $user_id)
    {
        return $query->where('type', 'targeted')
            ->whereJsonContains('target_users', $user_id);
    }

    /**
     * Scope a query for room-based notifications to specific room.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $room_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForRoom($query, $room_id)
    {
        return $query->where('type', 'room_based')
            ->whereJsonContains('target_rooms', $room_id);
    }
}
