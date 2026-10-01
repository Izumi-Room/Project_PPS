<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class KaprodiReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['KAPRODI', 'SUPERADMIN']);
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:VERIFY,REJECT'],
            'reason' => ['required_if:decision,REJECT', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'decision.required' => 'Keputusan verifikasi Kaprodi wajib dipilih (Verifikasi / Tolak).',
            'reason.required_if' => 'Alasan penolakan oleh Kaprodi wajib diisi.',
        ];
    }
}
