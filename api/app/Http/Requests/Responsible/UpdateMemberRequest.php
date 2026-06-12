<?php

namespace App\Http\Requests\Responsible;

use App\Rules\TogoPhone;
use App\Rules\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isResponsible() ?? false;
    }

    public function rules(): array
    {
        $memberId = $this->route('member');
        $orgId    = $this->user()->organization_id;

        return [
            'notebook_number' => [
                'sometimes', 'required', 'string', 'min:3', 'max:50',
                'regex:' . ValidationRules::REGEX_ADDRESS,
                // Unique par organisation sauf pour ce membre (ignore)
                Rule::unique('members')
                    ->where('organization_id', $orgId)
                    ->ignore($memberId),
            ],
            'firstname' => ValidationRules::name(required: false),
            'lastname'  => ValidationRules::name(required: false),
            'phone'     => ['sometimes', 'required', 'string', new TogoPhone(), "unique:members,phone,{$memberId}"],
            'gender'    => ['sometimes', 'required', 'in:M,F'],
            'address'   => ValidationRules::address(),
            'status'    => ['sometimes', 'required', 'in:active,suspended'],
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
