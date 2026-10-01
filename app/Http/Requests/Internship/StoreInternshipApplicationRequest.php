<?php

namespace App\Http\Requests\Internship;

use App\Models\InternshipPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInternshipApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['MHS', 'SUPERADMIN']);
    }

    public function rules(): array
    {
        $isSubmit = $this->input('action') === 'submit';

        $rules = [
            'action' => ['required', 'in:draft,submit'],
            'study_program_id' => ['nullable', 'exists:study_programs,id'],
            'partner_institution_id' => ['required', 'exists:partner_institutions,id'],
            'internship_period_id' => [
                'required',
                'exists:internship_periods,id',
                function ($attribute, $value, $fail) {
                    $period = InternshipPeriod::find($value);
                    if ($period && ! $period->is_active) {
                        $fail('Periode magang yang dipilih tidak aktif.');
                    }
                },
            ],
            'student_name' => ['required', 'string', 'max:255'],
            'student_nim' => ['required', 'string', 'max:50'],
            'student_phone' => ['required', 'string', 'max:30'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'proposal_title' => ['nullable', 'string', 'max:255'],
            'internship_plan' => ['required', 'string', 'min:10'],

            // Documents validation
            'document_transcript' => [
                $isSubmit ? 'required' : 'nullable',
                'file',
                'mimes:pdf,png,jpg,jpeg',
                'max:10240', // 10MB
            ],
            'document_proposal' => [
                $isSubmit ? 'required' : 'nullable',
                'file',
                'mimes:pdf,doc,docx',
                'max:10240',
            ],
            'document_cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'document_parent_consent' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
            'document_other' => ['nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg', 'max:10240'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'document_transcript.required' => 'Transkrip nilai akademik wajib diunggah saat mengajukan pendaftaran.',
            'document_proposal.required' => 'Proposal atau rencana magang wajib diunggah saat mengajukan pendaftaran.',
            'document_transcript.mimes' => 'Format file transkrip harus berupa PDF, PNG, JPG, atau JPEG.',
            'document_proposal.mimes' => 'Format file proposal harus berupa PDF, DOC, atau DOCX.',
            'document_transcript.max' => 'Ukuran file transkrip maksimal 10MB.',
            'document_proposal.max' => 'Ukuran file proposal maksimal 10MB.',
            'end_date.after_or_equal' => 'Tanggal selesai magang harus sama dengan atau setelah tanggal mulai.',
            'internship_plan.min' => 'Rencana magang minimal berisi 10 karakter penjelasan kegiatan.',
        ];
    }
}
