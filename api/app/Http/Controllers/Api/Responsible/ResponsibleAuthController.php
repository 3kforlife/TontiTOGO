<?php

namespace App\Http\Controllers\Api\Responsible;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Responsible\RegisterResponsibleRequest;
use App\Http\Resources\UserResource;
use App\Models\Organization;
use App\Models\User;
use App\Rules\PasswordRules;
use App\Rules\TogoPhone;
use App\Rules\ValidationRules;
use App\Services\CloudinaryService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class ResponsibleAuthController extends ApiController
{
    public function __construct(
        private readonly CloudinaryService $cloudinary
    ) {}

    #[OA\Post(
        path: '/api/responsible/register',
        summary: 'Inscription d\'un nouveau responsable',
        tags: ['Auth Responsable'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['organization_name', 'firstname', 'lastname', 'phone', 'password', 'password_confirmation'],
                    properties: [
                        new OA\Property(property: 'organization_name', type: 'string', example: 'Tontine Solidarité'),
                        new OA\Property(property: 'firstname',         type: 'string', example: 'Kofi'),
                        new OA\Property(property: 'lastname',          type: 'string', example: 'Mensah'),
                        new OA\Property(property: 'phone',             type: 'string', example: '22890123456'),
                        new OA\Property(property: 'email',             type: 'string', format: 'email', example: 'kofi@example.tg'),
                        new OA\Property(property: 'password',          type: 'string', format: 'password', example: 'secret123'),
                        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'secret123'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Compte créé avec succès'),
            new OA\Response(response: 422, description: 'Erreurs de validation'),
        ]
    )]
    public function register(RegisterResponsibleRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $organization = Organization::create(['name' => $request->organization_name]);

            $responsible = User::create([
                'organization_id'      => $organization->id,
                'firstname'            => $request->firstname,
                'lastname'             => $request->lastname,
                'email'                => $request->email,
                'phone'                => $request->phone,
                'role'                 => UserRole::Responsible,
                'status'               => UserStatus::Active,
                'password'             => Hash::make($request->password),
                'must_change_password' => false,
            ]);

            $token = $responsible->createToken('responsible-token', ['role:responsible'])->plainTextToken;

            // Déclencher l'envoi de l'email de vérification
            event(new Registered($responsible));

            return $this->created([
                'user'  => new UserResource($responsible->load('organization')),
                'token' => $token,
                'email_verification_required' => true,
            ], __('http.register_success'));
        });
    }

    #[OA\Post(
        path: '/api/responsible/login',
        summary: 'Connexion du responsable',
        tags: ['Auth Responsable'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['login', 'password'],
                properties: [
                    new OA\Property(property: 'login',    type: 'string', example: 'kofi@example.tg', description: 'Email ou téléphone'),
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
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where(function ($q) use ($request) {
                $q->where('email', $request->login)
                  ->orWhere('phone', $request->login);
            })
            ->where('role', UserRole::Responsible)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Identifiant ou mot de passe incorrect.'],
            ]);
        }

        if (! $user->isActive()) {
            return $this->error(__('http.account_suspended_admin'), 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('responsible-token', ['role:responsible'])->plainTextToken;

        return $this->success([
            'user'  => new UserResource($user->load('organization')),
            'token' => $token,
        ], __('http.login_success'));
    }

    #[OA\Post(
        path: '/api/responsible/logout',
        summary: 'Déconnexion du responsable',
        tags: ['Auth Responsable'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Déconnecté')]
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, __('http.logout_success'));
    }

    #[OA\Get(
        path: '/api/responsible/me',
        summary: 'Profil du responsable authentifié',
        tags: ['Auth Responsable'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Profil retourné')]
    )]
    public function me(Request $request): JsonResponse
    {
        return $this->success(
            new UserResource($request->user()->load('organization'))
        );
    }

    #[OA\Put(
        path: '/api/responsible/profile',
        summary: 'Mettre à jour le profil du responsable',
        tags: ['Auth Responsable'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'firstname',        type: 'string'),
                        new OA\Property(property: 'lastname',         type: 'string'),
                        new OA\Property(property: 'email',            type: 'string', format: 'email'),
                        new OA\Property(property: 'phone',            type: 'string', example: '22890123456'),
                        new OA\Property(property: 'current_password', type: 'string', format: 'password'),
                        new OA\Property(property: 'password',         type: 'string', format: 'password'),
                        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                        new OA\Property(property: 'avatar',           type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Profil mis à jour'),
            new OA\Response(response: 422, description: 'Erreurs de validation'),
        ]
    )]
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'firstname'        => array_merge(ValidationRules::name(required: false), ['sometimes']),
            'lastname'         => array_merge(ValidationRules::name(required: false), ['sometimes']),
            'email'            => ['nullable', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'phone'            => ['sometimes', 'required', 'string', new TogoPhone(), "unique:users,phone,{$user->id}"],
            'current_password' => ['sometimes', 'required', 'string', 'current_password'],
            'password'         => array_merge(['sometimes'], PasswordRules::update()),
            'avatar'           => ['sometimes', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $data = $request->only(['firstname', 'lastname', 'email']);

        if ($request->filled('phone')) {
            $data['phone'] = TogoPhone::normalize($request->phone) ?? $request->phone;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar_url) {
                $this->cloudinary->delete($this->cloudinary->extractPublicId($user->avatar_url));
            }
            $data['avatar_url'] = $this->cloudinary->upload(
                $request->file('avatar'), 'tontitogo/responsibles'
            );
        }

        $user->update($data);

        return $this->success(
            new UserResource($user->fresh()->load('organization')),
            __('http.profile_updated')
        );
    }

    #[OA\Delete(
        path: '/api/responsible/account',
        summary: 'Supprimer définitivement son compte (droit à l\'oubli)',
        tags: ['Auth Responsable'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['password'],
                properties: [
                    new OA\Property(property: 'password', type: 'string', format: 'password', description: 'Mot de passe actuel pour confirmation'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Compte supprimé définitivement'),
            new OA\Response(response: 422, description: 'Mot de passe incorrect'),
        ]
    )]
    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($user) {
            $user->tokens()->delete();

            if ($user->avatar_url) {
                $this->cloudinary->delete($this->cloudinary->extractPublicId($user->avatar_url));
            }

            $user->organization->delete();

            return $this->success(null, __('http.account_deleted'));
        });
    }
}
