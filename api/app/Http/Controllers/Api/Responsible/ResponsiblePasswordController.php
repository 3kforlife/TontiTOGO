<?php

namespace App\Http\Controllers\Api\Responsible;

use App\Enums\UserRole;
use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use App\Rules\PasswordRules;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;


class ResponsiblePasswordController extends ApiController
{
    // Étape 1 : Envoi du lien de réinitialisation

    #[OA\Post(
        path: '/api/responsible/password/forgot',
        summary: 'Envoi d\'un lien de réinitialisation par email',
        tags: ['Auth Responsable'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'kofi@example.tg'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Lien envoyé (même réponse si email inconnu)'),
            new OA\Response(response: 422, description: 'Email invalide'),
        ]
    )]
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email'    => 'L\'adresse e-mail n\'est pas valide.',
        ]);

        $user = User::where('email', $request->email)
            ->where('role', UserRole::Responsible)
            ->first();

        if (! $user) {
            return $this->success(
                null,
                __('http.otp_sent')
            );
        }

        $status = Password::sendResetLink(['email' => $request->email]);

        if ($status === Password::RESET_LINK_SENT) {
            return $this->success(
                null,
                __('http.password_reset_email_sent')
            );
        }

        return $this->error(
            __('http.password_reset_throttled'),
            429
        );
    }

    // Étape 2 : Réinitialisation avec le token email

    #[OA\Post(
        path: '/api/responsible/password/reset',
        summary: 'Réinitialisation du mot de passe avec le token reçu par email',
        tags: ['Auth Responsable'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['token', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'token',                 type: 'string', description: 'Token reçu dans l\'email'),
                    new OA\Property(property: 'email',                 type: 'string', format: 'email', example: 'kofi@example.tg'),
                    new OA\Property(property: 'password',              type: 'string', format: 'password', example: 'nouveauMdp123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'nouveauMdp123'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Mot de passe réinitialisé'),
            new OA\Response(response: 422, description: 'Token invalide ou expiré'),
        ]
    )]
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => PasswordRules::creation(),
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'      => Hash::make($password),
                    'remember_token'=> Str::random(60),
                ])->save();

                // Révoquer tous les tokens Sanctum existants
                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->success(
                null,
                __('http.password_reset_success')
            );
        }

        throw ValidationException::withMessages([
            'email' => [match ($status) {
                Password::INVALID_TOKEN => 'Le lien de réinitialisation est invalide ou a expiré.',
                Password::INVALID_USER  => 'Aucun compte trouvé pour cette adresse e-mail.',
                default                 => 'Une erreur est survenue. Veuillez réessayer.',
            }],
        ]);
    }
}
