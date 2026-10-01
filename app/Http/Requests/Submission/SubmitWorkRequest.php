<?php

namespace App\Http\Requests\Submission;

use Illuminate\Foundation\Http\FormRequest;

class SubmitWorkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_conversion_id' => ['required', 'integer', 'exists:course_conversions,id'],
            'file' => ['nullable', 'file', 'mimes:pdf,zip,rar,tar,gz,7z,jpg,png,doc,docx,ppt,pptx', 'max:20480'], // max 20MB
            'link_url' => ['nullable', 'url', 'max:1000'],
            'student_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_conversion_id.required' => 'Mata kuliah konversi wajib dipilih.',
            'file.max' => 'Ukuran file dokumen/berkas maksimal 20MB.',
            'link_url.url' => 'Format tautan URL tidak valid (harus diawali http:// atau https://).',
        ];
    }
}
