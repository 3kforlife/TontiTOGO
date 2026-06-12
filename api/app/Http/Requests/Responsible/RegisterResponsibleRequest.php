<?php

namespace App\Http\Requests\Responsible;

use App\Rules\PasswordRules;
use App\Rules\TogoPhone;
use App\Rules\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class RegisterResponsibleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization_name' => ValidationRules::organizationName(),
            'firstname'         => ValidationRules::name(),
            'lastname'          => ValidationRules::name(),
            'phone'             => ['required', 'string', new TogoPhone(), 'unique:users,phone'],
            'email'             => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'          => PasswordRules::creation(),
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
        return [];
    }
}
