<?php

namespace App\Http\Controllers;

use App\Models\PerformanceSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PerformanceController extends Controller
{
    /**
     * Update the performance of a user with a new session.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function createPerformance(Request $request)
    {
        $validated = $request->validate([
            'speed' => 'required|numeric|min:0|max:300',
            'distance' => 'required|numeric|min:0',
            'duration' => 'nullable|integer|min:1',
            'average_speed' => 'nullable|numeric|min:0',
            'session_id' => 'nullable|string|max:255',
        ]);

        $user_id = $request->user['id'];

        try {
            $maxSpeed = round($validated['speed'], 1);
            $distance = round($validated['distance'], 3);
            $duration = $validated['duration'] ?? 0;
            $averageSpeed = round($validated['average_speed'] ?? 0, 1);
            $sessionId = $validated['session_id'] ?? uniqid('session_', true);

            $session = PerformanceSession::create([
                'user_id' => $user_id,
                'session_id' => $sessionId,
                'max_speed' => $maxSpeed,
                'average_speed' => $averageSpeed,
                'distance' => $distance,
                'duration' => $duration,
                'session_date' => now(),
            ]);

            $allSessions = PerformanceSession::byUser($user_id)->get();
            $sessionCount = $allSessions->count();

            $globalStats = [
                'best_speed' => $allSessions->max('max_speed'),
                'total_distance' => $allSessions->sum('distance'),
                'total_duration' => $allSessions->sum('duration'),
                'average_speed' => round($allSessions->avg('average_speed'), 1),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Performance enregistrée avec succès.',
                'data' => [
                    'session' => $session,
                    'global_stats' => $globalStats,
                    'session_count' => $sessionCount
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the performances of a user with his sessions.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getUserPerformances(Request $request)
    {
        try {
            $user_id = $request->user['id'];

            $performanceSessions = PerformanceSession::byUser($user_id)
                ->orderByDate()
                ->get();

            $sessions = $performanceSessions->map(function ($session) {
                return [
                    'id' => $session->id,
                    'session_id' => $session->session_id,
                    'max_speed' => $session->max_speed,
                    'average_speed' => $session->average_speed,
                    'distance' => $session->distance,
                    'duration' => $session->duration,
                    'session_date' => $session->created_at->toISOString(),
                ];
            });

            $stats = [
                'total_sessions' => $performanceSessions->count(),
                'total_distance' => $performanceSessions->sum('distance'),
                'total_duration' => $performanceSessions->sum('duration'),
                'best_speed' => $performanceSessions->max('max_speed') ?? 0,
                'best_average_speed' => $performanceSessions->max('average_speed') ?? 0,
                'best_distance' => $performanceSessions->max('distance') ?? 0,
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'sessions' => $sessions,
                    'stats' => $stats
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des performances: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des performances: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a performance session.
     * @param Request $request
     * @param string $sessionId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deletePerformanceSession(Request $request, $sessionId)
    {
        try {
            $user_id = $request->user['id'];

            if (!$sessionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de session requis'
                ], 400);
            }

            $session = PerformanceSession::where('session_id', $sessionId)
                ->where('user_id', $user_id)
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session non trouvée'
                ], 404);
            }

            $session->delete();

            return response()->json([
                'success' => true,
                'message' => 'Session supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }
}
