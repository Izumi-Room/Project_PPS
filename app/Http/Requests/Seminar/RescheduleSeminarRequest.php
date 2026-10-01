<?php

namespace App\Http\Requests\Seminar;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleSeminarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->hasRole('DOSEN_MK') || $this->user()->hasRole('SUPERADMIN'));
    }

    public function rules(): array
    {
        return [
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'scheduled_time' => ['required', 'string', 'max:50'],
            'location_or_link' => ['required', 'string', 'max:255'],
            'information' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'scheduled_date.required' => 'Tanggal baru wajib diisi.',
            'scheduled_time.required' => 'Waktu pelaksanaan wajib diisi.',
            'location_or_link.required' => 'Tempat atau tautan seminar wajib diisi.',
        ];
    }
}
