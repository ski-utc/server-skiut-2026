<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\UserPerformance;

class ClassementController extends Controller
{
    /**
     * Calcule le classement des chambres
     */
    public function classementChambres()
    {
        try {
            $rooms = Room::with(['challengeProofs' => function ($query) {
                $query->where('valid', 1);
            }])
            ->get()
            ->map(function ($room) {
                $totalPoints = $room->challengeProofs->sum(function ($proof) {
                    return $proof->challenge ? $proof->challenge->nbPoints : 0;
                });

                return [
                    'roomNumber' => $room->name,
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

    /**
     * Calcule le classement des performances
     */
    public function classementPerformances()
    {
        $type = request()->query('type', 'speed');

        $orderColumn = match($type) {
            'distance' => 'total_distance',
            'duration' => 'duration',
            default => 'max_speed',
        };

        // Filtrer les performances avec des valeurs > 0 pour éviter les entrées vides
        $performances = UserPerformance::with('user:id,firstName,lastName')
            ->where($orderColumn, '>', 0)
            ->orderBy($orderColumn, 'desc')
            ->get(['user_id', 'max_speed', 'total_distance', 'duration']);

        $formatPerformance = function ($performance) {
            return [
                'user_id' => $performance->user_id,
                'max_speed' => $performance->max_speed,
                'total_distance' => $performance->total_distance,
                'duration' => $performance->duration,
                'full_name' => $performance->user
                    ? "{$performance->user->firstName} {$performance->user->lastName}"
                    : "ID {$performance->user_id}",
            ];
        };

        $podiumPerformances = $performances->take(3)->map($formatPerformance);

        $restPerformances = $performances->slice(3)->map($formatPerformance);

        return response()->json([
            'success' => true,
            'podium' => $podiumPerformances,
            'rest' => $restPerformances,
        ]);
    }
}
