<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://tontitogo-responsable.vercel.app',
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],

    'allowed_origins_patterns' => [
        // Autorise tous les sous-domaines vercel.app (previews de déploiement)
        '#^https://.*\.vercel\.app$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400,

    // false car on utilise Bearer tokens (pas de cookies de session)
    'supports_credentials' => false,

];
