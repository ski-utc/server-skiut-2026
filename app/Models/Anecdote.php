<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anecdote extends Model
{
    use HasFactory;

    protected $table = 'anecdotes';
    protected $fillable = [
        'id',
        'text',
        'room_id',
        'user_id',
        'valid',
        'delete',
        'active'
    ];

    /**
     * Get the user that owns the anecdote.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the room that owns the anecdote.
     *
     * @return BelongsTo
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }

    /**
     * Get the likes that belong to the anecdote.
     *
     * @return HasMany
     */
    public function likes()
    {
        return $this->hasMany(AnecdotesLike::class, 'anecdote_id');
    }

    /**
     * Get the warns that belong to the anecdote.
     *
     * @return HasMany
     */
    public function warns()
    {
        return $this->hasMany(AnecdotesWarn::class, 'anecdote_id');
    }

    /**
     * Check if the anecdote is liked by a specific user.
     *
     * @param int $user_id
     * @return bool
     */
    public function isLikedBy($user_id)
    {
        return $this->likes()->where('user_id', $user_id)->exists();
    }

    /**
     * Check if the anecdote is warned by a specific user.
     *
     * @param int $user_id
     * @return bool
     */
    public function isWarnedBy($user_id)
    {
        return $this->warns()->where('user_id', $user_id)->exists();
    }

    /**
     * Get the number of likes for the anecdote.
     *
     * @return int
     */
    public function getLikesCount()
    {
        return $this->likes()->count();
    }

    /**
     * Get the number of warns for the anecdote.
     *
     * @return int
     */
    public function getWarnsCount()
    {
        return $this->warns()->count();
    }

    /**
     * Toggle like for a user on the anecdote.
     *
     * @param int $user_id
     * @param bool $like
     * @return bool
     */
    public function toggleLike($user_id, $like)
    {
        $existingLike = $this->likes()->where('user_id', $user_id)->first();

        if ($like && !$existingLike) {
            AnecdotesLike::create(['user_id' => $user_id, 'anecdote_id' => $this->id]);
            return true;
        } elseif (!$like && $existingLike) {
            $existingLike->delete();
            return true;
        }

        return false;
    }

    /**
     * Toggle warn for a user on the anecdote.
     *
     * @param int $user_id
     * @param bool $warn
     * @return bool
     */
    public function toggleWarn($user_id, $warn)
    {
        $existingWarn = $this->warns()->where('user_id', $user_id)->first();

        if ($warn && !$existingWarn) {
            AnecdotesWarn::create(['user_id' => $user_id, 'anecdote_id' => $this->id]);
            return true;
        } elseif (!$warn && $existingWarn) {
            $existingWarn->delete();
            return true;
        }

        return false;
    }

    /**
     * Scope a query to only include valid anecdotes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValid($query)
    {
        return $query->where('valid', true);
    }

    /**
     * Scope a query to only include pending anecdotes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('valid', false);
    }

    /**
     * Scope a query to only include reported anecdotes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReported($query)
    {
        return $query->whereHas('warns');
    }

    /**
     * Scope a query to only include active anecdotes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('delete', false);
    }
}
