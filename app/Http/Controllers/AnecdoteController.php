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
        $anecdotes = Anecdote::withCount('likes')  // Suppose que vous avez une relation avec `anecdotes_likes`
            ->orderBy('likes_count', 'desc')  // Trier par le nombre de likes
            ->take(10)
            ->get();
    
        return response()->json(['success' => true, 'data' => $anecdotes]);
    }

    public function likeAnecdote(Request $request)
    {
        $userId = $request->user()->id;
        $anecdoteId = $request->input('anecdoteId');

        // Ajouter un like à l'anecdote
        AnecdotesLike::create(['userId' => $userId, 'anecdoteId' => $anecdoteId]);

        return response()->json(['success' => true]);
    }

    public function warnAnecdote(Request $request)
    {
        $userId = $request->user()->id;
        $anecdoteId = $request->input('anecdoteId');

        // Ajouter une alerte à l'anecdote
        AnecdotesWarn::create(['userId' => $userId, 'anecdoteId' => $anecdoteId]);

        return response()->json(['success' => true]);
    }

    public function sendAnecdote(Request $request){
        try{
            $publicKey = config('services.crypt.public');
            $token = $request->bearerToken();
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));     
            $userId = $decoded->key;
    
            $text = $request->input('texte');

            $room = User::where('id', $userId)->first()->roomID;
    
            Anecdote::create(["text"=>$text, 'room'=>$room, 'userId'=>$userId]);
            return response()->json(['success' =>true, "message"=>"Anecdote postée avec succès !"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, "message"=>"Erreur".$e]);
        }    
    }
}