<?php

namespace App\Http\Controllers;

use App\Models\ShotgunChambresAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use League\OAuth2\Client\Provider\GenericProvider;

class AuthBackOfficeController extends Controller
{
    public GenericProvider $provider;

    public function __construct()
    {
        $this->provider = new GenericProvider([
            'clientId'                => config('services.oauth.client_id'),
            'clientSecret'            => config('services.oauth.client_secret'),
            'redirectUri'             => config('services.oauth.backoffice_redirect_uri', config('app.url') . '/skiutc/auth/shotgun-chambre/callback'),
            'urlAuthorize'            => config('services.oauth.authorize_url'),
            'urlAccessToken'          => config('services.oauth.access_token_url'),
            'urlResourceOwnerDetails' => config('services.oauth.owner_details_url'),
            'scopes'                  => config('services.oauth.scopes'),
        ]);
    }

    /**
     * Gère le login d'un utilisateur via OAuth2 pour le back-office
     */
    public function login(Request $request)
    {
        $state = bin2hex(random_bytes(16));
        $request->session()->put('oauth2state_backoffice', $state);

        $authorizationUrl = $this->provider->getAuthorizationUrl([
            'state' => $state
        ]);

        return redirect($authorizationUrl);
    }

    /**
     * Gère le callback de l'OAuth du SiMDE pour le back-office
     */
    public function callback(Request $request)
    {
        $storedState = $request->session()->pull('oauth2state_backoffice');

        if (!$request->has('state') || $request->get('state') !== $storedState) {
            abort(400, 'Invalid state: ' . $request->get('state') . ' VS ' . $storedState);
        }

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
                abort(401, 'Compte supprimé ou désactivé');
            }

            $isAdmin = ShotgunChambresAdmin::isAdmin($userDetails['email']) ? true : false;

            session(['admin' => $isAdmin]);
            session(['email' => $userDetails['email']]);

            return redirect()->route('filament.shotgun-chambre.pages.dashboard');
        } catch (\Exception $e) {
            abort(401, 'Erreur d\'authentification : ' . $e->getMessage());
        }
    }

    /**
     * Déconnexion de l'utilisateur du back-office
     */
    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('https://auth.assos.utc.fr/logout');
    }
}
