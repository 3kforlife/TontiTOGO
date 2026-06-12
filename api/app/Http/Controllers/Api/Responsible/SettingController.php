<?php

namespace App\Http\Controllers\Api\Responsible;

use App\Http\Controllers\Api\ApiController;
use App\Models\Setting;
use App\Rules\ValidationRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SettingController extends ApiController
{
    
    private const ALLOWED_KEYS = [
        'sms_template_confirmation', 
        'sms_template_reminder',     
        'sms_reminder_time',         
        'organization_name',         
    ];

    #[OA\Get(
        path: '/api/responsible/settings',
        summary: 'Récupérer tous les paramètres de l\'organisation',
        tags: ['Paramètres'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Map clé → valeur des paramètres')]
    )]
    public function index(Request $request): JsonResponse
    {
        $orgId = $request->user()->organization_id;

        $settings = Setting::where('organization_id', $orgId)
            ->whereIn('key', self::ALLOWED_KEYS)
            ->get()
            ->pluck('value', 'key');

        // Fournir les valeurs par défaut pour les clés manquantes
        $defaults = [
            'sms_template_confirmation' => 'Bonjour {nom}, votre cotisation de {montant} FCFA pour la tontine {tontine} (Réf: {reference}) a bien été enregistrée.',
            'sms_template_reminder'     => 'Bonjour {nom}, nous vous rappelons que votre cotisation pour la tontine {tontine} est attendue aujourd\'hui.',
            'sms_reminder_time'         => '17:30',
            'organization_name'         => $request->user()->organization?->name,
        ];

        $result = collect($defaults)->map(function ($default, $key) use ($settings) {
            return $settings->get($key, $default);
        });

        return $this->success($result);
    }

    #[OA\Put(
        path: '/api/responsible/settings',
        summary: 'Mettre à jour un ou plusieurs paramètres de l\'organisation',
        tags: ['Paramètres'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'sms_template_confirmation', type: 'string', example: 'Bonjour {nom}, votre cotisation de {montant} FCFA a bien été enregistrée.'),
                    new OA\Property(property: 'sms_template_reminder',     type: 'string', example: 'Bonjour {nom}, votre cotisation pour {tontine} est attendue aujourd\'hui.'),
                    new OA\Property(property: 'sms_reminder_time',         type: 'string', example: '17:30'),
                    new OA\Property(property: 'organization_name',         type: 'string', example: 'Tontine Solidarité'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Paramètres mis à jour'),
            new OA\Response(response: 422, description: 'Erreurs de validation'),
        ]
    )]
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'sms_template_confirmation' => ['sometimes', 'string', 'max:500'],
            'sms_template_reminder'     => ['sometimes', 'string', 'max:500'],
            'sms_reminder_time'         => ['sometimes', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'organization_name'         => array_merge(['sometimes'], ValidationRules::organizationName()),
        ]);

        $orgId = $request->user()->organization_id;
        $updated = [];

        foreach (self::ALLOWED_KEYS as $key) {
            if ($request->has($key)) {
                Setting::set($orgId, $key, $request->input($key));
                $updated[$key] = $request->input($key);

                // Synchroniser le nom de l'organisation dans sa propre table
                if ($key === 'organization_name') {
                    $request->user()->organization->update(['name' => $request->input($key)]);
                }
            }
        }

        return $this->success($updated, __('http.settings_updated'));
    }
}
