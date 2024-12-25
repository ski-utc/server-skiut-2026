<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'oauth' => [
        'client_id' => trim(file_get_contents(env('OAUTH_CLIENT_ID_PATH', storage_path('app/public/OAuthClientId.txt')))),
        'client_secret' => trim(file_get_contents(env('OAUTH_CLIENT_SECRET_PATH', storage_path('app/private/OAuthClientSecret.txt')))),
        'redirect_uri' => env('OAUTH_REDIRECT_URI', 'http://172.25.22.170:8000/skiutc/auth/callback'),
        'scopes' => env('OAUTH_SCOPES', 'users-infos read-memberships read-assos'),
        'authorize_url' => env('OAUTH_AUTHORIZE_URL', 'https://auth.assos.utc.fr/oauth/authorize'),
        'access_token_url' => env('OAUTH_ACCESS_TOKEN_URL', 'https://auth.assos.utc.fr/oauth/token'),
        'owner_details_url' => env('OAUTH_RESOURCE_OWNER_DETAILS', 'https://auth.assos.utc.fr/api/user'),
        'logout_url' => env('OAUTH_LOGOUT_URL', 'https://auth.assos.utc.fr/logout'),
    ],

    'crypt' => [
        'public' => trim(file_get_contents(storage_path(env('JWT_PUBLIC_KEY_PATH', 'app/public/public.pem')))),
        'private' => trim(file_get_contents(storage_path(env('JWT_PRIVATE_KEY_PATH', 'app/private/private.pem')))),
    ],

/*
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
*/
];
