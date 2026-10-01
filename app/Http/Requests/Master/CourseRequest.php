<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole('SUPERADMIN') ||
            $this->user()->hasPermission('manage:courses') ||
            $this->user()->hasPermission('manage:master-data')
        );
    }

    public function rules(): array
    {
        return [
            'study_program_id' => ['required', 'exists:study_programs,id'],
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'credits' => ['required', 'integer', 'min:1', 'max:12'],
            'semester' => ['required', 'integer', 'min:1', 'max:8'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->code)),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function messages(): array
    {
        return [
            'study_program_id.required' => 'Program studi wajib dipilih.',
            'study_program_id.exists' => 'Program studi yang dipilih tidak valid.',
            'code.required' => 'Kode mata kuliah wajib diisi.',
            'name.required' => 'Nama mata kuliah wajib diisi.',
            'credits.required' => 'Bobot SKS wajib diisi.',
            'credits.integer' => 'Bobot SKS harus berupa angka numerik.',
            'credits.min' => 'Bobot SKS minimal 1.',
            'semester.required' => 'Semester wajib ditentukan.',
            'semester.min' => 'Semester minimal 1.',
            'semester.max' => 'Semester maksimal 8.',
        ];
    }
}
