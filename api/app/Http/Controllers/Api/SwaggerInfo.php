<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'TontiTOGO API',
    version: '1.0.0',
    description: 'API RESTful de la plateforme TontiTOGO — Gestion numérique des tontines au Togo avec suivi des cotisations et notifications SMS.',
    contact: new OA\Contact(
        name: 'Support TontiTOGO',
        email: 'support@tontitogo.tg'
    )
)]
#[OA\Server(
    url: 'http://127.0.0.1:8000',
    description: 'Serveur local de développement'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Token Laravel Sanctum. Après connexion, copiez le token et cliquez sur Authorize.'
)]
#[OA\Tag(name: 'Auth Responsable',    description: 'Inscription, connexion et profil du responsable')]
#[OA\Tag(name: 'Auth Agent',          description: 'Connexion, profil et changement de mot de passe de l\'agent')]
#[OA\Tag(name: 'Dashboard',           description: 'KPIs et graphiques du tableau de bord responsable')]
#[OA\Tag(name: 'Agents',              description: 'Gestion des agents collecteurs')]
#[OA\Tag(name: 'Membres',             description: 'Gestion des membres et carnets')]
#[OA\Tag(name: 'Tontines',            description: 'Gestion des tontines et participants')]
#[OA\Tag(name: 'Cotisations',         description: 'Journal des cotisations et exports PDF/Excel')]
#[OA\Tag(name: 'Règlements',          description: 'Clôture de caisse journalière')]
#[OA\Tag(name: 'SMS',                 description: 'Logs Termii et envoi de rappels')]
#[OA\Tag(name: 'Carte GPS',           description: 'Points de collecte géolocalisés Leaflet')]
#[OA\Tag(name: 'Paramètres',          description: 'Configuration SMS et organisation')]
#[OA\Tag(name: 'Agent - Membres',     description: 'Recherche et inscription de membres sur le terrain')]
#[OA\Tag(name: 'Agent - Cotisations', description: 'Encaissement de cotisations sur le terrain')]
class SwaggerInfo
{
    // Fichier dédié aux annotations OpenAPI globales (PHP 8 Attributes).
    // Ne pas instancier.
}
