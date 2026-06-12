<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'member_code'      => $this->member_code,
            'notebook_number'  => $this->notebook_number,
            'firstname'        => $this->firstname,
            'lastname'         => $this->lastname,
            'full_name'        => $this->full_name,
            'phone'            => $this->phone,
            'gender'           => $this->gender->value,
            'gender_label'     => $this->gender->label(),
            'address'          => $this->address,
            'status'           => $this->status->value,
            'status_label'     => $this->status->label(),
            'created_by_agent' => $this->whenLoaded('createdByAgent', fn() =>
                $this->createdByAgent?->full_name
            ),
            'participations'   => TontineParticipantResource::collection(
                $this->whenLoaded('tontineParticipations')
            ),
            'created_at'       => $this->created_at?->format('d/m/Y'),
        ];
    }
}
