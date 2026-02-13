<?php

return [
    /*
    |---------------------------------------------------------------------------
    | Chemin des clés JWT
    |---------------------------------------------------------------------------
    | Définir les chemins vers les clés publiques et privées pour les algorithmes
    | asymétriques comme RS256.
    |
    */
    'keys' => [
        'public' => env('JWT_PUBLIC_KEY_PATH', storage_path('app/private/keys/public.pem').'.pub'),
        'private' => env('JWT_PRIVATE_KEY_PATH', storage_path('app/private/keys/private.pem')),
    ],

    /*
    |---------------------------------------------------------------------------
    | Algorithme de signature JWT
    |---------------------------------------------------------------------------
    | Indique quel algorithme de signature est utilisé pour générer et vérifier
    | les tokens JWT. Par défaut, nous utilisons RS256.
    |
    */
    'algo' => env('JWT_ALGO', 'RS256'),

    /*
    |---------------------------------------------------------------------------
    | Leeway (marge de temps)
    |---------------------------------------------------------------------------
    | Permet d'ajouter une marge pour les vérifications de temps de validité
    | des tokens (en secondes). Cela permet de gérer d'éventuelles différences
    | de synchronisation de l'horloge entre serveurs.
    |
    */
    'leeway' => env('JWT_LEEWAY', 60),

    /*
    |---------------------------------------------------------------------------
    | Durée de vie du token JWT (en minutes)
    |---------------------------------------------------------------------------
    | Spécifie la durée pendant laquelle un token est valide. La valeur par
    | défaut est de 60 minutes. Si null, le token n'expirera jamais (non recommandé).
    |
    */
    'ttl' => env('JWT_TTL', 60),

    /*
    |---------------------------------------------------------------------------
    | Durée de vie du refresh token
    |---------------------------------------------------------------------------
    | Spécifie la durée pendant laquelle un token peut être rafraîchi.
    |
    */
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),  // 2 semaines

    /*
    |---------------------------------------------------------------------------
    | Liste noire (Blacklist) activée
    |---------------------------------------------------------------------------
    | Si activé, les tokens invalidés seront enregistrés dans une liste noire.
    | Sinon, les tokens ne pourront pas être révoqués.
    |
    */
    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),

    /*
    |---------------------------------------------------------------------------
    | Détection des tokens blacklistés
    |---------------------------------------------------------------------------
    | Afficher une exception lorsqu'un token blacklisté est utilisé.
    |
    */
    'show_black_list_exception' => env('JWT_SHOW_BLACKLIST_EXCEPTION', true),
];
