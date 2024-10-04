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
        $public_key_path=storage_path('app/private/key.pub');
        $public_key = openssl_pkey_get_public('file://' . $public_key_path);
        if (!$public_key) {
            return response()->json(['message' => 'Invalid public key','JWT_ERROR' => true], 500);
        }
        $token = $request->bearerToken();
        if ($token === null) {
            if (env("APP_API_NO_LOGIN", false)) {
                // Si activé dans le .env, bypass de le login (pour le developpement uniquement !)
                $user = User::find(1);  // utiliser le user 1
                $request->merge(['user'=>$user->toArray()]);
                error_log(json_encode($request));
                return $next($request);
            }
            return response()->json(['message'=>'Missing Json Web Token For Validation','JWT_ERROR'=>true],400);
        }
        try{
            $decoded_id = JWT::decode($token,new Key($public_key,'RS256'));
        }catch(ExpiredException){
            return response()->json(['message'=>'Json Web Token Expired','JWT_ERROR'=>true],401);
        }catch(SignatureInvalidException){
            return response()->json(['message'=>'Invalid Signature In Sent Json Web Token','JWT_ERROR'=>true],401);
        }catch (LogicException) {
            // errors having to do with environmental setup or malformed JWT Keys
            return response()->json(['message'=>'Error having to do with environmental setup or malformed JWT Keys','JWT_ERROR'=>true],401);
        } catch (UnexpectedValueException) {
            return response()->json(['message'=>'Error having to do with JWT signature and claims','JWT_ERROR'=>true],401);
        }
        if (!isset($decoded_id->data->id)) {
            return response()->json(['message' => 'Invalid token payload','JWT_ERROR' => true], 401);
        } else {
            $user = User::find($decoded_id->data->id);
            $user = User::find($decoded_id->data->id);
            if (!$user) {
                return response()->json(['message' => 'User not found','JWT_ERROR' => true], 404);
            } else {
                $request->merge(['user'=>$user->toArray()]);
                return $next($request);
            }
        }
    }
}
