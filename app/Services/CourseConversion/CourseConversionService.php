<?php

namespace App\Services\CourseConversion;

use App\Models\Course;
use App\Models\CourseConversion;
use App\Models\CourseConversionStatusHistory;
use App\Models\InternshipApplication;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CourseConversionService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Mahasiswa submits a new course conversion request.
     *
     * @throws ValidationException|AuthorizationException
     */
    public function submitConversion(User $student, array $data): CourseConversion
    {
        return DB::transaction(function () use ($student, $data) {
            $application = InternshipApplication::findOrFail($data['internship_application_id']);

            // Ownership check
            if ($application->user_id !== $student->id) {
                throw new AuthorizationException('Anda tidak memiliki izin mengajukan konversi untuk pendaftaran magang ini.');
            }

            // Prerequisite check: Must be approved and have accepted advisor
            if ($application->status !== InternshipApplication::STATUS_APPROVED || $application->advisor_status !== InternshipApplication::STATUS_ADVISOR_ACCEPTED) {
                throw ValidationException::withMessages([
                    'internship_application_id' => 'Pendaftaran magang harus telah disetujui resmi dan memiliki Dosen Pembimbing aktif sebelum mengajukan konversi MK.',
                ]);
            }

            $course = Course::findOrFail($data['course_id']);
            if (! $course->is_active) {
                throw ValidationException::withMessages([
                    'course_id' => 'Mata kuliah yang dipilih sedang tidak aktif.',
                ]);
            }

            // Prevent duplicate active conversions for the same course in this application
            $existing = CourseConversion::where('internship_application_id', $application->id)
                ->where('course_id', $course->id)
                ->whereIn('status', [
                    CourseConversion::STATUS_SUBMITTED,
                    CourseConversion::STATUS_APPROVED_DOSEN_MK,
                    CourseConversion::STATUS_VERIFIED_DOSBING,
                    CourseConversion::STATUS_APPROVED_WADEK1,
                    CourseConversion::STATUS_ACKNOWLEDGED_KAPRODI,
                ])
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'course_id' => 'Konversi untuk mata kuliah ini sudah pernah diajukan dan sedang dalam proses atau telah diselesaikan.',
                ]);
            }

            $conversion = CourseConversion::create([
                'internship_application_id' => $application->id,
                'user_id' => $student->id,
                'course_id' => $course->id,
                'activity_plan' => $data['activity_plan'],
                'status' => CourseConversion::STATUS_SUBMITTED,
            ]);

            // Audit history
            CourseConversionStatusHistory::create([
                'course_conversion_id' => $conversion->id,
                'actor_id' => $student->id,
                'action' => 'SUBMIT',
                'from_status' => null,
                'to_status' => CourseConversion::STATUS_SUBMITTED,
                'notes' => 'Pengajuan konversi mata kuliah oleh mahasiswa.',
            ]);

            // Notification to Dosen MK
            $this->notificationService->notifyRole(
                'DOSEN_MK',
                'Pengajuan Konversi MK Baru',
                "Mahasiswa {$student->name} mengajukan konversi untuk mata kuliah {$course->name} ({$course->code}).",
                'INFO',
                route('dosen-mk.conversions.show', $conversion->id),
                $student
            );

            return $conversion;
        });
    }

    /**
     * Mahasiswa resubmits a rejected course conversion request.
     *
     * @throws ValidationException|AuthorizationException
     */
    public function resubmitConversion(CourseConversion $conversion, User $student, array $data): CourseConversion
    {
        return DB::transaction(function () use ($conversion, $student, $data) {
            if ($conversion->user_id !== $student->id) {
                throw new AuthorizationException('Anda tidak berhak memperbarui pengajuan konversi ini.');
            }

            // State machine validation
            if ($conversion->status !== CourseConversion::STATUS_REJECTED) {
                throw ValidationException::withMessages([
                    'status' => 'Pengajuan konversi hanya dapat diajukan ulang jika berstatus Ditolak.',
                ]);
            }

            $oldStatus = $conversion->status;

            $conversion->update([
                'activity_plan' => $data['activity_plan'],
                'status' => CourseConversion::STATUS_SUBMITTED,
                'rejection_stage' => null,
                'rejection_reason' => null,
                'dosen_mk_id' => null,
                'dosen_mk_approved_at' => null,
                'dosen_mk_notes' => null,
                'dosbing_id' => null,
                'dosbing_verified_at' => null,
                'dosbing_notes' => null,
                'wadek1_id' => null,
                'wadek1_approved_at' => null,
                'wadek1_notes' => null,
                'kaprodi_id' => null,
                'kaprodi_acknowledged_at' => null,
                'kaprodi_notes' => null,
            ]);

            // Audit history
            CourseConversionStatusHistory::create([
                'course_conversion_id' => $conversion->id,
                'actor_id' => $student->id,
                'action' => 'RESUBMIT',
                'from_status' => $oldStatus,
                'to_status' => CourseConversion::STATUS_SUBMITTED,
                'notes' => 'Pengajuan ulang konversi mata kuliah setelah perbaikan.',
            ]);

            // Notify Dosen MK
            $this->notificationService->notifyRole(
                'DOSEN_MK',
                'Pengajuan Ulang Konversi MK',
                "Mahasiswa {$student->name} telah memperbaiki dan mengajukan ulang konversi mata kuliah {$conversion->course->name}.",
                'INFO',
                route('dosen-mk.conversions.show', $conversion->id),
                $student
            );

            return $conversion;
        });
    }

    /**
     * Dosen MK reviews the conversion request (Approve or Reject).
     *
     * @throws ValidationException|AuthorizationException
     */
    public function reviewByDosenMk(CourseConversion $conversion, User $dosenMk, string $action, ?string $notes = null, ?string $rejectionReason = null): CourseConversion
    {
        return DB::transaction(function () use ($conversion, $dosenMk, $action, $notes, $rejectionReason) {
            if (! $dosenMk->hasAnyRole(['DOSEN_MK', 'SUPERADMIN'])) {
                throw new AuthorizationException('Hanya Dosen Pengampu MK yang berhak meninjau konversi ini.');
            }

            // State machine validation: Only DIAJUKAN can be reviewed by Dosen MK
            if ($conversion->status !== CourseConversion::STATUS_SUBMITTED) {
                throw ValidationException::withMessages([
                    'status' => 'Status pengajuan tidak valid untuk ditinjau oleh Dosen MK. Status saat ini: ' . $conversion->status,
                ]);
            }

            $action = strtoupper(trim($action));

            if ($action === 'APPROVE') {
                $conversion->update([
                    'status' => CourseConversion::STATUS_APPROVED_DOSEN_MK,
                    'dosen_mk_id' => $dosenMk->id,
                    'dosen_mk_approved_at' => now(),
                    'dosen_mk_notes' => $notes,
                ]);

                CourseConversionStatusHistory::create([
                    'course_conversion_id' => $conversion->id,
                    'actor_id' => $dosenMk->id,
                    'action' => 'APPROVE_DOSEN_MK',
                    'from_status' => CourseConversion::STATUS_SUBMITTED,
                    'to_status' => CourseConversion::STATUS_APPROVED_DOSEN_MK,
                    'notes' => $notes ?: 'Disetujui oleh Dosen MK.',
                ]);

                // Notify Dosen Pembimbing
                $advisor = $conversion->internshipApplication->advisor;
                if ($advisor) {
                    $this->notificationService->notifyUser(
                        $advisor,
                        'Verifikasi Konversi MK',
                        "Pengajuan konversi MK {$conversion->course->name} oleh {$conversion->student->name} telah disetujui Dosen MK dan memerlukan verifikasi Anda.",
                        'INFO',
                        route('academic.conversions.show', $conversion->id),
                        $dosenMk
                    );
                }

                // Notify Mahasiswa
                $this->notificationService->notifyUser(
                    $conversion->student,
                    'Konversi MK Disetujui Dosen MK',
                    "Konversi MK {$conversion->course->name} telah disetujui Dosen MK dan diteruskan ke Dosen Pembimbing.",
                    'SUCCESS',
                    route('conversions.show', $conversion->id),
                    $dosenMk
                );
            } elseif ($action === 'REJECT') {
                if (blank($rejectionReason)) {
                    throw ValidationException::withMessages([
                        'rejection_reason' => 'Alasan penolakan wajib diisi.',
                    ]);
                }

                $conversion->update([
                    'status' => CourseConversion::STATUS_REJECTED,
                    'rejection_stage' => 'DOSEN_MK',
                    'rejection_reason' => $rejectionReason,
                    'dosen_mk_id' => $dosenMk->id,
                    'dosen_mk_notes' => $notes,
                ]);

                CourseConversionStatusHistory::create([
                    'course_conversion_id' => $conversion->id,
                    'actor_id' => $dosenMk->id,
                    'action' => 'REJECT',
                    'from_status' => CourseConversion::STATUS_SUBMITTED,
                    'to_status' => CourseConversion::STATUS_REJECTED,
                    'notes' => 'Ditolak Dosen MK: ' . $rejectionReason,
                ]);

                // Notify Mahasiswa
                $this->notificationService->notifyUser(
                    $conversion->student,
                    'Pengajuan Konversi MK Ditolak Dosen MK',
                    "Pengajuan konversi MK {$conversion->course->name} ditolak oleh Dosen MK. Alasan: {$rejectionReason}",
                    'WARNING',
                    route('conversions.show', $conversion->id),
                    $dosenMk
                );
            } else {
                throw ValidationException::withMessages([
                    'action' => 'Aksi tidak valid. Pilih APPROVE atau REJECT.',
                ]);
            }

            return $conversion;
        });
    }

    /**
     * Dosen Pembimbing verifies the conversion request.
     *
     * @throws ValidationException|AuthorizationException
     */
    public function verifyByDosbing(CourseConversion $conversion, User $dosbing, string $action, ?string $notes = null, ?string $rejectionReason = null): CourseConversion
    {
        return DB::transaction(function () use ($conversion, $dosbing, $action, $notes, $rejectionReason) {
            $isSuperadmin = $dosbing->hasRole('SUPERADMIN');
            $isAssignedAdvisor = $conversion->internshipApplication->advisor_id === $dosbing->id;

            if (! $isSuperadmin && (! $dosbing->hasRole('DOSBING') || ! $isAssignedAdvisor)) {
                throw new AuthorizationException('Hanya Dosen Pembimbing mahasiswa bersangkutan yang dapat memverifikasi pengajuan konversi ini.');
            }

            // State machine validation: Only DISETUJUI_DOSEN_MK can be verified by Dosbing
            if ($conversion->status !== CourseConversion::STATUS_APPROVED_DOSEN_MK) {
                throw ValidationException::withMessages([
                    'status' => 'Status pengajuan tidak valid untuk diverifikasi Dosbing. Status saat ini: ' . $conversion->status,
                ]);
            }

            $action = strtoupper(trim($action));

            if ($action === 'APPROVE' || $action === 'VERIFY') {
                $conversion->update([
                    'status' => CourseConversion::STATUS_VERIFIED_DOSBING,
                    'dosbing_id' => $dosbing->id,
                    'dosbing_verified_at' => now(),
                    'dosbing_notes' => $notes,
                ]);

                CourseConversionStatusHistory::create([
                    'course_conversion_id' => $conversion->id,
                    'actor_id' => $dosbing->id,
                    'action' => 'VERIFY_DOSBING',
                    'from_status' => CourseConversion::STATUS_APPROVED_DOSEN_MK,
                    'to_status' => CourseConversion::STATUS_VERIFIED_DOSBING,
                    'notes' => $notes ?: 'Diverifikasi oleh Dosen Pembimbing.',
                ]);

                // Notify Wadek 1
                $this->notificationService->notifyRole(
                    'WADEK1',
                    'Konversi MK Menunggu Persetujuan Wadek 1',
                    "Konversi MK {$conversion->course->name} oleh mahasiswa {$conversion->student->name} telah diverifikasi Dosbing dan siap disetujui.",
                    'INFO',
                    route('wadek1.conversions.show', $conversion->id),
                    $dosbing
                );

                // Notify Mahasiswa
                $this->notificationService->notifyUser(
                    $conversion->student,
                    'Konversi MK Diverifikasi Dosen Pembimbing',
                    "Konversi MK {$conversion->course->name} telah diverifikasi oleh Dosen Pembimbing dan diteruskan ke Wakil Dekan 1.",
                    'SUCCESS',
                    route('conversions.show', $conversion->id),
                    $dosbing
                );
            } elseif ($action === 'REJECT') {
                if (blank($rejectionReason)) {
                    throw ValidationException::withMessages([
                        'rejection_reason' => 'Alasan penolakan wajib diisi.',
                    ]);
                }

                $conversion->update([
                    'status' => CourseConversion::STATUS_REJECTED,
                    'rejection_stage' => 'DOSBING',
                    'rejection_reason' => $rejectionReason,
                    'dosbing_id' => $dosbing->id,
                    'dosbing_notes' => $notes,
                ]);

                CourseConversionStatusHistory::create([
                    'course_conversion_id' => $conversion->id,
                    'actor_id' => $dosbing->id,
                    'action' => 'REJECT',
                    'from_status' => CourseConversion::STATUS_APPROVED_DOSEN_MK,
                    'to_status' => CourseConversion::STATUS_REJECTED,
                    'notes' => 'Ditolak Dosen Pembimbing: ' . $rejectionReason,
                ]);

                // Notify Mahasiswa
                $this->notificationService->notifyUser(
                    $conversion->student,
                    'Pengajuan Konversi MK Ditolak Dosbing',
                    "Pengajuan konversi MK {$conversion->course->name} ditolak oleh Dosen Pembimbing. Alasan: {$rejectionReason}",
                    'WARNING',
                    route('conversions.show', $conversion->id),
                    $dosbing
                );
            } else {
                throw ValidationException::withMessages([
                    'action' => 'Aksi tidak valid. Pilih APPROVE atau REJECT.',
                ]);
            }

            return $conversion;
        });
    }

    /**
     * Wadek 1 approves the conversion request.
     *
     * @throws ValidationException|AuthorizationException
     */
    public function approveByWadek1(CourseConversion $conversion, User $wadek1, string $action, ?string $notes = null, ?string $rejectionReason = null): CourseConversion
    {
        return DB::transaction(function () use ($conversion, $wadek1, $action, $notes, $rejectionReason) {
            if (! $wadek1->hasAnyRole(['WADEK1', 'SUPERADMIN'])) {
                throw new AuthorizationException('Hanya Wakil Dekan 1 yang berhak menyetujui konversi ini.');
            }

            // State machine validation: Only DIVERIFIKASI_DOSBING can be approved by Wadek 1
            if ($conversion->status !== CourseConversion::STATUS_VERIFIED_DOSBING) {
                throw ValidationException::withMessages([
                    'status' => 'Status pengajuan tidak valid untuk disetujui Wadek 1. Status saat ini: ' . $conversion->status,
                ]);
            }

            $action = strtoupper(trim($action));

            if ($action === 'APPROVE') {
                $conversion->update([
                    'status' => CourseConversion::STATUS_APPROVED_WADEK1,
                    'wadek1_id' => $wadek1->id,
                    'wadek1_approved_at' => now(),
                    'wadek1_notes' => $notes,
                ]);

                CourseConversionStatusHistory::create([
                    'course_conversion_id' => $conversion->id,
                    'actor_id' => $wadek1->id,
                    'action' => 'APPROVE_WADEK1',
                    'from_status' => CourseConversion::STATUS_VERIFIED_DOSBING,
                    'to_status' => CourseConversion::STATUS_APPROVED_WADEK1,
                    'notes' => $notes ?: 'Disetujui oleh Wakil Dekan 1.',
                ]);

                // Notify Kaprodi
                $this->notificationService->notifyRole(
                    'KAPRODI',
                    'Konversi MK Menunggu Pengesahan Kaprodi',
                    "Konversi MK {$conversion->course->name} oleh mahasiswa {$conversion->student->name} telah disetujui Wadek 1. Silakan tandai Diketahui.",
                    'INFO',
                    route('kaprodi.conversions.show', $conversion->id),
                    $wadek1
                );

                // Notify Mahasiswa
                $this->notificationService->notifyUser(
                    $conversion->student,
                    'Konversi MK Disetujui Wadek 1',
                    "Konversi MK {$conversion->course->name} telah disetujui oleh Wakil Dekan 1.",
                    'SUCCESS',
                    route('conversions.show', $conversion->id),
                    $wadek1
                );
            } elseif ($action === 'REJECT') {
                if (blank($rejectionReason)) {
                    throw ValidationException::withMessages([
                        'rejection_reason' => 'Alasan penolakan wajib diisi.',
                    ]);
                }

                $conversion->update([
                    'status' => CourseConversion::STATUS_REJECTED,
                    'rejection_stage' => 'WADEK1',
                    'rejection_reason' => $rejectionReason,
                    'wadek1_id' => $wadek1->id,
                    'wadek1_notes' => $notes,
                ]);

                CourseConversionStatusHistory::create([
                    'course_conversion_id' => $conversion->id,
                    'actor_id' => $wadek1->id,
                    'action' => 'REJECT',
                    'from_status' => CourseConversion::STATUS_VERIFIED_DOSBING,
                    'to_status' => CourseConversion::STATUS_REJECTED,
                    'notes' => 'Ditolak Wadek 1: ' . $rejectionReason,
                ]);

                // Notify Mahasiswa
                $this->notificationService->notifyUser(
                    $conversion->student,
                    'Pengajuan Konversi MK Ditolak Wadek 1',
                    "Pengajuan konversi MK {$conversion->course->name} ditolak oleh Wakil Dekan 1. Alasan: {$rejectionReason}",
                    'WARNING',
                    route('conversions.show', $conversion->id),
                    $wadek1
                );
            } else {
                throw ValidationException::withMessages([
                    'action' => 'Aksi tidak valid. Pilih APPROVE atau REJECT.',
                ]);
            }

            return $conversion;
        });
    }

    /**
     * Kaprodi marks the approved conversion as "Diketahui" (Acknowledged).
     *
     * @throws ValidationException|AuthorizationException
     */
    public function acknowledgeByKaprodi(CourseConversion $conversion, User $kaprodi, ?string $notes = null): CourseConversion
    {
        return DB::transaction(function () use ($conversion, $kaprodi, $notes) {
            if (! $kaprodi->hasAnyRole(['KAPRODI', 'SUPERADMIN'])) {
                throw new AuthorizationException('Hanya Kaprodi yang berhak menandai Diketahui pada konversi ini.');
            }

            // State machine validation: Only DISETUJUI_WADEK1 can be acknowledged by Kaprodi
            if ($conversion->status !== CourseConversion::STATUS_APPROVED_WADEK1) {
                throw ValidationException::withMessages([
                    'status' => 'Status pengajuan tidak valid untuk disahkan Kaprodi. Status saat ini: ' . $conversion->status,
                ]);
            }

            $conversion->update([
                'status' => CourseConversion::STATUS_ACKNOWLEDGED_KAPRODI,
                'kaprodi_id' => $kaprodi->id,
                'kaprodi_acknowledged_at' => now(),
                'kaprodi_notes' => $notes,
            ]);

            CourseConversionStatusHistory::create([
                'course_conversion_id' => $conversion->id,
                'actor_id' => $kaprodi->id,
                'action' => 'ACKNOWLEDGE_KAPRODI',
                'from_status' => CourseConversion::STATUS_APPROVED_WADEK1,
                'to_status' => CourseConversion::STATUS_ACKNOWLEDGED_KAPRODI,
                'notes' => $notes ?: 'Telah ditandai Diketahui oleh Ketua Program Studi.',
            ]);

            // Notify Mahasiswa
            $this->notificationService->notifyUser(
                $conversion->student,
                'Konversi Mata Kuliah Selesai',
                "Konversi MK {$conversion->course->name} telah diketahui dan disahkan oleh Kaprodi.",
                'SUCCESS',
                route('conversions.show', $conversion->id),
                $kaprodi
            );

            return $conversion;
        });
    }
}
