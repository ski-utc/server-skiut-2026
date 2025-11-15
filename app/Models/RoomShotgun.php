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
        'name',
        'responsable_chambre',
        'ambiance',
        'locked_until',
        'locked_by_email',
    ];
    protected $casts = [
        'locked_until' => 'datetime',
    ];

    /**
     * Get the users that belong to the room shotgun.
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(UserRoomShotgun::class, 'room_shotgun_id');
    }

    /**
     * Check if the room shotgun is locked.
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Check if the room shotgun is locked by another email.
     *
     * @param string $email
     * @return bool
     */
    public function isLockedByOther(string $email): bool
    {
        return $this->isLocked() && $this->locked_by_email !== $email;
    }

    /**
     * Check if the room shotgun is full.
     *
     * @return bool
     */
    public function isFull(): bool
    {
        return $this->users()->count() >= $this->nb_places;
    }

    /**
     * Check if the room shotgun is available.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return !$this->isLocked() && !$this->isFull();
    }

    /**
     * Lock the room shotgun.
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
     * Unlock the room shotgun.
     */
    public function unlock(): void
    {
        $this->locked_until = null;
        $this->locked_by_email = null;
        $this->save();
    }
}
