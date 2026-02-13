<?php

namespace App\Http\Controllers;

use App\Models\User;

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
}
