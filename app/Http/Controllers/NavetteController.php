<?php
namespace App\Http\Controllers;
use App\Models\Transport;
use App\Models\User; 
use Illuminate\Http\Request;

class NavetteController extends Controller
{
    public function index()
    {
        $transports = Transport::all();

        return response()->json([
            'success' => true,
            'data' => $transports
        ]);
    }

    public function getNavettes(Request $request)
{
    try {
        // Get user ID from the request
        $id = $request->user['id'];
        
        // Fetch the user along with their associated transports (navettes)
        $user = User::with('transports')->where('id', $id)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        // Get the transports associated with the user
        $transports = $user->transports;

        // Organize the transports by type
        $transportsByType = [
            'Aller' => [],
            'Retour' => [],
        ];

        foreach ($transports as $transport) {
            // Format the transport data as needed
            $formattedTransport = [
                'id' => $transport->id,
                'departure' => $transport->departure,
                'arrival' => $transport->arrival,
                'horaire_depart' => $transport->horaire_depart,
                'horaire_arrivee' => $transport->horaire_arrivee,
                'colour' => $transport->colour,
                'type' => $transport->type,
            ];

            // Categorize by transport type
            if ($transport->type === 'Aller') {
                $transportsByType['Aller'][] = $formattedTransport;
            } elseif ($transport->type === 'Retour') {
                $transportsByType['Retour'][] = $formattedTransport;
            }
        }

        // Return the response with the organized transport data
        return response()->json([
            'success' => true,
            'data' => $transportsByType,
            'message' => 'Transports fetched successfully.',
        ]);
    } catch (\Exception $e) {
        // Return error response in case of exception
        return response()->json([
            'success' => false,
            'message' => 'Une erreur est survenue lors de la récupération des navettes : ' . $e->getMessage(),
        ], 500);
    }
}

}