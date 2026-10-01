<?php

namespace App\Http\Requests\Seminar;

use Illuminate\Foundation\Http\FormRequest;

class DecideSeminarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->hasRole('DOSEN_MK') || $this->user()->hasRole('SUPERADMIN'));
    }

    public function rules(): array
    {
        return [
            'course_conversion_id' => ['required', 'integer', 'exists:course_conversions,id'],
            'is_required' => ['required', 'boolean'],
            'scheduled_date' => ['nullable', 'required_if:is_required,1,true', 'date', 'after_or_equal:today'],
            'scheduled_time' => ['nullable', 'required_if:is_required,1,true', 'string', 'max:50'],
            'location_or_link' => ['nullable', 'required_if:is_required,1,true', 'string', 'max:255'],
            'information' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_conversion_id.required' => 'Konversi MK wajib dipilih.',
            'is_required.required' => 'Keputusan seminar wajib ditentukan (Perlu / Tidak Perlu).',
            'scheduled_date.required_if' => 'Tanggal seminar wajib diisi jika seminar diperlukan.',
            'scheduled_time.required_if' => 'Waktu pelaksanaan wajib diisi jika seminar diperlukan.',
            'location_or_link.required_if' => 'Tempat atau tautan seminar wajib diisi jika seminar diperlukan.',
        ];
    }
}
