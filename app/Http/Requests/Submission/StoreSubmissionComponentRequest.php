<?php

namespace App\Http\Requests\Submission;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'name' => ['required', 'string', 'max:255'],
            'submission_type' => ['required', 'string', 'in:FILE,LINK,FILE_OR_LINK,DOCUMENT,PROJECT_URL'],
            'deadline' => ['required', 'date'],
            'weight' => ['required', 'integer', 'min:1', 'max:100'],
            'is_required' => ['nullable', 'boolean'],
            'instructions' => ['nullable', 'string', 'max:3000'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'Mata kuliah wajib dipilih.',
            'name.required' => 'Nama komponen pengumpulan wajib diisi.',
            'submission_type.required' => 'Jenis pengumpulan wajib ditentukan.',
            'deadline.required' => 'Batas waktu (deadline) wajib ditentukan.',
            'weight.required' => 'Bobot penilaian komponen wajib diisi (1 - 100%).',
        ];
    }
}
