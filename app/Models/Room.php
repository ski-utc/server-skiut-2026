<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';
    protected $fillable = ['id', 'roomNumber', 'capacity', 'mood', 'name', 'photoPath', 'description', 'passions', 'totalPoints', 'user_id', 'locked_until', 'locked_by_email'];

    protected $casts = [
        'locked_until' => 'datetime',
        'passions' => 'array',
    ];

    public function respUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function challengeProofs()
    {
        return $this->hasMany(ChallengeProof::class, 'room_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'room_id');
    }

    public function givenArticles()
    {
        return $this->hasMany(Monoprut::class, 'giver_room_id');
    }

    public function receivedArticles()
    {
        return $this->hasMany(Monoprut::class, 'receiver_room_id');
    }

    public function likedByRooms()
    {
        return $this->hasMany(SkinderLike::class, 'room_liked_id');
    }

    public function likedRooms()
    {
        return $this->hasMany(SkinderLike::class, 'room_liker_id');
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function isLockedByOther(string $email): bool
    {
        return $this->isLocked() && $this->locked_by_email !== $email;
    }

    public function isFull(): bool
    {
        return $this->users()->count() >= $this->capacity;
    }

    public function isAvailable(): bool
    {
        return !$this->isLocked() && !$this->isFull();
    }

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

    public function unlock(): void
    {
        $this->locked_until = null;
        $this->locked_by_email = null;
        $this->save();
    }

    public function getNumeroAttribute(): string
    {
        return (string) $this->roomNumber;
    }

    public function getNbPlacesAttribute(): int
    {
        return $this->capacity;
    }

    public function getAmbianceAttribute(): string
    {
        return $this->mood;
    }
}
