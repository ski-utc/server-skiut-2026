<?php

namespace App\Http\Controllers;

use App\Models\PerformanceSession;
use App\Models\UserPerformance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserPerformanceController extends Controller
{
    /**
     * Mettre à jour la performance d'un utilisateur avec une nouvelle session.
     */
    public function updatePerformance(Request $request)
    {
        // Récupérer l'ID utilisateur depuis le middleware JWT
        $user_id = $request->user['id'];

        // Valider les données entrantes
        $validator = Validator::make($request->all(), [
            'speed' => 'required|numeric|min:0|max:300', // Vitesse max réaliste
            'distance' => 'required|numeric|min:0',
            'duration' => 'integer|min:1',
            'average_speed' => 'numeric|min:0',
            'session_id' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Arrondir les valeurs
            $maxSpeed = round($request->speed, 1);
            $distance = round($request->distance, 3);
            $duration = $request->input('duration', 0);
            $averageSpeed = round($request->input('average_speed', 0), 1);
            $sessionId = $request->input('session_id', uniqid('session_', true));

            // Créer une nouvelle session de performance
            $session = PerformanceSession::create([
                'user_id' => $user_id,
                'session_id' => $sessionId,
                'max_speed' => $maxSpeed,
                'average_speed' => $averageSpeed,
                'distance' => $distance,
                'duration' => $duration,
            ]);

            // Rechercher ou créer la performance globale pour l'utilisateur
            $performance = UserPerformance::firstOrCreate(
                ['user_id' => $user_id],
                ['max_speed' => 0, 'total_distance' => 0, 'duration' => 0, 'average_speed' => 0]
            );

            // Mettre à jour la vitesse maximale si elle est supérieure à l'actuelle
            if ($maxSpeed > $performance->max_speed) {
                $performance->max_speed = $maxSpeed;
            }

            // Ajouter la distance parcourue à la distance totale
            $performance->total_distance += $distance;

            // Ajouter la durée à la durée totale
            $performance->duration += $duration;

            // Recalculer la vitesse moyenne globale basée sur toutes les sessions
            $allSessions = PerformanceSession::where('user_id', $user_id)->get();
            $totalSpeed = $allSessions->sum('average_speed');
            $sessionCount = $allSessions->count();

            if ($sessionCount > 0) {
                $performance->average_speed = round($totalSpeed / $sessionCount, 1);
            }

            // Mettre à jour avec les infos de la dernière session
            $performance->session_id = $sessionId;
            $performance->session_date = now();

            // Sauvegarder les modifications
            $performance->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Performance enregistrée avec succès.',
                'data' => [
                    'session' => $session,
                    'global_performance' => $performance,
                    'session_count' => $sessionCount
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les performances d'un utilisateur avec ses sessions.
     */
    public function getUserPerformances(Request $request)
    {
        try {
            $user_id = $request->user['id'];

            // Récupérer toutes les sessions de l'utilisateur
            $sessions = PerformanceSession::where('user_id', $user_id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($session) {
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

            // Calculer les statistiques globales
            $stats = [
                'total_sessions' => $sessions->count(),
                'total_distance' => $sessions->sum('distance'),
                'total_duration' => $sessions->sum('duration'),
                'best_speed' => $sessions->max('max_speed') ?? 0,
                'best_average_speed' => $sessions->max('average_speed') ?? 0,
                'best_distance' => $sessions->max('distance') ?? 0,
            ];

            return response()->json([
                'success' => true,
                'sessions' => $sessions,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des performances: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une session de performance.
     */
    public function deletePerformanceSession(Request $request)
    {
        try {
            $user_id = $request->user['id'];
            $session_id = $request->input('session_id');

            if (!$session_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de session requis'
                ], 400);
            }

            DB::beginTransaction();

            // Trouver et supprimer la session
            $session = PerformanceSession::where('user_id', $user_id)
                ->where('session_id', $session_id)
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session non trouvée'
                ], 404);
            }

            $session->delete();

            // Recalculer les statistiques globales
            $this->recalculateGlobalStats($user_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Session supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalculer les statistiques globales d'un utilisateur.
     */
    private function recalculateGlobalStats($user_id)
    {
        $sessions = PerformanceSession::where('user_id', $user_id)->get();

        if ($sessions->isEmpty()) {
            // Supprimer la performance globale si aucune session
            UserPerformance::where('user_id', $user_id)->delete();
            return;
        }

        $performance = UserPerformance::where('user_id', $user_id)->first();

        if ($performance) {
            $performance->max_speed = $sessions->max('max_speed');
            $performance->total_distance = $sessions->sum('distance');
            $performance->duration = $sessions->sum('duration');
            $performance->average_speed = round($sessions->avg('average_speed'), 1);
            $performance->save();
        }
    }
}
