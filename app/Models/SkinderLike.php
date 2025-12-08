<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    /**
     * Check if two rooms have matched (liked each other).
     *
     * @param int $room1_id
     * @param int $room2_id
     * @return bool
     */
    public static function hasMatch($room1_id, $room2_id)
    {
        $like1 = self::where('room_liker_id', $room1_id)
            ->where('room_liked_id', $room2_id)
            ->exists();

        $like2 = self::where('room_liker_id', $room2_id)
            ->where('room_liked_id', $room1_id)
            ->exists();

        return $like1 && $like2;
    }

    /**
     * Get all matches for a room.
     *
     * @param int $room_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getMatchesForRoom($room_id)
    {
        return self::where('room_liker_id', $room_id)
            ->whereExists(function ($query) use ($room_id) {
                $query->select(DB::raw(1))
                    ->from('skinder_likes as sl2')
                    ->whereColumn('sl2.room_liker_id', 'skinder_likes.room_liked_id')
                    ->where('sl2.room_liked_id', $room_id);
            })
            ->with('roomLiked')
            ->get();
    }

    /**
     * Scope a query for likes from a specific room.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $room_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFromRoom($query, $room_id)
    {
        return $query->where('room_liker_id', $room_id);
    }

    /**
     * Scope a query for likes to a specific room.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $room_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToRoom($query, $room_id)
    {
        return $query->where('room_liked_id', $room_id);
    }

    /**
     * Create or toggle a like between two rooms.
     *
     * @param int $liker_id
     * @param int $liked_id
     * @return self
     */
    public static function toggleLike($liker_id, $liked_id)
    {
        $like = self::where('room_liker_id', $liker_id)
            ->where('room_liked_id', $liked_id)
            ->first();

        if ($like) {
            $like->delete();
            return null;
        }

        return self::create([
            'room_liker_id' => $liker_id,
            'room_liked_id' => $liked_id,
        ]);
    }
}
