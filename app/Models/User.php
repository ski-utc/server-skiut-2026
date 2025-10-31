<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = ['id', 'cas', 'firstName', 'lastName', 'email', 'roomID', 'location', 'admin', 'member', 'alumniOrExte'];

    public function anecdotes()
    {
        return $this->hasMany(Anecdote::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'roomID');
    }

    public function transports()
    {
        return $this->belongsToMany(Transport::class, 'transport_user', 'user_id', 'transport_id');
    }

    public function performances()
    {
        return $this->hasOne(UserPerformance::class);
    }

    public function responsiblePermanences()
    {
        return $this->hasMany(Permanence::class, 'responsible_user_id');
    }

    public function allPermanences()
    {
        return Permanence::forUser($this->id);
    }
}
