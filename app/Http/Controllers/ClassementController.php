<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\UserPerformance;

class ClassementController extends Controller
{
    public function classementChambres()
    {
        try {
            $rooms = Room::with(['challengeProofs' => function ($query) {
                $query->where('valid', 1); // Filtrer uniquement les preuves validées
            }])
            ->get()
            ->map(function ($room) {
                $totalPoints = $room->challengeProofs->sum(function ($proof) {
                    return $proof->challenge ? $proof->challenge->nbPoints : 0;
                });
    
                return [
                    'roomNumber' => $room->roomNumber,
                    'totalPoints' => $totalPoints,
                ];
            })
            ->sortByDesc('totalPoints')
            ->values();
    
            $podiumRooms = $rooms->take(3);
    
            $restRooms = $rooms->slice(3);
    
            return response()->json([
                'success' => true,
                'podium' => $podiumRooms,
                'rest' => $restRooms,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du calcul du classement : ' . $e->getMessage(),
            ], 500);
        }
    }
    

    public function classementPerformances()
    {
        // Récupérer toutes les performances triées par vitesse maximale décroissante
        $performances = UserPerformance::with('user:id,firstName,lastName')
            ->orderBy('max_speed', 'desc')
            ->get(['user_id', 'max_speed', 'total_distance']);

        // Préparer les données du podium et du reste
        $formatPerformance = function ($performance) {
            return [
                'user_id' => $performance->user_id,
                'max_speed' => $performance->max_speed,
                'total_distance' => $performance->total_distance,
                'full_name' => $performance->user
                    ? "{$performance->user->firstName} {$performance->user->lastName}"
                    : "ID {$performance->user_id}",
            ];
        };

        // Séparer les 3 premières performances pour le podium
        $podiumPerformances = $performances->take(3)->map($formatPerformance);

        // Séparer le reste des performances
        $restPerformances = $performances->slice(3)->map($formatPerformance);

        return response()->json([
            'success' => true,
            'podium' => $podiumPerformances,
            'rest' => $restPerformances,
        ]);
    }
}
