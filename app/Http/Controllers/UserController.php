<?php

namespace App\Http\Controllers;

use App\Models\PushToken;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Create or update a user in the database.
     * @param array $userDetails
     * @return User
     * @throws \Exception
     */
    public function createOrUpdateUser($userDetails)
    {
        $user = User::updateOrCreate(
            ['email' => $userDetails['email']],
            [
                'cas' => $userDetails['provider_data']['username'] ?? 'undefined',
                'firstName' => $userDetails['firstName'],
                'lastName' => $userDetails['lastName'],
                'room_id' => $userDetails['room_id'] ?? 0,
                'location' => $userDetails['location'] ?? null,
                'admin' => $userDetails['admin'] ?? false,
                'alumniOrExte' => $userDetails['alumniOrExte'] ?? false
            ]
        );

        return $user;
    }

    /**
     * Save a push token for a user.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function saveToken(Request $request)
    {
        $validated = $request->validate([
            'userToken' => 'required|string',
            'device_type' => 'nullable|string',
            'device_name' => 'nullable|string',
        ]);

        try {
            $userId = $request->user['id'] ?? null;

            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non authentifié'], 401);
            }

            PushToken::updateOrCreate(
                ['token' => $validated['userToken']],
                [
                    'user_id' => $userId,
                    'device_type' => $validated['device_type'],
                    'device_name' => $validated['device_name'],
                    'active' => true,
                    'last_used_at' => now(),
                ]
            );

            return response()->json(['success' => true, 'message' => 'Token enregistré avec succès']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la récupération du token : '. $e->getMessage()], 500);
        }
    }
}
