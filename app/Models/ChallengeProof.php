<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeProof extends Model
{
    /** @use HasFactory<\Database\Factories\ChallengeProofFactory> */
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $table = 'proofs';
    protected $fillable = ['id', 'file', 'nbLikes', 'valid', 'alert', 'delete', 'active']; 

    // Define the inverse relationship with Room
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Define the inverse relationship with Challenge
    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

}
