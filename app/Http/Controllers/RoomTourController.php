<?php

namespace App\Http\Controllers;

use App\Models\RoomTour;
use App\Models\RoomTourVisit;
use App\Models\TourBinome;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomTourController extends Controller
{
    /**
     * Récupère toutes les tournées (admin)
     * Filtre: uniquement les tournées d'aujourd'hui et futures
     * Tri: ordre chronologique croissant
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
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des tournées : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une nouvelle tournée (admin)
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
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la tournée : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/désactiver une tournée (admin)
     */
    public function toggleTour(Request $request, $tourId)
    {
        try {
            $tour = RoomTour::findOrFail($tourId);

            if ($request->input('activate', false)) {
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
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification de la tournée : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une tournée (admin)
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
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la tournée : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère la tournée d'aujourd'hui pour un utilisateur membre (active ou non)
     */
    public function getUserTour(Request $request)
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

            $todayTour = RoomTour::getTodayTour();

            if (!$todayTour) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Aucune tournée aujourd\'hui'
                ]);
            }

            $userBinome = $todayTour->binomes()
                                    ->forUser($userId)
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

            $members = $userBinome->getMembers()->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->firstName . ' ' . $member->lastName
                ];
            });

            $data = [
                'tour_id' => $todayTour->id,
                'tour_date' => $todayTour->tour_date->format('Y-m-d'),
                'binome' => [
                    'id' => $userBinome->id,
                    'name' => $userBinome->binome_name,
                    'members' => $members,
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
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la tournée : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère le statut de la tournée pour un voyageur (widget home)
     * N'affiche le widget que si la tournée est active ET la chambre n'a pas encore été visitée
     */
    public function getTourStatusForTraveler(Request $request)
    {
        try {
            $userId = $request->user['id'];
            $user = User::find($userId);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            // Récupérer uniquement la tournée ACTIVE d'aujourd'hui
            $activeTour = RoomTour::getTodayActiveTour();

            if (!$activeTour) {
                return response()->json([
                    'success' => true,
                    'data' => null
                ]);
            }

            $roomVisit = null;
            $binomeInfo = null;

            // Trouver la visite de la chambre de l'utilisateur
            foreach ($activeTour->binomes as $binome) {
                $visit = $binome->visits()->where('room_id', $user->roomID)->first();
                if ($visit) {
                    $roomVisit = $visit;
                    $binomeInfo = $binome;
                    break;
                }
            }

            // Ne pas afficher si la chambre n'est pas dans les visites
            if (!$roomVisit) {
                return response()->json([
                    'success' => true,
                    'data' => null
                ]);
            }

            // Ne pas afficher si la chambre a déjà été visitée
            if ($roomVisit->visited) {
                return response()->json([
                    'success' => true,
                    'data' => null
                ]);
            }

            // Calculer le nombre de chambres non visitées avant celle de l'utilisateur
            $roomsBefore = $binomeInfo->visits()
                                     ->where('visit_order', '<', $roomVisit->visit_order)
                                     ->where('visited', false)
                                     ->count();

            // Récupérer les membres du binôme avec nom et prénom
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
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du statut : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marquer une chambre comme visitée (membres uniquement)
     */
    public function markRoomVisited(Request $request, $visitId)
    {
        try {
            $request->validate([
                'notes' => 'nullable|string|max:500'
            ]);

            $userId = $request->user['id'];
            $user = User::find($userId);

            if (!$user || !$user->member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès réservé aux membres de l\'association'
                ], 403);
            }

            $visit = RoomTourVisit::findOrFail($visitId);
            $binome = $visit->tourBinome;

            if (!$binome->hasMember($userId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à modifier cette visite'
                ], 403);
            }

            $success = $binome->markRoomAsVisited($visit->room_id, $request->notes);

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
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Annuler la visite d'une chambre (membres uniquement)
     */
    public function unmarkVisited(Request $request, $visitId)
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

            $visit = RoomTourVisit::findOrFail($visitId);
            $binome = $visit->tourBinome;

            if (!$binome->hasMember($userId)) {
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
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Réorganiser l'ordre des chambres pour un binôme (membres uniquement)
     */
    public function reorderRooms(Request $request)
    {
        try {
            $request->validate([
                'room_order' => 'required|array'
            ]);

            $userId = $request->user['id'];
            $user = User::find($userId);

            if (!$user || !$user->member) {
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

            $binome = $activeTour->binomes()->forUser($userId)->first();

            if (!$binome) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne participez pas à la tournée d\'aujourd\'hui'
                ], 403);
            }

            $binome->reorderRooms($request->room_order);

            return response()->json([
                'success' => true,
                'message' => 'Ordre des chambres mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réorganisation : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère la liste des chambres disponibles pour créer une tournée
     */
    public function getAvailableRooms()
    {
        try {
            $rooms = User::select('roomID')
                        ->distinct()
                        ->whereNotNull('roomID')
                        ->where('roomID', '!=', '')
                        ->orderBy('roomID')
                        ->get()
                        ->map(function ($user) {
                            $occupants = User::where('roomID', $user->roomID)
                                           ->select('id', 'firstName', 'lastName')
                                           ->get();

                            return [
                                'room_id' => $user->roomID,
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
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des chambres : ' . $e->getMessage()
            ], 500);
        }
    }
}
