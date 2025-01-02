<?php

namespace App\Http\Controllers;

use App\Models\User;

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
        $user = User::updateOrCreate( // va être un find User pour s'assurer que c'est un participant
            ['uuid' => $userDetails['uuid']],
            [
                'cas' => $userDetails['provider_data']['username'] ?? 'undefined',
                'firstName' => $userDetails['firstName'],
                'lastName' => $userDetails['lastName'],
                'email' => $userDetails['email'],
                'roomID' => $userDetails['roomID'] ?? 1,
                'location' => $userDetails['location'] ?? null,
                'admin' => $userDetails['admin'] ?? false,
                'alumniOrExte' => $userDetails['alumniOrExte'] ?? false
            ]
        );
    
        return $user;
    }
    
}
