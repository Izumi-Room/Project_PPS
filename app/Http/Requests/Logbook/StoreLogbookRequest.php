<?php

namespace App\Http\Requests\Logbook;

use Illuminate\Foundation\Http\FormRequest;

class StoreLogbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'internship_application_id' => ['required', 'integer', 'exists:internship_applications,id'],
            'week_number' => ['required', 'integer', 'min:1', 'max:52'],
            'activity_date' => ['required', 'date'],
            'activity_title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'], // max 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'internship_application_id.required' => 'Pendaftaran magang wajib dipilih.',
            'week_number.required' => 'Minggu ke berapa kegiatan wajib diisi.',
            'activity_date.required' => 'Tanggal kegiatan wajib diisi.',
            'activity_title.required' => 'Judul kegiatan wajib diisi.',
            'description.required' => 'Deskripsi rincian kegiatan wajib diisi.',
            'description.min' => 'Deskripsi kegiatan minimal 10 karakter.',
            'attachment.mimes' => 'Lampiran harus berupa file PDF, JPG, PNG, DOC, atau DOCX.',
            'attachment.max' => 'Ukuran lampiran maksimal 5MB.',
        ];
    }
}
