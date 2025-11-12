<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeProof extends Model
{
    use HasFactory;

    protected $table = 'challenge_proofs';
    protected $fillable = [
        'id',
        'file',
        'media_type',
        'nb_likes',
        'valid',
        'alert',
        'delete',
        'room_id',
        'user_id',
        'challenge_id'
    ];

    /**
     * Get the room that owns the challenge proof.
     *
     * @return BelongsTo
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * Get the user that owns the challenge proof.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the challenge that owns the challenge proof.
     *
     * @return BelongsTo
     */
    public function challenge()
    {
        return $this->belongsTo(Challenge::class, 'challenge_id');
    }

    /**
     * Increment likes count.
     *
     * @param int $count
     * @return bool
     */
    public function addLikes($count = 1)
    {
        return $this->increment('nb_likes', $count);
    }

    /**
     * Scope a query to only include valid proofs.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValid($query)
    {
        return $query->where('valid', true);
    }

    /**
     * Scope a query to only include pending proofs.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('valid', false)->where('delete', false);
    }

    /**
     * Scope a query to only include alerted proofs.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAlerted($query)
    {
        return $query->where('alert', true);
    }

    /**
     * Scope a query for proofs by room.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $room_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByRoom($query, $room_id)
    {
        return $query->where('room_id', $room_id);
    }

    /**
     * Scope a query for proofs by challenge.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $challenge_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByChallenge($query, $challenge_id)
    {
        return $query->where('challenge_id', $challenge_id);
    }
}
