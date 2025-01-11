<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\PushToken;

class UserController extends Controller
{
    /**
     * Create or update a user in the database
     *
     * @param $userDetails
     * @param $currentAssociations
     * @return User
     */
    public function createOrUpdateUser($userDetails)
    {
        $user = User::updateOrCreate(
            ['email' => $userDetails['email']],
            [
                'cas' => $userDetails['provider_data']['username'] ?? 'undefined',
                'firstName' => $userDetails['firstName'],
                'lastName' => $userDetails['lastName'],
                'roomID' => $userDetails['roomID'] ?? 0,
                'location' => $userDetails['location'] ?? null,
                'admin' => $userDetails['admin'] ?? false,
                'alumniOrExte' => $userDetails['alumniOrExte'] ?? false
            ]
        ); // si se connecte avec email, utiliser comme clé primaire (et cas sinon)
    
        return $user;
    }

    public function saveToken(Request $request) {   
        try{
            PushToken::updateOrCreate(
                ['token' => $request->userToken],
                ['user_id' => $request->user['id']]
            );
        
            return response()->json(['success'=>true, 'message' => 'Token enregistré avec succès']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la récupération du token : '. $e]);
        }
    }
}