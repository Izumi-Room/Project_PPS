<?php

namespace App\Services\Submission;

use App\Models\CourseConversion;
use App\Models\StudentSubmission;
use App\Models\SubmissionComponent;
use App\Models\SubmissionVersion;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DynamicSubmissionService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Dosen MK creates a dynamic submission component for a course.
     *
     * @throws ValidationException|AuthorizationException
     */
    public function createComponent(User $dosenMk, array $data): SubmissionComponent
    {
        if (! $dosenMk->hasAnyRole(['DOSEN_MK', 'SUPERADMIN'])) {
            throw new AuthorizationException('Hanya Dosen Pengampu MK yang berhak membuat komponen pengumpulan.');
        }

        return SubmissionComponent::create([
            'course_id' => $data['course_id'],
            'created_by' => $dosenMk->id,
            'name' => $data['name'],
            'submission_type' => strtoupper($data['submission_type'] ?? 'FILE'),
            'deadline' => $data['deadline'],
            'weight' => $data['weight'] ?? 100,
            'is_required' => (bool) ($data['is_required'] ?? true),
            'instructions' => $data['instructions'] ?? null,
            'is_active' => true,
        ]);
    }

    /**
     * Dosen MK updates a submission component.
     *
     * @throws AuthorizationException
     */
    public function updateComponent(SubmissionComponent $component, User $dosenMk, array $data): SubmissionComponent
    {
        if (! $dosenMk->hasAnyRole(['DOSEN_MK', 'SUPERADMIN'])) {
            throw new AuthorizationException('Anda tidak berhak mengubah komponen pengumpulan ini.');
        }

        $component->update([
            'name' => $data['name'] ?? $component->name,
            'submission_type' => strtoupper($data['submission_type'] ?? $component->submission_type),
            'deadline' => $data['deadline'] ?? $component->deadline,
            'weight' => $data['weight'] ?? $component->weight,
            'is_required' => isset($data['is_required']) ? (bool) $data['is_required'] : $component->is_required,
            'instructions' => array_key_exists('instructions', $data) ? $data['instructions'] : $component->instructions,
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $component->is_active,
        ]);

        return $component;
    }

    /**
     * Dosen MK deletes or archives a submission component.
     *
     * @throws AuthorizationException
     */
    public function deleteComponent(SubmissionComponent $component, User $dosenMk): void
    {
        if (! $dosenMk->hasAnyRole(['DOSEN_MK', 'SUPERADMIN'])) {
            throw new AuthorizationException('Anda tidak berhak menghapus komponen pengumpulan ini.');
        }

        if ($component->submissions()->exists()) {
            $component->update(['is_active' => false]);
        } else {
            $component->delete();
        }
    }

    /**
     * Mahasiswa submits or resubmits work for a component.
     * If resubmitting, previous files are NOT deleted; a new version is created.
     *
     * @throws ValidationException|AuthorizationException
     */
    public function submitWork(
        User $student,
        SubmissionComponent $component,
        CourseConversion $conversion,
        array $data,
        ?UploadedFile $file = null
    ): StudentSubmission {
        return DB::transaction(function () use ($student, $component, $conversion, $data, $file) {
            // 1. Authorization: Only the student owner of this conversion
            if ($conversion->user_id !== $student->id) {
                throw new AuthorizationException('Anda tidak berhak mengumpulkan tugas untuk konversi mata kuliah ini.');
            }

            // 2. Course match check
            if ($component->course_id !== $conversion->course_id) {
                throw ValidationException::withMessages([
                    'component' => 'Komponen pengumpulan tidak sesuai dengan mata kuliah konversi yang dipilih.',
                ]);
            }

            // 3. Deadline Check
            if ($component->isPassedDeadline()) {
                throw ValidationException::withMessages([
                    'deadline' => 'Batas waktu pengumpulan (deadline) untuk komponen ini telah berakhir pada ' . $component->deadline->format('d/m/Y H:i') . '.',
                ]);
            }

            // 4. Validate file or link according to submission_type & is_required
            $linkUrl = ! empty($data['link_url']) ? trim($data['link_url']) : null;
            $hasFile = ($file !== null);
            $hasLink = ! empty($linkUrl);

            if ($component->is_required && ! $hasFile && ! $hasLink) {
                throw ValidationException::withMessages([
                    'submission' => 'Komponen ini wajib mengunggah file atau mencantumkan tautan link tugas.',
                ]);
            }

            if ($component->submission_type === 'FILE' && ! $hasFile && $component->is_required) {
                throw ValidationException::withMessages([
                    'file' => 'Wajib mengunggah file dokumen untuk komponen pengumpulan ini.',
                ]);
            }

            if ($component->submission_type === 'LINK' && ! $hasLink && $component->is_required) {
                throw ValidationException::withMessages([
                    'link_url' => 'Wajib menyertakan tautan link URL untuk komponen pengumpulan ini.',
                ]);
            }

            // 5. Find or create StudentSubmission record
            $submission = StudentSubmission::firstOrCreate(
                [
                    'submission_component_id' => $component->id,
                    'user_id' => $student->id,
                ],
                [
                    'course_conversion_id' => $conversion->id,
                    'status' => StudentSubmission::STATUS_NOT_SUBMITTED,
                    'current_version' => 0,
                ]
            );

            // Cannot resubmit if already approved
            if ($submission->isApproved()) {
                throw ValidationException::withMessages([
                    'status' => 'Pengumpulan tugas ini telah disetujui resmi dan tidak dapat diubah lagi.',
                ]);
            }

            $isResubmission = ($submission->current_version > 0);
            $newVersionNumber = $submission->current_version + 1;

            // 6. Store file safely without deleting past version files
            $filePath = null;
            $fileName = null;
            $fileSize = null;

            if ($file) {
                $filePath = $file->store("submissions/comp_{$component->id}/user_{$student->id}/v{$newVersionNumber}", 'public');
                $fileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
            }

            // 7. Create SubmissionVersion
            $version = SubmissionVersion::create([
                'student_submission_id' => $submission->id,
                'version_number' => $newVersionNumber,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'link_url' => $linkUrl,
                'student_notes' => $data['student_notes'] ?? null,
                'submitted_at' => now(),
                'status' => $isResubmission ? StudentSubmission::STATUS_RESUBMITTED : StudentSubmission::STATUS_SUBMITTED,
            ]);

            // 8. Update StudentSubmission status
            $submission->update([
                'course_conversion_id' => $conversion->id,
                'status' => $isResubmission ? StudentSubmission::STATUS_RESUBMITTED : StudentSubmission::STATUS_SUBMITTED,
                'current_version' => $newVersionNumber,
                'latest_submitted_at' => now(),
                'latest_feedback' => null, // clear feedback for new version awaiting review
            ]);

            // 9. Notify Dosen MK
            $this->notificationService->notifyRole(
                'DOSEN_MK',
                $isResubmission ? 'Pengiriman Ulang Tugas Mahasiswa' : 'Pengumpulan Tugas Mahasiswa Baru',
                "Mahasiswa {$student->name} mengumpulkan {$component->name} ({$component->course->code}) - Versi {$newVersionNumber}.",
                'INFO',
                route('dosen-mk.submissions.show', $submission->id),
                $student
            );

            return $submission;
        });
    }

    /**
     * Dosen MK reviews the student submission (Approve or Request Revision).
     *
     * @throws ValidationException|AuthorizationException
     */
    public function reviewSubmission(StudentSubmission $submission, User $dosenMk, string $action, ?string $feedback = null): StudentSubmission
    {
        return DB::transaction(function () use ($submission, $dosenMk, $action, $feedback) {
            if (! $dosenMk->hasAnyRole(['DOSEN_MK', 'SUPERADMIN'])) {
                throw new AuthorizationException('Hanya Dosen Pengampu MK yang berhak mereview pengumpulan tugas.');
            }

            $action = strtoupper(trim($action));

            if ($action === 'APPROVE') {
                $submission->update([
                    'status' => StudentSubmission::STATUS_APPROVED,
                    'latest_feedback' => $feedback,
                    'reviewed_by' => $dosenMk->id,
                    'reviewed_at' => now(),
                ]);

                // Update latest version record
                $submission->latestVersion?->update([
                    'status' => StudentSubmission::STATUS_APPROVED,
                    'reviewer_feedback' => $feedback,
                    'reviewed_by' => $dosenMk->id,
                    'reviewed_at' => now(),
                ]);

                // Notify student
                $this->notificationService->notifyUser(
                    $submission->student,
                    'Tugas Disetujui Dosen MK',
                    "Pengumpulan tugas {$submission->component->name} telah diperiksa dan disetujui oleh Dosen MK.",
                    'SUCCESS',
                    route('submissions.show', $submission->id),
                    $dosenMk
                );
            } elseif ($action === 'REVISION') {
                // Catatan / feedback adalah WAJIB saat request revision
                if (blank($feedback)) {
                    throw ValidationException::withMessages([
                        'feedback' => 'Catatan revisi wajib diisi untuk menginstruksikan perbaikan kepada mahasiswa.',
                    ]);
                }

                $submission->update([
                    'status' => StudentSubmission::STATUS_REVISION_NEEDED,
                    'latest_feedback' => $feedback,
                    'reviewed_by' => $dosenMk->id,
                    'reviewed_at' => now(),
                ]);

                $submission->latestVersion?->update([
                    'status' => StudentSubmission::STATUS_REVISION_NEEDED,
                    'reviewer_feedback' => $feedback,
                    'reviewed_by' => $dosenMk->id,
                    'reviewed_at' => now(),
                ]);

                // Notify student
                $this->notificationService->notifyUser(
                    $submission->student,
                    'Perbaikan Tugas Diperlukan (Revisi)',
                    "Dosen MK meminta perbaikan pada tugas {$submission->component->name}. Catatan: {$feedback}",
                    'WARNING',
                    route('submissions.show', $submission->id),
                    $dosenMk
                );
            } else {
                throw ValidationException::withMessages([
                    'action' => 'Aksi review tidak valid. Pilih APPROVE atau REVISION.',
                ]);
            }

            return $submission;
        });
    }
}
