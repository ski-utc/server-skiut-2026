<?php

namespace App\Http\Controllers;

use App\Models\Anecdote;
use App\Models\User;
use App\Models\AnecdotesLike;
use App\Models\AnecdotesWarn;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AnecdoteController extends Controller
{
    public function getAnecdotes(Request $request)
    {
        try {
            $userId = $request->user['id'];;
    
            $quantity = $request->input('quantity', 10);
    
            if (!is_numeric($quantity) || (int)$quantity <= 0) {
                return response()->json(['success' => false, 'message' => 'Le paramètre quantity doit être un entier positif.']);
            }
    
            $anecdotes = Anecdote::withCount('likes')
                ->where("valid", true)
                ->orderBy('likes_count', 'desc')
                ->take((int)$quantity)
                ->get();
    
            $data = $anecdotes->map(function ($anecdote) use ($userId) {
                return [
                    'id' => $anecdote->id,
                    'text' => $anecdote->text,
                    'room' => $anecdote->room,
                    'liked' => $anecdote->likes()->where('user_id', $userId)->exists(),
                    'nbLikes' => $anecdote->likes_count,
                    'warned' => $anecdote->warns()->where('user_id', $userId)->exists(),
                    'authorId' => $anecdote->userId,
                ];
            });
    
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }  
    

    public function likeAnecdote(Request $request)
    {
        $userId = $request->user['id'];;
    
        $anecdoteId = $request->input('anecdoteId');
    
        $existingLike = AnecdotesLike::where('user_id', $userId)
            ->where('anecdote_id', $anecdoteId)
            ->first();
    
        if ($request->input('like')) {
            if (!$existingLike) {
                AnecdotesLike::create(['user_id' => $userId, 'anecdote_id' => $anecdoteId]);
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

    public function warnAnecdote(Request $request)
    {
        $userId = $request->user['id'];;
    
        $anecdoteId = $request->input('anecdoteId');
    
        $existingWarn = AnecdotesWarn::where('user_id', $userId)
            ->where('anecdote_id', $anecdoteId)
            ->first();
    
        if ($request->input('warn')) {
            if (!$existingWarn) {
                AnecdotesWarn::create(['user_id' => $userId, 'anecdote_id' => $anecdoteId]);
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

    public function sendAnecdote(Request $request){
        try{
            $userId = $request->user['id'];;
    
            $text = $request->input('texte');

            $room = User::where('id', $userId)->first()->roomID;
    
            Anecdote::create(["text"=>$text, 'room'=>$room, 'userId'=>$userId]);
            return response()->json(['success' =>true, "message"=>"Anecdote postée avec succès ! Elle sera visible une fois validée par le bureau"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, "message"=>"Erreur".$e]);
        }    
    }

    public function deleteAnecdote(Request $request)
    {
        try {
            $userId = $request->user['id'];;
    
            $anecdoteId = $request->input('anecdoteId');
    
            $anecdote = Anecdote::find($anecdoteId);
    
            if (!$anecdote) {
                return response()->json(['success' => false, 'message' => 'Anecdote introuvable.']);
            }
    
            if ($anecdote->userId !== $userId) {
                return response()->json(['success' => false, 'message' => 'Vous n\'êtes pas autorisé à supprimer cette anecdote.']);
            }
    
            $anecdote->delete();
    
            return response()->json(['success' => true, 'message' => 'Anecdote supprimée avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
    
}