<?php

namespace App\Http\Requests\Submission;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubmissionComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'submission_type' => ['required', 'string', 'in:FILE,LINK,FILE_OR_LINK,DOCUMENT,PROJECT_URL'],
            'deadline' => ['required', 'date'],
            'weight' => ['required', 'integer', 'min:1', 'max:100'],
            'is_required' => ['nullable', 'boolean'],
            'instructions' => ['nullable', 'string', 'max:3000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
