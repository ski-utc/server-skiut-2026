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
        'member_ids',
        'assigned_rooms',
        'visited_rooms'
    ];

    protected $casts = [
        'member_ids' => 'array',
        'assigned_rooms' => 'array',
        'visited_rooms' => 'array'
    ];

    public function roomTour()
    {
        return $this->belongsTo(RoomTour::class);
    }

    public function visits()
    {
        return $this->hasMany(RoomTourVisit::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'tour_binome_members', 'tour_binome_id', 'user_id');
    }

    /**
     * Récupère les membres de ce binôme
     */
    public function getMembersAttribute()
    {
        if (empty($this->member_ids)) {
            return collect([]);
        }

        return User::whereIn('id', $this->member_ids)
                  ->select('id', 'firstName', 'lastName')
                  ->get();
    }

    /**
     * Scope pour récupérer les binômes d'un utilisateur
     */
    public function scopeForUser($query, $userId)
    {
        return $query->whereJsonContains('member_ids', $userId);
    }

    /**
     * Vérifie si un utilisateur fait partie de ce binôme
     */
    public function hasMember($userId)
    {
        return in_array($userId, $this->member_ids ?: []);
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

            // Mettre à jour la liste des chambres visitées
            $visitedRooms = $this->visited_rooms ?: [];
            if (!in_array($roomId, $visitedRooms)) {
                $visitedRooms[] = $roomId;
                $this->update(['visited_rooms' => $visitedRooms]);
            }

            return true;
        }

        return false;
    }

    /**
     * Réordonne les chambres à visiter
     */
    public function reorderRooms($newOrder)
    {
        // $newOrder est un tableau avec room_id => ordre
        foreach ($newOrder as $roomId => $order) {
            $this->visits()
                 ->where('room_id', $roomId)
                 ->update(['visit_order' => $order]);
        }

        // Mettre à jour assigned_rooms avec le nouvel ordre
        $orderedRooms = collect($newOrder)
                       ->sortBy(function ($order, $roomId) {
                           return $order;
                       })
                       ->keys()
                       ->toArray();

        $this->update(['assigned_rooms' => $orderedRooms]);
    }
}
