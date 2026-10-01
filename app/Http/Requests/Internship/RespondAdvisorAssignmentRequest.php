<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class RespondAdvisorAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['DOSBING', 'SUPERADMIN']);
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:ACCEPT,REJECT'],
            'reason' => ['required_if:decision,REJECT', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'decision.required' => 'Keputusan penerimaan bimbingan wajib dipilih (Terima / Tolak).',
            'reason.required_if' => 'Alasan penolakan bimbingan wajib diisi jika Anda menolak penugasan.',
        ];
    }
}
