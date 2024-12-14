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
        $publicKey = config('services.crypt.public');
        $token = $request->bearerToken();
        if ($token === null) {
            return response()->json(['message'=>'Missing Json Web Token For Validation','JWT_ERROR'=>true],400);
        }
        try{
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));
        }catch(ExpiredException){
            return response()->json(['message'=>'Json Web Token Expired','JWT_ERROR'=>true],401);
        }catch(SignatureInvalidException){
            return response()->json(['message'=>'Invalid Signature In Sent Json Web Token','JWT_ERROR'=>true],401);
        }catch (LogicException) {
            return response()->json(['message'=>'Error having to do with environmental setup or malformed JWT Keys','JWT_ERROR'=>true],401);
        } catch (UnexpectedValueException) {
            return response()->json(['message'=>'Error having to do with JWT signature and claims','JWT_ERROR'=>true],401);
        }
        $uuid = $decoded->key;
        $user = User::where('id', $uuid)->first();
        $request->merge(['user'=>$user->toArray()]);
        return $next($request);
    }
}

