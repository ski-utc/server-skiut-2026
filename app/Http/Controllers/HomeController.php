<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Anecdote;
use App\Models\Challenge;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Obtenir l'activité la plus proche, un défi au hasard, et les contacts "Team Info".
     */
    public function getRandomData(Request $request)
    {
        try {
            $currentDate = Carbon::today();
            $currentTime = Carbon::now()->format('H:i:s');

            $closestActivity = Activity::whereDate('date', '>=', $currentDate)
            ->where(function ($query) use ($currentDate, $currentTime) {
                $query->where('date', '>', $currentDate) 
                      ->orWhere(function ($subQuery) use ($currentDate, $currentTime) {
                          $subQuery->where('date', '=', $currentDate) 
                                   ->where('endTime', '>=', $currentTime); 
                      });
            })
            ->whereNotNull('startTime')
            ->orderBy('date', 'ASC')
            ->orderBy('startTime', 'ASC')
            ->first();

            if ($closestActivity) {
                // Vérifie et formate startTime et endTime s'ils existent
                if ($closestActivity->startTime) {
                    if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $closestActivity->startTime)) {
                        $closestActivity->startTime = Carbon::createFromFormat('H:i:s', $closestActivity->startTime)->format('H\hi');
                    } else {
                        $closestActivity->startTime = 'Format invalide';
                    }
                } else {
                    $closestActivity->startTime = 'N/A';
                }

                if ($closestActivity->endTime) {
                    if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $closestActivity->endTime)) {
                        $closestActivity->endTime = Carbon::createFromFormat('H:i:s', $closestActivity->endTime)->format('H\hi');
                    } else {
                        $closestActivity->endTime = 'Format invalide';
                    }
                } else {
                    $closestActivity->endTime = 'N/A';
                }
            }

            // Récupération de l'utilisateur et de sa salle
            $userId = $request->user['id'] ?? null;
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non authentifié.']);
            }

            $roomId = User::where('id', $userId)->first()->roomID;

            // Récupération d'un défi aléatoire
	    $randomChallenge = Challenge::whereDoesntHave('challengeProofs', function ($query) use ($roomId) {
            	$query->where('room_id', $roomId);
          	})
            ->inRandomOrder()
            ->first();

            // Récupération de la meilleure anecdote
            $bestAnecdote = Anecdote::withCount('likes')
                ->where("valid", true)
                ->orderBy('likes_count', 'desc')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'closestActivity' => $closestActivity,
                    'randomChallenge' => $randomChallenge ? $randomChallenge->title : null,
                    'bestAnecdote' => $bestAnecdote ? $bestAnecdote->text : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "L'application n'est pas tout à fait finie... " . $e->getMessage()]);
        }
    }
}

