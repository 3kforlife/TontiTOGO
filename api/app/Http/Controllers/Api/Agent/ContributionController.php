<?php

namespace App\Http\Controllers\Api\Agent;

use App\Enums\ContributionSettlementStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Agent\StoreContributionRequest;
use App\Http\Resources\ContributionResource;
use App\Jobs\SendContributionSmsJob;
use App\Models\Contribution;
use App\Models\TontineParticipant;
use App\Services\ReferenceGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class ContributionController extends ApiController
{
    public function __construct(
        private readonly ReferenceGeneratorService $referenceGenerator
    ) {}

    #[OA\Get(
        path: '/api/agent/contributions',
        summary: 'Historique des cotisations de l\'agent (infinite scroll, 20 par page)',
        tags: ['Agent - Cotisations'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'settlement_status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['pending', 'settled'])),
            new OA\Parameter(name: 'date',              in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'page',              in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Liste paginée des cotisations de l\'agent')]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Contribution::where('user_id', $request->user()->id)
            ->with([
                'tontineParticipant.member:id,firstname,lastname,phone',
                'tontineParticipant.tontine:id,name,frequency',
            ])
            ->orderByDesc('created_at');

        if ($status = $request->query('settlement_status')) {
            $query->where('settlement_status', ContributionSettlementStatus::from($status)->value);
        }

        if ($date = $request->query('date')) {
            $query->filterByPeriod($date);
        }

        $contributions = $query->paginate(20);

        return $this->success([
            'data'         => ContributionResource::collection($contributions->items()),
            'total'        => $contributions->total(),
            'per_page'     => $contributions->perPage(),
            'current_page' => $contributions->currentPage(),
            'last_page'    => $contributions->lastPage(),
            'total_amount' => (float) Contribution::where('user_id', $request->user()->id)
                ->filterByPeriod($request->query('date', today()->toDateString()))
                ->sum('amount'),
        ]);
    }

    #[OA\Post(
        path: '/api/agent/contributions',
        summary: 'Enregistrer une cotisation terrain',
        tags: ['Agent - Cotisations'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['tontine_participant_id', 'amount'],
                properties: [
                    new OA\Property(property: 'tontine_participant_id', type: 'integer', example: 1),
                    new OA\Property(property: 'amount',                 type: 'number',  example: 5000),
                    new OA\Property(property: 'latitude',               type: 'number',  format: 'float', example: 6.1375),
                    new OA\Property(property: 'longitude',              type: 'number',  format: 'float', example: 1.2123),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Cotisation enregistrée + SMS envoyé'),
            new OA\Response(response: 422, description: 'Erreur métier ou validation'),
            new OA\Response(response: 403, description: 'Tontine hors organisation'),
        ]
    )]
    public function store(StoreContributionRequest $request): JsonResponse
    {
        $agent       = $request->user()->load('organization');
        $participant = TontineParticipant::where('id', $request->tontine_participant_id)
            ->active()
            ->with(['tontine', 'member'])
            ->firstOrFail();

        if ($participant->tontine->organization_id !== $agent->organization_id) {
            return $this->error(__('http.tontine_wrong_org'), 403);
        }

        if (! $participant->tontine->isActive()) {
            return $this->error(__('http.tontine_inactive'), 422);
        }

        if ($request->amount < $participant->tontine->minimum_amount) {
            return $this->error(
                "Le montant est inférieur au minimum requis de {$participant->tontine->minimum_amount} FCFA.",
                422
            );
        }

        if ($request->amount < $participant->chosen_amount) {
            return $this->error(
                "Le montant est inférieur au montant choisi par le membre ({$participant->chosen_amount} FCFA).",
                422
            );
        }

        $today = now()->toDateString();
        $existingContribution = Contribution::where('tontine_participant_id', $participant->id)
            ->whereDate('created_at', $today)
            ->first();

        if ($existingContribution) {
            return $this->error(
                'Une cotisation a déjà été enregistrée pour ce membre aujourd\'hui. Une seule cotisation par jour est autorisée.',
                422
            );
        }

        $contribution = DB::transaction(fn() => Contribution::create([
            'tontine_participant_id' => $participant->id,
            'user_id'               => $agent->id,
            'reference'             => $this->referenceGenerator->generateContributionReference(),
            'amount'                => $request->amount,
            'latitude'              => $request->latitude,
            'longitude'             => $request->longitude,
            'settlement_status'     => ContributionSettlementStatus::Pending,
        ]));

        SendContributionSmsJob::dispatch(
            $participant->member->phone,
            $participant->member->full_name,
            (float) $request->amount,
            $contribution->reference,
            $agent->organization?->name ?? 'TontiTOGO',
            $agent->organization_id,
        );

        $contribution->load([
            'tontineParticipant.member:id,firstname,lastname,phone',
            'tontineParticipant.tontine:id,name',
        ]);

        return $this->created(
            new ContributionResource($contribution),
            __('http.contribution_sms_sent')
        );
    }

    #[OA\Get(
        path: '/api/agent/contributions/{id}',
        summary: 'Détail d\'une cotisation de l\'agent',
        tags: ['Agent - Cotisations'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Détail de la cotisation'),
            new OA\Response(response: 404, description: 'Non trouvée'),
        ]
    )]
    public function show(Request $request, int $id): JsonResponse
    {
        $contribution = Contribution::where('user_id', $request->user()->id)
            ->with([
                'tontineParticipant.member',
                'tontineParticipant.tontine',
                'agent:id,firstname,lastname',
            ])
            ->findOrFail($id);

        return $this->success(new ContributionResource($contribution));
    }
}
