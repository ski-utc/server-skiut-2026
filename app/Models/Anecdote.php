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
    protected $fillable = ['id', 'title', 'description', 'nbLikes', 'valid', 'alert', 'delete', 'active'];

    // Define the inverse relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
