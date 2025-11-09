<?php

namespace App\Http\Controllers;

use App\Models\PerformanceSession;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClassementController extends Controller
{
    /**
     * Get the ranking of the rooms
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
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
     * Get the ranking of the performances
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function classementPerformances()
    {
        $type = request()->query('type', 'speed');

        $orderColumn = match($type) {
            'distance' => 'distance',
            'duration' => 'duration',
            default => 'max_speed',
        };

        $userStats = PerformanceSession::select(
            'user_id',
            DB::raw('MAX(max_speed) as max_speed'),
            DB::raw('SUM(distance) as total_distance'),
            DB::raw('SUM(duration) as total_duration'),
            DB::raw('AVG(average_speed) as average_speed')
        )
            ->groupBy('user_id')
            ->orderByRaw(match($type) {
                'distance' => 'SUM(distance) DESC',
                'duration' => 'SUM(duration) DESC',
                default => 'MAX(max_speed) DESC',
            })
            ->get();

        $performancesByPosition = [];
        $position = 1;

        foreach ($userStats as $stat) {
            $user = User::find($stat->user_id);
            $performancesByPosition[$position] = [
                'user_id' => (int)$stat->user_id,
                'max_speed' => (float)$stat->max_speed,
                'total_distance' => (float)$stat->total_distance,
                'duration' => (int)$stat->total_duration,
                'full_name' => $user
                    ? "{$user->firstName} {$user->lastName}"
                    : "ID {$stat->user_id}",
            ];
            $position++;
        }

        return response()->json([
            'success' => true,
            'data' => $performancesByPosition,
        ]);
    }
}
