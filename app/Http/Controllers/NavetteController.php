<?php
namespace App\Http\Controllers;
use App\Models\Transport;
use Illuminate\Http\Request;

class NavetteController extends Controller
{
    public function index()
    {
        $transports = Transport::all();

        // Retournez les données
        return response()->json([
            'success' => true,
            'data' => $transports
        ]);
    }
}
