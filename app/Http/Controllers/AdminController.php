<?php

namespace App\Http\Controllers;

use App\Models\Anecdote;
use App\Models\ChallengeProof;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Check if the user is admin
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getAdmin(Request $request)
    {
        try {
            $user_id = $request->user['id'];
            $user = User::findOrFail($user_id);

            if ($user->isAdmin()) {
                return response()->json(['success' => true, 'message' => 'Vous êtes admin.'], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Vous n\'êtes pas admin.'], 403);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de l\'admin: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de la vérification de l\'admin: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get the challenges (retrieval, validation, deletion)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getAdminChallenges(Request $request)
    {
        $validated = $request->validate([
            'filter' => 'nullable|in:pending,valid,all',
        ]);

        try {
            $filter = $validated['filter'] ?? 'all';

            $query = ChallengeProof::with(['room', 'user', 'challenge'])->where('delete', false);

            switch ($filter) {
                case 'pending':
                    $query->pending();
                    break;

                case 'valid':
                    $query->valid();
                    break;

                case 'all':
                default:
                    break;
            }

            $challenges = $query->orderBy('id', 'desc')
                ->get();

            $data = $challenges->map(function ($proof) {
                return [
                    'id' => $proof->id,
                    'challenge' => [
                        'id' => $proof->challenge->id,
                        'title' => $proof->challenge->title,
                        'description' => $proof->challenge->description,
                    ],
                    'user' => [
                        'id' => $proof->user->id,
                        'firstName' => $proof->user->firstName,
                        'lastName' => $proof->user->lastName,
                    ],
                    'room' => [
                        'id' => $proof->room->id,
                        'name' => $proof->room->name,
                    ],
                    'proof_media' => asset($proof->file),
                    'proof_media_type' => $proof->media_type,
                    'valid' => (bool) $proof->valid,
                    'delete' => (bool) $proof->delete,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des défis: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des défis : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the details of a specific challenge by its ID
     * @param int $challengeId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getChallengeDetails($challengeId)
    {
        try {
            $challenge = ChallengeProof::with(['user', 'room', 'challenge'])->findOrFail($challengeId);

            return response()->json([
                'success' => true,
                'data' => [
                    'challenge' => [
                        'id' => $challenge->id,
                        'valid' => (bool) $challenge->valid,
                        'created_at' => $challenge->created_at,
                        'user' => [
                            'firstName' => $challenge->user->firstName,
                            'lastName' => $challenge->user->lastName,
                        ],
                        'challenge' => [
                            'title' => $challenge->challenge->title,
                            'points' => $challenge->challenge->points ?? 0,
                        ],
                    ],
                    'imagePath' => asset($challenge->file),
                    'mediaType' => $challenge->media_type,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des détails du défi: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du défi : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the status of a challenge (validate or invalidate)
     * @param Request $request
     * @param int $challengeId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function updateChallengeStatus(Request $request, $challengeId)
    {
        $validated = $request->validate([
            'is_valid' => 'required|boolean',
            'is_delete' => 'required|boolean',
        ]);

        try {
            $challenge = ChallengeProof::findOrFail($challengeId);

            $isValid = $validated['is_valid'];
            $isDelete = $validated['is_delete'];

            $challenge->valid = $isValid;
            $challenge->delete = $isDelete;
            $challenge->save();

            if ($isValid && $isDelete) {
                $message = 'Challenge refusé avec succès';
            } elseif ($isValid && !$isDelete) {
                $message = 'Challenge validé avec succès';
            } elseif (!$isValid && !$isDelete) {
                $message = 'Challenge invalidé avec succès';
            } else {
                $message = 'Challenge mis à jour avec succès';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des anecdotes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut du challenge : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the details of a specific anecdote by its ID
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getAdminAnecdotes(Request $request)
    {
        $validated = $request->validate([
            'filter' => 'nullable|in:pending,reported,all',
        ]);

        try {
            $filter = $validated['filter'] ?? 'all';

            $query = Anecdote::with(['user', 'likes', 'warns']); // Might can remove likes and warns joins

            switch ($filter) {
                case 'pending':
                    $query->pending();
                    break;

                case 'reported':
                    $query->reported();
                    break;

                case 'all':
                default:
                    break;
            }

            $anecdotes = $query->where('delete', false)
                ->orderBy('id', 'desc')
                ->get();

            $data = $anecdotes->map(function ($anecdote) {
                return [
                    'id' => $anecdote->id,
                    'text' => $anecdote->text,
                    'user' => [
                        'id' => $anecdote->user->id,
                        'firstName' => $anecdote->user->firstName,
                        'lastName' => $anecdote->user->lastName,
                    ],
                    'room' => [
                        'id' => $anecdote->room->id,
                        'name' => $anecdote->room->name,
                        'roomNumber' => $anecdote->room->roomNumber,
                    ],
                    'nbLikes' => $anecdote->getLikesCount(),
                    'nbWarns' => $anecdote->getWarnsCount(),
                    'valid' => (bool) $anecdote->valid,
                    'alert' => (bool) $anecdote->alert,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des anecdotes : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the details of a specific anecdote by its ID
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getAnecdoteDetails($id)
    {
        try {

            $anecdote = Anecdote::with(['user.room', 'likes', 'warns'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $anecdote->id,
                    'text' => $anecdote->text,
                    'room_id' => $anecdote->room_id,
                    'user_id' => $anecdote->user_id,
                    'valid' => (int) $anecdote->valid,
                    'alert' => (bool) $anecdote->alert,
                    'delete' => (bool) $anecdote->delete,
                    'created_at' => $anecdote->created_at,
                    'user' => [
                        'firstName' => $anecdote->user->firstName,
                        'lastName' => $anecdote->user->lastName,
                        'room' => $anecdote->user->room ? $anecdote->user->room->name : 'N/A',
                    ],
                    'nbLikes' => $anecdote->getLikesCount(),
                    'nbWarns' => $anecdote->getWarnsCount()
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des détails de l\'anecdote: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'anecdote : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the status of a anecdote (validate or invalidate)
     * @param Request $request
     * @param int $anecdoteId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function updateAnecdoteStatus(Request $request, $anecdoteId)
    {
        $validated = $request->validate([
            'is_valid' => 'required|boolean',
        ]);

        try {
            $anecdote = Anecdote::findOrFail($anecdoteId);

            $anecdote->valid = $validated['is_valid'];
            $anecdote->save();

            return response()->json([
                'success' => true,
                'message' => $validated['is_valid'] ? 'Anecdote validée avec succès.' : 'Anecdote désactivée avec succès.',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du statut de l\'anecdote: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut de l\'anecdote : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an anecdote (soft delete by setting delete flag)
     * @param int $anecdoteId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteAnecdote($anecdoteId)
    {
        try {
            $anecdote = Anecdote::findOrFail($anecdoteId);

            $anecdote->delete = true;
            $anecdote->save();

            return response()->json([
                'success' => true,
                'message' => 'Anecdote supprimée avec succès.',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'anecdote: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'anecdote : ' . $e->getMessage(),
            ], 500);
        }
    }
}
