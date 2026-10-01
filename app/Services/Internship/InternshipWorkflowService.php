<?php

namespace App\Services\Internship;

use App\Models\InternshipApplication;
use App\Models\InternshipDocument;
use App\Models\InternshipStatusHistory;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class InternshipWorkflowService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Create or update draft/submitted application by student.
     */
    public function saveApplication(User $student, array $data, array $uploadedFiles = [], bool $isSubmit = false, ?InternshipApplication $existing = null): InternshipApplication
    {
        return DB::transaction(function () use ($student, $data, $uploadedFiles, $isSubmit, $existing) {
            $initialStatus = $isSubmit ? InternshipApplication::STATUS_SUBMITTED : InternshipApplication::STATUS_DRAFT;
            $oldStatus = $existing ? $existing->status : null;

            $payload = [
                'user_id' => $student->id,
                'study_program_id' => $data['study_program_id'] ?? $student->study_program_id,
                'partner_institution_id' => $data['partner_institution_id'],
                'internship_period_id' => $data['internship_period_id'],
                'student_name' => $data['student_name'] ?? $student->name,
                'student_nim' => $data['student_nim'] ?? ($student->identifier_number ?? '-'),
                'student_phone' => $data['student_phone'] ?? ($student->phone ?? '-'),
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'proposal_title' => $data['proposal_title'] ?? null,
                'internship_plan' => $data['internship_plan'],
            ];

            if ($isSubmit) {
                $payload['status'] = InternshipApplication::STATUS_SUBMITTED;
                $payload['review_notes'] = null; // Clear prior review notes on new submission
            } elseif (! $existing) {
                $payload['status'] = InternshipApplication::STATUS_DRAFT;
            }

            if ($existing) {
                $existing->update($payload);
                $application = $existing;
            } else {
                $application = InternshipApplication::create($payload);
            }

            // Save uploaded documents
            $this->storeDocuments($application, $uploadedFiles);

            // Log status history if submitting
            if ($isSubmit) {
                $isResubmit = ($oldStatus === InternshipApplication::STATUS_INCOMPLETE);
                
                InternshipStatusHistory::create([
                    'internship_application_id' => $application->id,
                    'actor_id' => $student->id,
                    'old_status' => $oldStatus,
                    'new_status' => InternshipApplication::STATUS_SUBMITTED,
                    'reason' => $isResubmit ? 'Mahasiswa melakukan perbaikan dan mengajukan ulang pendaftaran' : 'Pendaftaran magang diajukan oleh mahasiswa',
                    'created_at' => now(),
                ]);

                // Trigger Notification
                $notifType = $isResubmit ? 'RESUBMISSION' : 'SUBMISSION';
                $title = $isResubmit ? 'Pengajuan Ulang Magang' : 'Pendaftaran Magang Baru';
                $msg = "Mahasiswa {$application->student_name} ({$application->student_nim}) telah {$title} untuk instansi {$application->partnerInstitution->name}.";

                // Notify TU Queue
                $this->notificationService->notifyRole(
                    'TU',
                    $title,
                    $msg,
                    $notifType,
                    route('tu.internships.show', $application),
                    $student
                );

                // Notify Student Confirmation
                $this->notificationService->notifyUser(
                    $student,
                    'Pendaftaran Magang Berhasil Diajukan',
                    'Pengajuan magang Anda telah masuk antrean verifikasi Tata Usaha (TU). Silakan pantau status secara berkala.',
                    $notifType,
                    route('internships.show', $application),
                    $student
                );
            }

            return $application;
        });
    }

    /**
     * Store uploaded application documents securely.
     */
    public function storeDocuments(InternshipApplication $application, array $files): void
    {
        $docTypeMap = [
            'document_transcript' => ['TRANSCRIPT', 'Transkrip Nilai Akademik'],
            'document_proposal' => ['PROPOSAL', 'Proposal / Rencana Magang'],
            'document_cv' => ['CV', 'Curriculum Vitae (CV)'],
            'document_parent_consent' => ['PARENT_CONSENT', 'Surat Persetujuan Orang Tua'],
            'document_other' => ['OTHER', 'Dokumen Pendukung Lainnya'],
        ];

        foreach ($files as $fieldKey => $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $typeInfo = $docTypeMap[$fieldKey] ?? ['OTHER', 'Dokumen'];
                $docType = $typeInfo[0];
                $docName = $typeInfo[1];

                // Remove existing document of the same type if replacing
                $existingDoc = $application->documents()->where('document_type', $docType)->first();
                if ($existingDoc) {
                    Storage::disk('local')->delete($existingDoc->file_path);
                    $existingDoc->delete();
                }

                $storedPath = $file->store("internships/{$application->id}/documents", 'local');

                InternshipDocument::create([
                    'internship_application_id' => $application->id,
                    'document_type' => $docType,
                    'document_name' => $docName . ' - ' . $file->getClientOriginalName(),
                    'file_path' => $storedPath,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
                    'is_verified' => false,
                ]);
            }
        }
    }

    /**
     * Process TU review: Lolos / Tidak Lengkap & issue reference letter.
     */
    public function reviewByTu(
        InternshipApplication $application,
        User $tuUser,
        string $decision,
        ?string $reason = null,
        ?string $referenceLetterNumber = null,
        ?UploadedFile $referenceLetterFile = null
    ): InternshipApplication {
        return DB::transaction(function () use ($application, $tuUser, $decision, $reason, $referenceLetterNumber, $referenceLetterFile) {
            if ($application->status !== InternshipApplication::STATUS_SUBMITTED) {
                throw ValidationException::withMessages([
                    'status' => 'Aplikasi ini tidak dalam status Diajukan untuk verifikasi TU.',
                ]);
            }

            $oldStatus = $application->status;

            if ($decision === 'RETURN') {
                if (blank($reason)) {
                    throw ValidationException::withMessages([
                        'reason' => 'Alasan pengembalian berkas wajib diisi agar mahasiswa mengetahui bagian yang perlu diperbaiki.',
                    ]);
                }

                $application->update([
                    'status' => InternshipApplication::STATUS_INCOMPLETE,
                    'review_notes' => $reason,
                ]);

                InternshipStatusHistory::create([
                    'internship_application_id' => $application->id,
                    'actor_id' => $tuUser->id,
                    'old_status' => $oldStatus,
                    'new_status' => InternshipApplication::STATUS_INCOMPLETE,
                    'reason' => $reason,
                    'created_at' => now(),
                ]);

                // Notify Student
                $this->notificationService->notifyUser(
                    $application->student,
                    'Berkas Pendaftaran Magang Perlu Perbaikan',
                    "Verifikasi TU menyatakan berkas tidak lengkap: {$reason}. Silakan perbaiki dan ajukan ulang.",
                    'INCOMPLETE',
                    route('internships.show', $application),
                    $tuUser
                );
            } elseif ($decision === 'PASS') {
                $updateData = [
                    'status' => InternshipApplication::STATUS_TU_VERIFIED,
                    'review_notes' => $reason,
                ];

                // If TU inputs/generates reference letter number
                if (! blank($referenceLetterNumber)) {
                    $updateData['reference_letter_number'] = $referenceLetterNumber;
                    $updateData['reference_letter_issued_at'] = now();
                }

                // If TU uploads scanned/signed reference letter
                if ($referenceLetterFile && $referenceLetterFile->isValid()) {
                    $refPath = $referenceLetterFile->store("internships/{$application->id}/reference_letter", 'local');
                    $updateData['reference_letter_path'] = $refPath;
                }

                $application->update($updateData);

                InternshipStatusHistory::create([
                    'internship_application_id' => $application->id,
                    'actor_id' => $tuUser->id,
                    'old_status' => $oldStatus,
                    'new_status' => InternshipApplication::STATUS_TU_VERIFIED,
                    'reason' => $reason ?: 'Dokumen persyaratan lengkap dan telah divalidasi oleh Tata Usaha.',
                    'metadata' => [
                        'reference_letter_number' => $referenceLetterNumber,
                    ],
                    'created_at' => now(),
                ]);

                // Notify Student
                $this->notificationService->notifyUser(
                    $application->student,
                    'Berkas Magang Lolos Validasi TU',
                    'Berkas pendaftaran magang Anda telah divalidasi oleh TU' . ($referenceLetterNumber ? " dengan Surat Pengantar No. {$referenceLetterNumber}" : '') . '. Tahap selanjutnya adalah Verifikasi Kaprodi.',
                    'APPROVAL',
                    route('internships.show', $application),
                    $tuUser
                );

                // Notify Kaprodi Queue
                $this->notificationService->notifyRole(
                    'KAPRODI',
                    'Antrean Verifikasi Magang Baru (Kaprodi)',
                    "Pendaftaran magang mahasiswa {$application->student_name} telah lolos validasi TU dan menunggu verifikasi Anda.",
                    'APPROVAL',
                    route('kaprodi.internships.show', $application),
                    $tuUser
                );
            } else {
                throw ValidationException::withMessages(['decision' => 'Keputusan verifikasi TU tidak valid.']);
            }

            return $application;
        });
    }

    /**
     * Process Kaprodi verification: Verify or Reject.
     */
    public function reviewByKaprodi(
        InternshipApplication $application,
        User $kaprodiUser,
        string $decision,
        ?string $reason = null
    ): InternshipApplication {
        return DB::transaction(function () use ($application, $kaprodiUser, $decision, $reason) {
            if ($application->status !== InternshipApplication::STATUS_TU_VERIFIED) {
                throw ValidationException::withMessages([
                    'status' => 'Aplikasi ini belum lolos verifikasi TU atau sudah diproses.',
                ]);
            }

            $oldStatus = $application->status;

            if ($decision === 'REJECT') {
                if (blank($reason)) {
                    throw ValidationException::withMessages([
                        'reason' => 'Alasan penolakan oleh Kaprodi wajib diisi.',
                    ]);
                }

                $application->update([
                    'status' => InternshipApplication::STATUS_REJECTED,
                    'review_notes' => $reason,
                ]);

                InternshipStatusHistory::create([
                    'internship_application_id' => $application->id,
                    'actor_id' => $kaprodiUser->id,
                    'old_status' => $oldStatus,
                    'new_status' => InternshipApplication::STATUS_REJECTED,
                    'reason' => $reason,
                    'created_at' => now(),
                ]);

                // Notify Student
                $this->notificationService->notifyUser(
                    $application->student,
                    'Pendaftaran Magang Ditolak oleh Kaprodi',
                    "Pendaftaran magang Anda ditolak oleh Ketua Program Studi: {$reason}",
                    'REJECTION',
                    route('internships.show', $application),
                    $kaprodiUser
                );
            } elseif ($decision === 'VERIFY') {
                $application->update([
                    'status' => InternshipApplication::STATUS_KAPRODI_VERIFIED,
                    'review_notes' => $reason,
                ]);

                InternshipStatusHistory::create([
                    'internship_application_id' => $application->id,
                    'actor_id' => $kaprodiUser->id,
                    'old_status' => $oldStatus,
                    'new_status' => InternshipApplication::STATUS_KAPRODI_VERIFIED,
                    'reason' => $reason ?: 'Pendaftaran magang telah disetujui dan diverifikasi oleh Kaprodi.',
                    'created_at' => now(),
                ]);

                // Notify Student
                $this->notificationService->notifyUser(
                    $application->student,
                    'Pendaftaran Magang Diverifikasi Kaprodi',
                    'Kaprodi telah menyetujui pendaftaran magang Anda. Pengajuan diteruskan ke Wakil Dekan 1 untuk persetujuan akhir.',
                    'APPROVAL',
                    route('internships.show', $application),
                    $kaprodiUser
                );

                // Notify Wadek 1 Queue
                $this->notificationService->notifyRole(
                    'WADEK1',
                    'Antrean Approval Magang (Wadek 1)',
                    "Pendaftaran magang mahasiswa {$application->student_name} ({$application->studyProgram->name}) menunggu persetujuan Anda.",
                    'APPROVAL',
                    route('wadek1.internships.show', $application),
                    $kaprodiUser
                );
            } else {
                throw ValidationException::withMessages(['decision' => 'Keputusan verifikasi Kaprodi tidak valid.']);
            }

            return $application;
        });
    }

    /**
     * Process Wadek 1 final approval: Approve or Reject.
     */
    public function reviewByWadek1(
        InternshipApplication $application,
        User $wadekUser,
        string $decision,
        ?string $reason = null
    ): InternshipApplication {
        return DB::transaction(function () use ($application, $wadekUser, $decision, $reason) {
            if ($application->status !== InternshipApplication::STATUS_KAPRODI_VERIFIED) {
                throw ValidationException::withMessages([
                    'status' => 'Aplikasi ini belum diverifikasi oleh Kaprodi.',
                ]);
            }

            $oldStatus = $application->status;

            if ($decision === 'REJECT') {
                if (blank($reason)) {
                    throw ValidationException::withMessages([
                        'reason' => 'Alasan penolakan oleh Wakil Dekan 1 wajib diisi.',
                    ]);
                }

                $application->update([
                    'status' => InternshipApplication::STATUS_REJECTED,
                    'review_notes' => $reason,
                ]);

                InternshipStatusHistory::create([
                    'internship_application_id' => $application->id,
                    'actor_id' => $wadekUser->id,
                    'old_status' => $oldStatus,
                    'new_status' => InternshipApplication::STATUS_REJECTED,
                    'reason' => $reason,
                    'created_at' => now(),
                ]);

                // Notify Student
                $this->notificationService->notifyUser(
                    $application->student,
                    'Pendaftaran Magang Ditolak oleh Wakil Dekan 1',
                    "Pendaftaran magang Anda tidak disetujui oleh Wakil Dekan 1: {$reason}",
                    'REJECTION',
                    route('internships.show', $application),
                    $wadekUser
                );
            } elseif ($decision === 'APPROVE') {
                $application->update([
                    'status' => InternshipApplication::STATUS_APPROVED,
                    'review_notes' => $reason,
                ]);

                InternshipStatusHistory::create([
                    'internship_application_id' => $application->id,
                    'actor_id' => $wadekUser->id,
                    'old_status' => $oldStatus,
                    'new_status' => InternshipApplication::STATUS_APPROVED,
                    'reason' => $reason ?: 'Pendaftaran magang telah disetujui resmi oleh Wakil Dekan 1.',
                    'created_at' => now(),
                ]);

                // Notify Student
                $this->notificationService->notifyUser(
                    $application->student,
                    'Selamat! Pendaftaran Magang Disetujui Penuh',
                    'Pendaftaran magang Anda telah resmi disetujui oleh Wakil Dekan 1. Selamat memulai program magang!',
                    'APPROVAL',
                    route('internships.show', $application),
                    $wadekUser
                );
            } else {
                throw ValidationException::withMessages(['decision' => 'Keputusan Wadek 1 tidak valid.']);
            }

            return $application;
        });
    }

    /**
     * Upload Surat Balasan Instansi by Student.
     */
    public function uploadAcceptanceLetter(
        InternshipApplication $application,
        User $student,
        UploadedFile $file
    ): InternshipApplication {
        return DB::transaction(function () use ($application, $student, $file) {
            if ($application->user_id !== $student->id) {
                throw ValidationException::withMessages([
                    'auth' => 'Anda tidak memiliki hak akses untuk mengunggah surat balasan pada aplikasi ini.',
                ]);
            }

            if (! $application->canUploadAcceptanceLetter()) {
                throw ValidationException::withMessages([
                    'status' => 'Surat balasan instansi hanya dapat diunggah setelah surat pengantar diterbitkan.',
                ]);
            }

            // Remove previous acceptance letter file if exists
            if ($application->acceptance_letter_path) {
                Storage::disk('local')->delete($application->acceptance_letter_path);
            }

            $storedPath = $file->store("internships/{$application->id}/acceptance_letter", 'local');

            $application->update([
                'acceptance_letter_path' => $storedPath,
                'acceptance_letter_uploaded_at' => now(),
            ]);

            // Notify TU that student has uploaded company response
            $this->notificationService->notifyRole(
                'TU',
                'Surat Balasan Instansi Diunggah',
                "Mahasiswa {$application->student_name} ({$application->student_nim}) telah mengunggah surat balasan dari {$application->partnerInstitution->name}.",
                'SUBMISSION',
                route('tu.internships.show', $application),
                $student
            );

            return $application;
        });
    }
}
