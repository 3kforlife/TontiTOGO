<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TontineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                        => $this->id,
            'name'                      => $this->name,
            'minimum_amount'            => (float) $this->minimum_amount,
            'frequency'                 => $this->frequency->value,
            'frequency_label'           => $this->frequency->label(),
            'start_date'                => $this->start_date?->format('d/m/Y'),
            'end_date'                  => $this->end_date?->format('d/m/Y'),
            'status'                    => $this->status->value,
            'status_label'              => $this->status->label(),
            'participants_count'        => $this->whenCounted('participants'),
            'active_participants_count' => $this->whenCounted('activeParticipants'),
            'participants'              => TontineParticipantResource::collection(
                $this->whenLoaded('participants')
            ),
            'created_at'                => $this->created_at?->format('d/m/Y'),
        ];
    }
}
