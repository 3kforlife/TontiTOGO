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
        $rules = [
            'password' => PasswordRules::update(),
        ];

        // Si l'agent n'a pas à changer son mot de passe (ce n'est pas la première connexion), on demande le mot de passe actuel
        if (! $this->user()?->must_change_password) {
            $rules['current_password'] = ['required', 'string', 'current_password'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [];
    }
}
