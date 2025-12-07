<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\Expo\ExpoPushToken;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

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
        return $this->belongsToMany(
            Transport::class,
            'transport_user',
            'user_id',
            'transport_id'
        );
    }

    /**
     * Get the performance sessions that belong to the user.
     *
     * @return HasMany
     */
    public function performanceSessions()
    {
        return $this->hasMany(PerformanceSession::class, 'user_id');
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

    /**
     * Check if the user is a member.
     *
     * @return bool
     */
    public function isMember()
    {
        return (bool) $this->member;
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return (bool) $this->admin;
    }

    /**
     * Get the room ID of the user.
     *
     * @return int|null
     */
    public function getRoomId()
    {
        return $this->room_id;
    }

    /**
     * Scope a query to only include members.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMembersOnly($query)
    {
        return $query->where('member', true);
    }

    /**
     * Scope a query to only include admins.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAdminsOnly($query)
    {
        return $query->where('admin', true);
    }

    /**
     * Get the Expo push tokens for notification routing.
     *
     * @return array
     */
    public function routeNotificationForExpo()
    {
        return $this->pushTokens()
            ->where('active', true)
            ->whereNotNull('token')
            ->pluck('token')
            ->map(function ($token) {
                return ExpoPushToken::make($token);
            })
            ->toArray();
    }

    /**
     * Anonymize user data for RGPD compliance.
     *
     * @return bool
     */
    public function anonymize()
    {
        return $this->update([
            'firstName' => 'Utilisateur',
            'lastName' => 'Anonymisé',
            'email' => 'anonyme_' . $this->id . '@etu.utc.fr',
            'cas' => 'anonyme_' . $this->id,
        ]);
    }
}
