<?php

namespace App\Http\Controllers\Api\Agent;

use App\Enums\MemberStatus;
use App\Enums\ParticipantStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Agent\EnrollMemberInTontineRequest;
use App\Http\Requests\Agent\StoreMobileMemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use App\Models\Tontine;
use App\Models\TontineParticipant;
use App\Services\ReferenceGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class MobileMemberController extends ApiController
{
    public function __construct(
        private readonly ReferenceGeneratorService $referenceGenerator
    ) {}

    #[OA\Get(
        path: '/api/agent/members/search',
        summary: 'Recherche d\'un membre sur le terrain',
        tags: ['Agent - Membres'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'query', in: 'query', required: true, schema: new OA\Schema(type: 'string', minLength: 2)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des membres correspondants (max 10)'),
            new OA\Response(response: 422, description: 'Terme de recherche invalide'),
        ]
    )]
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => ['required', 'string', 'min:2'],
        ]);

        $members = Member::forOrganization($request->user()->organization_id)
            ->active()
            ->search($request->query('query'))
            ->limit(10)
            ->get();

        return $this->success(MemberResource::collection($members));
    }

    #[OA\Get(
        path: '/api/agent/members/{id}/tontines',
        summary: 'Tontines actives d\'un membre (pour l\'écran d\'encaissement)',
        tags: ['Agent - Membres'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Membre + participations actives avec montants choisis'),
            new OA\Response(response: 404, description: 'Membre non trouvé'),
        ]
    )]
    public function tontines(Request $request, int $memberId): JsonResponse
    {
        $member = Member::forOrganization($request->user()->organization_id)
            ->active()
            ->findOrFail($memberId);

        $participations = TontineParticipant::where('member_id', $member->id)
            ->active()
            ->with(['tontine' => fn($q) => $q->active()])
            ->get()
            ->filter(fn($p) => $p->tontine !== null);

        $result = $participations->map(fn($p) => [
            'participant_id' => $p->id,
            'tontine'        => [
                'id'              => $p->tontine->id,
                'name'            => $p->tontine->name,
                'frequency'       => $p->tontine->frequency->value,
                'frequency_label' => $p->tontine->frequency->label(),
            ],
            'chosen_amount'  => (float) $p->chosen_amount,
            'minimum_amount' => (float) $p->tontine->minimum_amount,
        ])->values();

        return $this->success([
            'member'         => new MemberResource($member),
            'participations' => $result,
        ]);
    }

    #[OA\Post(
        path: '/api/agent/members',
        summary: 'Enregistrer un nouveau membre sur le terrain',
        tags: ['Agent - Membres'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['notebook_number', 'firstname', 'lastname', 'phone', 'gender'],
                properties: [
                    new OA\Property(property: 'notebook_number', type: 'string'),
                    new OA\Property(property: 'firstname',       type: 'string'),
                    new OA\Property(property: 'lastname',        type: 'string'),
                    new OA\Property(property: 'phone',           type: 'string', example: '22890123456'),
                    new OA\Property(property: 'gender',          type: 'string', enum: ['M', 'F']),
                    new OA\Property(property: 'address',         type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Membre enregistré'),
            new OA\Response(response: 422, description: 'Erreurs de validation'),
        ]
    )]
    public function store(StoreMobileMemberRequest $request): JsonResponse
    {
        $agent  = $request->user();
        $member = DB::transaction(fn() => Member::create([
            'organization_id'     => $agent->organization_id,
            'member_code'         => $this->referenceGenerator->generateMemberCode(),
            'notebook_number'     => $request->notebook_number,
            'firstname'           => $request->firstname,
            'lastname'            => $request->lastname,
            'phone'               => $request->phone,
            'gender'              => $request->gender,
            'address'             => $request->address,
            'status'              => MemberStatus::Active,
            'created_by_agent_id' => $agent->id,
        ]));

        return $this->created(new MemberResource($member), 'Membre enregistré avec succès.');
    }

    #[OA\Post(
        path: '/api/agent/members/{id}/enroll',
        summary: 'Inscrire un membre à une tontine depuis le terrain',
        description: 'L\'agent inscrit instantanément un membre à une tontine. La date d\'adhésion est forcée à aujourd\'hui par le serveur.',
        tags: ['Agent - Membres'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), description: 'ID du membre'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['tontine_id', 'chosen_amount'],
                properties: [
                    new OA\Property(property: 'tontine_id',    type: 'integer', example: 1),
                    new OA\Property(property: 'chosen_amount', type: 'number',  example: 500, description: 'Montant choisi >= minimum de la tontine'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Membre inscrit à la tontine avec succès'),
            new OA\Response(response: 403, description: 'Tontine hors de l\'organisation'),
            new OA\Response(response: 422, description: 'Membre déjà inscrit, montant insuffisant ou tontine inactive'),
        ]
    )]
    public function enroll(EnrollMemberInTontineRequest $request, int $memberId): JsonResponse
    {
        $agent = $request->user();

        // Vérifier que le membre appartient à l'organisation de l'agent
        $member = Member::forOrganization($agent->organization_id)
            ->active()
            ->findOrFail($memberId);

        // Vérifier que la tontine appartient à l'organisation de l'agent
        $tontine = Tontine::where('id', $request->tontine_id)
            ->where('organization_id', $agent->organization_id)
            ->first();

        if (! $tontine) {
            return $this->error(__('http.tontine_wrong_org'), 403);
        }

        if (! $tontine->isActive()) {
            return $this->error(__('http.tontine_inactive'), 422);
        }

        // Vérifier que le membre n'est pas déjà inscrit à cette tontine
        $alreadyEnrolled = TontineParticipant::where('tontine_id', $tontine->id)
            ->where('member_id', $member->id)
            ->exists();

        if ($alreadyEnrolled) {
            return $this->error(__('http.participant_exists'), 422);
        }

        // Vérifier que le montant choisi est >= au minimum de la tontine
        if ($request->chosen_amount < $tontine->minimum_amount) {
            return $this->error(
                __('http.chosen_below_minimum', [
                    'chosen'  => number_format($request->chosen_amount, 0, ',', ' '),
                    'minimum' => number_format($tontine->minimum_amount, 0, ',', ' '),
                ]),
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

        return $this->created([
            'participant_id' => $participant->id,
            'member'         => [
                'id'        => $member->id,
                'full_name' => $member->full_name,
                'phone'     => $member->phone,
            ],
            'tontine'        => [
                'id'              => $tontine->id,
                'name'            => $tontine->name,
                'frequency_label' => $tontine->frequency->label(),
                'minimum_amount'  => (float) $tontine->minimum_amount,
            ],
            'chosen_amount'  => (float) $participant->chosen_amount,
            'joined_at'      => $participant->joined_at?->format('d/m/Y'),
        ], 'Membre inscrit à la tontine avec succès.');
    }

    #[OA\Get(
        path: '/api/agent/dashboard',
        summary: 'Tableau de bord agent : stats du jour + derniers encaissements',
        tags: ['Auth Agent'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Stats + 5 dernières cotisations')]
    )]
    public function agentDashboard(Request $request): JsonResponse
    {
        $agent = $request->user();

        $stats = [
            'today_amount'       => (float) $agent->contributions()->collectedToday()->sum('amount'),
            'today_count'        => $agent->contributions()->collectedToday()->count(),
            'week_amount'        => (float) $agent->contributions()->collectedThisWeek()->sum('amount'),
            'month_amount'       => (float) $agent->contributions()->collectedThisMonth()->sum('amount'),
            'pending_amount'     => (float) $agent->contributions()->pending()->sum('amount'),
            'members_registered' => Member::where('created_by_agent_id', $agent->id)->count(),
        ];

        $recentContributions = $agent->contributions()
            ->with([
                'tontineParticipant.member:id,firstname,lastname',
                'tontineParticipant.tontine:id,name',
            ])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($c) => [
                'reference'  => $c->reference,
                'amount'     => (float) $c->amount,
                'member'     => $c->tontineParticipant?->member?->full_name,
                'tontine'    => $c->tontineParticipant?->tontine?->name,
                'created_at' => $c->created_at?->format('d/m/Y H:i'),
            ]);

        return $this->success([
            'stats'                => $stats,
            'recent_contributions' => $recentContributions,
        ]);
    }
}
