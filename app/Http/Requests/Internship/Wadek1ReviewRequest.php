<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class Wadek1ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['WADEK1', 'SUPERADMIN']);
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:APPROVE,REJECT'],
            'reason' => ['required_if:decision,REJECT', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'decision.required' => 'Keputusan persetujuan Wadek 1 wajib dipilih (Setujui / Tolak).',
            'reason.required_if' => 'Alasan penolakan oleh Wakil Dekan 1 wajib diisi.',
        ];
    }
}
