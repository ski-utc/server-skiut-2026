<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    /** @use HasFactory<\Database\Factories\RoomFactory> */
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $table = 'rooms';
    protected $fillable = ['id', 'name', 'roomNumber', 'capacity', 'mood', 'totalPoints', 'respRoom'];

    // Define the one-to-many relationship with ChallengeProofs
    public function challengeProofs()
    {
        return $this->hasMany(ChallengeProof::class);
    }

    // Define the one-to-many relationship with User
    public function users()
    {
        return $this->hasMany(User::class);
    }

}

