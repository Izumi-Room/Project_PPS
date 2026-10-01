<?php

namespace App\Http\Requests\Internship;

use App\Models\InternshipApplication;
use App\Models\InternshipPeriod;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInternshipApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $application = $this->route('internship') ?? $this->route('application');
        return $this->user()->hasAnyRole(['MHS', 'SUPERADMIN'])
            && ($this->user()->hasRole('SUPERADMIN') || $application->user_id === $this->user()->id);
    }

    public function rules(): array
    {
        $application = $this->route('internship') ?? $this->route('application');
        $isSubmit = $this->input('action') === 'submit';

        // Check if application already has documents attached
        $hasTranscript = $application && $application->documents()->where('document_type', 'TRANSCRIPT')->exists();
        $hasProposal = $application && $application->documents()->where('document_type', 'PROPOSAL')->exists();

        return [
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

            'document_transcript' => [
                ($isSubmit && ! $hasTranscript) ? 'required' : 'nullable',
                'file',
                'mimes:pdf,png,jpg,jpeg',
                'max:10240',
            ],
            'document_proposal' => [
                ($isSubmit && ! $hasProposal) ? 'required' : 'nullable',
                'file',
                'mimes:pdf,doc,docx',
                'max:10240',
            ],
            'document_cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'document_parent_consent' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
            'document_other' => ['nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg', 'max:10240'],
        ];
    }
}
