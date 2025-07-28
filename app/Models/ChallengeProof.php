<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeProof extends Model
{
    use HasFactory;

    protected $table = 'challenge_proofs';
    protected $fillable = ['id', 'file', 'nb_likes', 'valid', 'alert', 'delete', 'room_id', 'user_id', 'challenge_id'];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class, 'challenge_id');
    }
}
