<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnecdotesLike extends Model
{
    use HasFactory;

    protected $table = 'anecdotes_likes';
    protected $fillable = ['user_id', 'anecdote_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function anecdote()
    {
        return $this->belongsTo(Anecdote::class);
    }
}
