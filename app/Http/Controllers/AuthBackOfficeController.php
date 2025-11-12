<?php

namespace App\Http\Controllers;

use App\Models\BackOfficeAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use League\OAuth2\Client\Provider\GenericProvider;
use Illuminate\Support\Facades\Log;

class AuthBackOfficeController extends Controller
{
    public GenericProvider $provider;

    public function __construct()
    {
        $this->provider = new GenericProvider([
            'clientId'                => config('services.oauth.client_id'),
            'clientSecret'            => config('services.oauth.client_secret'),
            'redirectUri'             => config('services.oauth.backoffice_redirect_uri', config('app.url') . '/skiutc/auth/back-office/callback'),
            'urlAuthorize'            => config('services.oauth.authorize_url'),
            'urlAccessToken'          => config('services.oauth.access_token_url'),
            'urlResourceOwnerDetails' => config('services.oauth.owner_details_url'),
            'scopes'                  => config('services.oauth.scopes'),
        ]);
    }

    /**
     * Handle the login of a user via OAuth2 for the back-office
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
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
     * Handle the callback of the SiMDE OAuth for the back-office
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
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

            $isAdmin = BackOfficeAdmin::isAdmin($userDetails['email']) ? true : false;

            session(['admin' => $isAdmin]);
            session(['email' => $userDetails['email']]);

            return redirect()->route('filament.back-office.pages.dashboard');
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'authentification: ' . $e->getMessage());
            abort(401, 'Erreur d\'authentification : ' . $e->getMessage());
        }
    }

    /**
     * Logout the user from the back-office
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('https://auth.assos.utc.fr/logout');
    }
}
