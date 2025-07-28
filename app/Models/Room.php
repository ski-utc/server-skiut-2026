<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';
    protected $fillable = ['id', 'roomNumber', 'capacity', 'mood', 'name', 'photoPath', 'description', 'passions', 'totalPoints', 'userID'];

    public function respUser()
    {
        return $this->belongsTo(User::class, 'userID');
    }

    public function challengeProofs()
    {
        return $this->hasMany(ChallengeProof::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
