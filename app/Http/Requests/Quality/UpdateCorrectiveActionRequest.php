<?php

namespace App\Http\Requests\Quality;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCorrectiveActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'root_cause' => ['nullable', 'string'],
            'action_description' => ['nullable', 'string'],
            // VALIDATED is intentionally excluded — only the dedicated
            // validate() action/route can set that status (see CorrectiveActionPolicy::validate()
            // and QualityService::validateCorrectiveAction()).
            'status' => ['nullable', 'in:OPEN,IN_PROGRESS,DONE'],
            'requires_reinspection' => ['sometimes', 'boolean'],
        ];
    }
}
