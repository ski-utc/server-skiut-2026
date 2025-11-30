<?php

namespace App\Http\Controllers;

use App\Models\Anecdote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnecdoteController extends Controller
{
    /**
     * Get the anecdotes of a user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getAnecdotes(Request $request)
    {
        $validated = $request->validate([
            'quantity' => 'nullable|integer|min:1|max:100',
        ]);

        try {
            $user_id = $request->user['id'];

            $quantity = $validated['quantity'] ?? 10;

            $anecdotes = Anecdote::withCount('likes')
                ->valid()
                ->orderBy('created_at', 'desc')
                ->take((int) $quantity)
                ->get();

            $data = $anecdotes->map(function ($anecdote) use ($user_id) {
                return [
                    'id' => $anecdote->id,
                    'text' => $anecdote->text,
                    'room' => [
                        'name' => $anecdote->room->name ?? null,
                        'roomNumber' => $anecdote->room->roomNumber ?? null,
                    ],
                    'liked' => $anecdote->isLikedBy($user_id),
                    'nbLikes' => $anecdote->likes_count,
                    'warned' => $anecdote->isWarnedBy($user_id),
                    'authorId' => $anecdote->user_id,
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des anecdotes: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Like an anecdote
     * @param Request $request
     * @param int $anecdoteId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function likeAnecdote(Request $request, $anecdoteId)
    {
        try {
            $validated = $request->validate([
                'like' => 'required|boolean',
            ]);

            $user_id = $request->user['id'];
            $anecdote = Anecdote::findOrFail($anecdoteId);

            $changed = $anecdote->toggleLike($user_id, $validated['like']);

            if ($changed) {
                return response()->json(['success' => true, 'data' => ['liked' => $validated['like']]]);
            }

            return response()->json(['success' => false, 'message' => 'Aucune modification effectuée.']);
        } catch (\Exception $e) {
            Log::error('Erreur lors du like: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors du like: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Warn an anecdote
     * @param Request $request
     * @param int $anecdoteId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function warnAnecdote(Request $request, $anecdoteId)
    {
        try {
            $validated = $request->validate([
                'warn' => 'required|boolean',
            ]);

            $user_id = $request->user['id'];
            $anecdote = Anecdote::findOrFail($anecdoteId);

            $changed = $anecdote->toggleWarn($user_id, $validated['warn']);

            if ($changed) {
                return response()->json(['success' => true, 'data' => ['warn' => $validated['warn']]]);
            }

            return response()->json(['success' => false, 'message' => 'Aucune modification effectuée.']);
        } catch (\Exception $e) {
            Log::error('Erreur lors du warn: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors du warn: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Send an anecdote (add in the database)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function sendAnecdote(Request $request)
    {
        $validated = $request->validate([
            'texte' => 'required|string|min:3|max:500',
        ]);

        try {
            $user_id = $request->user['id'];
            $text = $validated['texte'];

            $user = User::where('id', $user_id)->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
            }

            if (!$user->room_id) {
                return response()->json(['success' => false, 'message' => 'Vous devez être assigné à une chambre'], 400);
            }

            Anecdote::create(['text' => $text, 'room_id' => $user->room_id, 'user_id' => $user_id]);

            return response()->json(['success' => true, 'message' => 'Anecdote postée ! Elle sera visible une fois validée par le bureau']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de l\'anecdote: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur' . $e]);
        }
    }

    /**
     * Delete an anecdote
     * @param Request $request
     * @param int $anecdoteId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteAnecdote(Request $request, $anecdoteId)
    {
        try {
            $user_id = $request->user['id'];
            $anecdote = Anecdote::find($anecdoteId);

            if (!$anecdote) {
                return response()->json(['success' => false, 'message' => 'Anecdote introuvable.']);
            }

            if ($anecdote->user_id !== $user_id) {
                return response()->json(['success' => false, 'message' => 'Vous n\'êtes pas autorisé à supprimer cette anecdote.']);
            }

            $anecdote->delete();

            return response()->json(['success' => true, 'message' => "L'anecdote a bien été supprimée"]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'anecdote: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}
