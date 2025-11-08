<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRoomShotgun extends Model
{
    use HasFactory;

    protected $table = 'user_room_shotguns';
    protected $fillable = [
        'user_id',
        'room_shotgun_id'
    ];

    /**
     * Get the user that owns the user room shotgun.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the room shotgun that owns the user room shotgun.
     *
     * @return BelongsTo
     */
    public function roomShotgun(): BelongsTo
    {
        return $this->belongsTo(RoomShotgun::class, 'room_shotgun_id');
    }
}
