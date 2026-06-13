<?php

namespace App\Http\Controllers\Api\Responsible;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;


class EmailVerificationController extends ApiController
{
    #[OA\Post(
        path: '/api/responsible/email/verification-notification',
        summary: 'Renvoi de l\'email de vérification',
        tags: ['Auth Responsable'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Email renvoyé'),
            new OA\Response(response: 204, description: 'Email déjà vérifié'),
        ]
    )]
    public function sendVerificationEmail(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->success(null, __('http.email_already_verified'));
        }

        $request->user()->sendEmailVerificationNotification();

        return $this->success(
            null,
            __('http.email_verification_sent')
        );
    }

    #[OA\Get(
        path: '/api/responsible/email/verify/{id}/{hash}',
        summary: 'Validation du lien de vérification d\'email',
        tags: ['Auth Responsable'],
        parameters: [
            new OA\Parameter(name: 'id',   in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'hash', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Email vérifié avec succès'),
            new OA\Response(response: 403, description: 'Lien invalide ou expiré'),
        ]
    )]
    public function verify(Request $request, $id): JsonResponse
    {
        $user = \App\Models\User::findOrFail($id);

        if (! hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
            return $this->error(__('http.email_verification_invalid'), 403);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->success(null, __('http.email_already_verified'));
        }

        $user->markEmailAsVerified();

        event(new Verified($user));

        return $this->success(
            null,
            __('http.email_verified')
        );
    }
}
