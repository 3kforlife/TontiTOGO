<?php

namespace App\Http\Requests\Responsible;

use App\Rules\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class ValidateSettlementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isResponsible() ?? false;
    }

    public function rules(): array
    {
        return [
            'agent_id'        => ['required', 'exists:users,id'],
            'date_settled'    => ['required', 'date', 'before_or_equal:today'],
            'received_amount' => ['required', 'numeric', 'min:0'],
            'notes'           => ValidationRules::notes(),
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
