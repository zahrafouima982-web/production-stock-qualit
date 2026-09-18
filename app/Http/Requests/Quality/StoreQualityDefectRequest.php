<?php

namespace App\Http\Requests\Quality;

use Illuminate\Foundation\Http\FormRequest;

class StoreQualityDefectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'defect_type' => ['required', 'in:DIMENSIONAL,ELECTRICAL,COSMETIC,ASSEMBLY,OTHER'],
            'description' => ['required', 'string'],
            'severity' => ['required', 'in:MINOR,MAJOR,CRITICAL'],
        ];
    }
}
