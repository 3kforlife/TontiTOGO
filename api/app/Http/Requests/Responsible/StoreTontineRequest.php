<?php

namespace App\Http\Requests\Responsible;

use App\Rules\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreTontineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isResponsible() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'           => ValidationRules::tontineName(),
            'minimum_amount' => ['required', 'numeric', 'min:100'],
            'frequency'      => ['required', 'in:daily,weekly,monthly'],
            'start_date'     => ['required', 'date', 'after_or_equal:today'],
            'end_date'       => ['nullable', 'date', 'after:start_date'],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
