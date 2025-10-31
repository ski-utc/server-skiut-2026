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
     * Récupère les activités de la semaine avec les permanences pour les membres
     */
    public function getPlanning(Request $request)
    {
        try {
            $userId = $request->user['id'];
            $user = User::find($userId);

            // Récupérer les activités régulières
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

            // Ajouter les permanences si l'utilisateur est membre
            if ($user && $user->member) {
                // Récupérer les permanences pour les 30 prochains jours
                $startDate = now()->subDays(1);
                $endDate = now()->addDays(30);

                $permanences = Permanence::forUser($userId)
                    ->inPeriod($startDate, $endDate)
                    ->with('responsibleUser')
                    ->get()
                    ->map(function ($permanence) use ($userId) {
                        $status = 'future';
                        if ($permanence->end_datetime->isPast()) {
                            $status = 'past';
                        } elseif ($permanence->start_datetime->isPast() && $permanence->end_datetime->isFuture()) {
                            $status = 'current';
                        }

                        return [
                            'id' => 'permanence_' . $permanence->id,
                            'activity' => '🛠️ ' . $permanence->name,
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
                                'is_responsible' => $permanence->responsible_user_id === $userId,
                                'responsible_name' => $permanence->responsibleUser->firstName . ' ' . $permanence->responsibleUser->lastName,
                                'status' => $permanence->status,
                                'duration_minutes' => $permanence->getDurationInMinutes()
                            ]
                        ];
                    });

                $allEvents = $allEvents->concat($permanences);
            }

            // Grouper par date et trier par heure
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
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Récupérer les permanences d'un utilisateur membre
     */
    public function getUserPermanences(Request $request)
    {
        try {
            $userId = $request->user['id'];
            $user = User::find($userId);

            if (!$user || !$user->member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès réservé aux membres de l\'association'
                ], 403);
            }

            $startDate = $request->input('start_date', now()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->addDays(30)->format('Y-m-d'));

            $permanences = Permanence::forUser($userId)
                ->inPeriod($startDate, $endDate)
                ->with('responsibleUser')
                ->orderBy('start_datetime')
                ->get();

            $data = $permanences->map(function ($permanence) use ($userId) {
                $allMembers = $permanence->getAllMembers();

                return [
                    'id' => $permanence->id,
                    'name' => $permanence->name,
                    'description' => $permanence->description,
                    'start_datetime' => $permanence->start_datetime->toISOString(),
                    'end_datetime' => $permanence->end_datetime->toISOString(),
                    'location' => $permanence->location,
                    'status' => $permanence->status,
                    'is_responsible' => $permanence->responsible_user_id === $userId,
                    'responsible' => [
                        'id' => $permanence->responsibleUser->id,
                        'name' => $permanence->responsibleUser->firstName . ' ' . $permanence->responsibleUser->lastName
                    ],
                    'all_members' => $allMembers->map(function ($member) {
                        return [
                            'id' => $member->id,
                            'name' => $member->firstName . ' ' . $member->lastName
                        ];
                    }),
                    'duration_minutes' => $permanence->getDurationInMinutes(),
                    'notes' => $permanence->notes
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}
