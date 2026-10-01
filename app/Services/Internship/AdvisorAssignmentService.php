<?php

namespace App\Services\Internship;

use App\Models\InternshipAdvisorAssignment;
use App\Models\InternshipApplication;
use App\Models\InternshipStatusHistory;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdvisorAssignmentService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Assign advisor to an approved internship application.
     *
     * @throws ValidationException
     */
    public function assignAdvisor(
        InternshipApplication $application,
        User $advisor,
        User $kaprodi,
        ?string $notes = null
    ): InternshipAdvisorAssignment {
        return DB::transaction(function () use ($application, $advisor, $kaprodi, $notes) {
            // 1. Prerequisite Rule: Application must be approved by Wadek 1
            if ($application->status !== InternshipApplication::STATUS_APPROVED) {
                throw ValidationException::withMessages([
                    'application' => 'Dosen pembimbing hanya dapat ditentukan untuk pendaftaran magang yang telah disetujui resmi oleh Wakil Dekan 1.',
                ]);
            }

            // 2. Duplicate Assignment Prevention: Check if active assignment exists
            if (in_array($application->advisor_status, [InternshipApplication::STATUS_ADVISOR_PENDING, InternshipApplication::STATUS_ADVISOR_ACCEPTED], true)) {
                throw ValidationException::withMessages([
                    'advisor' => 'Pendaftaran magang ini sudah memiliki penugasan dosen pembimbing yang sedang berjalan atau telah diterima.',
                ]);
            }

            $hasActiveAssignment = $application->advisorAssignments()
                ->whereIn('status', [InternshipAdvisorAssignment::STATUS_PENDING, InternshipAdvisorAssignment::STATUS_ACCEPTED])
                ->exists();

            if ($hasActiveAssignment) {
                throw ValidationException::withMessages([
                    'advisor' => 'Pendaftaran magang ini sudah memiliki penugasan dosen pembimbing aktif dalam antrean.',
                ]);
            }

            // 3. Role Validation: Selected user must have DOSBING role
            if (! $advisor->hasRole('DOSBING')) {
                throw ValidationException::withMessages([
                    'advisor_id' => 'Pengguna yang dipilih tidak memiliki peran sebagai Dosen Pembimbing (DOSBING).',
                ]);
            }

            // 4. Create Assignment Record
            $assignment = InternshipAdvisorAssignment::create([
                'internship_application_id' => $application->id,
                'advisor_id' => $advisor->id,
                'assigned_by' => $kaprodi->id,
                'status' => InternshipAdvisorAssignment::STATUS_PENDING,
                'assigned_at' => now(),
                'notes' => $notes,
            ]);

            // 5. Update Application Advisor State
            $application->update([
                'advisor_id' => $advisor->id,
                'advisor_status' => InternshipApplication::STATUS_ADVISOR_PENDING,
            ]);

            // 6. Log Status History
            InternshipStatusHistory::create([
                'internship_application_id' => $application->id,
                'actor_id' => $kaprodi->id,
                'old_status' => $application->status,
                'new_status' => $application->status,
                'reason' => "Kaprodi mengajukan penugasan Dosen Pembimbing: {$advisor->name}" . ($advisor->identifier_number ? " (NIDN: {$advisor->identifier_number})" : ''),
                'metadata' => [
                    'assignment_id' => $assignment->id,
                    'advisor_id' => $advisor->id,
                ],
                'created_at' => now(),
            ]);

            // 7. Dispatch Notifications
            // Notify Dosen
            $this->notificationService->notifyUser(
                $advisor,
                'Penugasan Bimbingan Magang Baru',
                "Anda telah ditugaskan oleh Kaprodi untuk membimbing mahasiswa {$application->student_name} ({$application->student_nim}) pada instansi {$application->partnerInstitution->name}. Silakan tinjau dan konfirmasi kesediaan Anda.",
                'SUBMISSION',
                route('academic.advisor-assignments.index'),
                $kaprodi
            );

            // Notify Mahasiswa
            $this->notificationService->notifyUser(
                $application->student,
                'Dosen Pembimbing Magang Ditentukan',
                "Kaprodi telah mengajukan {$advisor->name} sebagai Dosen Pembimbing magang Anda. Menunggu konfirmasi penerimaan dari dosen yang bersangkutan.",
                'SUBMISSION',
                route('internships.show', $application),
                $kaprodi
            );

            return $assignment;
        });
    }

    /**
     * Respond to advisor assignment (Accept or Reject).
     *
     * @throws AuthorizationException|ValidationException
     */
    public function respondAssignment(
        InternshipAdvisorAssignment $assignment,
        User $actor,
        string $decision,
        ?string $reason = null
    ): InternshipAdvisorAssignment {
        return DB::transaction(function () use ($assignment, $actor, $decision, $reason) {
            // 1. Authorization: Only the assigned lecturer (or superadmin) can respond
            if ($actor->id !== $assignment->advisor_id && ! $actor->hasRole('SUPERADMIN')) {
                throw new AuthorizationException('Akses ditolak: Anda hanya dapat menanggapi penugasan bimbingan yang ditujukan kepada akun Anda.');
            }

            // 2. Validate current assignment state
            if ($assignment->status !== InternshipAdvisorAssignment::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'status' => 'Penugasan bimbingan ini telah direspon sebelumnya dan tidak dapat diubah.',
                ]);
            }

            $application = $assignment->application;

            if ($decision === 'ACCEPT') {
                $assignment->update([
                    'status' => InternshipAdvisorAssignment::STATUS_ACCEPTED,
                    'responded_at' => now(),
                ]);

                $application->update([
                    'advisor_id' => $assignment->advisor_id,
                    'advisor_status' => InternshipApplication::STATUS_ADVISOR_ACCEPTED,
                ]);

                // Log History
                InternshipStatusHistory::create([
                    'internship_application_id' => $application->id,
                    'actor_id' => $actor->id,
                    'old_status' => $application->status,
                    'new_status' => $application->status,
                    'reason' => "Dosen Pembimbing {$actor->name} MENERIMA penugasan bimbingan magang.",
                    'created_at' => now(),
                ]);

                // Notify Mahasiswa
                $this->notificationService->notifyUser(
                    $application->student,
                    'Dosen Pembimbing Menerima Bimbingan',
                    "Bapak/Ibu {$actor->name} telah MENERIMA penugasan bimbingan magang Anda. Bimbingan kini resmi aktif.",
                    'APPROVAL',
                    route('internships.show', $application),
                    $actor
                );

                // Notify Kaprodi (who assigned or study program head)
                $this->notificationService->notifyRole(
                    'KAPRODI',
                    'Penugasan Dosen Pembimbing Diterima',
                    "Dosen {$actor->name} telah menerima bimbingan untuk mahasiswa {$application->student_name} ({$application->student_nim}).",
                    'APPROVAL',
                    route('kaprodi.advisors.index'),
                    $actor
                );
            } elseif ($decision === 'REJECT') {
                if (blank($reason)) {
                    throw ValidationException::withMessages([
                        'reason' => 'Alasan penolakan bimbingan wajib diisi.',
                    ]);
                }

                $assignment->update([
                    'status' => InternshipAdvisorAssignment::STATUS_REJECTED,
                    'rejection_reason' => $reason,
                    'responded_at' => now(),
                ]);

                // Clear advisor from application and return to Kaprodi queue
                $application->update([
                    'advisor_id' => null,
                    'advisor_status' => InternshipApplication::STATUS_ADVISOR_REJECTED,
                ]);

                // Log History
                InternshipStatusHistory::create([
                    'internship_application_id' => $application->id,
                    'actor_id' => $actor->id,
                    'old_status' => $application->status,
                    'new_status' => $application->status,
                    'reason' => "Dosen Pembimbing {$actor->name} MENOLAK penugasan bimbingan: {$reason}",
                    'created_at' => now(),
                ]);

                // Notify Kaprodi: return to Kaprodi queue for reassignment
                $this->notificationService->notifyRole(
                    'KAPRODI',
                    'Penugasan Bimbingan Ditolak oleh Dosen',
                    "Dosen {$actor->name} menolak penugasan bimbingan untuk mahasiswa {$application->student_name} ({$application->student_nim}) dengan alasan: '{$reason}'. Mahasiswa telah kembali ke antrean siap penentuan dosen pembimbing.",
                    'REJECTION',
                    route('kaprodi.advisors.show', $application),
                    $actor
                );

                // Notify Mahasiswa
                $this->notificationService->notifyUser(
                    $application->student,
                    'Pembaruan Status Dosen Pembimbing',
                    "Dosen pembimbing yang diajukan berhalangan: '{$reason}'. Kaprodi akan menentukan dosen pembimbing pengganti.",
                    'REJECTION',
                    route('internships.show', $application),
                    $actor
                );
            } else {
                throw ValidationException::withMessages([
                    'decision' => 'Keputusan penugasan tidak valid (ACCEPT/REJECT).',
                ]);
            }

            return $assignment;
        });
    }
}
