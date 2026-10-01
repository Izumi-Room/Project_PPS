<?php

namespace App\Http\Requests\CourseConversion;

use Illuminate\Foundation\Http\FormRequest;

class ReviewConversionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:APPROVE,VERIFY,REJECT'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'rejection_reason' => ['required_if:action,REJECT', 'nullable', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Aksi persetujuan wajib dipilih.',
            'action.in' => 'Aksi persetujuan tidak valid.',
            'rejection_reason.required_if' => 'Alasan penolakan wajib diisi jika menolak pengajuan konversi.',
            'rejection_reason.min' => 'Alasan penolakan minimal 5 karakter.',
        ];
    }
}
