<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TenantStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class TransitionTenantStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('transitionStatus', $this->route('tenant')) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(TenantStatus::class)],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * After base validation passes, confirm the transition is allowed
     * by the state machine before the controller ever sees the request.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('status')) {
                    return; // already invalid, skip state-machine check
                }

                $tenant  = $this->route('tenant');
                $current = $tenant->status ?? TenantStatus::PENDING;
                $target  = TenantStatus::from($this->input('status'));

                if (! $current->canTransitionTo($target)) {
                    $validator->errors()->add(
                        'status',
                        "Cannot transition from [{$current->label()}] to [{$target->label()}]."
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'A target status is required.',
            'status.Illuminate\Validation\Rules\Enum' => 'Invalid status value.',
        ];
    }
}
