<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Anecdote;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Obtenir l'activité la plus proche, un défi au hasard, et les contacts "Team Info".
     */
    public function getRandomData()
    {
        try {
            $currentDate = Carbon::today();

            $closestActivity = Activity::whereDate('date', '>=', $currentDate)
                ->whereNotNull('startTime')
                ->orderBy('date', 'ASC')
                ->orderBy('startTime', 'ASC')
                ->first();

            if ($closestActivity) {
                $closestActivity->startTime = Carbon::createFromFormat('H:i:s', $closestActivity->startTime)->format('H\hi');
                $closestActivity->endTime = Carbon::createFromFormat('H:i:s', $closestActivity->endTime)->format('H\hi');
            }

            $randomChallenge = Challenge::inRandomOrder()->first();

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
