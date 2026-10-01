<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class AssignAdvisorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['KAPRODI', 'SUPERADMIN']);
    }

    public function rules(): array
    {
        return [
            'advisor_id' => ['required', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'advisor_id.required' => 'Dosen pembimbing wajib dipilih.',
            'advisor_id.exists' => 'Dosen yang dipilih tidak valid atau tidak ditemukan dalam sistem.',
        ];
    }
}
