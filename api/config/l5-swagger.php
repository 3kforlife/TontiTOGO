<?php

return [
    'default' => 'default',

    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'TontiTOGO API',
            ],

            'routes' => [
                /*
                 * URL d'accès à l'interface Swagger UI
                 */
                'api' => 'api/documentation',
                
                /*
                 * URL pour le fichier JSON de la documentation
                 */
                'docs' => 'docs',
            ],
            
            /*
             * URL de base pour l'API (force HTTPS)
             */
            'url' => env('L5_SWAGGER_URL', 'https://tontitogo.onrender.com'),

            'paths' => [
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', false),

                'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),

                /*
                 * Nom du fichier JSON généré (servi sur /docs/api-docs.json)
                 */
                'docs_json' => 'api-docs.json',

                'docs_yaml' => 'api-docs.yaml',

                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),

                /*
                 * Répertoires scannés pour les annotations OpenAPI.
                 * On scanne uniquement les Controllers pour la rapidité.
                 */
                'annotations' => [
                    base_path('app/Http/Controllers/Api'),
                ],
            ],
        ],
    ],

    'defaults' => [
        'routes' => [
            /*
             * Route servant le fichier JSON généré
             * → accessible sur http://127.0.0.1:8000/docs/api-docs.json
             */
            'docs' => 'docs',

            'oauth2_callback' => 'api/oauth2-callback',

            'middleware' => [
                'api'            => [],
                'asset'          => [],
                'docs'           => [],
                'oauth2_callback'=> [],
            ],

            'group_options' => [],
        ],

        'paths' => [
            /*
             * Dossier où le fichier api-docs.json sera écrit
             * → storage/api-docs/api-docs.json
             */
            'docs' => storage_path('api-docs'),

            'views' => base_path('resources/views/vendor/l5-swagger'),

            'base' => env('L5_SWAGGER_BASE_PATH', null),

            'excludes' => [],
        ],

        'scanOptions' => [
            'default_processors_configuration' => [],
            'analyser'   => null,
            'analysis'   => null,
            'processors' => [],
            'pattern'    => null,
            'exclude'    => [],
            'open_api_spec_version' => env(
                'L5_SWAGGER_OPEN_API_SPEC_VERSION',
                \L5Swagger\Generator::OPEN_API_DEFAULT_SPEC_VERSION
            ),
        ],

        'securityDefinitions' => [
            'securitySchemes' => [
                /*
                 * Authentification Bearer Token (Laravel Sanctum)
                 * Utilisé sur toutes les routes protégées de TontiTOGO
                 */
                'sanctum' => [
                    'type'        => 'http',
                    'scheme'      => 'bearer',
                    'bearerFormat'=> 'JWT',
                    'description' => 'Entrez votre token Sanctum : Bearer {token}',
                ],
            ],

            'security' => [
                ['sanctum' => []],
            ],
        ],

        /*
         * En développement : régénère la doc à chaque requête.
         * Mettre false en production.
         */
        'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', true),

        'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),

        'proxy' => false,

        'additional_config_url' => null,

        'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', 'alpha'),

        'validator_url' => null,

        'ui' => [
            'display' => [
                'dark_mode'     => env('L5_SWAGGER_UI_DARK_MODE', false),
                'doc_expansion' => env('L5_SWAGGER_UI_DOC_EXPANSION', 'list'),
                'filter'        => env('L5_SWAGGER_UI_FILTERS', true),
            ],
            'authorization' => [
                'persist_authorization' => env('L5_SWAGGER_UI_PERSIST_AUTHORIZATION', true),
                'oauth2' => [
                    'use_pkce_with_authorization_code_grant' => false,
                ],
            ],
        ],

        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'https://tontitogo.onrender.com'),
        ],
    ],
];
