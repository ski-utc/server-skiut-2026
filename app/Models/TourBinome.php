<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourBinome extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_tour_id',
        'binome_name',
        'member_1_id',
        'member_2_id'
    ];

    /**
     * Get the room tour that owns the tour binome.
     *
     * @return BelongsTo
     */
    public function roomTour()
    {
        return $this->belongsTo(RoomTour::class);
    }

    /**
     * Get the visits that belong to the tour binome.
     *
     * @return HasMany
     */
    public function visits()
    {
        return $this->hasMany(RoomTourVisit::class);
    }

    /**
     * Get the first member that belongs to the tour binome.
     *
     * @return BelongsTo
     */
    public function member1()
    {
        return $this->belongsTo(User::class, 'member_1_id');
    }

    /**
     * Get the second member that belongs to the tour binome.
     *
     * @return BelongsTo
     */
    public function member2()
    {
        return $this->belongsTo(User::class, 'member_2_id');
    }

    /**
     * Get the teammate of a user.
     *
     * @param int $user_id
     * @return User
     */
    public function getTeammate($user_id)
    {
        return $this->member1->id == $user_id ? $this->member2 : $this->member1;
    }

    /**
     * Get the 2 members of the binome.
     *
     * @return Collection
     */
    public function getMembers()
    {
        $members = collect();

        if ($this->member1) {
            $members->push($this->member1);
        }

        if ($this->member2) {
            $members->push($this->member2);
        }

        return $members;
    }

    /**
     * Scope to get the binomes of a user.
     *
     * @param Builder $query
     * @param int $user_id
     * @return Builder
     */
    public function scopeForUser($query, $user_id)
    {
        return $query->where(function ($q) use ($user_id) {
            $q->where('member_1_id', $user_id)
                ->orWhere('member_2_id', $user_id);
        });
    }

    /**
     * Check if a user is part of this binome.
     *
     * @param int $user_id
     * @return bool
     */
    public function hasMember($user_id)
    {
        return $this->member_1_id == $user_id || $this->member_2_id == $user_id;
    }

    /**
     * Get the visit statistics for this binome.
     *
     * @return array
     */
    public function getVisitStats()
    {
        $totalRooms = $this->visits->count();
        $visitedRooms = $this->visits->where('visited', true)->count();

        return [
            'total_rooms' => $totalRooms,
            'visited_rooms' => $visitedRooms,
            'progress_percentage' => $totalRooms > 0 ? round(($visitedRooms / $totalRooms) * 100) : 0,
            'next_room' => $this->getNextRoom()
        ];
    }

    /**
     * Get the next room to visit.
     *
     * @return RoomTourVisit
     */
    public function getNextRoom()
    {
        return $this->visits()
            ->where('visited', false)
            ->orderBy('visit_order')
            ->first();
    }

    /**
     * Mark a room as visited.
     *
     * @param int $roomId
     * @param string $notes
     * @return bool
     */
    public function markRoomAsVisited($roomId, $notes = null)
    {
        $visit = $this->visits()->where('room_id', $roomId)->first();

        if ($visit) {
            $visit->update([
                'visited' => true,
                'visited_at' => now(),
                'notes' => $notes
            ]);

            return true;
        }

        return false;
    }

    /**
     * Reorder the rooms to visit.
     *
     * @param array $newOrder
     */
    public function reorderRooms($newOrder)
    {
        foreach ($newOrder as $roomId => $order) {
            $this->visits()
                ->where('room_id', $roomId)
                ->update(['visit_order' => $order]);
        }
    }
}
