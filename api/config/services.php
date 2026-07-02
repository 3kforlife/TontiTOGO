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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloudinary — Stockage média externe
    |--------------------------------------------------------------------------
    */
    'cloudinary' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key'    => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
        // Mettre false UNIQUEMENT en développement local (SSL non vérifié)
        // En production, laisser true ou ne pas définir la variable
        'verify_ssl' => env('CLOUDINARY_VERIFY_SSL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | NGH Corp — Passerelle SMS (Moov / Yas Togo) — Fournisseur principal
    |--------------------------------------------------------------------------
    */
    'nghcorp' => [
        'api_key'    => env('NGHCORP_API_KEY'),
        'api_secret' => env('NGHCORP_API_SECRET'),
        'base_url'   => env('NGHCORP_BASE_URL', 'https://extranet.nghcorp.net'),
        'verify_ssl' => env('NGHCORP_VERIFY_SSL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Termii — Passerelle SMS (conservé en backup)
    |--------------------------------------------------------------------------
    */
    'termii' => [
        'api_key'   => env('TERMII_API_KEY'),
        'sender_id' => env('TERMII_SENDER_ID', 'TontiTOGO'),
        'base_url'  => env('TERMII_BASE_URL', 'https://v3.api.termii.com'),
    ],

];
