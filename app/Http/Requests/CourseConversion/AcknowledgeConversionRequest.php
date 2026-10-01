<?php

namespace App\Http\Requests\CourseConversion;

use Illuminate\Foundation\Http\FormRequest;

class AcknowledgeConversionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
