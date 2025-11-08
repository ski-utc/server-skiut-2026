<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Monoprut extends Model
{
    use HasFactory;

    protected $table = 'monoprut';
    protected $fillable = [
        'product',
        'quantity',
        'type',
        'giver_room_id',
        'receiver_room_id',
        'retrieved'
    ];
    protected $casts = [
        'type' => 'string',
        'retrieved' => 'boolean',
    ];

    /**
     * Get the room that gave the monoprut.
     *
     * @return BelongsTo
     */
    public function giverRoom()
    {
        return $this->belongsTo(Room::class, 'giver_room_id');
    }

    /**
     * Get the room that received the monoprut.
     *
     * @return BelongsTo
     */
    public function receiverRoom()
    {
        return $this->belongsTo(Room::class, 'receiver_room_id');
    }

    /**
     * Check if the room is the giver room.
     *
     * @param User $user
     * @return bool
     */
    public function isGiverRoom(User $user)
    {
        return $this->giver_room_id == $user->room_id;
    }

    /**
     * Check if the room is the receiver room.
     *
     * @param User $user
     * @return bool
     */
    public function isReceiverRoom(User $user)
    {
        return $this->receiver_room_id == $user->room_id;
    }
}
