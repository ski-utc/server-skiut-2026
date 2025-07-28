<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Anecdote;
use App\Models\Challenge;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Obtenir l'activité la plus proche, un défi au hasard, et les contacts "Team Info".
     */
    public function getRandomData(Request $request)
    {
        try {
            $currentDate = Carbon::today();

            $closestActivity = Activity::whereDate('date', '>=', $currentDate)
                ->whereNotNull('startTime')
                ->orderBy('date', 'ASC')
                ->orderBy('startTime', 'ASC')
                ->first();

            if ($closestActivity) {

                if ($closestActivity->startTime) {
                    $closestActivity->startTime = Carbon::createFromFormat('H:i', $closestActivity->startTime)->format('H\hi');
                } else {
                    $closestActivity->startTime = 'N/A';
                }

                if ($closestActivity->endTime) {
                    $closestActivity->endTime = Carbon::createFromFormat('H:i', $closestActivity->endTime)->format('H\hi');
                } else {
                    $closestActivity->endTime = 'N/A';
                }
            }

            $userId = $request->user['id'];
            $roomId = User::where('id', $userId)->first()->roomID;

            $randomChallenge = Challenge::whereNot('room_id', $roomId)->inRandomOrder()->first();

            $bestAnecdote = Anecdote::withCount('likes')
                ->where('valid', true)
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
