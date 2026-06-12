<?php

namespace App\Http\Requests\Agent;

use Illuminate\Foundation\Http\FormRequest;

class StoreContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAgent() ?? false;
    }

    public function rules(): array
    {
        return [
            'tontine_participant_id' => ['required', 'exists:tontine_participants,id'],
            'amount'                 => ['required', 'numeric', 'min:100'],
            'latitude'               => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'              => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'tontine_participant_id.required' => 'La participation à la tontine est obligatoire.',
            'tontine_participant_id.exists'   => 'Cette participation à la tontine est introuvable.',
            'amount.required'                 => 'Le montant de la cotisation est obligatoire.',
            'amount.numeric'                  => 'Le montant doit être un nombre.',
            'amount.min'                      => 'Le montant minimum est de 100 FCFA.',
            'latitude.numeric'                => 'La latitude doit être un nombre.',
            'latitude.between'               => 'La latitude doit être comprise entre -90 et 90.',
            'longitude.numeric'               => 'La longitude doit être un nombre.',
            'longitude.between'              => 'La longitude doit être comprise entre -180 et 180.',
        ];
    }
}
