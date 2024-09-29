<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class Example extends Controller
{
    public function exampleFunction(Request $request)
    {
        $response_data = array();
        $response_data['success'] = true;
        $response_data['message'] = 'Bip Boup from le serveur';
        return($response_data);
    }
}
