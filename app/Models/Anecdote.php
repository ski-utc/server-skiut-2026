<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anecdote extends Model
{
    /** @use HasFactory<\Database\Factories\AnecdoteFactory> */
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $table = 'anecdotes';
    protected $fillable = ['id', 'text', 'room', 'valid', 'alert', 'delete', 'active'];

    // Define the inverse relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);   
    }

    // Nombre de likes
    public function nbLikes()
    {
        return $this->hasMany(AnecdotesLike::class);
    }

    // Nombre de warn
    public function nbWarn()
    {
        return $this->hasMany(AnecdotesWarn::class);
    }

    // Vérifie si un utilisateur a liké cette anecdote
    public function isLikedBy($userId)
    {
        return $this->nbLikes()->where('user_id', $userId)->exists();
    }

    // Vérifie si un utilisateur a signalé cette anecdote
    public function isWarnedBy($userId)
    {
        return $this->nbWarn()->where('user_id', $userId)->exists();
    }
}
