<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';
    protected $fillable = ['id', 'cas', 'firstName', 'lastName', 'email', 'password', 'roomID', 'location', 'admin'];


    // Define the one-to-many relationship with Anecdote
    public function anecdotes()
    {
        return $this->hasMany(Anecdote::class);
    }

    // Define the one-to-many relationship with Statistics
    public function statistics()
    {
        return $this->hasMany(Statistics::class);
    }

    // Define the inverse relationship with Room 
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Define the many-to-many relationship with Transport
    public function transports()
    {
        return $this->belongsToMany(Transport::class, 'transport_user');
    }
}
