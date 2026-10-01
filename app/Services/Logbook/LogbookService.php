<?php

namespace App\Services\Logbook;

use App\Models\InternshipApplication;
use App\Models\InternshipLogbook;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class LogbookService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Mahasiswa creates a weekly logbook entry.
     *
     * @throws ValidationException|AuthorizationException
     */
    public function createLogbook(User $student, array $data, ?UploadedFile $attachment = null): InternshipLogbook
    {
        return DB::transaction(function () use ($student, $data, $attachment) {
            $application = InternshipApplication::findOrFail($data['internship_application_id']);

            // Ownership check
            if ($application->user_id !== $student->id) {
                throw new AuthorizationException('Anda tidak berhak membuat logbook untuk pendaftaran magang ini.');
            }

            // Prerequisite check: Must be approved application
            if ($application->status !== InternshipApplication::STATUS_APPROVED) {
                throw ValidationException::withMessages([
                    'internship_application_id' => 'Logbook hanya dapat diisi jika pendaftaran magang telah disetujui resmi.',
                ]);
            }

            $attachmentPath = null;
            if ($attachment) {
                $attachmentPath = $attachment->store('logbook_attachments', 'public');
            }

            $logbook = InternshipLogbook::create([
                'internship_application_id' => $application->id,
                'user_id' => $student->id,
                'week_number' => (int) $data['week_number'],
                'activity_date' => $data['activity_date'],
                'activity_title' => $data['activity_title'],
                'description' => $data['description'],
                'attachment_path' => $attachmentPath,
            ]);

            // Notify advisor if assigned
            $advisor = $application->advisor;
            if ($advisor && $application->advisor_status === InternshipApplication::STATUS_ADVISOR_ACCEPTED) {
                $this->notificationService->notifyUser(
                    $advisor,
                    'Logbook Baru Mahasiswa Bimbingan',
                    "Mahasiswa {$student->name} mengisi logbook Minggu ke-{$logbook->week_number} ({$logbook->activity_title}).",
                    'INFO',
                    route('academic.logbooks.student', $application->id),
                    $student
                );
            }

            return $logbook;
        });
    }

    /**
     * Mahasiswa updates a weekly logbook entry.
     *
     * @throws ValidationException|AuthorizationException
     */
    public function updateLogbook(InternshipLogbook $logbook, User $student, array $data, ?UploadedFile $attachment = null): InternshipLogbook
    {
        return DB::transaction(function () use ($logbook, $student, $data, $attachment) {
            if ($logbook->user_id !== $student->id) {
                throw new AuthorizationException('Anda tidak berhak mengubah logbook ini.');
            }

            $updateData = [
                'week_number' => (int) $data['week_number'],
                'activity_date' => $data['activity_date'],
                'activity_title' => $data['activity_title'],
                'description' => $data['description'],
            ];

            if ($attachment) {
                if ($logbook->attachment_path && Storage::disk('public')->exists($logbook->attachment_path)) {
                    Storage::disk('public')->delete($logbook->attachment_path);
                }
                $updateData['attachment_path'] = $attachment->store('logbook_attachments', 'public');
            }

            $logbook->update($updateData);

            return $logbook;
        });
    }

    /**
     * Mahasiswa deletes a logbook entry.
     *
     * @throws AuthorizationException
     */
    public function deleteLogbook(InternshipLogbook $logbook, User $student): void
    {
        if ($logbook->user_id !== $student->id && ! $student->hasRole('SUPERADMIN')) {
            throw new AuthorizationException('Anda tidak berhak menghapus logbook ini.');
        }

        if ($logbook->attachment_path && Storage::disk('public')->exists($logbook->attachment_path)) {
            Storage::disk('public')->delete($logbook->attachment_path);
        }

        $logbook->delete();
    }

    /**
     * Dosen Pembimbing adds feedback / notes to student logbook.
     *
     * @throws ValidationException|AuthorizationException
     */
    public function provideFeedback(InternshipLogbook $logbook, User $dosbing, string $feedback): InternshipLogbook
    {
        return DB::transaction(function () use ($logbook, $dosbing, $feedback) {
            $application = $logbook->internshipApplication;
            $isSuperadmin = $dosbing->hasRole('SUPERADMIN');
            $isAssignedAdvisor = ($application->advisor_id === $dosbing->id && $application->advisor_status === InternshipApplication::STATUS_ADVISOR_ACCEPTED);

            if (! $isSuperadmin && (! $dosbing->hasRole('DOSBING') || ! $isAssignedAdvisor)) {
                throw new AuthorizationException('Hanya Dosen Pembimbing mahasiswa bersangkutan yang dapat memberikan catatan review.');
            }

            if (blank($feedback)) {
                throw ValidationException::withMessages([
                    'feedback' => 'Catatan review / feedback wajib diisi.',
                ]);
            }

            $logbook->update([
                'dosbing_feedback' => $feedback,
                'dosbing_reviewed_at' => now(),
                'reviewed_by' => $dosbing->id,
            ]);

            // Notify Mahasiswa
            $this->notificationService->notifyUser(
                $logbook->student,
                'Catatan Review Logbook',
                "Dosen Pembimbing memberikan catatan untuk logbook Minggu ke-{$logbook->week_number}.",
                'INFO',
                route('logbooks.show', $logbook->id),
                $dosbing
            );

            return $logbook;
        });
    }
}
