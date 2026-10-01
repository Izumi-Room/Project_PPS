<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InternshipPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole('SUPERADMIN') ||
            $this->user()->hasPermission('manage:periods') ||
            $this->user()->hasPermission('manage:master-data')
        );
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'string', 'max:20'],
            'semester_type' => ['required', 'string', Rule::in(['GANJIL', 'GENAP'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama periode magang wajib diisi.',
            'academic_year.required' => 'Tahun akademik wajib diisi (contoh: 2026/2027).',
            'semester_type.required' => 'Jenis semester wajib dipilih (GANJIL / GENAP).',
            'start_date.required' => 'Tanggal mulai magang wajib diisi.',
            'end_date.required' => 'Tanggal selesai magang wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama dengan atau setelah tanggal mulai.',
        ];
    }
}
