<?php

namespace App\Http\Requests\Agent;

use App\Rules\TogoPhone;
use Illuminate\Foundation\Http\FormRequest;

class AgentLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone'    => ['required', 'string', new TogoPhone()],
            'password' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->phone) {
            $normalized = TogoPhone::normalize($this->phone);
            if ($normalized) {
                $this->merge(['phone' => $normalized]);
            }
        }
    }

    public function messages(): array
    {
        return [
            'phone.required'    => 'Le numéro de téléphone est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ];
    }
}
