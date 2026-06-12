<?php

namespace App\Http\Controllers\Api\Agent;

use App\Enums\UserRole;
use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use App\Rules\PasswordRules;
use App\Services\TermiiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;


class AgentPasswordResetController extends ApiController
{
    /** Durée de validité de l'OTP en minutes */
    private const OTP_TTL_MINUTES = 10;

    /** Préfixe de clé cache pour isoler les OTP agents */
    private const CACHE_PREFIX = 'agent_otp_reset_';

    public function __construct(
        private readonly TermiiService $termii
    ) {}

    // -------------------------------------------------------
    // Étape 1 : Demande de réinitialisation
    // -------------------------------------------------------

    #[OA\Post(
        path: '/api/agent/password/forgot',
        summary: 'Demande de réinitialisation de mot de passe par SMS (OTP)',
        tags: ['Auth Agent'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['phone'],
                properties: [
                    new OA\Property(
                        property: 'phone',
                        type: 'string',
                        example: '22890123456',
                        description: 'Numéro de téléphone togolais de l\'agent'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'SMS envoyé (ou numéro inconnu — même réponse par sécurité)'),
            new OA\Response(response: 422, description: 'Numéro invalide'),
        ]
    )]
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        // Chercher l'agent sans révéler son existence en cas de numéro inconnu
        $agent = User::where('phone', $request->phone)
            ->where('role', UserRole::Agent)
            ->first();

        // Réponse identique même si l'agent n'existe pas (anti-énumération)
        if (! $agent) {
            return $this->success(
                null,
                __('http.otp_sent')
            );
        }

        if (! $agent->isActive()) {
            return $this->error(
                __('http.account_suspended'),
                403
            );
        }

        // Générer l'OTP à 6 chiffres
        $otp       = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $cacheKey  = self::CACHE_PREFIX . $agent->phone;

        // Stocker l'OTP haché dans le cache avec TTL de 10 minutes
        Cache::put($cacheKey, Hash::make($otp), now()->addMinutes(self::OTP_TTL_MINUTES));

        // Envoyer le SMS via Termii
        $message = "TontiTOGO: Votre code de réinitialisation est {$otp}. "
                 . "Valide " . self::OTP_TTL_MINUTES . " minutes. Ne le partagez pas.";

        $this->termii->send($agent->phone, $message);

        return $this->success(
            null,
            __('http.otp_sent')
        );
    }

    // -------------------------------------------------------
    // Étape 2 : Réinitialisation avec l'OTP reçu
    // -------------------------------------------------------

    #[OA\Post(
        path: '/api/agent/password/reset',
        summary: 'Réinitialisation du mot de passe avec le code OTP reçu par SMS',
        tags: ['Auth Agent'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['phone', 'otp', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'phone',                 type: 'string',  example: '22890123456'),
                    new OA\Property(property: 'otp',                   type: 'string',  example: '482916', description: 'Code à 6 chiffres reçu par SMS'),
                    new OA\Property(property: 'password',              type: 'string',  format: 'password', example: 'nouveauMdp123'),
                    new OA\Property(property: 'password_confirmation', type: 'string',  format: 'password', example: 'nouveauMdp123'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Mot de passe réinitialisé avec succès'),
            new OA\Response(response: 422, description: 'Code OTP invalide, expiré ou données incorrectes'),
            new OA\Response(response: 404, description: 'Agent introuvable'),
        ]
    )]
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'phone'    => ['required', 'string'],
            'otp'      => ['required', 'string', 'digits:6'],
            'password' => PasswordRules::creation(),
        ]);

        $agent = User::where('phone', $request->phone)
            ->where('role', UserRole::Agent)
            ->first();

        if (! $agent) {
            return $this->error(__('http.agent_not_found'), 404);
        }

        $cacheKey  = self::CACHE_PREFIX . $agent->phone;
        $hashedOtp = Cache::get($cacheKey);

        // Vérifier que l'OTP existe et n'a pas expiré
        if (! $hashedOtp) {
            return $this->error(
                __('http.otp_expired'),
                422
            );
        }

        // Vérifier que l'OTP correspond
        if (! Hash::check($request->otp, $hashedOtp)) {
            return $this->error(
                __('http.otp_invalid'),
                422
            );
        }

        // OTP valide → mettre à jour le mot de passe
        $agent->update([
            'password'             => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        // Révoquer tous les tokens existants pour forcer une reconnexion
        $agent->tokens()->delete();

        // Supprimer l'OTP du cache immédiatement (usage unique)
        Cache::forget($cacheKey);

        return $this->success(
            null,
            __('http.password_reset_success')
        );
    }
}
