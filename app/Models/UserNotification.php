<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    use HasFactory;

    protected $table = 'user_notifications';
    protected $fillable = [
        'user_id',
        'notification_id',
        'read',
        'read_at'
    ];
    protected $casts = [
        'read' => 'boolean',
        'read_at' => 'datetime'
    ];

    /**
     * Get the user that owns the user notification.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the notification that owns the user notification.
     *
     * @return BelongsTo
     */
    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    /**
     * Mark notification as read.
     *
     * @return bool
     */
    public function markAsRead()
    {
        return $this->update([
            'read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Mark notification as unread.
     *
     * @return bool
     */
    public function markAsUnread()
    {
        return $this->update([
            'read' => false,
            'read_at' => null
        ]);
    }

    /**
     * Scope a query to only include unread notifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    /**
     * Scope a query to only include read notifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRead($query)
    {
        return $query->where('read', true);
    }

    /**
     * Scope a query for notifications of a specific user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $user_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $user_id)
    {
        return $query->where('user_id', $user_id);
    }
}
