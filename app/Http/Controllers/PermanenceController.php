<?php

namespace App\Http\Controllers;

use App\Models\Permanence;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermanenceController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Récupérer toutes les permanences pour un utilisateur (membre)
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

            // Récupérer les permanences de l'utilisateur pour les 30 prochains jours
            $startDate = now();
            $endDate = now()->addDays(30);

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

    /**
     * Récupérer toutes les permanences pour l'admin
     */
    public function getAllPermanences(Request $request)
    {
        try {
            $startDate = $request->input('start_date', now()->subDays(7)->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->addDays(30)->format('Y-m-d'));

            $permanences = Permanence::inPeriod($startDate, $endDate)
                ->with(['responsibleUser'])
                ->orderBy('start_datetime')
                ->get();

            $data = $permanences->map(function ($permanence) {
                $allMembers = $permanence->getAllMembers();

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

    /**
     * Créer une nouvelle permanence (admin)
     */
    public function createPermanence(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_datetime' => 'required|date|after:now',
                'end_datetime' => 'required|date|after:start_datetime',
                'responsible_user_id' => 'required|exists:users,id',
                'additional_members' => 'nullable|array',
                'additional_members.*' => 'exists:users,id',
                'location' => 'nullable|string|max:255',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier que le responsable est membre
            $responsible = User::find($request->responsible_user_id);
            if (!$responsible->member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le responsable doit être membre de l\'association'
                ], 400);
            }

            // Vérifier que les membres additionnels sont bien membres
            if ($request->additional_members) {
                $additionalUsers = User::whereIn('id', $request->additional_members)->get();
                foreach ($additionalUsers as $user) {
                    if (!$user->member) {
                        return response()->json([
                            'success' => false,
                            'message' => "L'utilisateur {$user->firstName} {$user->lastName} n'est pas membre de l'association"
                        ], 400);
                    }
                }
            }

            $permanence = Permanence::create([
                'name' => $request->name,
                'description' => $request->description,
                'start_datetime' => $request->start_datetime,
                'end_datetime' => $request->end_datetime,
                'responsible_user_id' => $request->responsible_user_id,
                'additional_members' => $request->additional_members ?? [],
                'location' => $request->location,
                'notes' => $request->notes,
                'status' => 'scheduled'
            ]);

            // Envoyer une notification à tous les participants
            $allMemberIds = array_merge(
                [$permanence->responsible_user_id],
                $permanence->additional_members ?? []
            );

            $this->firebaseService->sendNotification(
                $allMemberIds,
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
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une permanence (admin)
     */
    public function updatePermanence(Request $request, $permanenceId)
    {
        try {
            $permanence = Permanence::findOrFail($permanenceId);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'start_datetime' => 'sometimes|required|date',
                'end_datetime' => 'sometimes|required|date|after:start_datetime',
                'responsible_user_id' => 'sometimes|required|exists:users,id',
                'additional_members' => 'nullable|array',
                'additional_members.*' => 'exists:users,id',
                'location' => 'nullable|string|max:255',
                'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $permanence->update($request->only([
                'name', 'description', 'start_datetime', 'end_datetime',
                'responsible_user_id', 'additional_members', 'location',
                'status', 'notes'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Permanence mise à jour avec succès',
                'data' => $permanence->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une permanence (admin)
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
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les membres de l'association pour l'assignation
     */
    public function getAssociationMembers()
    {
        try {
            $members = User::where('member', true)
                ->select('id', 'firstName', 'lastName', 'email', 'roomID')
                ->orderBy('firstName')
                ->get()
                ->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->firstName . ' ' . $member->lastName,
                        'email' => $member->email,
                        'roomID' => $member->roomID
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $members
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer les notifications de rappel (appelée par une tâche cron)
     */
    public function sendReminders()
    {
        try {
            $now = now();
            $oneHourFromNow = $now->copy()->addHour();

            // Trouver toutes les permanences qui commencent dans l'heure
            $upcomingPermanences = Permanence::where('status', 'scheduled')
                ->where('notification_sent', false)
                ->whereBetween('start_datetime', [$now, $oneHourFromNow])
                ->get();

            $notificationsSent = 0;

            foreach ($upcomingPermanences as $permanence) {
                $allMemberIds = array_merge(
                    [$permanence->responsible_user_id],
                    $permanence->additional_members ?? []
                );

                $result = $this->firebaseService->sendNotification(
                    $allMemberIds,
                    'Rappel de permanence',
                    "Votre permanence '{$permanence->name}' commence dans 1 heure",
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
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}
