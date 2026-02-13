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

    /**
     * Check if the article is available (not shotgunned).
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->receiver_room_id === null;
    }

    /**
     * Shotgun the article by a room.
     *
     * @param int $room_id
     * @return bool
     */
    public function shotgunBy($room_id)
    {
        if (!$this->isAvailable()) {
            return false;
        }

        return $this->update(['receiver_room_id' => $room_id]);
    }

    /**
     * Mark article as retrieved.
     *
     * @return bool
     */
    public function markAsRetrieved()
    {
        return $this->update(['retrieved' => true]);
    }

    /**
     * Scope a query to only include available articles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->whereNull('receiver_room_id');
    }

    /**
     * Scope a query for articles given by a room.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $room_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGivenBy($query, $room_id)
    {
        return $query->where('giver_room_id', $room_id);
    }

    /**
     * Scope a query for articles received by a room.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $room_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReceivedBy($query, $room_id)
    {
        return $query->where('receiver_room_id', $room_id);
    }

    /**
     * Scope a query to only include retrieved articles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRetrieved($query)
    {
        return $query->where('retrieved', true);
    }

    /**
     * Scope a query to only include non-retrieved articles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotRetrieved($query)
    {
        return $query->where('retrieved', false);
    }

    /**
     * Scope a query by article type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
