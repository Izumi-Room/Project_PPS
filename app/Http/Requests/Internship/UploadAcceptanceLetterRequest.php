<?php

namespace App\Http\Requests\Internship;

use Illuminate\Foundation\Http\FormRequest;

class UploadAcceptanceLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['MHS', 'SUPERADMIN']);
    }

    public function rules(): array
    {
        return [
            'acceptance_letter_file' => [
                'required',
                'file',
                'mimes:pdf,png,jpg,jpeg',
                'max:10240', // 10MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'acceptance_letter_file.required' => 'File surat balasan instansi wajib dipilih.',
            'acceptance_letter_file.mimes' => 'Format surat balasan harus berupa PDF, PNG, JPG, atau JPEG.',
            'acceptance_letter_file.max' => 'Ukuran file surat balasan maksimal 10MB.',
        ];
    }
}
