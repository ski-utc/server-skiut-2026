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
     */
    public function getAllTours()
    {
        try {
            $tours = RoomTour::with(['binomes' => function ($query) {
                $query->withCount('visits');
            }])
            ->orderBy('tour_date', 'desc')
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
                'binomes.*.member_ids' => 'required|array|min:1',
                'binomes.*.member_ids.*' => 'exists:users,id',
                'binomes.*.assigned_rooms' => 'required|array|min:1',
                'binomes.*.assigned_rooms.*' => 'string'
            ]);

            DB::beginTransaction();

            // Vérifier qu'il n'y a pas déjà une tournée pour cette date
            $existingTour = RoomTour::where('tour_date', $request->tour_date)->first();
            if ($existingTour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une tournée existe déjà pour cette date'
                ], 400);
            }

            // Créer la tournée
            $roomTour = RoomTour::create([
                'tour_date' => $request->tour_date,
                'is_active' => false,
                'room_assignments' => $request->binomes
            ]);

            // Créer les binômes et leurs visites
            foreach ($request->binomes as $binomeData) {
                $binome = TourBinome::create([
                    'room_tour_id' => $roomTour->id,
                    'binome_name' => $binomeData['name'],
                    'member_ids' => $binomeData['member_ids'],
                    'assigned_rooms' => $binomeData['assigned_rooms'],
                    'visited_rooms' => []
                ]);

                // Créer les visites pour chaque chambre
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
                'data' => $roomTour->load('binomes.visits')
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
     * Récupère la tournée active d'aujourd'hui pour un utilisateur membre
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

            $activeTour = RoomTour::getTodayActiveTour();

            if (!$activeTour) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Aucune tournée active aujourd\'hui'
                ]);
            }

            // Trouver le binôme de l'utilisateur
            $userBinome = $activeTour->binomes()
                                    ->forUser($userId)
                                    ->with('visits.roomInfo')
                                    ->first();

            if (!$userBinome) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Vous ne participez pas à la tournée d\'aujourd\'hui'
                ]);
            }

            $binomeStats = $userBinome->getVisitStats();

            $data = [
                'tour_id' => $activeTour->id,
                'tour_date' => $activeTour->tour_date->format('Y-m-d'),
                'binome' => [
                    'id' => $userBinome->id,
                    'name' => $userBinome->binome_name,
                    'members' => $userBinome->members->map(function ($member) {
                        return [
                            'id' => $member->id,
                            'name' => $member->firstName . ' ' . $member->lastName
                        ];
                    }),
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

            $activeTour = RoomTour::getTodayActiveTour();

            if (!$activeTour) {
                return response()->json([
                    'success' => true,
                    'data' => null
                ]);
            }

            // Chercher si la chambre de l'utilisateur est dans la tournée
            $roomVisit = null;
            $binomeInfo = null;

            foreach ($activeTour->binomes as $binome) {
                $visit = $binome->visits()->where('room_id', $user->roomID)->first();
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

            // Calculer la position dans la tournée
            $totalRooms = $binomeInfo->visits()->count();
            $roomsBefore = $binomeInfo->visits()
                                     ->where('visit_order', '<', $roomVisit->visit_order)
                                     ->count();

            $data = [
                'tour_active' => true,
                'room_position' => $roomVisit->visit_order,
                'total_rooms' => $totalRooms,
                'rooms_before' => $roomsBefore,
                'visited' => $roomVisit->visited,
                'visited_at' => $roomVisit->visited_at,
                'binome' => [
                    'name' => $binomeInfo->binome_name,
                    'members' => $binomeInfo->members->map(function ($member) {
                        return $member->firstName . ' ' . $member->lastName;
                    })->toArray()
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
    public function markRoomVisited(Request $request)
    {
        try {
            $request->validate([
                'visit_id' => 'required|exists:room_tour_visits,id',
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

            $visit = RoomTourVisit::findOrFail($request->visit_id);
            $binome = $visit->tourBinome;

            // Vérifier que l'utilisateur fait partie de ce binôme
            if (!$binome->hasMember($userId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à modifier cette visite'
                ], 403);
            }

            // Marquer comme visitée
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
     * Réorganiser l'ordre des chambres pour un binôme (membres uniquement)
     */
    public function reorderRooms(Request $request)
    {
        try {
            $request->validate([
                'binome_id' => 'required|exists:tour_binomes,id',
                'room_orders' => 'required|array',
                'room_orders.*' => 'integer|min:1'
            ]);

            $userId = $request->user['id'];
            $user = User::find($userId);

            if (!$user || !$user->member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès réservé aux membres de l\'association'
                ], 403);
            }

            $binome = TourBinome::findOrFail($request->binome_id);

            // Vérifier que l'utilisateur fait partie de ce binôme
            if (!$binome->hasMember($userId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à modifier ce binôme'
                ], 403);
            }

            // Réorganiser les chambres
            $binome->reorderRooms($request->room_orders);

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
