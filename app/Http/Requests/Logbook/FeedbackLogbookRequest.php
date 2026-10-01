<?php

namespace App\Http\Requests\Logbook;

use Illuminate\Foundation\Http\FormRequest;

class FeedbackLogbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dosbing_feedback' => ['required', 'string', 'min:3', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'dosbing_feedback.required' => 'Catatan review / feedback wajib diisi.',
            'dosbing_feedback.min' => 'Catatan review minimal 3 karakter.',
        ];
    }
}
