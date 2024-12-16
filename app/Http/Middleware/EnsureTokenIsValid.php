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

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $publicKey = config('services.crypt.public');
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message'=>"JWT absent pour l'authentification",'JWT_ERROR'=>true],400);
        }
        try{
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));
        }catch(ExpiredException){
            return response()->json(['message'=>'JWT expiré','JWT_ERROR'=>true],401);
        }catch(SignatureInvalidException){
            return response()->json(['message'=>'Signature invalide pour le JWT envoyé','JWT_ERROR'=>true],401);
        } catch (LogicException $e) {
            return response()->json(['message' => 'Erreur dans la configuration ou les clés JWT', 'JWT_ERROR' => true], 400);
        } catch (UnexpectedValueException $e) {
            return response()->json(['message' => 'Le JWT est mal formé ou contient des données invalides', 'JWT_ERROR' => true], 400);
        }        
        $uuid = $decoded->key;
        $user = User::find($uuid);
        if (!$user) {
            return response()->json(['message' => "Utilisateur non trouvé pour le token fourni", 'JWT_ERROR' => true], 404);
        }
        $request->merge(['user' => $user->toArray()]);        
        return $next($request);
    }
}

