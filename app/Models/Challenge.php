<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Challenge extends Model
{
    use HasFactory;

    protected $table = 'challenges';
    protected $fillable = ['id','title', 'nbPoints'];

    public function challengeProofs()
    {
        return $this->hasMany(ChallengeProof::class);
    }
}
