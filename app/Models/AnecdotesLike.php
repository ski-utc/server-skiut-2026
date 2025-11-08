<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnecdotesLike extends Model
{
    use HasFactory;

    protected $table = 'anecdotes_likes';
    protected $fillable = [
        'user_id',
        'anecdote_id'
    ];

    /**
     * Get the user that owns the anecdotes like.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the anecdote that owns the anecdotes like.
     *
     * @return BelongsTo
     */
    public function anecdote()
    {
        return $this->belongsTo(Anecdote::class);
    }
}
