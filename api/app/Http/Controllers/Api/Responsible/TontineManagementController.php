<?php

namespace App\Http\Controllers\Api\Responsible;

use App\Enums\ParticipantStatus;
use App\Enums\TontineStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Responsible\AddParticipantRequest;
use App\Http\Requests\Responsible\StoreTontineRequest;
use App\Http\Requests\Responsible\UpdateTontineRequest;
use App\Http\Resources\TontineResource;
use App\Models\Member;
use App\Models\Tontine;
use App\Models\TontineParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class TontineManagementController extends ApiController
{
    #[OA\Get(
        path: '/api/responsible/tontines',
        summary: 'Liste paginée des tontines',
        tags: ['Tontines'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['active', 'closed'])),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'page',   in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Liste des tontines')]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Tontine::forOrganization($request->user()->organization_id)
            ->withCount(['participants', 'activeParticipants']);

        if ($status = $request->query('status')) {
            $query->where('status', TontineStatus::from($status));
        }

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $tontines = $query->orderByDesc('created_at')->paginate(6);

        return $this->success([
            'data'         => TontineResource::collection($tontines->items()),
            'total'        => $tontines->total(),
            'per_page'     => $tontines->perPage(),
            'current_page' => $tontines->currentPage(),
            'last_page'    => $tontines->lastPage(),
        ]);
    }

    #[OA\Get(
        path: '/api/responsible/tontines/{id}',
        summary: 'Détail d\'une tontine avec ses participants',
        tags: ['Tontines'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Tontine + stats + participants'),
            new OA\Response(response: 404, description: 'Non trouvée'),
        ]
    )]
    public function show(Request $request, int $id): JsonResponse
    {
        $tontine = $this->findTontine($request, $id);
        $tontine->load([
            'participants.member:id,firstname,lastname,phone,member_code',
            'participants' => fn($q) => $q->withSum('contributions', 'amount'),
        ]);
        $tontine->loadCount(['participants', 'activeParticipants']);

        $totalCollected = 0;
        $pendingSettlement = 0;

        try {
            $totalCollected = (float) $tontine->contributions()->sum('amount');
        } catch (\Exception $e) {
            $totalCollected = 0;
        }

        try {
            $pendingSettlement = (float) $tontine->contributions()->where('settlement_status', 'pending')->sum('amount');
        } catch (\Exception $e) {
            $pendingSettlement = 0;
        }

        $stats = [
            'total_collected'    => $totalCollected,
            'active_members'     => $tontine->activeParticipants()->count(),
            'pending_settlement' => $pendingSettlement,
        ];

        return $this->success([
            'tontine' => new TontineResource($tontine),
            'stats'   => $stats,
        ]);
    }

    #[OA\Post(
        path: '/api/responsible/tontines',
        summary: 'Créer une tontine',
        tags: ['Tontines'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'minimum_amount', 'frequency', 'start_date'],
                properties: [
                    new OA\Property(property: 'name',           type: 'string',  example: 'Tontine Mensuelle'),
                    new OA\Property(property: 'minimum_amount', type: 'number',  example: 5000),
                    new OA\Property(property: 'frequency',      type: 'string',  enum: ['daily', 'weekly', 'monthly'], example: 'monthly'),
                    new OA\Property(property: 'start_date',     type: 'string',  format: 'date', example: '2025-01-01'),
                    new OA\Property(property: 'end_date',       type: 'string',  format: 'date', example: '2025-12-31'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Tontine créée'),
            new OA\Response(response: 422, description: 'Erreurs de validation'),
        ]
    )]
    public function store(StoreTontineRequest $request): JsonResponse
    {
        $tontine = Tontine::create([
            'organization_id' => $request->user()->organization_id,
            'name'            => $request->name,
            'minimum_amount'  => $request->minimum_amount,
            'frequency'       => $request->frequency,
            'start_date'      => $request->start_date,
            'end_date'        => $request->end_date,
            'status'          => TontineStatus::Active,
        ]);

        return $this->created(new TontineResource($tontine), 'Tontine créée avec succès.');
    }

    #[OA\Put(
        path: '/api/responsible/tontines/{id}',
        summary: 'Modifier une tontine',
        tags: ['Tontines'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Tontine mise à jour')]
    )]
    public function update(UpdateTontineRequest $request, int $id): JsonResponse
    {
        $tontine = $this->findTontine($request, $id);
        $tontine->update($request->only([
            'name', 'minimum_amount', 'frequency',
            'start_date', 'end_date', 'status',
        ]));

        return $this->success(
            new TontineResource($tontine->fresh()),
            'Tontine mise à jour avec succès.'
        );
    }

    #[OA\Delete(
        path: '/api/responsible/tontines/{id}',
        summary: 'Supprimer une tontine',
        tags: ['Tontines'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Supprimée')]
    )]
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->findTontine($request, $id)->delete();

        return $this->noContent();
    }

    #[OA\Post(
        path: '/api/responsible/tontines/{tontine}/participants',
        summary: 'Ajouter un participant à une tontine',
        tags: ['Tontines'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'tontine', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['member_id', 'chosen_amount'],
                properties: [
                    new OA\Property(property: 'member_id',      type: 'integer', example: 1),
                    new OA\Property(property: 'chosen_amount',  type: 'number',  example: 5000),
                    new OA\Property(property: 'joined_at',      type: 'string',  format: 'date', example: '2025-01-01'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Participant ajouté'),
            new OA\Response(response: 422, description: 'Erreur métier ou validation'),
        ]
    )]
    public function addParticipant(AddParticipantRequest $request, int $tontine): JsonResponse
    {
        $tontine = $this->findTontine($request, $tontine);

        if (! $tontine->isActive()) {
            return $this->error(__('http.participant_add_inactive'), 422);
        }

        $member = Member::forOrganization($request->user()->organization_id)
            ->active()
            ->findOrFail($request->member_id);

        $alreadyExists = TontineParticipant::where('tontine_id', $tontine->id)
            ->where('member_id', $member->id)
            ->exists();

        if ($alreadyExists) {
            return $this->error(__('http.participant_exists'), 422);
        }

        if ($request->chosen_amount < $tontine->minimum_amount) {
            return $this->error(
                "Le montant choisi ({$request->chosen_amount} FCFA) est inférieur au minimum ({$tontine->minimum_amount} FCFA).",
                422
            );
        }

        $participant = DB::transaction(fn() => TontineParticipant::create([
            'tontine_id'    => $tontine->id,
            'member_id'     => $member->id,
            'chosen_amount' => $request->chosen_amount,
            'joined_at'     => $request->joined_at,
            'status'        => ParticipantStatus::Active,
        ]));

        $participant->load('member:id,firstname,lastname,phone');

        return $this->created([
            'participant_id' => $participant->id,
            'member'         => [
                'id'        => $member->id,
                'full_name' => $member->full_name,
                'phone'     => $member->phone,
            ],
            'tontine'        => $tontine->name,
            'chosen_amount'  => (float) $participant->chosen_amount,
            'joined_at'      => $participant->joined_at?->format('d/m/Y'),
        ], 'Participant ajouté à la tontine avec succès.');
    }

    #[OA\Delete(
        path: '/api/responsible/tontines/{tontine}/participants/{participant}',
        summary: 'Retirer un participant d\'une tontine',
        tags: ['Tontines'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'tontine',     in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'participant', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 204, description: 'Participant retiré')]
    )]
    public function removeParticipant(Request $request, int $tontineId, int $participantId): JsonResponse
    {
        $tontine = $this->findTontine($request, $tontineId);

        TontineParticipant::where('id', $participantId)
            ->where('tontine_id', $tontine->id)
            ->firstOrFail()
            ->update(['status' => ParticipantStatus::Cancelled]);

        return $this->noContent();
    }

    // Helper privé

    private function findTontine(Request $request, int $id): Tontine
    {
        return Tontine::forOrganization($request->user()->organization_id)->findOrFail($id);
    }
}
