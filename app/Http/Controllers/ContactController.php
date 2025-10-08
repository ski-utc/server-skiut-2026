<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Contact;

class ContactController extends Controller
{
    /**
     * Obtenir les contacts.
     */
    public function getContacts(Request $request)
    {
        try {
            $contacts = Contact::all();

            return response()->json([
                    'success' => true,
                    'data' => $contacts
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
