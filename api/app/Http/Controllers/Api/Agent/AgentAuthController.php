<?php

namespace App\Http\Controllers\Api\Agent;

use App\Enums\UserRole;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Agent\AgentLoginRequest;
use App\Http\Requests\Agent\ChangePasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\Contribution;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AgentAuthController extends ApiController
{
    #[OA\Post(
        path: '/api/agent/login',
        summary: 'Connexion de l\'agent par numéro de téléphone',
        tags: ['Auth Agent'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['phone', 'password'],
                properties: [
                    new OA\Property(property: 'phone',    type: 'string', example: '22890123456'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Connexion réussie — retourne user + token'),
            new OA\Response(response: 422, description: 'Identifiants incorrects'),
            new OA\Response(response: 403, description: 'Compte suspendu'),
        ]
    )]
    public function login(AgentLoginRequest $request): JsonResponse
    {
        $user = User::where('phone', $request->phone)
            ->where('role', UserRole::Agent)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['Numéro de téléphone ou mot de passe incorrect.'],
            ]);
        }

        if (! $user->isActive()) {
            return $this->error(__('http.account_suspended'), 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('agent-mobile-token', ['role:agent'])->plainTextToken;

        return $this->success([
            'user'                 => new UserResource($user->load('organization')),
            'token'                => $token,
            'must_change_password' => $user->must_change_password,
        ], __('http.login_success'));
    }

    #[OA\Post(
        path: '/api/agent/logout',
        summary: 'Déconnexion de l\'agent',
        tags: ['Auth Agent'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Déconnecté')]
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, __('http.logout_success'));
    }

    #[OA\Get(
        path: '/api/agent/me',
        summary: 'Profil de l\'agent connecté + stats du jour',
        tags: ['Auth Agent'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Profil + stats du jour')]
    )]
    public function me(Request $request): JsonResponse
    {
        $agent = $request->user()->load('organization:id,name');

        return $this->success([
            'user' => new UserResource($agent),
            'today_stats' => [
                'collected_today' => (float) $agent->contributions()->collectedToday()->sum('amount'),
                'count_today'     => $agent->contributions()->collectedToday()->count(),
            ],
        ]);
    }

    #[OA\Post(
        path: '/api/agent/password/change',
        summary: 'Changement de mot de passe obligatoire à la première connexion',
        tags: ['Auth Agent'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'password',              type: 'string', format: 'password', example: 'newSecret123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'newSecret123'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Mot de passe changé avec succès'),
            new OA\Response(response: 422, description: 'Erreurs de validation'),
        ]
    )]
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $agent = $request->user();

        $agent->update([
            'password'             => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        $currentTokenId = $request->user()->currentAccessToken()->id;
        $agent->tokens()->where('id', '!=', $currentTokenId)->delete();

        return $this->success(null, __('http.password_changed'));
    }
}
