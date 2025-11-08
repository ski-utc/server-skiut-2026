<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkinderLike extends Model
{
    use HasFactory;

    protected $table = 'skinder_likes';
    protected $fillable = [
        'room_liker_id',
        'room_liked_id'
    ];

    /**
     * Get the room liker.
     *
     * @return BelongsTo
     */
    public function roomLiker()
    {
        return $this->belongsTo(Room::class, 'room_liker_id');
    }

    /**
     * Get the room liked.
     *
     * @return BelongsTo
     */
    public function roomLiked()
    {
        return $this->belongsTo(Room::class, 'room_liked_id');
    }
}
