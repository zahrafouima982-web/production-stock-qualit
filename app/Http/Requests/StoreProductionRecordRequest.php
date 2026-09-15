<?php

namespace App\Http\Requests\ProductionRecord;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductionRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\ProductionRecord::class);
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
