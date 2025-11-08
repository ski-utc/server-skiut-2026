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
        'locked_until',
        'locked_by_email'
    ];
    protected $casts = [
        'locked_until' => 'datetime',
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

    /**
     * Check if the room is locked.
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Check if the room is locked by another email.
     *
     * @param string $email
     * @return bool
     */
    public function isLockedByOther(string $email): bool
    {
        return $this->isLocked() && $this->locked_by_email !== $email;
    }

    /**
     * Check if the room is full.
     *
     * @return bool
     */
    public function isFull(): bool
    {
        return $this->users()->count() >= $this->capacity;
    }

    /**
     * Check if the room is available.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return !$this->isLocked() && !$this->isFull();
    }

    /**
     * Lock the room.
     *
     * @param string $email
     * @return bool
     */
    public function lock(string $email): bool
    {
        if ($this->isLockedByOther($email)) {
            return false;
        }

        $this->locked_until = now()->addMinutes(5);
        $this->locked_by_email = $email;
        $this->save();
        return true;
    }

    /**
     * Unlock the room.
     */
    public function unlock(): void
    {
        $this->locked_until = null;
        $this->locked_by_email = null;
        $this->save();
    }

    /**
     * Get the room number attribute.
     *
     * @return string
     */
    public function getNumeroAttribute(): string
    {
        return (string) $this->roomNumber;
    }

    /**
     * Get the number of places attribute.
     *
     * @return int
     */
    public function getNbPlacesAttribute(): int
    {
        return $this->capacity;
    }

    /**
     * Get the ambiance attribute.
     *
     * @return string
     */
    public function getAmbianceAttribute(): string
    {
        return $this->mood;
    }
}
