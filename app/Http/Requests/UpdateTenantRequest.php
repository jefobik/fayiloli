<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TenantModule;
use App\Enums\TenantType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * Validates editable tenant metadata.
 *
 * Status and is_active are intentionally excluded — those fields are
 * managed exclusively through the status transition workflow
 * (TransitionTenantStatusRequest / TenantController::transitionStatus).
 */
class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('tenant')) ?? false;
    }

    public function rules(): array
    {
        /** @var \App\Models\Tenant $tenant */
        $tenant = $this->route('tenant');

        return [
            'organization_name' => ['required', 'string', 'max:255'],

            // Unique rule ignores the tenant being edited to allow saving
            // without changing the short_name.
            'short_name'        => [
                'required',
                'string',
                'min:2',
                'max:30',
                'regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/',
                Rule::unique('tenants', 'short_name')->ignore($tenant?->id),
            ],

            'admin_email'       => ['required', 'email', 'max:255'],
            'tenant_type'       => ['required', new Enum(TenantType::class)],
            'notes'             => ['nullable', 'string', 'max:1000'],
            // Module access — optional array; falls back to TenantModule::defaults()
            'modules'           => ['nullable', 'array'],
            'modules.*'         => ['string', 'in:' . implode(',', TenantModule::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'short_name.regex'  => 'Short name may only contain lowercase letters, numbers, and hyphens, and must start and end with a letter or digit.',
            'short_name.unique' => 'This short name is already in use by another tenant.',
            'modules.*.in'      => 'One or more selected modules are invalid.',
        ];
    }
}
