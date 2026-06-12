<?php

namespace App\Http\Controllers\Api\Responsible;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Responsible\StoreAgentRequest;
use App\Http\Requests\Responsible\UpdateAgentRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class AgentManagementController extends ApiController
{
    public function __construct(private readonly CloudinaryService $cloudinary) {}

    #[OA\Get(path: '/api/responsible/agents', summary: 'Liste paginée des agents', tags: ['Agents'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Liste des agents')]
    )]
    public function index(Request $request): JsonResponse
    {
        $agents = User::forOrganization($request->user()->organization_id)
            ->agents()->withCount('contributions')->orderByDesc('created_at')->paginate(6);

        return $this->success([
            'data' => UserResource::collection($agents->items()),
            'total' => $agents->total(), 'per_page' => $agents->perPage(),
            'current_page' => $agents->currentPage(), 'last_page' => $agents->lastPage(),
        ]);
    }

    #[OA\Get(path: '/api/responsible/agents/{id}', summary: 'Détail et stats d\'un agent', tags: ['Agents'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Agent + stats'), new OA\Response(response: 404, description: 'Non trouvé')]
    )]
    public function show(Request $request, int $id): JsonResponse
    {
        $agent = $this->findAgent($request, $id);
        return $this->success([
            'agent' => new UserResource($agent),
            'stats' => [
                'total_contributions' => $agent->contributions()->count(),
                'total_collected'     => (float) $agent->contributions()->sum('amount'),
                'pending_settlement'  => (float) $agent->contributions()->pending()->sum('amount'),
                'last_activity'       => $agent->contributions()->latest()->value('created_at'),
            ],
        ]);
    }

    #[OA\Post(path: '/api/responsible/agents', summary: 'Créer un nouvel agent', tags: ['Agents'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true,
            content: new OA\MediaType(mediaType: 'multipart/form-data',
                schema: new OA\Schema(required: ['firstname','lastname','phone','avatar'],
                    properties: [
                        new OA\Property(property: 'firstname', type: 'string'),
                        new OA\Property(property: 'lastname',  type: 'string'),
                        new OA\Property(property: 'phone',     type: 'string', example: '22890123456'),
                        new OA\Property(property: 'email',     type: 'string', format: 'email'),
                        new OA\Property(property: 'avatar',    type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        responses: [new OA\Response(response: 201, description: 'Agent créé + mot de passe temporaire')]
    )]
    public function store(StoreAgentRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $avatarUrl    = $this->cloudinary->upload($request->file('avatar'), 'tontitogo/agents');
            $tempPassword = Str::random(10);
            $agent = User::create([
                'organization_id' => $request->user()->organization_id,
                'firstname' => $request->firstname, 'lastname' => $request->lastname,
                'email' => $request->email, 'phone' => $request->phone,
                'role' => UserRole::Agent, 'status' => UserStatus::Active,
                'password' => Hash::make($tempPassword), 'must_change_password' => true,
                'avatar_url' => $avatarUrl,
            ]);

            // Ne pas envoyer de SMS — le responsable communique lui-même les identifiants à l'agent.
            // Le mot de passe temporaire est retourné dans la réponse pour affichage immédiat.

            return $this->created([
                'agent'          => new UserResource($agent),
                'credentials'    => [
                    'full_name'      => $agent->full_name,
                    'email'          => $agent->email,
                    'phone'          => $agent->phone,
                    'temp_password'  => $tempPassword,
                ],
            ], 'Agent créé avec succès.');
        });
    }

    #[OA\Put(path: '/api/responsible/agents/{id}', summary: 'Modifier un agent', tags: ['Agents'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Agent mis à jour')]
    )]
    public function update(UpdateAgentRequest $request, int $id): JsonResponse
    {
        $agent = $this->findAgent($request, $id);
        return DB::transaction(function () use ($request, $agent) {
            $data = $request->only(['firstname', 'lastname', 'email', 'phone']);
            if ($request->filled('status')) $data['status'] = UserStatus::from($request->status);
            if ($request->hasFile('avatar')) {
                if ($agent->avatar_url) $this->cloudinary->delete($this->cloudinary->extractPublicId($agent->avatar_url));
                $data['avatar_url'] = $this->cloudinary->upload($request->file('avatar'), 'tontitogo/agents');
            }
            $agent->update($data);
            return $this->success(new UserResource($agent->fresh()), 'Agent mis à jour avec succès.');
        });
    }

    #[OA\Patch(path: '/api/responsible/agents/{id}/toggle-status', summary: 'Suspendre / réactiver un agent', tags: ['Agents'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Statut mis à jour')]
    )]
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $agent     = $this->findAgent($request, $id);
        $newStatus = $agent->status === UserStatus::Active ? UserStatus::Suspended : UserStatus::Active;
        $agent->update(['status' => $newStatus]);
        return $this->success(new UserResource($agent->fresh()),
            "L'agent a été " . ($newStatus === UserStatus::Active ? 'réactivé' : 'suspendu') . ' avec succès.');
    }

    #[OA\Delete(path: '/api/responsible/agents/{id}', summary: 'Supprimer un agent', tags: ['Agents'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Supprimé')]
    )]
    public function destroy(Request $request, int $id): JsonResponse
    {
        $agent = $this->findAgent($request, $id);
        DB::transaction(function () use ($agent) { $agent->tokens()->delete(); $agent->delete(); });
        return $this->noContent();
    }

    private function findAgent(Request $request, int $id): User
    {
        return User::forOrganization($request->user()->organization_id)->agents()->findOrFail($id);
    }
}
