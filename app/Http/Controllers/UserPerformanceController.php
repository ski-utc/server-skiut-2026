<?php

namespace App\Http\Controllers;

use App\Models\UserPerformance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserPerformanceController extends Controller
{
    /**
     * Mettre à jour la performance d'un utilisateur.
     */
    public function updatePerformance(Request $request)
    {
        // Valider les données entrantes
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'speed' => 'required|numeric|min:0',
            'distance' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user_id = $request->user_id;

        // Arrondir les valeurs de vitesse et de distance
        $speed = round($request->speed, 1);
        $distance = round($request->distance, 2);

        // Rechercher ou créer une performance pour l'utilisateur
        $performance = UserPerformance::firstOrCreate(
            ['user_id' => $user_id],
            ['max_speed' => 0, 'total_distance' => 0]
        );

        // Mettre à jour la vitesse maximale si elle est supérieure à l'actuelle
        if ($speed > $performance->max_speed) {
            $performance->max_speed = $speed;
        }

        // Ajouter la distance parcourue à la distance totale
        $performance->total_distance += $distance;

        // Sauvegarder les modifications
        $performance->save();

        return response()->json([
            'success' => true,
            'message' => 'Performance mise à jour avec succès.',
            'data' => $performance,
        ]);
    }
}
