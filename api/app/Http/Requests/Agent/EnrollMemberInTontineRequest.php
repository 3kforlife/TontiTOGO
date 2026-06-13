<?php

namespace App\Http\Requests\Agent;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation pour l'inscription d'un membre à une tontine par l'agent sur le terrain.
 *
 * Règle métier :
 *   - joined_at est forcé à aujourd'hui côté backend (prepareForValidation).
 *     L'agent ne choisit pas la date — elle est instantanée.
 *   - chosen_amount >= minimum_amount de la tontine (vérifié dans le controller).
 */
class EnrollMemberInTontineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAgent() ?? false;
    }

    public function rules(): array
    {
        return [
            'tontine_id'    => ['required', 'exists:tontines,id'],
            'chosen_amount' => ['required', 'numeric', 'min:100'],
            'joined_at'     => ['required', 'date', 'after_or_equal:today', 'before_or_equal:today'],
        ];
    }

    /**
     * L'agent ne doit jamais pouvoir antidater une adhésion.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'joined_at' => now()->toDateString(),
            'member_id' => (int) $this->route('id'),
        ]);
    }

    public function messages(): array
    {
        return [];
    }
}
