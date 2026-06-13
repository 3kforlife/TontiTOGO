<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TontineParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'chosen_amount'       => (float) $this->chosen_amount,
            'joined_at'           => $this->joined_at?->format('d/m/Y'),
            'status'              => $this->status->value,
            'status_label'        => $this->status->label(),
            'total_paid'          => (float) ($this->contributions_sum_amount ?? $this->total_contributions),
            'contributions_count' => $this->whenCounted('contributions'),
            'member'              => new MemberResource($this->whenLoaded('member')),
            'tontine'             => $this->whenLoaded('tontine', fn() => [
                'id'                => $this->tontine->id,
                'name'              => $this->tontine->name,
                'minimum_amount'    => (float) $this->tontine->minimum_amount,
                'frequency'         => $this->tontine->frequency->value,
                'frequency_label'   => $this->tontine->frequency->label(),
            ]),
        ];
    }
}
