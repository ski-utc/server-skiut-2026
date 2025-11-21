<?php

namespace App\Http\Controllers;

use App\Models\RoomTour;
use App\Models\RoomTourVisit;
use App\Models\TourBinome;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomTourController extends Controller
{
    /**
     * Get all tours (admin).
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getAllTours()
    {
        try {
            $tours = RoomTour::with(['binomes' => function ($query) {
                $query->withCount('visits');
            }])
            ->whereDate('tour_date', '>=', now()->toDateString())
            ->orderBy('tour_date', 'asc')
            ->get()
            ->map(function ($tour) {
                $stats = $tour->getProgressStats();
                return [
                    'id' => $tour->id,
                    'tour_date' => $tour->tour_date->format('Y-m-d'),
                    'is_active' => $tour->is_active,
                    'binomes_count' => $stats['binomes_count'],
                    'total_rooms' => $stats['total_rooms'],
                    'visited_rooms' => $stats['visited_rooms'],
                    'progress_percentage' => $stats['progress_percentage'],
                    'created_at' => $tour->created_at
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $tours
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des tournées: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des tournées : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new tour (admin).
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function createTour(Request $request)
    {
        try {
            $request->validate([
                'tour_date' => 'required|date|after_or_equal:today',
                'binomes' => 'required|array|min:1',
                'binomes.*.name' => 'required|string|max:255',
                'binomes.*.member_ids' => 'required|array|size:2',
                'binomes.*.member_ids.*' => 'exists:users,id',
                'binomes.*.assigned_rooms' => 'required|array|min:1',
                'binomes.*.assigned_rooms.*' => 'string'
            ]);

            DB::beginTransaction();

            $existingTour = RoomTour::where('tour_date', $request->tour_date)->first();
            if ($existingTour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une tournée existe déjà pour cette date'
                ], 400);
            }

            $roomTour = RoomTour::create([
                'tour_date' => $request->tour_date,
                'is_active' => false
            ]);

            foreach ($request->binomes as $binomeData) {
                $binome = TourBinome::create([
                    'room_tour_id' => $roomTour->id,
                    'binome_name' => $binomeData['name'],
                    'member_1_id' => $binomeData['member_ids'][0],
                    'member_2_id' => $binomeData['member_ids'][1]
                ]);

                foreach ($binomeData['assigned_rooms'] as $index => $roomId) {
                    RoomTourVisit::create([
                        'tour_binome_id' => $binome->id,
                        'room_id' => $roomId,
                        'visit_order' => $index + 1,
                        'visited' => false
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tournée créée avec succès',
                'data' => $roomTour->load(['binomes.member1', 'binomes.member2', 'binomes.visits'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la tournée: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la tournée : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate/deactivate a tour (admin).
     * @param Request $request
     * @param string $tourId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function toggleTour(Request $request, $tourId)
    {
        $validated = $request->validate([
            'activate' => 'nullable|boolean',
        ]);

        try {
            $tour = RoomTour::findOrFail($tourId);

            if ($validated['activate'] ?? false) {
                $tour->start();
                $message = 'Tournée activée avec succès';
            } else {
                $tour->stop();
                $message = 'Tournée désactivée avec succès';
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la modification de la tournée: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification de la tournée : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a tour (admin).
     * @param string $tourId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteTour($tourId)
    {
        try {
            $tour = RoomTour::findOrFail($tourId);
            $tour->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tournée supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de la tournée: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la tournée : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the tour of the day for a member (active or not).
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getUserTour(Request $request)
    {
        try {
            $user_id = $request->user['id'];
            $user = User::findOrFail($user_id);

            if (!$user->isMember()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès réservé aux membres de l\'association'
                ], 403);
            }

            $todayTour = RoomTour::getTodayTour();

            if (!$todayTour) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Aucune tournée aujourd\'hui'
                ]);
            }

            $userBinome = $todayTour->binomes()
                                    ->forUser($user_id)
                                    ->with('visits')
                                    ->first();

            if (!$userBinome) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Vous ne participez pas à la tournée d\'aujourd\'hui'
                ]);
            }

            $binomeStats = $userBinome->getVisitStats();

            $teammate = $userBinome->getTeammate($user_id);

            $data = [
                'tour_id' => $todayTour->id,
                'tour_date' => $todayTour->tour_date->format('Y-m-d'),
                'binome' => [
                    'id' => $userBinome->id,
                    'name' => $userBinome->binome_name,
                    'teammate' => [
                        'id' => $teammate->id,
                        'name' => $teammate->firstName . ' ' . $teammate->lastName
                    ],
                    'stats' => $binomeStats
                ],
                'visits' => $userBinome->visits()
                                     ->orderBy('visit_order')
                                     ->get()
                                     ->map(function ($visit) {
                                         return [
                                             'id' => $visit->id,
                                             'room_id' => $visit->room_id,
                                             'room_info' => $visit->room_info,
                                             'visit_order' => $visit->visit_order,
                                             'visited' => $visit->visited,
                                             'visited_at' => $visit->visited_at,
                                             'notes' => $visit->notes
                                         ];
                                     })
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la tournée: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la tournée : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the status of the tour for a traveler (widget home).
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getTourStatusForTraveler(Request $request)
    {
        try {
            $user_id = $request->user['id'];
            $user = User::find($user_id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            $activeTour = RoomTour::getTodayActiveTour();

            if (!$activeTour) {
                return response()->json([
                    'success' => true,
                    'data' => null
                ]);
            }

            $roomVisit = null;
            $binomeInfo = null;

            foreach ($activeTour->binomes as $binome) {
                $visit = $binome->visits()->where('room_id', $user->room_id)->first();
                if ($visit) {
                    $roomVisit = $visit;
                    $binomeInfo = $binome;
                    break;
                }
            }

            if (!$roomVisit) {
                return response()->json([
                    'success' => true,
                    'data' => null
                ]);
            }

            if ($roomVisit->visited) {
                return response()->json([
                    'success' => true,
                    'data' => null
                ]);
            }

            $roomsBefore = $binomeInfo->visits()
                                     ->where('visit_order', '<', $roomVisit->visit_order)
                                     ->where('visited', false)
                                     ->count();

            $members = $binomeInfo->getMembers()->map(function ($member) {
                return [
                    'firstName' => $member->firstName,
                    'lastName' => $member->lastName,
                    'fullName' => $member->firstName . ' ' . $member->lastName
                ];
            })->toArray();

            $data = [
                'tour_active' => true,
                'rooms_before' => $roomsBefore,
                'binome' => [
                    'name' => $binomeInfo->binome_name,
                    'members' => $members
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du statut: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du statut : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a room as visited (members only).
     * @param Request $request
     * @param string $visitId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function markRoomVisited(Request $request, $visitId)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $user_id = $request->user['id'];
            $user = User::findOrFail($user_id);

            if (!$user->isMember()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès réservé aux membres de l\'association'
                ], 403);
            }

            $visit = RoomTourVisit::findOrFail($visitId);
            $binome = $visit->tourBinome;

            if (!$binome->hasMember($user_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à modifier cette visite'
                ], 403);
            }

            $success = $binome->markRoomAsVisited($visit->room_id, $validated['notes'] ?? null);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Chambre marquée comme visitée'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel the visit of a room (members only).
     * @param Request $request
     * @param string $visitId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function unmarkVisited(Request $request, $visitId)
    {
        try {
            $user_id = $request->user['id'];
            $user = User::findOrFail($user_id);

            if (!$user->isMember()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès réservé aux membres de l\'association'
                ], 403);
            }

            $visit = RoomTourVisit::findOrFail($visitId);
            $binome = $visit->tourBinome;

            if (!$binome->hasMember($user_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à modifier cette visite'
                ], 403);
            }

            $visit->update([
                'visited' => false,
                'visited_at' => null,
                'notes' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Visite annulée avec succès',
                'data' => $visit->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'annulation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder the rooms for a binome (members only).
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function reorderRooms(Request $request)
    {
        $validated = $request->validate([
            'room_order' => 'required|array'
        ]);

        try {
            $user_id = $request->user['id'];
            $user = User::findOrFail($user_id);

            if (!$user->isMember()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès réservé aux membres de l\'association'
                ], 403);
            }

            $activeTour = RoomTour::getTodayActiveTour();

            if (!$activeTour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune tournée active aujourd\'hui'
                ], 404);
            }

            $binome = $activeTour->binomes()->forUser($user_id)->first();

            if (!$binome) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne participez pas à la tournée d\'aujourd\'hui'
                ], 403);
            }

            $binome->reorderRooms($validated['room_order']);

            return response()->json([
                'success' => true,
                'message' => 'Ordre des chambres mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la réorganisation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réorganisation : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the list of available rooms for creating a tour.
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getAvailableRooms()
    {
        try {
            $rooms = User::select('room_id')
                        ->distinct()
                        ->whereNotNull('room_id')
                        ->where('room_id', '!=', '')
                        ->orderBy('room_id')
                        ->get()
                        ->map(function ($user) {
                            $occupants = User::where('room_id', $user->room_id)
                                           ->select('id', 'firstName', 'lastName')
                                           ->get();

                            return [
                                'room_id' => $user->room_id,
                                'occupants' => $occupants->map(function ($occupant) {
                                    return [
                                        'id' => $occupant->id,
                                        'name' => $occupant->firstName . ' ' . $occupant->lastName
                                    ];
                                })->toArray(),
                                'occupants_count' => $occupants->count()
                            ];
                        });

            return response()->json([
                'success' => true,
                'data' => $rooms
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des chambres: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des chambres : ' . $e->getMessage()
            ], 500);
        }
    }
}
