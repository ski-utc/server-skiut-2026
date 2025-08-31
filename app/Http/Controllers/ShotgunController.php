<?php

namespace App\Http\Controllers;

use App\Models\Shotguns;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShotgunController extends Controller
{
    // Route GET /game
    public function showGame()
    {
        $token = Str::uuid()->toString();

        Cache::put("game_token:$token", true, now()->addMinutes(5));

        return view('shotgun.game', ['token' => $token]);
    }

    // Route POST /submit
    public function submit(Request $request)
    {
        try {
            $request->validate([
                'email' => [
                    'required',
                    'email',
                    'regex:/^[a-zA-Z0-9._%+-]+@etu\.utc\.fr$/',    # /^[a-zA-Z0-9._%+-]+@(etu\.)?utc\.fr$' | /^[a-zA-Z0-9._%+-]+@([a-z0-9-]+\.)?utc\.fr$
                ],
                'token' => 'required|string',
            ]);

            $token = $request->input('token');
            if (! Cache::pull("game_token:$token")) {
                return response()->json(['error' => 'Invalid or expired token'], 400);
            }

            $exists = Shotguns::where('email', $request->input('email'));
            if ($exists->exists()) {
                $position = $exists->first()->position;
                $win = $position <= 420 ? true : false;  // TODO : remplacer avec le nombre de places

                return response()->json(['success' => true, 'position' => $position, 'win' => $win, 'new' => false]);
            }

            $lastPosition = Shotguns::max('position') ?? 0;
            $position = $lastPosition + 1;
            Shotguns::create(['email' => $request->input('email'), 'position' => $position]);

            $win = $position <= 420 ? true : false;  // TODO : remplacer avec le nombre de places

            return response()->json(['success' => true, 'position' => $position, 'win' => $win, 'new' => true]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Adresse email invalide. Utilisez une adresse @etu.utc.fr.',
                'details' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
