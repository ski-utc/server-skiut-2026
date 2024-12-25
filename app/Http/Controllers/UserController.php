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
        $user = User::firstOrCreate(
            ['id' => $userDetails['uuid']],
            [
                'firstName' => $userDetails['firstName'],
                'lastName' => $userDetails['lastName'],
                'email' => $userDetails['email'],
            ]
        );

        return $user;
    }
}
