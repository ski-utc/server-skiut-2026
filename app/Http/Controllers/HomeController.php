<?php

namespace App\Http\Controllers;

use App\Models\Activity;
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
        $currentDate = Carbon::today();
        $currentTime = Carbon::now()->format('H:i:s');

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

        return response()->json([
            'success' => true,
            'data' => [
                'closestActivity' => $closestActivity,
                'randomChallenge' => $randomChallenge,
            ]
        ]);
    }
}
