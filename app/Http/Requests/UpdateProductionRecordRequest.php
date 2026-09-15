<?php

namespace App\Http\Requests\ProductionRecord;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductionRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('record'));
    }

    public function rules(): array
    {
        return [
            'produced_quantity' => ['required', 'integer', 'min:1'],
            'shift' => ['nullable', 'string', 'max:50'],
            'recorded_at' => ['nullable', 'date'],
        ];
    }
}
