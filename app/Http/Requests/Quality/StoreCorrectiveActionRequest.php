<?php

namespace App\Http\Requests\Quality;

use Illuminate\Foundation\Http\FormRequest;

class StoreCorrectiveActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'root_cause' => ['nullable', 'string'],
            'action_description' => ['required', 'string'],
            'responsible_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'requires_reinspection' => ['sometimes', 'boolean'],
        ];
    }
}
