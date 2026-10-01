<?php

namespace App\Http\Requests\CourseConversion;

use Illuminate\Foundation\Http\FormRequest;

class ResubmitCourseConversionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activity_plan' => ['required', 'string', 'min:10', 'max:3000'],
        ];
    }

    public function messages(): array
    {
        return [
            'activity_plan.required' => 'Rencana / relevansi perbaikan kegiatan magang wajib diisi.',
            'activity_plan.min' => 'Rencana kegiatan minimal 10 karakter.',
        ];
    }
}
