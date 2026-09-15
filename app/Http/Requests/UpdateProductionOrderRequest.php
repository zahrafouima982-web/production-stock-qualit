<?php

namespace App\Http\Requests\ProductionOrder;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductionOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('order'));
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'production_line_id' => ['required', 'integer', 'exists:production_lines,id'],
            'planned_quantity' => ['required', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
        ];
    }
}
