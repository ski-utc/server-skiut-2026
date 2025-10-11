<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

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
