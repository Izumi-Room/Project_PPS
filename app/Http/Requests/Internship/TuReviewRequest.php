<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class TuReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['TU', 'SUPERADMIN']);
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:PASS,RETURN'],
            'reason' => ['required_if:decision,RETURN', 'nullable', 'string', 'max:1000'],
            'reference_letter_number' => ['nullable', 'string', 'max:100'],
            'reference_letter_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'decision.required' => 'Keputusan validasi TU wajib dipilih (Lolos / Kembalikan).',
            'reason.required_if' => 'Alasan pengembalian berkas wajib diisi jika status tidak lengkap.',
            'reference_letter_file.mimes' => 'File surat pengantar harus berformat PDF.',
        ];
    }
}
