<?php

namespace App\Http\Requests\CourseConversion;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseConversionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'internship_application_id' => ['required', 'integer', 'exists:internship_applications,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'activity_plan' => ['required', 'string', 'min:10', 'max:3000'],
        ];
    }

    public function messages(): array
    {
        return [
            'internship_application_id.required' => 'Pendaftaran magang wajib dipilih.',
            'course_id.required' => 'Mata kuliah wajib dipilih.',
            'activity_plan.required' => 'Rencana / relevansi kegiatan magang wajib diisi.',
            'activity_plan.min' => 'Rencana kegiatan minimal 10 karakter.',
        ];
    }
}
