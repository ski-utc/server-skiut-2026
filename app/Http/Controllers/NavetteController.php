<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class NavetteController extends Controller
{
    /**
     * Récupère les navettes d'un utilisateur
     */
    public function getNavettes(Request $request)
    {
        try {
            $id = $request->user['id'];
            $user = User::with('transports')->where('id', $id)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            $transports = $user->transports;
            $transportsByType = [
                'Aller' => [],
                'Retour' => [],
            ];

            foreach ($transports as $transport) {
                $formattedTransport = [
                    'id' => $transport->id,
                    'departure' => $transport->departure,
                    'arrival' => $transport->arrival,
                    'horaire_depart' => $transport->horaire_depart,
                    'horaire_arrivee' => $transport->horaire_arrivee,
                    'colour' => $transport->colour,
                    'colourName' => $transport->colourName,
                    'type' => $transport->type,
                ];

                if ($transport->type === 'aller') {
                    $transportsByType['Aller'][] = $formattedTransport;
                } elseif ($transport->type === 'retour') {
                    $transportsByType['Retour'][] = $formattedTransport;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $transportsByType,
                'message' => 'Transports fetched successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des navettes : ' . $e->getMessage(),
            ], 500);
        }
    }
}
