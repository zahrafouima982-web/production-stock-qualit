<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'component_id' => ['required', 'integer', 'exists:components,id'],
            'type' => ['required', 'in:IN,OUT'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'reference' => ['nullable', 'string', 'max:255'],
            'performed_at' => ['nullable', 'date'],
        ];
    }
}
