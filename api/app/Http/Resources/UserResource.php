<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'firstname'            => $this->firstname,
            'lastname'             => $this->lastname,
            'full_name'            => $this->full_name,
            'email'                => $this->email,
            'phone'                => $this->phone,
            'role'                 => $this->role->value,
            'role_label'           => $this->role->label(),
            'status'               => $this->status->value,
            'status_label'         => $this->status->label(),
            'avatar_url'           => $this->avatar_url,
            'must_change_password' => $this->must_change_password,
            'total_contributions'  => $this->whenCounted('contributions'),
            'organization'         => $this->whenLoaded('organization', fn() => [
                'id'   => $this->organization->id,
                'name' => $this->organization->name,
            ]),
            'created_at'           => $this->created_at?->format('d/m/Y'),
        ];
    }
}
