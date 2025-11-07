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

    public function roomTour()
    {
        return $this->belongsTo(RoomTour::class);
    }

    public function visits()
    {
        return $this->hasMany(RoomTourVisit::class);
    }

    public function member1()
    {
        return $this->belongsTo(User::class, 'member_1_id');
    }

    public function member2()
    {
        return $this->belongsTo(User::class, 'member_2_id');
    }

    /**
     * Récupère les 2 membres du binôme
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
     * Scope pour récupérer les binômes d'un utilisateur
     */
    public function scopeForUser($query, $user_id)
    {
        return $query->where('member_1_id', $user_id)
                    ->orWhere('member_2_id', $user_id);
    }

    /**
     * Vérifie si un utilisateur fait partie de ce binôme
     */
    public function hasMember($user_id)
    {
        return $this->member_1_id == $user_id || $this->member_2_id == $user_id;
    }

    /**
     * Récupère les statistiques de visite pour ce binôme
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
     * Récupère la prochaine chambre à visiter
     */
    public function getNextRoom()
    {
        return $this->visits()
                   ->where('visited', false)
                   ->orderBy('visit_order')
                   ->first();
    }

    /**
     * Marque une chambre comme visitée
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
     * Réordonne les chambres à visiter
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
