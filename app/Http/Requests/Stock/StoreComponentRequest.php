<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

/**
 * current_quantity is deliberately absent from these rules — it is never
 * settable directly. A new component starts at 0; the only way to change it
 * is through StockService::recordIn()/recordOut(), so stock_movements stays
 * the single source of truth.
 */
class StoreComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:components,code'],
            'name' => ['required', 'string', 'max:255'],
            'unit_of_measure' => ['required', 'string', 'max:50'],
            'safety_stock_threshold' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
