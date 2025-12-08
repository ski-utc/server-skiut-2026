<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * Get the contacts
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getContacts()
    {
        try {
            $contacts = Contact::all();

            $data = $contacts->map(function ($contact) {
                return [
                    'name' => $contact->name,
                    'role' => $contact->role,
                    'phoneNumber' => $contact->phoneNumber,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des contacts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
