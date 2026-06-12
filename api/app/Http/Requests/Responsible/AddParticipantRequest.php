<?php

namespace App\Http\Requests\Responsible;

use App\Models\Tontine;
use Illuminate\Foundation\Http\FormRequest;

class AddParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isResponsible() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'member_id'     => (int) $this->member_id,
            'chosen_amount' => (float) $this->chosen_amount,
        ]);
    }

    public function rules(): array
    {
        // Récupérer la date de début de la tontine depuis la route
        // pour l'utiliser comme borne inférieure de joined_at.
        $tontineStartDate = $this->resolveTontineStartDate();

        return [
            'member_id'     => ['required', 'exists:members,id'],
            'chosen_amount' => ['required', 'numeric', 'min:100'],

            /*
             * Règle responsable (Web) :
             *   - Obligatoire et valide
             *   - >= date de début de la tontine (rétroactivité limitée à la création)
             *   - <= aujourd'hui (pas dans le futur)
             *
             * Le responsable peut donc régulariser une adhésion passée
             * tant qu'elle reste postérieure au lancement de la tontine.
             */
            'joined_at' => array_filter([
                'required',
                'date',
                $tontineStartDate ? "after_or_equal:{$tontineStartDate}" : null,
                'before_or_equal:today',
            ]),
        ];
    }

    public function messages(): array
    {
        return [];
    }

    // Helper privé
    
    private function resolveTontineStartDate(): ?string
    {
        $tontineId = (int) $this->route('tontine');

        if (! $tontineId) {
            return null;
        }

        $tontine = Tontine::find($tontineId);

        return $tontine?->start_date?->format('Y-m-d');
    }
}
