<?php

namespace App\Services\Seminar;

use App\Models\CourseConversion;
use App\Models\InternshipApplication;
use App\Models\InternshipSeminar;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Validation\ValidationException;

class SeminarService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Dosen MK determines if seminar is needed (YES / NO).
     * If NO: status = TIDAK_DIPERLUKAN
     * If YES: Dosen MK schedules (date, time, location, info) -> status = TERJADWAL
     */
    public function decideSeminar(CourseConversion $conversion, User $dosenMk, bool $isRequired, array $scheduleData = []): InternshipSeminar
    {
        $application = $conversion->internshipApplication;

        $seminar = InternshipSeminar::firstOrNew([
            'internship_application_id' => $application->id,
            'course_conversion_id' => $conversion->id,
        ]);

        $seminar->user_id = $conversion->user_id;
        $seminar->course_id = $conversion->course_id;
        $seminar->dosen_mk_id = $dosenMk->id;
        $seminar->is_required = $isRequired;

        if (! $isRequired) {
            $seminar->status = InternshipSeminar::STATUS_NOT_REQUIRED;
            $seminar->scheduled_date = null;
            $seminar->scheduled_time = null;
            $seminar->location_or_link = null;
            $seminar->information = null;
            $seminar->save();

            return $seminar;
        }

        // Seminar is required -> Schedule it
        if (empty($scheduleData['scheduled_date']) || empty($scheduleData['scheduled_time']) || empty($scheduleData['location_or_link'])) {
            throw ValidationException::withMessages([
                'scheduled_date' => 'Tanggal, waktu, dan tempat/link wajib diisi untuk seminar yang terjadwal.',
            ]);
        }

        $seminar->scheduled_date = $scheduleData['scheduled_date'];
        $seminar->scheduled_time = $scheduleData['scheduled_time'];
        $seminar->location_or_link = $scheduleData['location_or_link'];
        $seminar->information = $scheduleData['information'] ?? null;
        $seminar->status = InternshipSeminar::STATUS_SCHEDULED;
        $seminar->save();

        // Send notification: seminar scheduled
        $this->notifySeminarScheduled($seminar, $dosenMk);

        return $seminar;
    }

    /**
     * Reschedule seminar by Dosen MK.
     */
    public function rescheduleSeminar(InternshipSeminar $seminar, User $dosenMk, array $scheduleData): InternshipSeminar
    {
        if (empty($scheduleData['scheduled_date']) || empty($scheduleData['scheduled_time']) || empty($scheduleData['location_or_link'])) {
            throw ValidationException::withMessages([
                'scheduled_date' => 'Tanggal, waktu, dan tempat/link wajib diisi untuk menjadwalkan ulang seminar.',
            ]);
        }

        $seminar->scheduled_date = $scheduleData['scheduled_date'];
        $seminar->scheduled_time = $scheduleData['scheduled_time'];
        $seminar->location_or_link = $scheduleData['location_or_link'];
        $seminar->information = $scheduleData['information'] ?? $seminar->information;
        $seminar->status = InternshipSeminar::STATUS_SCHEDULED;
        $seminar->rescheduled_at = now();
        $seminar->reschedule_count += 1;

        // Reset approval flags so full workflow can rerun cleanly
        $seminar->dosbing_id = null;
        $seminar->dosbing_acknowledged_at = null;
        $seminar->dosbing_notes = null;
        $seminar->kaprodi_id = null;
        $seminar->kaprodi_acknowledged_at = null;
        $seminar->kaprodi_notes = null;
        $seminar->wadek1_id = null;
        $seminar->wadek1_approved_at = null;
        $seminar->wadek1_rejected_at = null;
        $seminar->rejection_reason = null;
        $seminar->save();

        // Send notification: jadwal berubah
        $this->notifyScheduleChanged($seminar, $dosenMk);

        return $seminar;
    }

    /**
     * Dosen Pembimbing marks seminar as "mengetahui" (acknowledged).
     */
    public function acknowledgeByDosbing(InternshipSeminar $seminar, User $dosbing, ?string $notes = null): InternshipSeminar
    {
        if ($seminar->status !== InternshipSeminar::STATUS_SCHEDULED) {
            throw ValidationException::withMessages([
                'status' => 'Hanya seminar dengan status Terjadwal yang dapat dikonfirmasi oleh Dosen Pembimbing.',
            ]);
        }

        $seminar->dosbing_id = $dosbing->id;
        $seminar->dosbing_acknowledged_at = now();
        $seminar->dosbing_notes = $notes;
        $seminar->save();

        // Send notification: dosen mengetahui
        $this->notifyDosbingAcknowledged($seminar, $dosbing);

        return $seminar;
    }

    /**
     * Kaprodi marks seminar as "mengetahui" (acknowledged).
     * Prerequisite: Dosbing must have acknowledged first.
     */
    public function acknowledgeByKaprodi(InternshipSeminar $seminar, User $kaprodi, ?string $notes = null): InternshipSeminar
    {
        if (! $seminar->dosbing_acknowledged_at) {
            throw ValidationException::withMessages([
                'status' => 'Dosen Pembimbing harus mengetahui jadwal seminar terlebih dahulu sebelum Kaprodi.',
            ]);
        }

        $seminar->kaprodi_id = $kaprodi->id;
        $seminar->kaprodi_acknowledged_at = now();
        $seminar->kaprodi_notes = $notes;
        $seminar->status = InternshipSeminar::STATUS_ACKNOWLEDGED;
        $seminar->save();

        // Send notification: kaprodi mengetahui
        $this->notifyKaprodiAcknowledged($seminar, $kaprodi);

        return $seminar;
    }

    /**
     * Wadek 1 approves seminar schedule.
     * Prerequisite: Status must be DIKETAHUI (acknowledged by Dosbing and Kaprodi).
     */
    public function approveByWadek1(InternshipSeminar $seminar, User $wadek1): InternshipSeminar
    {
        if ($seminar->status !== InternshipSeminar::STATUS_ACKNOWLEDGED) {
            throw ValidationException::withMessages([
                'status' => 'Seminar harus telah diketahui oleh Dosen Pembimbing dan Kaprodi sebelum disetujui Wadek 1.',
            ]);
        }

        $seminar->wadek1_id = $wadek1->id;
        $seminar->wadek1_approved_at = now();
        $seminar->wadek1_rejected_at = null;
        $seminar->rejection_reason = null;
        $seminar->status = InternshipSeminar::STATUS_APPROVED;
        $seminar->save();

        // Send notification: wadek approve
        $this->notifyWadekDecision($seminar, $wadek1, isApproved: true);

        return $seminar;
    }

    /**
     * Wadek 1 rejects seminar schedule with mandatory reason.
     */
    public function rejectByWadek1(InternshipSeminar $seminar, User $wadek1, string $reason): InternshipSeminar
    {
        if ($seminar->status !== InternshipSeminar::STATUS_ACKNOWLEDGED) {
            throw ValidationException::withMessages([
                'status' => 'Hanya seminar yang telah berstatus Diketahui yang dapat direview oleh Wadek 1.',
            ]);
        }

        $cleanReason = trim($reason);
        if (empty($cleanReason)) {
            throw ValidationException::withMessages([
                'rejection_reason' => 'Catatan alasan penolakan wajib diisi oleh Wadek 1.',
            ]);
        }

        $seminar->wadek1_id = $wadek1->id;
        $seminar->wadek1_rejected_at = now();
        $seminar->wadek1_approved_at = null;
        $seminar->rejection_reason = $cleanReason;
        $seminar->status = InternshipSeminar::STATUS_REJECTED;
        $seminar->save();

        // Send notification: wadek reject
        $this->notifyWadekDecision($seminar, $wadek1, isApproved: false, reason: $cleanReason);

        return $seminar;
    }

    /**
     * Mark seminar as conducted.
     * Rule: "Seminar tidak boleh dilaksanakan sebelum approval Wadek 1."
     */
    public function conductSeminar(InternshipSeminar $seminar, User $actor): InternshipSeminar
    {
        if ($seminar->status !== InternshipSeminar::STATUS_APPROVED) {
            throw ValidationException::withMessages([
                'status' => 'Seminar tidak boleh dilaksanakan sebelum mendapatkan approval dari Wadek 1.',
            ]);
        }

        $seminar->status = InternshipSeminar::STATUS_CONDUCTED;
        $seminar->conducted_at = now();
        $seminar->save();

        return $seminar;
    }

    /* -------------------------------------------------------------------------- */
    /* Notifications Dispatchers                                                  */
    /* -------------------------------------------------------------------------- */

    /**
     * 1. Notification: seminar scheduled
     */
    protected function notifySeminarScheduled(InternshipSeminar $seminar, User $actor): void
    {
        $seminar->loadMissing(['student', 'internshipApplication.advisor', 'course']);

        $courseName = $seminar->course?->name ?? 'Mata Kuliah Magang';
        $dateStr = $seminar->scheduled_date?->format('d/m/Y');

        // Notify student
        if ($seminar->student) {
            $this->notificationService->notifyUser(
                $seminar->student,
                'Jadwal Seminar Magang Ditentukan',
                "Dosen MK telah menjadwalkan seminar untuk mata kuliah {$courseName} pada tanggal {$dateStr} pukul {$seminar->scheduled_time}.",
                'SEMINAR_SCHEDULED',
                route('seminars.index'),
                $actor
            );
        }

        // Notify Dosbing
        $dosbing = $seminar->internshipApplication?->advisor;
        if ($dosbing) {
            $this->notificationService->notifyUser(
                $dosbing,
                'Jadwal Seminar Magang Mahasiswa Bimbingan',
                "Dosen MK telah menjadwalkan seminar magang untuk mahasiswa {$seminar->student?->name} pada {$dateStr}. Mohon untuk melakukan konfirmasi.",
                'SEMINAR_SCHEDULED',
                route('academic.seminars.index'),
                $actor
            );
        }
    }

    /**
     * 2. Notification: dosen mengetahui
     */
    protected function notifyDosbingAcknowledged(InternshipSeminar $seminar, User $actor): void
    {
        $seminar->loadMissing('student');

        // Notify Kaprodi role
        $this->notificationService->notifyRole(
            'KAPRODI',
            'Dosen Pembimbing Mengetahui Jadwal Seminar',
            "Dosen Pembimbing telah mengonfirmasi mengetahui jadwal seminar magang mahasiswa {$seminar->student?->name}.",
            'SEMINAR_DOSEN_ACKNOWLEDGED',
            route('kaprodi.seminars.index'),
            $actor
        );

        // Notify student
        if ($seminar->student) {
            $this->notificationService->notifyUser(
                $seminar->student,
                'Dosen Pembimbing Mengonfirmasi Seminar',
                'Dosen Pembimbing telah mengetahui jadwal pelaksanaan seminar magang Anda.',
                'SEMINAR_DOSEN_ACKNOWLEDGED',
                route('seminars.index'),
                $actor
            );
        }
    }

    /**
     * 3. Notification: kaprodi mengetahui
     */
    protected function notifyKaprodiAcknowledged(InternshipSeminar $seminar, User $actor): void
    {
        $seminar->loadMissing('student');

        // Notify Wadek 1 role
        $this->notificationService->notifyRole(
            'WADEK1',
            'Seminar Magang Siap Direview Wadek 1',
            "Kaprodi telah mengetahui jadwal seminar mahasiswa {$seminar->student?->name}. Menunggu persetujuan Dekanat (Wadek 1).",
            'SEMINAR_KAPRODI_ACKNOWLEDGED',
            route('wadek1.seminars.index'),
            $actor
        );

        // Notify student
        if ($seminar->student) {
            $this->notificationService->notifyUser(
                $seminar->student,
                'Kaprodi Mengetahui Jadwal Seminar',
                'Kaprodi telah mengetahui jadwal seminar magang Anda. Menunggu persetujuan Wadek 1.',
                'SEMINAR_KAPRODI_ACKNOWLEDGED',
                route('seminars.index'),
                $actor
            );
        }
    }

    /**
     * 4. Notification: wadek approve/reject
     */
    protected function notifyWadekDecision(InternshipSeminar $seminar, User $actor, bool $isApproved, ?string $reason = null): void
    {
        $seminar->loadMissing(['student', 'dosenMk', 'internshipApplication.advisor', 'course']);

        $title = $isApproved ? 'Seminar Magang Disetujui Wadek 1' : 'Seminar Magang Ditolak Wadek 1';
        $message = $isApproved
            ? "Persetujuan seminar magang untuk {$seminar->student?->name} telah diterbitkan oleh Wadek 1."
            : "Seminar magang untuk {$seminar->student?->name} ditolak oleh Wadek 1. Alasan: {$reason}";
        $type = $isApproved ? 'SEMINAR_WADEK_APPROVED' : 'SEMINAR_WADEK_REJECTED';

        // Notify student
        if ($seminar->student) {
            $this->notificationService->notifyUser(
                $seminar->student,
                $title,
                $message,
                $type,
                route('seminars.index'),
                $actor
            );
        }

        // Notify Dosen MK
        if ($seminar->dosenMk) {
            $this->notificationService->notifyUser(
                $seminar->dosenMk,
                $title,
                $message,
                $type,
                route('dosen-mk.seminars.index'),
                $actor
            );
        }

        // Notify Dosbing
        $dosbing = $seminar->internshipApplication?->advisor;
        if ($dosbing) {
            $this->notificationService->notifyUser(
                $dosbing,
                $title,
                $message,
                $type,
                route('academic.seminars.index'),
                $actor
            );
        }
    }

    /**
     * 5. Notification: jadwal berubah (rescheduled)
     */
    protected function notifyScheduleChanged(InternshipSeminar $seminar, User $actor): void
    {
        $seminar->loadMissing(['student', 'internshipApplication.advisor', 'course']);
        $dateStr = $seminar->scheduled_date?->format('d/m/Y');
        $timeStr = $seminar->scheduled_time;
        $title = 'Jadwal Seminar Magang Berubah';
        $message = "Jadwal seminar magang telah diubah ke tanggal {$dateStr} pukul {$timeStr} (Lokasi/Link: {$seminar->location_or_link}).";
        $type = 'SEMINAR_SCHEDULE_CHANGED';

        // Notify student
        if ($seminar->student) {
            $this->notificationService->notifyUser(
                $seminar->student,
                $title,
                $message,
                $type,
                route('seminars.index'),
                $actor
            );
        }

        // Notify Dosbing
        $dosbing = $seminar->internshipApplication?->advisor;
        if ($dosbing) {
            $this->notificationService->notifyUser(
                $dosbing,
                $title,
                $message,
                $type,
                route('academic.seminars.index'),
                $actor
            );
        }

        // Notify Kaprodi
        $this->notificationService->notifyRole(
            'KAPRODI',
            $title,
            $message,
            $type,
            route('kaprodi.seminars.index'),
            $actor
        );

        // Notify Wadek 1
        $this->notificationService->notifyRole(
            'WADEK1',
            $title,
            $message,
            $type,
            route('wadek1.seminars.index'),
            $actor
        );
    }
}
