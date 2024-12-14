<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\GenericProvider;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;
use UnexpectedValueException;
use LogicException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class AuthController extends Controller
{
    /**
     * The OAuth2 provider instance.
     *
     * @var GenericProvider
     */
    public GenericProvider $provider;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Initialize the OAuth2 provider with configuration values from environment variables
        $this->provider = new GenericProvider([
            'clientId'                => config("services.oauth.client_id"),
            'clientSecret'            => config("services.oauth.client_secret"),
            'redirectUri'             => config("services.oauth.redirect_uri"),
            'urlAuthorize'            => config("services.oauth.authorize_url"),
            'urlAccessToken'          => config("services.oauth.access_token_url"),
            'urlResourceOwnerDetails' => config("services.oauth.owner_details_url"),
            'scopes'                  => config("services.oauth.scopes"),
        ]);
    }

    /**
     * Handle user login via OAuth2.
     *
     * @param  Request $request The HTTP request object.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        if (config('auth.app_no_login', false)) {
            $userId='896c4495-6145-412c-a928-5a93263a0459';
            try {      
                $accessTokenPayload = [
                    'key' => $userId,
                    'exp' => now()->addMinutes(60)->timestamp,
                ];
                $accessToken = JWT::encode($accessTokenPayload, env('APP_JWT_SECRET'), 'RS256');
    
                $refreshTokenPayload = [
                    'key' => $userId,
                    'exp' => now()->addDays(30)->timestamp,
                ];
                $refreshToken = JWT::encode($refreshTokenPayload, env('APP_JWT_SECRET'), 'RS256');
    
                return response()->json([
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                ]);
            } catch (\Exception $e) {
                Log::error("Callback error: " . $e->getMessage());
                return response()->json(['error' => 'Authentication failed'], 400);
            }
        }

        // Generate a random state parameter
        $state = bin2hex(random_bytes(16));
        $request->session()->put('oauth2state', $state);

        // Redirect the user to the OAuth2 authorization URL
        $authorizationUrl = $this->provider->getAuthorizationUrl([
            'state' => $state
        ]);

        return redirect($authorizationUrl);
    }

    /**
     * Handle the OAuth2 callback.
     */
    public function callback(Request $request, UserController $userController)
    {
        $storedState = $request->session()->pull('oauth2state');

        // Check if the state parameter is present and valid
        if (!$request->has('state') || $request->get('state') !== $storedState) {
            abort(400, 'Invalid state: '. $request->get('state') . ' VS ' . $storedState);
        }

        // Check if the authorization code is present
        if (!$request->has('code')) {
            abort(400, 'No authorization code');
        }
        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $request->get('code'),
            ]);

            $resourceOwner = $this->provider->getResourceOwner($accessToken);
            $userDetails = $resourceOwner->toArray();

            if ($userDetails['deleted_at'] != null || $userDetails['active'] != 1) {
                abort(401, 'Account deleted or deactivated');
            }

            $user = $userController->createOrUpdateUser($userDetails);

            $accessTokenPayload = [
                'key' => $user->id,
                'exp' => now()->addMinutes(60)->timestamp,
            ];
            $privateKey = config("services.crypt.private");
            $accessToken = JWT::encode($accessTokenPayload, $privateKey, 'RS256');

            $refreshTokenPayload = [
                'key' => $user->id,
                'exp' => now()->addDays(30)->timestamp,
            ];
            $refreshToken = JWT::encode($refreshTokenPayload, $privateKey, 'RS256');
                        
            return redirect()->route('api-connected',[
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Authentication failed', "Callback error: " => $e->getMessage()], 400);
        }
    }

    public function refresh(Request $request)
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

        $accessTokenPayload = [
            'key' => $uuid,
            'exp' => now()->addMinutes(60)->timestamp,
        ];
        $accessToken = JWT::encode($accessTokenPayload, env('APP_JWT_SECRET'), 'RS256');

        return response()->json([
            'message'=>'Authentication Successfully Executed',
            'data'=>[
                'access_token'=>$accessToken,
            ]
        ]);
    }

    /**
     * Récupère les informations de l'utilisateur à partir d'un token.
     */
    public function getUserData(Request $request)
    {
        $token = $request->bearerToken();

        try {
            $publicKey = config("services.crypt.public");
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));            

            $uuid = $decoded->key;

            $user = User::where('id', $uuid)->first();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            return response()->json([
                'id'=> $user->id,
                'name'=> $user->firstName,
                'lastName'=> $user->lastName,
                'room'=>$user->room(),
                'location'=> $user->location,
                'admin'=> $user->admin
            ]);
        } catch (\Exception $e) {
            Log::error("Token decoding error: " . $e->getMessage());
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }
}