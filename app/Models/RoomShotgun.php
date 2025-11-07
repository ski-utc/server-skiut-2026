<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomShotgun extends Model
{
    use HasFactory;
    protected $table = 'room_shotguns';

    protected $fillable = [
        'numero',
        'nb_places',
        'responsable_chambre',
        'ambiance',
        'locked_until',
        'locked_by_email',
    ];

    protected $casts = [
        'locked_until' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(UserRoomShotgun::class, 'room_shotgun_id');
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
        return $this->users()->count() >= $this->nb_places;
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
}
