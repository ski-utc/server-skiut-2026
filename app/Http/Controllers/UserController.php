<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
            [
                'id' => $userDetails['uuid'],
                'name' => $userDetails['firstName'],
                'email' => $userDetails['email'],
            ]
        );

        return $user;
    }
}
