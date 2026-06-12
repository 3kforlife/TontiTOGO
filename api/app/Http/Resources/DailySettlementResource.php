<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailySettlementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'date_settled'    => $this->date_settled?->format('d/m/Y'),
            'expected_amount' => (float) $this->expected_amount,
            'received_amount' => (float) $this->received_amount,
            'discrepancy'     => $this->discrepancy,
            'status'          => $this->status->value,
            'status_label'    => $this->status->label(),
            'notes'           => $this->notes,
            'agent'           => $this->whenLoaded('agent', fn() => [
                'id'         => $this->agent->id,
                'full_name'  => $this->agent->full_name,
                'avatar_url' => $this->agent->avatar_url,
            ]),
            'validated_by'    => $this->whenLoaded('validatedByResponsible', fn() =>
                $this->validatedByResponsible?->full_name
            ),
            'created_at'      => $this->created_at?->format('d/m/Y H:i'),
        ];
    }
}
