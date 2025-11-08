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
}
