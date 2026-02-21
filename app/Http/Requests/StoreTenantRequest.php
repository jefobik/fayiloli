<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TenantModule;
use App\Enums\TenantType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Tenant::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'organization_name' => ['required', 'string', 'max:255'],
            'admin_email'       => ['required', 'email', 'max:255'],
            'tenant_type'       => ['required', new Enum(TenantType::class)],
            'domain'            => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?(\.[a-z0-9\-]{2,})+$/',
                'unique:domains,domain',
            ],
            'notes'             => ['nullable', 'string', 'max:1000'],
            // Module access — optional array; falls back to TenantModule::defaults()
            'modules'           => ['nullable', 'array'],
            'modules.*'         => ['string', 'in:' . implode(',', TenantModule::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'domain.regex'   => 'Domain must be a valid fully-qualified hostname (e.g. ict.youredms.com).',
            'domain.unique'  => 'This domain is already assigned to another tenant.',
            'modules.*.in'   => 'One or more selected modules are invalid.',
        ];
    }
}
