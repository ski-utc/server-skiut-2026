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
        'notes'
    ];

    protected $casts = [
        'visited' => 'boolean',
        'visited_at' => 'datetime'
    ];

    public function tourBinome()
    {
        return $this->belongsTo(TourBinome::class);
    }

    /**
     * Récupère les informations de la chambre depuis la table users
     */
    public function getRoomInfoAttribute()
    {
        $users = User::where('roomID', $this->room_id)
                    ->select('id', 'firstName', 'lastName', 'roomID')
                    ->get();

        // Récupérer les infos de la chambre (mood et nom)
        // Note: room_id peut être soit un ID, soit un roomNumber selon comment les données sont stockées
        $room = Room::where('roomNumber', $this->room_id)
                   ->orWhere('id', $this->room_id)
                   ->first();

        // Debug: Log si la chambre n'est pas trouvée
        if (!$room) {
            \Log::warning("Chambre non trouvée pour room_id: {$this->room_id}");
        }

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

    /**
     * Scope pour les chambres visitées
     */
    public function scopeVisited($query)
    {
        return $query->where('visited', true);
    }

    /**
     * Scope pour les chambres non visitées
     */
    public function scopePending($query)
    {
        return $query->where('visited', false);
    }

    /**
     * Scope pour trier par ordre de visite
     */
    public function scopeOrderedByVisit($query)
    {
        return $query->orderBy('visit_order');
    }
}
