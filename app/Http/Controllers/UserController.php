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
                'firstname' => $userDetails['firstName'],
                'lastname' => $userDetails['lastName'],
                'email' => $userDetails['email'],
            ]
        );

        return $user;
    }

    /**
     * Update language preference of the authenticated user
     *
     * @param $language
     */
    public static function updateLanguagePreference($language)
    {
        // Find the user by ID
        $user = User::find(Auth::user()->id);
        $user->language = $language;
        $user->save();

        return response('', 200);
    }
}
