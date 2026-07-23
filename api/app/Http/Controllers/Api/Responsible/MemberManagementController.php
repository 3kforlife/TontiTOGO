<?php

namespace App\Http\Controllers\Api\Responsible;

use App\Enums\MemberStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Responsible\StoreMemberRequest;
use App\Http\Requests\Responsible\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use App\Services\ReferenceGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class MemberManagementController extends ApiController
{
    public function __construct(private readonly ReferenceGeneratorService $referenceGenerator) {}

    #[OA\Get(path: '/api/responsible/members', summary: 'Liste paginée des membres', tags: ['Membres'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['active','suspended'])),
            new OA\Parameter(name: 'page',   in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Liste des membres')]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Member::forOrganization($request->user()->organization_id)
            ->with('createdByAgent:id,firstname,lastname');
        if ($search = $request->query('search')) $query->search($search);
        if ($status = $request->query('status'))  $query->where('status', MemberStatus::from($status)->value);
        $members = $query->orderByDesc('created_at')->paginate(4);
        return $this->success([
            'data' => MemberResource::collection($members->items()),
            'total' => $members->total(), 'per_page' => $members->perPage(),
            'current_page' => $members->currentPage(), 'last_page' => $members->lastPage(),
        ]);
    }

    #[OA\Get(path: '/api/responsible/members/{id}', summary: 'Détail d\'un membre', tags: ['Membres'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Fiche membre + participations')]
    )]
    public function show(Request $request, int $id): JsonResponse
    {
        $member = $this->findMember($request, $id);
        $member->load(['tontineParticipations.tontine:id,name,frequency,minimum_amount,status',
            'tontineParticipations.contributions', 'createdByAgent:id,firstname,lastname']);
        return $this->success(new MemberResource($member));
    }

    #[OA\Post(path: '/api/responsible/members', summary: 'Créer un membre', tags: ['Membres'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true,
            content: new OA\JsonContent(required: ['notebook_number','firstname','lastname','phone','gender'],
                properties: [
                    new OA\Property(property: 'notebook_number', type: 'string'),
                    new OA\Property(property: 'firstname',       type: 'string'),
                    new OA\Property(property: 'lastname',        type: 'string'),
                    new OA\Property(property: 'phone',           type: 'string', example: '22890123456'),
                    new OA\Property(property: 'gender',          type: 'string', enum: ['M','F']),
                    new OA\Property(property: 'address',         type: 'string'),
                ]
            )
        ),
        responses: [new OA\Response(response: 201, description: 'Membre créé')]
    )]
    public function store(StoreMemberRequest $request): JsonResponse
    {
        $member = DB::transaction(fn() => Member::create([
            'organization_id' => $request->user()->organization_id,
            'member_code'     => $this->referenceGenerator->generateMemberCode(),
            'notebook_number' => $request->notebook_number,
            'firstname' => $request->firstname, 'lastname' => $request->lastname,
            'phone' => $request->phone, 'gender' => $request->gender,
            'address' => $request->address, 'status' => MemberStatus::Active,
            'created_by_agent_id' => null,
        ]));
        return $this->created(new MemberResource($member), 'Membre enregistré avec succès.');
    }

    #[OA\Put(path: '/api/responsible/members/{id}', summary: 'Modifier un membre', tags: ['Membres'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Membre mis à jour')]
    )]
    public function update(UpdateMemberRequest $request, int $id): JsonResponse
    {
        $member = $this->findMember($request, $id);
        $member->update($request->only(['notebook_number','firstname','lastname','phone','gender','address','status']));
        return $this->success(new MemberResource($member->fresh()), 'Membre mis à jour avec succès.');
    }

    #[OA\Patch(path: '/api/responsible/members/{id}/toggle-status', summary: 'Suspendre / réactiver un membre', tags: ['Membres'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Statut mis à jour')]
    )]
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $member = $this->findMember($request, $id);
        $newStatus = $member->status === MemberStatus::Active ? MemberStatus::Suspended : MemberStatus::Active;
        $member->update(['status' => $newStatus]);
        return $this->success(new MemberResource($member->fresh()),
            "Le membre a été " . ($newStatus === MemberStatus::Active ? 'réactivé' : 'suspendu') . ' avec succès.');
    }

    #[OA\Delete(path: '/api/responsible/members/{id}', summary: 'Supprimer un membre', tags: ['Membres'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Supprimé')]
    )]
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->findMember($request, $id)->delete();
        return $this->noContent();
    }

    private function findMember(Request $request, int $id): Member
    {
        return Member::forOrganization($request->user()->organization_id)->findOrFail($id);
    }
}
