<?php

namespace App\Http\Requests\Responsible;

use App\Rules\TogoPhone;
use App\Rules\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isResponsible() ?? false;
    }

    public function rules(): array
    {
        $orgId = $this->user()->organization_id;

        return [
            // Unique par organisation : C-001 peut exister dans deux organisations différentes
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
