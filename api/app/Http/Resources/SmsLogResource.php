<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SmsLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'recipient'        => $this->recipient,
            'message'          => $this->message,
            'type'             => $this->type->value,
            'type_label'       => $this->type->label(),
            'status'           => $this->status->value,
            'status_label'     => $this->status->label(),
            'response_payload' => $this->when(
                request()->routeIs('*.sms.show'),
                $this->response_payload
            ),
            'created_at'       => $this->created_at?->format('d/m/Y H:i'),
        ];
    }
}
