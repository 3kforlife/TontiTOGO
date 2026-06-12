<?php

namespace App\Http\Requests\Agent;

use App\Rules\PasswordRules;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAgent() ?? false;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password'         => PasswordRules::update(),
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
