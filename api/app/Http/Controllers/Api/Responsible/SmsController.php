<?php

namespace App\Http\Controllers\Api\Responsible;

use App\Enums\SmsStatus;
use App\Enums\SmsType;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\SmsLogResource;
use App\Jobs\SendReminderSmsJob;
use App\Models\SmsLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SmsController extends ApiController
{
    #[OA\Get(
        path: '/api/responsible/sms',
        summary: 'Liste paginée des logs SMS',
        tags: ['SMS'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['sent', 'failed'])),
            new OA\Parameter(name: 'type',   in: 'query', schema: new OA\Schema(type: 'string', enum: ['confirmation', 'reminder'])),
            new OA\Parameter(name: 'page',   in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Liste des logs SMS + stats')]
    )]
    public function index(Request $request): JsonResponse
    {
        $orgId = $request->user()->organization_id;

        $query = SmsLog::forOrganization($orgId)->orderByDesc('created_at');

        if ($status = $request->query('status')) {
            $query->where('status', SmsStatus::from($status));
        }

        if ($type = $request->query('type')) {
            $query->where('type', SmsType::from($type));
        }

        $logs = $query->paginate(6);

        return $this->success([
            'data'         => SmsLogResource::collection($logs->items()),
            'total'        => $logs->total(),
            'per_page'     => $logs->perPage(),
            'current_page' => $logs->currentPage(),
            'last_page'    => $logs->lastPage(),
            'stats'        => [
                'sent'   => SmsLog::forOrganization($orgId)->sent()->count(),
                'failed' => SmsLog::forOrganization($orgId)->failed()->count(),
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/responsible/sms/{id}',
        summary: 'Détail d\'un log SMS avec payload Termii brut',
        tags: ['SMS'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Détail du log SMS'),
            new OA\Response(response: 404, description: 'Non trouvé'),
        ]
    )]
    public function show(Request $request, int $id): JsonResponse
    {
        $log = SmsLog::forOrganization($request->user()->organization_id)->findOrFail($id);

        return $this->success(new SmsLogResource($log));
    }

    #[OA\Post(
        path: '/api/responsible/sms/send-reminders',
        summary: 'Déclencher manuellement les SMS de rappel',
        tags: ['SMS'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Envoi des rappels lancé en arrière-plan')]
    )]
    public function sendReminders(Request $request): JsonResponse
    {
        SendReminderSmsJob::dispatch($request->user()->organization_id);

        return $this->success(null, __('http.reminders_dispatched'));
    }
}
