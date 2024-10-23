<?php

namespace App\Http\Controllers;

use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

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
            $user=User::find(1);
            Auth::login($user);
            return redirect()->intended(RouteServiceProvider::HOME);
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
     *
     * @param  Request $request The HTTP request object.
     * @param  UserController $userController The user controller instance.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request, UserController $userController)
    {
        // Retrieve the state parameter from the session
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
            // Exchange the authorization code for an access token
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $request->get('code')
            ]);

            $resourceOwner = $this->provider->getResourceOwner($accessToken);
            $userDetails = $resourceOwner->toArray();

            // Check if user account has been deleted
            if ($userDetails['deleted_at'] != null) {
                abort(401, 'Account deleted');
            }

            // Create or update the user in the database
            $user = $userController->createOrUpdateUser($userDetails);

            // Store the user in the session
            Auth::login($user);

            if (Auth::check()) {
                return redirect()->route('accueil');
            } else {
                abort(500, 'User authentication failed');
            }

            return redirect()->intended(RouteServiceProvider::HOME);
        } catch (IdentityProviderException $e) {
            abort(400, 'Authentication failed: ' . $e->getMessage());
        }
    }

    /**
     * Log the user out of the application.
     *
     * @param  Request  $request  The HTTP request object.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Clear the user's session data
        $request->session()->forget('user');

        // Log the user out
        Auth::guard('web')->logout();

        // Invalidate the session
        $request->session()->invalidate();

        // Regenerate the CSRF token
        $request->session()->regenerateToken();

        return redirect(config('services.oauth.logout_url'));
    }
}