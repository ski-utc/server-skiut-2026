<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    /** @use HasFactory<\Database\Factories\ChallengeFactory> */
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $table = 'challenges';
    protected $fillable = ['id', 'title', 'nbPoints']; 

    // Define the one-to-many relationship with ChallengeProof
    public function challengeProofs()
    {
        return $this->hasMany(ChallengeProof::class);
    }
}
