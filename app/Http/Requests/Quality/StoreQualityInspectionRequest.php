<?php

namespace App\Http\Requests\Quality;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Authorization is handled explicitly in the controller via $this->authorize()
 * (matching the current ProductController / ProductionOrderController /
 * ProductionRecordController pattern), not via authorizeResource() — kept
 * simple to true so this Request is validation-only.
 */
class StoreQualityInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'production_record_id' => ['required', 'integer', 'exists:production_records,id'],
            'inspected_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.criterion_name' => ['required', 'string', 'max:255'],
            'items.*.result' => ['required', 'in:PASS,FAIL,N_A'],
            'items.*.remarks' => ['nullable', 'string', 'max:255'],
        ];
    }
}
