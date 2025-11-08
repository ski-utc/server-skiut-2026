<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';
    protected $fillable = [
        'id',
        'cas',
        'firstName',
        'lastName',
        'email',
        'room_id',
        'admin',
        'member',
        'alumniOrExte'
    ];

    /**
     * Get the anecdotes that belong to the user.
     *
     * @return HasMany
     */
    public function anecdotes()
    {
        return $this->hasMany(Anecdote::class, 'user_id');
    }

    /**
     * Get the room that belongs to the user.
     *
     * @return BelongsTo
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * Get the transports that belong to the user.
     *
     * @return HasMany
     */
    public function transports()
    {
        return $this->belongsToMany(Transport::class, 'transport_user',
    'user_id',
    'transport_id');
    }

    /**
     * Get the performances that belong to the user.
     *
     * @return HasOne
     */
    public function performances()
    {
        return $this->hasOne(UserPerformance::class, 'user_id');
    }

    /**
     * Get the responsible permanences that belong to the user.
     *
     * @return HasMany
     */
    public function responsiblePermanences()
    {
        return $this->hasMany(Permanence::class, 'responsible_user_id');
    }

    /**
     * Get all the permanences that belong to the user.
     *
     * @return HasMany
     */
    public function allPermanences()
    {
        return Permanence::forUser($this->id);
    }

    /**
     * Get the push tokens that belong to the user.
     *
     * @return HasMany
     */
    public function pushTokens()
    {
        return $this->hasMany(PushToken::class, 'user_id');
    }

    /**
     * Get the active push tokens that belong to the user.
     *
     * @return HasMany
     */
    public function activePushTokens()
    {
        return $this->hasMany(PushToken::class, 'user_id')->where('active', true);
    }
}
