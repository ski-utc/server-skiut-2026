<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = ['id', 'cas', 'firstName', 'lastName', 'email', 'roomID', 'location', 'admin', 'alumniOrExte'];


    public function anecdotes()
    {
        return $this->hasMany(Anecdote::class);
    }

    public function statistics()
    {
        return $this->hasMany(Statistics::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'roomID');
    }

    public function transports()
    {
        return $this->belongsToMany(Transport::class, 'transport_user');
    }
}
