<?php

namespace App\Http\Controllers;

use App\Models\Anecdote;
use App\Models\ChallengeProof;
use App\Models\User;
use Illuminate\Http\Request;

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
            $user = User::find($user_id);

            if ($user && $user->admin) {
                return response()->json(['success' => true, 'message' => 'Vous êtes admin.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Vous n\'êtes pas admin.']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
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
                    $query->where('valid', false);
                    break;

                case 'valid':
                    $query->where('valid', true);
                    break;

                case 'all':
                default:
                    break;
            }

            $challenges = $query->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $challenges,
            ]);
        } catch (\Exception $e) {
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
                'data' => $challenge,
                'imagePath' => asset($challenge->file)
            ]);

        } catch (\Exception $e) {
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

            $query = Anecdote::with(['user', 'likes', 'warns']);

            switch ($filter) {
                case 'pending':
                    $query->where('valid', false);
                    break;

                case 'reported':
                    $query->whereHas('warns', function ($q) {
                        $q->groupBy('anecdote_id')
                        ->havingRaw('COUNT(*) > 0');
                    });
                    break;

                case 'all':
                default:
                    break;
            }

            $anecdotes = $query->where('delete', false)
                ->orderBy('id', 'desc')
                ->get();

            foreach ($anecdotes as $anecdote) {
                $anecdote->nbWarns = $anecdote->warns()->count();
            }

            return response()->json([
                'success' => true,
                'data' => $anecdotes,
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

            $anecdote = Anecdote::with(['user', 'likes', 'warns'])->findOrFail($id);
            $nbLikes = $anecdote->likes()->count();
            $nbWarns = $anecdote->warns()->count();

            return response()->json([
                'success' => true,
                'data' => $anecdote,
                'nbLikes' => $nbLikes,
                'nbWarns' => $nbWarns
            ]);
        } catch (\Exception $e) {
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
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut de l\'anecdote : ' . $e->getMessage(),
            ], 500);
        }
    }
}
