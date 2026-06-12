<?php

namespace App\Http\Controllers\Api\Responsible;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\ContributionResource;
use App\Models\Contribution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MapController extends ApiController
{
    #[OA\Get(
        path: '/api/responsible/map/markers',
        summary: 'Points GPS des cotisations géolocalisées pour la carte Leaflet',
        tags: ['Carte GPS'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'date',     in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'agent_id', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Marqueurs géolocalisés')]
    )]
    public function markers(Request $request): JsonResponse
    {
        $query = Contribution::forOrganization($request->user()->organization_id)
            ->geolocated()
            ->with([
                'agent:id,firstname,lastname',
                'tontineParticipant.member:id,firstname,lastname,member_code,phone',
                'tontineParticipant.tontine:id,name',
            ])
            ->orderByDesc('created_at');

        if ($date = $request->query('date')) {
            $query->filterByPeriod($date);
        }

        if ($agentId = $request->query('agent_id')) {
            $query->where('user_id', $agentId);
        }

        $contributions = $query->get();

        return $this->success([
            'count'   => $contributions->count(),
            'markers' => ContributionResource::collection($contributions),
        ]);
    }
}
