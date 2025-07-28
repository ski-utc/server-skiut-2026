<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $publicKey = config('services.crypt.public');
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => "JWT absent pour l'authentification",'JWT_ERROR' => true], 400);
        }

        try {
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));
            $id = $decoded->key;
            $user = User::find($id);
            if (!$user) {
                return response()->json(['message' => 'Utilisateur non trouvé pour le token fourni', 'JWT_ERROR' => true], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur dans la configuration ou les clés JWT', 'JWT_ERROR' => true], 400);
        }

        if (!$user->admin) {
            return response()->json(['message' => 'Vous n\'êtes pas admin.'], 403);
        }
        return $next($request);
    }
}
