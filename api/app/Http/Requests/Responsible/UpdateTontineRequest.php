<?php

namespace App\Http\Requests\Responsible;

use App\Rules\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTontineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isResponsible() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'           => ValidationRules::tontineName(required: false),
            'minimum_amount' => ['sometimes', 'required', 'numeric', 'min:100'],
            'frequency'      => ['sometimes', 'required', 'in:daily,weekly,monthly'],
            'start_date'     => ['sometimes', 'required', 'date'],
            'end_date'       => ['nullable', 'date', 'after:start_date'],
            'status'         => ['sometimes', 'required', 'in:active,closed,archived'],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
