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
            $userId=env('USER_ID');
            try {      
                $accessTokenPayload = [
                    'key' => $userId,
                    'exp' => now()->addMinutes(60)->timestamp,
                ];
                $privateKey = config("services.crypt.private");
                $accessToken = JWT::encode($accessTokenPayload, $privateKey, 'RS256');
    
                $refreshTokenPayload = [
                    'key' => $userId,
                    'exp' => now()->addDays(30)->timestamp,
                ];
                $refreshToken = JWT::encode($refreshTokenPayload, $privateKey, 'RS256');
    
                return redirect()->route('api-connected',[
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
        if (!$token) {
            return response()->json(['message'=>"Refresh JWT absent pour l'authentification",'JWT_ERROR'=>true],400);
        }
        try{
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));
        }catch(ExpiredException){
            return response()->json(['message'=>'Refresh JWT expiré','JWT_ERROR'=>true],401);
        }catch(SignatureInvalidException){
            return response()->json(['message'=>'Signature invalide pour le refresh JWT envoyé','JWT_ERROR'=>true],401);
        } catch (LogicException $e) {
            return response()->json(['message' => 'Erreur dans la configuration ou les clés du JWT de refresh', 'JWT_ERROR' => true], 400);
        } catch (UnexpectedValueException $e) {
            return response()->json(['message' => 'Le refresh JWT est mal formé ou contient des données invalides', 'JWT_ERROR' => true], 400);
        }        
        $id = $decoded->key;
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => "Utilisateur non trouvé pour le refresh token fourni", 'JWT_ERROR' => true], 404);
        }

        $accessTokenPayload = [
            'key' => $id,
            'exp' => now()->addMinutes(60)->timestamp,
        ];
        $privateKey = config("services.crypt.private");
        $accessToken = JWT::encode($accessTokenPayload, $privateKey, 'RS256');

        return response()->json(['access_token'=>$accessToken]);
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

            $id = $decoded->key;

            $user = User::where('id', $id)->first();

            if (!$user) {
                return response()->json(['error' => 'User non trouvé'], 404);
            }

            redirect(route('api-connected'));

            return response()->json([
                'id'=> $user->id,
                'name'=> $user->firstName,
                'lastName'=> $user->lastName,
                'room'=>$user->roomID,
                'admin'=> $user->admin
            ]);
        } catch (\Exception $e) {
            Log::error("Error du décodage JWT " . $e->getMessage());
            return response()->json(['Erreur' => 'Invalid token'], 401);
        }
    }

    public function logout(Request $request)
    {
        $cookie = cookie('auth_session', null, -1);
        return redirect('https://auth.assos.utc.fr/logout')->withCookie($cookie);
    }
}