<?php

namespace App\Http\Requests\Responsible;

use App\Rules\TogoPhone;
use App\Rules\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isResponsible() ?? false;
    }

    public function rules(): array
    {
        $agentId = $this->route('agent');

        return [
            'firstname' => ValidationRules::name(required: false),
            'lastname'  => ValidationRules::name(required: false),
            'phone'     => ['sometimes', 'required', 'string', new TogoPhone(), "unique:users,phone,{$agentId}"],
            'email'     => ['nullable', 'email', 'max:255', "unique:users,email,{$agentId}"],
            'status'    => ['sometimes', 'required', 'in:active,suspended'],
            'avatar'    => ['sometimes', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
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
