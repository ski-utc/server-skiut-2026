<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Permanence;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
    /**
     * Get the activities of the week with the permanences for the members
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getPlanning(Request $request)
    {
        try {
            $user_id = $request->user['id'];
            $user = User::find($user_id);

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
                    'payant' => $activity->payant,
                    'date' => $activity->date,
                    'status' => $activityStatus,
                    'type' => 'activity',
                    'is_permanence' => false
                ];
            });

            $allEvents = collect($activities);


            if ($user && $user->member) {

                $startDate = now()->subDays(1);
                $endDate = now()->addDays(30);

                $permanences = Permanence::forUser($user_id)
                    ->inPeriod($startDate, $endDate)
                    ->get()
                    ->map(function ($permanence) {
                        $status = 'future';
                        if ($permanence->end_datetime->isPast()) {
                            $status = 'past';
                        } elseif ($permanence->start_datetime->isPast() && $permanence->end_datetime->isFuture()) {
                            $status = 'current';
                        }

                        return [
                            'id' => 'permanence_' . $permanence->id,
                            'activity' => $permanence->name,
                            'time' => [
                                'start' => $permanence->start_datetime->format('H:i'),
                                'end' => $permanence->end_datetime->format('H:i'),
                            ],
                            'payant' => false,
                            'date' => $permanence->start_datetime->format('Y-m-d'),
                            'status' => $status,
                            'type' => 'permanence',
                            'is_permanence' => true,
                            'permanence_data' => [
                                'id' => $permanence->id,
                                'name' => $permanence->name,
                                'description' => $permanence->description,
                                'location' => $permanence->location,
                                'status' => $permanence->status
                            ]
                        ];
                    });

                $allEvents = $allEvents->concat($permanences);
            }


            $data = $allEvents->groupBy('date')
                             ->map(function ($dayEvents) {
                                 return $dayEvents->sortBy(function ($event) {
                                     return $event['time']['start'];
                                 })->values();
                             });

            return response()->json([
                'success' => true,
                'data' => $data,
                'user_is_member' => $user ? $user->member : false
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du planning: ' . $e->getMessage()
            ], 500);
        }
    }
}
