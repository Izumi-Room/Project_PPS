<?php

namespace App\Http\Requests\Seminar;

use Illuminate\Foundation\Http\FormRequest;

class RejectSeminarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->hasRole('WADEK1') || $this->user()->hasRole('SUPERADMIN'));
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Catatan alasan penolakan wajib diisi.',
            'rejection_reason.min' => 'Alasan penolakan minimal berisi 5 karakter.',
        ];
    }
}
