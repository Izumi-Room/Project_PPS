<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Course;
use App\Models\CourseConversion;
use App\Models\InternshipApplication;
use App\Models\InternshipPeriod;
use App\Models\InternshipSeminar;
use App\Models\PartnerInstitution;
use App\Models\StudyProgram;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InternshipSeminarTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $studentB;
    protected User $dosenMk;
    protected User $dosbing;
    protected User $kaprodi;
    protected User $wadek1;
    protected Course $course;
    protected CourseConversion $conversion;
    protected InternshipApplication $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);
        $this->seed(UserSeeder::class);

        $prodi = StudyProgram::where('code', 'TI')->firstOrFail();
        $institution = PartnerInstitution::where('is_active', true)->firstOrFail();
        $period = InternshipPeriod::where('is_active', true)->firstOrFail();
        $this->course = Course::where('study_program_id', $prodi->id)->firstOrFail();

        // 1. Mahasiswa
        $this->student = User::factory()->create([
            'email' => 'student.p6@magang.ac.id',
            'name' => 'Fajar Pratama',
            'identifier_number' => '2201010333',
            'study_program_id' => $prodi->id,
            'password' => Hash::make('Password123!'),
        ]);
        $this->student->assignRole('MHS');

        $this->studentB = User::factory()->create([
            'email' => 'student.p6.b@magang.ac.id',
            'name' => 'Rina Salsabila',
            'identifier_number' => '2201010444',
            'study_program_id' => $prodi->id,
            'password' => Hash::make('Password123!'),
        ]);
        $this->studentB->assignRole('MHS');

        // 2. Role Users
        $this->dosenMk = User::where('email', 'dosenmk@magang.ac.id')->firstOrFail();
        $this->dosbing = User::where('email', 'dosbing@magang.ac.id')->firstOrFail();
        $this->kaprodi = User::where('email', 'kaprodi@magang.ac.id')->firstOrFail();
        $this->wadek1 = User::where('email', 'wadek1@magang.ac.id')->firstOrFail();

        // 3. Approved Internship Application
        $this->application = InternshipApplication::create([
            'user_id' => $this->student->id,
            'study_program_id' => $prodi->id,
            'partner_institution_id' => $institution->id,
            'internship_period_id' => $period->id,
            'student_name' => $this->student->name,
            'student_nim' => $this->student->identifier_number,
            'student_phone' => '081234567892',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'proposal_title' => 'Pengembangan Sistem Seminar Magang Terintegrasi',
            'internship_plan' => 'Rencana kegiatan magang.',
            'status' => InternshipApplication::STATUS_APPROVED,
            'advisor_id' => $this->dosbing->id,
            'advisor_status' => InternshipApplication::STATUS_ADVISOR_ACCEPTED,
        ]);

        // 4. Approved Course Conversion
        $this->conversion = CourseConversion::create([
            'internship_application_id' => $this->application->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'activity_plan' => 'Pekerjaan proyek backend yang relevan dengan silabus MK.',
            'status' => CourseConversion::STATUS_ACKNOWLEDGED_KAPRODI,
            'dosen_mk_id' => $this->dosenMk->id,
            'dosbing_id' => $this->dosbing->id,
        ]);
    }

    /**
     * Test Path 1: Seminar tidak diperlukan (is_required = NO).
     */
    public function test_path_1_seminar_tidak_diperlukan(): void
    {
        $response = $this->actingAs($this->dosenMk)->post(route('dosen-mk.seminars.decide'), [
            'course_conversion_id' => $this->conversion->id,
            'is_required' => 0,
        ]);

        $response->assertRedirect(route('dosen-mk.seminars.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('internship_seminars', [
            'internship_application_id' => $this->application->id,
            'course_conversion_id' => $this->conversion->id,
            'user_id' => $this->student->id,
            'is_required' => false,
            'status' => InternshipSeminar::STATUS_NOT_REQUIRED,
        ]);

        $seminar = InternshipSeminar::first();
        $this->assertTrue($seminar->isNotRequired());

        // Mahasiswa views the status
        $studentResponse = $this->actingAs($this->student)->get(route('seminars.show', $seminar));
        $studentResponse->assertOk();
        $studentResponse->assertSee('Seminar Tidak Diperlukan');
    }

    /**
     * Test Path 2: Seminar diperlukan dan disetujui (End-to-End Workflow).
     */
    public function test_path_2_seminar_diperlukan_dan_disetujui(): void
    {
        $targetDate = now()->addDays(7)->format('Y-m-d');

        // Step 1: Dosen MK schedules seminar
        $scheduleResponse = $this->actingAs($this->dosenMk)->post(route('dosen-mk.seminars.decide'), [
            'course_conversion_id' => $this->conversion->id,
            'is_required' => 1,
            'scheduled_date' => $targetDate,
            'scheduled_time' => '09:00 - 11:30 WIB',
            'location_or_link' => 'Ruang Seminar 301 Kampus Utama',
            'information' => 'Siapkan slide presentasi 15 menit dan laporan magang.',
        ]);

        $scheduleResponse->assertRedirect(route('dosen-mk.seminars.index'));

        $seminar = InternshipSeminar::where('course_conversion_id', $this->conversion->id)->firstOrFail();
        $this->assertEquals(InternshipSeminar::STATUS_SCHEDULED, $seminar->status);
        $this->assertTrue($seminar->is_required);

        // Check notification: seminar scheduled
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->student->id,
            'type' => 'SEMINAR_SCHEDULED',
        ]);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->dosbing->id,
            'type' => 'SEMINAR_SCHEDULED',
        ]);

        // Verify seminar CANNOT be conducted before Wadek 1 approval
        $this->assertFalse($seminar->canBeConducted());
        $conductFailResponse = $this->actingAs($this->dosenMk)->post(route('dosen-mk.seminars.conduct', $seminar));
        $conductFailResponse->assertSessionHasErrors(['status']);

        // Step 2: Dosen Pembimbing marks "mengetahui"
        $dosbingResponse = $this->actingAs($this->dosbing)->post(route('academic.seminars.acknowledge', $seminar), [
            'notes' => 'Mahasiswa telah siap presentasi hasil magang.',
        ]);
        $dosbingResponse->assertSessionHas('success');

        $seminar->refresh();
        $this->assertNotNull($seminar->dosbing_acknowledged_at);
        $this->assertEquals('Mahasiswa telah siap presentasi hasil magang.', $seminar->dosbing_notes);

        // Check notification: dosen mengetahui
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->student->id,
            'type' => 'SEMINAR_DOSEN_ACKNOWLEDGED',
        ]);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->kaprodi->id,
            'type' => 'SEMINAR_DOSEN_ACKNOWLEDGED',
        ]);

        // Step 3: Kaprodi marks "mengetahui"
        $kaprodiResponse = $this->actingAs($this->kaprodi)->post(route('kaprodi.seminars.acknowledge', $seminar), [
            'notes' => 'Telah diverifikasi sesuai jadwal prodi.',
        ]);
        $kaprodiResponse->assertSessionHas('success');

        $seminar->refresh();
        $this->assertNotNull($seminar->kaprodi_acknowledged_at);
        $this->assertEquals(InternshipSeminar::STATUS_ACKNOWLEDGED, $seminar->status);

        // Check notification: kaprodi mengetahui
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->student->id,
            'type' => 'SEMINAR_KAPRODI_ACKNOWLEDGED',
        ]);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->wadek1->id,
            'type' => 'SEMINAR_KAPRODI_ACKNOWLEDGED',
        ]);

        // Step 4: Wadek 1 approves
        $wadekResponse = $this->actingAs($this->wadek1)->post(route('wadek1.seminars.approve', $seminar));
        $wadekResponse->assertRedirect(route('wadek1.seminars.index'));

        $seminar->refresh();
        $this->assertEquals(InternshipSeminar::STATUS_APPROVED, $seminar->status);
        $this->assertNotNull($seminar->wadek1_approved_at);

        // Check notification: wadek approve
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->student->id,
            'type' => 'SEMINAR_WADEK_APPROVED',
        ]);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->dosenMk->id,
            'type' => 'SEMINAR_WADEK_APPROVED',
        ]);

        // Step 5: After Wadek 1 approval, seminar can be conducted
        $this->assertTrue($seminar->canBeConducted());
        $conductResponse = $this->actingAs($this->dosenMk)->post(route('dosen-mk.seminars.conduct', $seminar));
        $conductResponse->assertSessionHas('success');

        $seminar->refresh();
        $this->assertEquals(InternshipSeminar::STATUS_CONDUCTED, $seminar->status);
        $this->assertNotNull($seminar->conducted_at);

        // Student views details
        $viewResponse = $this->actingAs($this->student)->get(route('seminars.show', $seminar));
        $viewResponse->assertOk();
        $viewResponse->assertSee('Ruang Seminar 301 Kampus Utama');
        $viewResponse->assertSee('09:00 - 11:30 WIB');
    }

    /**
     * Test Wadek 1 Rejection and Dosen MK Rescheduling.
     */
    public function test_seminar_rejection_and_rescheduling(): void
    {
        // 1. Initial Schedule
        $seminar = InternshipSeminar::create([
            'internship_application_id' => $this->application->id,
            'course_conversion_id' => $this->conversion->id,
            'course_id' => $this->course->id,
            'user_id' => $this->student->id,
            'dosen_mk_id' => $this->dosenMk->id,
            'is_required' => true,
            'status' => InternshipSeminar::STATUS_ACKNOWLEDGED,
            'scheduled_date' => now()->addDays(5)->toDateString(),
            'scheduled_time' => '13:00 - 15:00 WIB',
            'location_or_link' => 'https://meet.google.com/xyz-seminar',
            'dosbing_acknowledged_at' => now()->subDay(),
            'kaprodi_acknowledged_at' => now()->subHours(5),
        ]);

        // 2. Wadek 1 Rejection without reason FAILS
        $emptyReasonResponse = $this->actingAs($this->wadek1)->post(route('wadek1.seminars.reject', $seminar), [
            'rejection_reason' => '',
        ]);
        $emptyReasonResponse->assertSessionHasErrors(['rejection_reason']);

        // 3. Wadek 1 Rejection with mandatory reason SUCCEEDS
        $rejectResponse = $this->actingAs($this->wadek1)->post(route('wadek1.seminars.reject', $seminar), [
            'rejection_reason' => 'Jadwal bertabrakan dengan Rapat Senat Terbuka Universitas. Mohon digeser ke hari lain.',
        ]);
        $rejectResponse->assertRedirect(route('wadek1.seminars.index'));

        $seminar->refresh();
        $this->assertEquals(InternshipSeminar::STATUS_REJECTED, $seminar->status);
        $this->assertNotNull($seminar->wadek1_rejected_at);
        $this->assertStringContainsString('Rapat Senat Terbuka', $seminar->rejection_reason);

        // Check notification: wadek reject
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->dosenMk->id,
            'type' => 'SEMINAR_WADEK_REJECTED',
        ]);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->student->id,
            'type' => 'SEMINAR_WADEK_REJECTED',
        ]);

        // 4. Dosen MK Reschedules
        $newDate = now()->addDays(10)->format('Y-m-d');
        $rescheduleResponse = $this->actingAs($this->dosenMk)->post(route('dosen-mk.seminars.reschedule', $seminar), [
            'scheduled_date' => $newDate,
            'scheduled_time' => '14:00 - 16:00 WIB',
            'location_or_link' => 'https://meet.google.com/new-seminar-room',
            'information' => 'Jadwal telah disesuaikan pasca Rapat Senat.',
        ]);
        $rescheduleResponse->assertSessionHas('success');

        $seminar->refresh();
        $this->assertEquals(InternshipSeminar::STATUS_SCHEDULED, $seminar->status);
        $this->assertEquals(1, $seminar->reschedule_count);
        $this->assertNotNull($seminar->rescheduled_at);
        $this->assertNull($seminar->rejection_reason);
        $this->assertNull($seminar->dosbing_acknowledged_at);
        $this->assertNull($seminar->kaprodi_acknowledged_at);
        $this->assertNull($seminar->wadek1_approved_at);
        $this->assertEquals('14:00 - 16:00 WIB', $seminar->scheduled_time);

        // Check notification: jadwal berubah
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->student->id,
            'type' => 'SEMINAR_SCHEDULE_CHANGED',
        ]);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->dosbing->id,
            'type' => 'SEMINAR_SCHEDULE_CHANGED',
        ]);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->kaprodi->id,
            'type' => 'SEMINAR_SCHEDULE_CHANGED',
        ]);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->wadek1->id,
            'type' => 'SEMINAR_SCHEDULE_CHANGED',
        ]);
    }

    /**
     * Test authorization: student can only view their own seminar.
     */
    public function test_student_can_only_view_own_seminar(): void
    {
        $seminar = InternshipSeminar::create([
            'internship_application_id' => $this->application->id,
            'course_conversion_id' => $this->conversion->id,
            'course_id' => $this->course->id,
            'user_id' => $this->student->id,
            'dosen_mk_id' => $this->dosenMk->id,
            'is_required' => true,
            'status' => InternshipSeminar::STATUS_SCHEDULED,
            'scheduled_date' => now()->addDays(3)->toDateString(),
            'scheduled_time' => '10:00 WIB',
            'location_or_link' => 'Ruang 101',
        ]);

        // Owner student can view
        $ownResponse = $this->actingAs($this->student)->get(route('seminars.show', $seminar));
        $ownResponse->assertOk();

        // Other student B gets 403 Forbidden
        $otherResponse = $this->actingAs($this->studentB)->get(route('seminars.show', $seminar));
        $otherResponse->assertForbidden();
    }
}
