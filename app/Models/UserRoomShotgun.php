<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRoomShotgun extends Model
{
    use HasFactory;
    protected $table = 'user_room_shotguns';

    protected $fillable = ['user_id', 'room_shotgun_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function roomShotgun(): BelongsTo
    {
        return $this->belongsTo(RoomShotgun::class, 'room_shotgun_id');
    }
}
