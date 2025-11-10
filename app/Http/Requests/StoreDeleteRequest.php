<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'target_type' => ['required', 'string', 'max:100', Rule::in([
                'user',
                'project',
                'project_folder',
                'project_file',
                'contractor',
                'project_manager',
                'external_stakeholder',
                'email_template',
                'task',
                'queue_job',
            ])],
            'target_id' => ['required', 'integer'],
            'target_label' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string'],
            'redirect_url' => ['nullable', 'string'],
        ];
    }
}

