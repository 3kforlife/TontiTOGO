<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContributionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'reference'         => $this->reference,
            'amount'            => (float) $this->amount,
            'settlement_status' => $this->settlement_status->value,
            'settlement_label'  => $this->settlement_status->label(),
            'latitude'          => $this->latitude ? (float) $this->latitude : null,
            'longitude'         => $this->longitude ? (float) $this->longitude : null,
            'agent'             => $this->whenLoaded('agent', fn() => [
                'id'        => $this->agent->id,
                'full_name' => $this->agent->full_name,
                'phone'     => $this->agent->phone ?? null,
            ]),
            'member'            => $this->when(
                $this->relationLoaded('tontineParticipant') && $this->tontineParticipant?->relationLoaded('member'),
                fn() => $this->tontineParticipant->member ? [
                    'id'          => $this->tontineParticipant->member->id,
                    'full_name'   => $this->tontineParticipant->member->full_name,
                    'phone'       => $this->tontineParticipant->member->phone,
                    'member_code' => $this->tontineParticipant->member->member_code,
                ] : null
            ),
            'tontine'           => $this->when(
                $this->relationLoaded('tontineParticipant') && $this->tontineParticipant?->relationLoaded('tontine'),
                fn() => $this->tontineParticipant->tontine ? [
                    'id'   => $this->tontineParticipant->tontine->id,
                    'name' => $this->tontineParticipant->tontine->name,
                ] : null
            ),
            'created_at'        => $this->created_at?->format('d/m/Y H:i'),
        ];
    }
}
