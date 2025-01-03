<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Carbon\Carbon;

class PlanningController extends Controller
{
    public function getPlanning()
    {
        try {
            $activities = Activity::all()->map(function ($activity) {
                $activityStatus = 'future';
                $endDateTime = Carbon::parse($activity->date . ' ' . $activity->endTime);
                $startDateTime = Carbon::parse($activity->date . ' ' . $activity->startTime);

                if ($endDateTime->isPast()) {
                    $activityStatus = 'past';
                } elseif ($startDateTime->isPast() && $endDateTime->isFuture()) {
                    $activityStatus = 'current';
                }

                return [
                    'id' => $activity->id,
                    'activity' => $activity->text,
                    'time' => [
                        'start' => Carbon::parse($activity->startTime)->format('H:i'), 
                        'end' => Carbon::parse($activity->endTime)->format('H:i'),
                    ],
                    'date' => $activity->date,
                    'status' => $activityStatus,
                ];
            });

            $data = $activities->groupBy('date');

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}
