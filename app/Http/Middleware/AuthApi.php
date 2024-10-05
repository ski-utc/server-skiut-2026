<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use UnexpectedValueException;
use LogicException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $public_key_path = config('jwt.keys.public');
        $public_key = openssl_pkey_get_public('file://' . $public_key_path);
        if (!$public_key) {
            return $this->respondWithError('Clé publique invalide', 500);
        }
        $token = $request->bearerToken();
        if ($token === null) {
            // Bypass si APP_API_NO_LOGIN est activé
            if (config('app.api_no_login', false)) {
                $user = $this->bypassAuthForDevelopment(); // Attribue le user 1
                $request->merge(['user' => $user->toArray()]);
                return $next($request);
            }
            return $this->respondWithError('Token JWT manquant', 400);
        }
        try {
            $decoded_token = JWT::decode($token, new Key($public_key, config('jwt.algo')));
            if (!isset($decoded_token->data->id)) {
                return $this->respondWithError('ID utilisateur manquant dans le JWT', 401);
            }
            $user = User::find($decoded_token->data->id);
            if (!$user) {
                return $this->respondWithError('Utilisateur non trouvé', 404);
            }
            $request->merge(['user' => $user->toArray()]);
        } catch (ExpiredException $e) {
            return $this->respondWithError('Le token JWT a expiré', 401);
        } catch (SignatureInvalidException $e) {
            return $this->respondWithError('Signature du token JWT invalide', 401);
        } catch (LogicException $e) {
            return $this->respondWithError('Erreur liée à la clé publique ou au JWT', 500);
        } catch (UnexpectedValueException $e) {
            return $this->respondWithError('Le token JWT est mal formé', 400);
        }

        return $next($request);
    }

    protected function respondWithError(string $message, int $statusCode)
    {
        return response()->json([
            'message' => $message,
            'JWT_ERROR' => true
        ], $statusCode);
    }

    protected function bypassAuthForDevelopment()
    {
        return User::find(1);
    }
}
