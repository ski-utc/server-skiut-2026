<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkinderLike extends Model
{
    use HasFactory;

    protected $table = 'skinder_likes';
    protected $fillable = ['room_liker_id', 'room_liked_id'];

    public function roomLiker()
    {
        return $this->belongsTo(Room::class, 'room_liker_id');
    }

    public function roomLiked()
    {
        return $this->belongsTo(Room::class, 'room_liked_id');
    }
}
