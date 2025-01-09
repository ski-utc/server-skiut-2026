<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Challenge extends Model
{
    /** @use HasFactory<\Database\Factories\ChallengeFactory> */
    use HasFactory;

    protected $table = 'challenges';  // Table name
    protected $fillable = ['id','title', 'nbPoints'];  // Fields to allow mass assignment

    // Define the one-to-many relationship with ChallengeProof
    public function challengeProofs()
    {
        return $this->hasMany(ChallengeProof::class);
    }
}
