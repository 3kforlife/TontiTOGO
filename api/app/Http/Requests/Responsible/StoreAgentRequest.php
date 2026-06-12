<?php

namespace App\Http\Requests\Responsible;

use App\Rules\TogoPhone;
use App\Rules\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isResponsible() ?? false;
    }

    public function rules(): array
    {
        return [
            'firstname' => ValidationRules::name(),
            'lastname'  => ValidationRules::name(),
            'phone'     => ['required', 'string', new TogoPhone(), 'unique:users,phone'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'avatar'    => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
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
