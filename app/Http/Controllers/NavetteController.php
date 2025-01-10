<?php
namespace App\Http\Controllers;
use App\Models\Transport;

class NavetteController extends Controller
{
    public function index()
    {
        $transports = Transport::all();

        return response()->json([
            'success' => true,
            'data' => $transports
        ]);
    }
}
