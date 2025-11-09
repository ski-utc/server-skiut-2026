<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';
    protected $fillable = [
        'id',
        'roomNumber',
        'capacity',
        'mood',
        'name',
        'photoPath',
        'description',
        'passions',
        'totalPoints',
        'user_id',
    ];
    protected $casts = [
        'passions' => 'array',
    ];

    /**
     * Get the user that is responsible for the room.
     *
     * @return BelongsTo
     */
    public function respUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the challenge proofs that belong to the room.
     *
     * @return HasMany
     */
    public function challengeProofs()
    {
        return $this->hasMany(ChallengeProof::class, 'room_id');
    }

    /**
     * Get the users that belong to the room.
     *
     * @return HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'room_id');
    }

    /**
     * Get the articles that have been given by the room.
     *
     * @return HasMany
     */
    public function givenArticles()
    {
        return $this->hasMany(Monoprut::class, 'giver_room_id');
    }

    /**
     * Get the articles that have been received by the room.
     *
     * @return HasMany
     */
    public function receivedArticles()
    {
        return $this->hasMany(Monoprut::class, 'receiver_room_id');
    }

    /**
     * Get the rooms that have been liked by the room.
     *
     * @return HasMany
     */
    public function likedByRooms()
    {
        return $this->hasMany(SkinderLike::class, 'room_liked_id');
    }

    /**
     * Get the rooms that have liked the room.
     *
     * @return HasMany
     */
    public function likedRooms()
    {
        return $this->hasMany(SkinderLike::class, 'room_liker_id');
    }
}
