<?php

namespace App\Http\Controllers;

use App\Models\Permanence;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PermanenceController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Get all the permanences for a user (member)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getUserPermanences(Request $request)
    {
        try {
            $user_id = $request->user['id'];
            $user = User::find($user_id);

            if (!$user || !$user->member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès réservé aux membres de l\'association'
                ], 403);
            }

            $startDate = now();
            $endDate = now()->addDays(30);

            $permanences = Permanence::forUser($user_id)
                ->inPeriod($startDate, $endDate)
                ->with('responsibleUser')
                ->orderBy('start_datetime')
                ->get();

            $data = $permanences->map(function ($permanence) use ($user_id) {
                return [
                    'id' => $permanence->id,
                    'name' => $permanence->name,
                    'description' => $permanence->description,
                    'start_datetime' => $permanence->start_datetime->toISOString(),
                    'end_datetime' => $permanence->end_datetime->toISOString(),
                    'location' => $permanence->location,
                    'status' => $permanence->status,
                    'is_responsible' => $permanence->responsible_user_id === $user_id,
                    'responsible' => [
                        'id' => $permanence->responsibleUser->id,
                        'name' => $permanence->responsibleUser->firstName . ' ' . $permanence->responsibleUser->lastName
                    ],
                    'duration_minutes' => $permanence->getDurationInMinutes(),
                    'notes' => $permanence->notes
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des permanences: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des permanences: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all the permanences for the admin
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getAllPermanences(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
        ]);

        try {
            $startDate = $validated['start_date'] ?? now()->subDays(7)->format('Y-m-d');
            $endDate = $validated['end_date'] ?? now()->addDays(30)->format('Y-m-d');

            $permanences = Permanence::inPeriod($startDate, $endDate)
                ->with(['responsibleUser'])
                ->orderBy('start_datetime')
                ->get();

            $data = $permanences->map(function ($permanence) {
                return [
                    'id' => $permanence->id,
                    'name' => $permanence->name,
                    'description' => $permanence->description,
                    'start_datetime' => $permanence->start_datetime->toISOString(),
                    'end_datetime' => $permanence->end_datetime->toISOString(),
                    'location' => $permanence->location,
                    'status' => $permanence->status,
                    'notification_sent' => $permanence->notification_sent,
                    'responsible' => [
                        'id' => $permanence->responsibleUser->id,
                        'name' => $permanence->responsibleUser->firstName . ' ' . $permanence->responsibleUser->lastName
                    ],
                    'duration_minutes' => $permanence->getDurationInMinutes(),
                    'notes' => $permanence->notes
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des permanences: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des permanences: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new permanence (admin)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function createPermanence(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
            'responsible_user_id' => 'required|exists:users,id',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        try {
            $responsible = User::find($validated['responsible_user_id']);
            if (!$responsible->member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le responsable doit être membre de l\'association'
                ], 400);
            }

            $permanence = Permanence::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'start_datetime' => $validated['start_datetime'],
                'end_datetime' => $validated['end_datetime'],
                'responsible_user_id' => $validated['responsible_user_id'],
                'location' => $validated['location'],
                'notes' => $validated['notes'],
                'status' => 'scheduled'
            ]);

            $this->firebaseService->sendNotification(
                [$permanence->responsible_user_id],
                'Nouvelle permanence assignée',
                "Permanence '{$permanence->name}' le " . $permanence->start_datetime->format('d/m à H:i'),
                [
                    'type' => 'permanence_assigned',
                    'permanence_id' => $permanence->id
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Permanence créée avec succès',
                'data' => $permanence
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la permanence: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la permanence: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a permanence (admin)
     * @param Request $request
     * @param int $permanenceId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function updatePermanence(Request $request, $permanenceId)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'sometimes|required|date',
            'end_datetime' => 'sometimes|required|date|after:start_datetime',
            'responsible_user_id' => 'sometimes|required|exists:users,id',
            'location' => 'nullable|string|max:255',
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled',
            'notes' => 'nullable|string'
        ]);

        try {
            $permanence = Permanence::findOrFail($permanenceId);

            $permanence->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Permanence mise à jour avec succès',
                'data' => $permanence->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de la permanence: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a permanence (admin)
     * @param Request $request
     * @param int $permanenceId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deletePermanence(Request $request, $permanenceId)
    {
        try {
            $permanence = Permanence::findOrFail($permanenceId);
            $permanence->delete();

            return response()->json([
                'success' => true,
                'message' => 'Permanence supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de la permanence: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la permanence: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the members of the association for the assignment
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getAssociationMembers()
    {
        try {
            $members = User::where('member', true)
                ->select('id', 'firstName', 'lastName', 'email', 'room_id')
                ->orderBy('firstName')
                ->get()
                ->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->firstName . ' ' . $member->lastName,
                        'email' => $member->email,
                        'room_id' => $member->room_id
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $members
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des membres de l\'association: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des membres de l\'association: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format the time difference in French (xxhxx or xxmin).
     * @param Carbon $dateTime
     * @return string
     */
    private function formatTimeDifferenceInFrench($dateTime)
    {
        $now = now();
        $diffInMinutes = $dateTime->diffInMinutes($now);

        if ($diffInMinutes < 0) {
            $diffInMinutes = abs($diffInMinutes);
        }

        $hours = intdiv($diffInMinutes, 60);
        $minutes = $diffInMinutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h{$minutes}";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}min";
        }
    }

    /**
     * Send the reminders (called by a cron job)
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function sendReminders()
    {
        try {
            $now = now();
            $oneHourFromNow = $now->copy()->addHour();

            $upcomingPermanences = Permanence::where('status', 'scheduled')
                ->where('notification_sent', false)
                ->whereBetween('start_datetime', [$now, $oneHourFromNow])
                ->get();

            $notificationsSent = 0;

            foreach ($upcomingPermanences as $permanence) {
                $result = $this->firebaseService->sendNotification(
                    [$permanence->responsible_user_id],
                    'Rappel de permanence',
                    "Votre permanence '{$permanence->name}' commence dans " . $this->formatTimeDifferenceInFrench($permanence->start_datetime),
                    [
                        'type' => 'permanence_reminder',
                        'permanence_id' => $permanence->id
                    ]
                );

                if ($result['success']) {
                    $permanence->update(['notification_sent' => true]);
                    $notificationsSent++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Rappels envoyés pour {$notificationsSent} permanences"
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des rappels: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi des rappels: ' . $e->getMessage()
            ], 500);
        }
    }
}
