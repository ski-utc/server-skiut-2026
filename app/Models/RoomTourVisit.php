<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomTourVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_binome_id',
        'room_id',
        'visited',
        'visited_at',
        'visit_order',
    ];

    protected $casts = [
        'visited' => 'boolean',
        'visited_at' => 'datetime'
    ];

    public function tourBinome()
    {
        return $this->belongsTo(TourBinome::class);
    }

    public function getRoomInfoAttribute()
    {
        $users = User::where('room_id', $this->room_id)
                    ->select('id', 'firstName', 'lastName', 'room_id')
                    ->get();

        $room = Room::where('roomNumber', $this->room_id)
                   ->orWhere('id', $this->room_id)
                   ->first();

        return [
            'room_id' => $this->room_id,
            'room_name' => $room && $room->name ? $room->name : null,
            'mood' => $room && $room->mood ? $room->mood : null,
            'occupants' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->firstName . ' ' . $user->lastName
                ];
            })->toArray(),
            'occupants_count' => $users->count()
        ];
    }

    public function scopeVisited($query)
    {
        return $query->where('visited', true);
    }

    public function scopePending($query)
    {
        return $query->where('visited', false);
    }

    public function scopeOrderedByVisit($query)
    {
        return $query->orderBy('visit_order');
    }
}
