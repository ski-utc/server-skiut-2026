<?php

namespace App\Http\Controllers;

use App\Models\Anecdote;
use Illuminate\Support\Facades\Auth;

class AnecdoteController extends Controller
{
    public function getAnecdotes()
    {
        $userId = Auth::id();

        $anecdotes = Anecdote::select('id', 'text', 'room', 'user_id')
            ->with('user:id,name')
            ->inRandomOrder()
            ->limit(15)
            ->get();

            $anecdotes->each(function ($anecdote) use ($userId) {
                $anecdote->nbLikes = $anecdote->nbLikes();
            });
        
            $anecdotes->each(function ($anecdote) use ($userId) {
            $anecdote->liked = $anecdote->isLikedBy($userId) ? 1 : 0;
        });

        $anecdotes->each(function ($anecdote) use ($userId) {
            $anecdote->warn = $anecdote->isWarnedBy($userId) ? 1 : 0;
        });

        return response()->json($anecdotes);
    }
}