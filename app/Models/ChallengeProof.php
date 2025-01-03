<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeProof extends Model
{
    /** @use HasFactory<\Database\Factories\ChallengeProofFactory> */
    use HasFactory;

    
    protected $table = 'challenge_proofs';
    protected $fillable = ['id', 'file', 'nbLikes', 'valid', 'alert', 'delete', 'active', 'room_id', 'user_id', 'challenge_id'];

    // Define the inverse relationship with Room
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Define the inverse relationship with Challenge
    public function challenge()
    {
        return $this->belongsTo(Challenge::class, 'challenge_id');
    }

}
