<?php

namespace App\Http\Requests\Agent;

use App\Rules\TogoPhone;
use App\Rules\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMobileMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAgent() ?? false;
    }

    public function rules(): array
    {
        $orgId = $this->user()->organization_id;

        return [
            // Unique par organisation — l'agent hérite de l'espace de numérotation de son organisation
            'notebook_number' => [
                'required', 'string', 'min:3', 'max:50',
                'regex:' . ValidationRules::REGEX_ADDRESS,
                Rule::unique('members')->where('organization_id', $orgId),
            ],
            'firstname' => ValidationRules::name(),
            'lastname'  => ValidationRules::name(),
            'phone'     => ['required', 'string', new TogoPhone(), 'unique:members,phone'],
            'gender'    => ['required', 'in:M,F'],
            'address'   => ValidationRules::address(),
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
