<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anecdote extends Model
{
    use HasFactory;

    protected $table = 'anecdotes';
    protected $fillable = ['id', 'text', 'room_id', 'user_id', 'valid', 'delete', 'active'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }

    public function likes()
    {
        return $this->hasMany(AnecdotesLike::class, 'anecdote_id');
    }

    public function warns()
    {
        return $this->hasMany(AnecdotesWarn::class, 'anecdote_id');
    }
}
