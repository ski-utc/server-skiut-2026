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
}
