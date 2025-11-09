<?php

namespace App\Http\Controllers;

use App\Models\Anecdote;
use App\Models\AnecdotesLike;
use App\Models\AnecdotesWarn;
use App\Models\User;
use Illuminate\Http\Request;

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
        try {
            $user_id = $request->user['id'];

            $quantity = $request->input('quantity', 10);

            if (!is_numeric($quantity) || (int)$quantity <= 0) {
                return response()->json(['success' => false, 'message' => 'Le paramètre quantity doit être un entier positif.']);
            }

            $anecdotes = Anecdote::withCount('likes')
                ->where('valid', true)
                ->orderBy('created_at', 'desc')
                ->take((int)$quantity)
                ->get();

            $data = $anecdotes->map(function ($anecdote) use ($user_id) {
                return [
                    'id' => $anecdote->id,
                    'text' => $anecdote->text,
                    'room' => $anecdote->room,
                    'liked' => $anecdote->likes()->where('user_id', $user_id)->exists(),
                    'nbLikes' => $anecdote->likes_count,
                    'warned' => $anecdote->warns()->where('user_id', $user_id)->exists(),
                    'authorId' => $anecdote->user_id,
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
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
        $user_id = $request->user['id'];

        $existingLike = AnecdotesLike::where('user_id', $user_id)
            ->where('anecdote_id', $anecdoteId)
            ->first();

        if ($request->input('like')) {
            if (!$existingLike) {
                AnecdotesLike::create(['user_id' => $user_id, 'anecdote_id' => $anecdoteId]);
                return response()->json(['success' => true, 'liked' => true]);
            }
        } else {
            if ($existingLike) {
                $existingLike->delete();
                return response()->json(['success' => true, 'liked' => false]);
            }
        }
        return response()->json(['success' => false, 'message' => 'Aucune modification effectuée.']);
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
        $user_id = $request->user['id'];

        $existingWarn = AnecdotesWarn::where('user_id', $user_id)
            ->where('anecdote_id', $anecdoteId)
            ->first();

        if ($request->input('warn')) {
            if (!$existingWarn) {
                AnecdotesWarn::create(['user_id' => $user_id, 'anecdote_id' => $anecdoteId]);
                return response()->json(['success' => true, 'warn' => true]);
            }
        } else {
            if ($existingWarn) {
                $existingWarn->delete();
                return response()->json(['success' => true, 'warn' => false]);
            }
        }
        return response()->json(['success' => false, 'message' => 'Aucune modification effectuée.']);
    }

    /**
     * Send an anecdote (add in the database)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function sendAnecdote(Request $request)
    {
        try {
            $user_id = $request->user['id'];
            $text = $request->input('texte');

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
            return response()->json(['success' => false, 'message' => 'Erreur'.$e]);
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
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}
