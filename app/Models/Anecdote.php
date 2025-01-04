<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Anecdote extends Model
{
    use HasFactory;

    protected $table = 'anecdotes';
    protected $fillable = ['id', 'text', 'room', 'userId', 'valid', 'alert', 'delete', 'active'];

    public function user()
    {
        Log::info('User ID: ' . $this->userId);  // Affiche l'ID utilisateur
        return $this->belongsTo(User::class, 'userId', 'id');
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
