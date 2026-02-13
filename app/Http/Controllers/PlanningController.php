<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Permanence;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
                return [
                    'id' => $activity->id,
                    'activity' => $activity->text,
                    'time' => [
                        'start' => Carbon::parse($activity->startTime)->format('H:i'),
                        'end' => Carbon::parse($activity->endTime)->format('H:i'),
                    ],
                    'payant' => $activity->payant,
                    'date' => $activity->date,
                    'type' => 'activity',
                    'is_permanence' => false
                ];
            });

            $allEvents = collect($activities);

            if ($user && $user->isMember()) {
                $startDate = now()->subDays(1);
                $endDate = now()->addDays(30);

                $permanences = Permanence::forUser($user_id)
                    ->inPeriod($startDate, $endDate)
                    ->with('responsibleUser')
                    ->get()
                    ->map(function ($permanence) use ($user_id) {
                        return [
                            'id' => 'permanence_' . $permanence->id,
                            'activity' => $permanence->name,
                            'time' => [
                                'start' => $permanence->start_datetime->format('H:i'),
                                'end' => $permanence->end_datetime->format('H:i'),
                            ],
                            'payant' => false,
                            'date' => $permanence->start_datetime->format('Y-m-d'),
                            'type' => 'permanence',
                            'is_permanence' => true,
                            'permanence_data' => [
                                'id' => $permanence->id,
                                'name' => $permanence->name,
                                'start_datetime' => $permanence->start_datetime->toISOString(),
                                'end_datetime' => $permanence->end_datetime->toISOString(),
                                'location' => $permanence->location,
                                'status' => $permanence->status,
                                'is_responsible' => $permanence->responsible_user_id === $user_id,
                                'responsible' => [
                                    'id' => $permanence->responsibleUser->id,
                                    'name' => $permanence->responsibleUser->firstName . ' ' . $permanence->responsibleUser->lastName,
                                    'email' => $permanence->responsibleUser->email
                                ],
                                'duration_minutes' => $permanence->getDurationInMinutes(),
                                'notes' => $permanence->notes
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
                'user_is_member' => $user ? $user->isMember() : false
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du planning: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du planning: ' . $e->getMessage()
            ], 500);
        }
    }
}
