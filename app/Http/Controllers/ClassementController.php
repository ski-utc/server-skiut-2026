<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\UserPerformance;

class ClassementController extends Controller
{
    public function classementChambres()
    {
        // Récupérer toutes les chambres triées par totalPoints décroissant
        $rooms = Room::orderBy('totalPoints', 'desc')->get(['roomNumber', 'totalPoints']);

        // Séparer les 3 premières chambres pour le podium
        $podiumRooms = $rooms->take(3);

        // Séparer le reste des chambres
        $restRooms = $rooms->slice(3);

        return response()->json([
            'success' => true,
            'podium' => $podiumRooms,
            'rest' => $restRooms
        ]);
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
