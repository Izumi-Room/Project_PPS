<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudyProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole('SUPERADMIN') ||
            $this->user()->hasPermission('manage:study-programs') ||
            $this->user()->hasPermission('manage:master-data')
        );
    }

    public function rules(): array
    {
        $id = $this->route('study_program')?->id ?? $this->route('study_program');

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('study_programs', 'code')->ignore($id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'degree_level' => ['required', 'string', Rule::in(['D3', 'D4', 'S1', 'S2', 'S3'])],
            'faculty' => ['required', 'string', 'max:255'],
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
            'code.required' => 'Kode program studi wajib diisi.',
            'code.unique' => 'Kode program studi ini sudah terdaftar.',
            'name.required' => 'Nama program studi wajib diisi.',
            'degree_level.required' => 'Jenjang pendidikan wajib dipilih.',
            'faculty.required' => 'Nama fakultas wajib diisi.',
        ];
    }
}
