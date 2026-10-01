<?php

namespace App\Http\Requests\Submission;

use Illuminate\Foundation\Http\FormRequest;

class ReviewSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:APPROVE,REVISION'],
            'feedback' => ['required_if:action,REVISION', 'nullable', 'string', 'min:5', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Aksi penilaian review wajib dipilih.',
            'action.in' => 'Aksi penilaian tidak valid.',
            'feedback.required_if' => 'Catatan revisi wajib diisi jika meminta perbaikan kepada mahasiswa.',
            'feedback.min' => 'Catatan revisi minimal 5 karakter.',
        ];
    }
}
