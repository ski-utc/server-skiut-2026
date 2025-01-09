<?php

namespace App\Http\Controllers;

use App\Models\Room;

class ClassementController extends Controller
{
    public function classementChambres()
    {
        // Récupérer toutes les chambres triées par totalPoints décroissant
        $rooms = Room::orderBy('totalPoints', 'desc')->get(['roomNumber', 'totalPoints']);

        // Séparer les 3 premières chambres pour le podium
        $podiumRooms = $rooms->take(3);

        // Séparer le reste des chambres
        $restRooms = $rooms->slice(3);

        return response()->json([
            'success' => true,
            'podium' => $podiumRooms,
            'rest' => $restRooms
        ]);
    }
}
