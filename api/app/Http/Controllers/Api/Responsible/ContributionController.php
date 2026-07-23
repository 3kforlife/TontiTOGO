<?php

namespace App\Http\Controllers\Api\Responsible;

use App\Exports\ContributionsExport;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\ContributionResource;
use App\Models\Contribution;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ContributionController extends ApiController
{
    #[OA\Get(
        path: '/api/responsible/contributions',
        summary: 'Journal général des cotisations',
        tags: ['Cotisations'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'date',       in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'agent_id',   in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'member_id',  in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'tontine_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'page',       in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Liste paginée des cotisations')]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Contribution::forOrganization($request->user()->organization_id)
            ->with([
                'agent:id,firstname,lastname',
                'tontineParticipant.member:id,firstname,lastname,phone,member_code',
                'tontineParticipant.tontine:id,name',
            ])
            ->orderByDesc('created_at');

        if ($date = $request->query('date')) {
            $query->filterByPeriod($date);
        }

        if ($agentId = $request->query('agent_id')) {
            $query->where('user_id', $agentId);
        }

        if ($memberId = $request->query('member_id')) {
            $query->whereHas('tontineParticipant', fn($q) =>
                $q->where('member_id', $memberId)
            );
        }

        if ($tontineId = $request->query('tontine_id')) {
            $query->whereHas('tontineParticipant', fn($q) =>
                $q->where('tontine_id', $tontineId)
            );
        }

        $contributions = $query->paginate(4);

        return $this->success([
            'data'         => ContributionResource::collection($contributions->items()),
            'total'        => $contributions->total(),
            'per_page'     => $contributions->perPage(),
            'current_page' => $contributions->currentPage(),
            'last_page'    => $contributions->lastPage(),
        ]);
    }

    #[OA\Get(
        path: '/api/responsible/contributions/{id}',
        summary: 'Détail d\'une cotisation',
        tags: ['Cotisations'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Détail de la cotisation'),
            new OA\Response(response: 404, description: 'Non trouvée'),
        ]
    )]
    public function show(Request $request, int $id): JsonResponse
    {
        $contribution = Contribution::forOrganization($request->user()->organization_id)
            ->with([
                'agent:id,firstname,lastname,phone',
                'tontineParticipant.member',
                'tontineParticipant.tontine',
            ])
            ->findOrFail($id);

        return $this->success(new ContributionResource($contribution));
    }

    #[OA\Get(
        path: '/api/responsible/contributions/export/pdf',
        summary: 'Exporter les cotisations en PDF',
        tags: ['Cotisations'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'date',       in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'agent_id',   in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'member_id',  in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'tontine_id', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Fichier PDF téléchargé')]
    )]
    public function exportPdf(Request $request): Response
    {
        $user          = $request->user();
        $contributions = $this->buildFilteredQuery($request)->get();

        $pdf = Pdf::loadView('exports.contributions-pdf', [
            'contributions' => $contributions,
            'organization'  => $user->organization?->name,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('cotisations-' . now()->format('Y-m-d') . '.pdf');
    }

    #[OA\Get(
        path: '/api/responsible/contributions/export/excel',
        summary: 'Exporter les cotisations en Excel',
        tags: ['Cotisations'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'date',       in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'agent_id',   in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'member_id',  in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'tontine_id', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Fichier XLSX téléchargé')]
    )]
    public function exportExcel(Request $request): BinaryFileResponse
    {
        return Excel::download(
            new ContributionsExport(
                $request->user()->organization_id,
                $request->only(['date', 'agent_id', 'member_id', 'tontine_id'])
            ),
            'cotisations-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    // -------------------------------------------------------
    // Helper privé
    // -------------------------------------------------------

    private function buildFilteredQuery(Request $request)
    {
        $query = Contribution::forOrganization($request->user()->organization_id)
            ->with([
                'agent:id,firstname,lastname',
                'tontineParticipant.member:id,firstname,lastname,phone,member_code',
                'tontineParticipant.tontine:id,name',
            ])
            ->orderByDesc('created_at');

        if ($date = $request->query('date')) {
            $query->filterByPeriod($date);
        }
        if ($agentId = $request->query('agent_id')) {
            $query->where('user_id', $agentId);
        }
        if ($memberId = $request->query('member_id')) {
            $query->whereHas('tontineParticipant', fn($q) => $q->where('member_id', $memberId));
        }
        if ($tontineId = $request->query('tontine_id')) {
            $query->whereHas('tontineParticipant', fn($q) => $q->where('tontine_id', $tontineId));
        }

        return $query;
    }
}
