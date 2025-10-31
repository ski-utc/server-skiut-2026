<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';
    protected $fillable = ['id', 'roomNumber', 'capacity', 'mood', 'name', 'photoPath', 'description', 'passions', 'totalPoints', 'userID', 'locked_until', 'locked_by_email'];

    protected $casts = [
        'locked_until' => 'datetime',
        'passions' => 'array',
    ];

    public function respUser()
    {
        return $this->belongsTo(User::class, 'userID');
    }

    public function challengeProofs()
    {
        return $this->hasMany(ChallengeProof::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'roomID');
    }

    // Méthodes pour le shotgun des chambres
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

    // Alias pour compatibilité avec les ressources Filament existantes
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
