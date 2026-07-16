<?php

namespace App\Http\Controllers\Api\Responsible;

use App\Enums\ContributionSettlementStatus;
use App\Enums\SettlementStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Responsible\ValidateSettlementRequest;
use App\Http\Resources\DailySettlementResource;
use App\Http\Resources\UserResource;
use App\Jobs\SendSettlementAlertSmsJob;
use App\Models\Contribution;
use App\Models\DailySettlement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class SettlementController extends ApiController
{
    #[OA\Get(
        path: '/api/responsible/settlements',
        summary: 'Historique des règlements journaliers',
        tags: ['Règlements'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'agent_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'status',   in: 'query', schema: new OA\Schema(type: 'string', enum: ['validated', 'discrepancy'])),
            new OA\Parameter(name: 'page',     in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Liste paginée des règlements')]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = DailySettlement::where('organization_id', $request->user()->organization_id)
            ->with([
                'agent:id,firstname,lastname,avatar_url',
                'validatedByResponsible:id,firstname,lastname',
            ])
            ->orderByDesc('date_settled');

        if ($agentId = $request->query('agent_id')) {
            $query->forAgent($agentId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', SettlementStatus::from($status)->value);
        }

        $settlements = $query->paginate(6);

        return $this->success([
            'data'         => DailySettlementResource::collection($settlements->items()),
            'total'        => $settlements->total(),
            'per_page'     => $settlements->perPage(),
            'current_page' => $settlements->currentPage(),
            'last_page'    => $settlements->lastPage(),
        ]);
    }

    #[OA\Post(
        path: '/api/responsible/settlements/validate',
        summary: 'Valider le versement journalier d\'un agent',
        tags: ['Règlements'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['agent_id', 'date_settled', 'received_amount'],
                properties: [
                    new OA\Property(property: 'agent_id',        type: 'integer', example: 1),
                    new OA\Property(property: 'date_settled',    type: 'string',  format: 'date', example: '2025-01-15'),
                    new OA\Property(property: 'received_amount', type: 'number',  example: 45000),
                    new OA\Property(property: 'notes',           type: 'string',  example: 'Versement en espèces'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Règlement enregistré'),
            new OA\Response(response: 422, description: 'Règlement déjà existant ou erreur de validation'),
        ]
    )]
    public function validate(ValidateSettlementRequest $request): JsonResponse
    {
        $responsible = $request->user();
        $orgId       = $responsible->organization_id;

        $agent = User::forOrganization($orgId)->agents()->findOrFail($request->agent_id);

        $exists = DailySettlement::forAgent($agent->id)->forDate($request->date_settled)->exists();

        if ($exists) {
            return $this->error(__('http.settlement_exists'), 422);
        }

        return DB::transaction(function () use ($request, $responsible, $agent) {
            $expectedAmount = Contribution::where('user_id', $agent->id)
                ->pending()
                ->filterByPeriod($request->date_settled)
                ->sum('amount');

            $receivedAmount = $request->received_amount;
            $diff           = abs($receivedAmount - $expectedAmount);
            $status         = $diff < 0.01 ? SettlementStatus::Validated : SettlementStatus::Discrepancy;

            $settlement = DailySettlement::create([
                'organization_id'             => $responsible->organization_id,
                'agent_id'                    => $agent->id,
                'validated_by_responsible_id' => $responsible->id,
                'date_settled'                => $request->date_settled,
                'expected_amount'             => $expectedAmount,
                'received_amount'             => $receivedAmount,
                'status'                      => $status,
                'notes'                       => $request->notes,
            ]);

            Contribution::where('user_id', $agent->id)
                ->pending()
                ->filterByPeriod($request->date_settled)
                ->update(['settlement_status' => ContributionSettlementStatus::Settled]);

            $settlement->load(['agent:id,firstname,lastname,avatar_url', 'validatedByResponsible:id,firstname,lastname']);

            /*if ($status === SettlementStatus::Discrepancy) {
                SendSettlementAlertSmsJob::dispatch(
                    $responsible->phone,
                    $agent->full_name,
                    (float) $expectedAmount,
                    (float) $receivedAmount,
                    $request->date_settled
                );
            }*/

            return $this->created(
                new DailySettlementResource($settlement),
                $status === SettlementStatus::Validated
                    ? __('http.settlement_validated')
                    : __('http.settlement_discrepancy')
            );
        });
    }

    #[OA\Get(
        path: '/api/responsible/settlements/pending-summary',
        summary: 'Résumé des cotisations en attente par agent pour une date',
        tags: ['Règlements'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date', example: '2025-01-15')),
        ],
        responses: [new OA\Response(response: 200, description: 'Résumé journalier des cotisations en attente')]
    )]
    public function pendingSummary(Request $request): JsonResponse
    {
        $orgId = $request->user()->organization_id;
        $date  = $request->query('date', today()->toDateString());

        $agents = User::forOrganization($orgId)
            ->agents()
            ->active()
            ->withSum(
                ['contributions as pending_amount' => fn($q) => $q->pending()->filterByPeriod($date)],
                'amount'
            )
            ->withCount(
                ['contributions as pending_count' => fn($q) => $q->pending()->filterByPeriod($date)]
            )
            ->get();

        $summary = $agents->map(function (User $agent) use ($date) {
            return [
                'agent_id'        => $agent->id,
                'full_name'       => $agent->full_name,
                'avatar_url'      => $agent->avatar_url,
                'pending_amount'  => (float) ($agent->pending_amount ?? 0),
                'pending_count'   => (int)   ($agent->pending_count  ?? 0),
                'already_settled' => DailySettlement::forAgent($agent->id)->forDate($date)->exists(),
            ];
        });

        return $this->success(['date' => $date, 'summary' => $summary]);
    }
}
