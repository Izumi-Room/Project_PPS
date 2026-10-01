<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Course;
use App\Models\CourseConversion;
use App\Models\CourseConversionStatusHistory;
use App\Models\InternshipAdvisorAssignment;
use App\Models\InternshipApplication;
use App\Models\InternshipLogbook;
use App\Models\InternshipPeriod;
use App\Models\PartnerInstitution;
use App\Models\StudyProgram;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseConversionAndLogbookTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $studentB;
    protected User $dosenMk;
    protected User $dosbing;
    protected User $dosbingOther;
    protected User $wadek1;
    protected User $kaprodi;
    protected Course $course;
    protected InternshipApplication $approvedApplication;

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

        // 1. Mahasiswa A
        $this->student = User::factory()->create([
            'email' => 'student.p4@magang.ac.id',
            'name' => 'Aditya Pratama',
            'identifier_number' => '2201010088',
            'phone' => '081234567891',
            'study_program_id' => $prodi->id,
            'password' => Hash::make('Password123!'),
        ]);
        $this->student->assignRole('MHS');

        // 2. Mahasiswa B (for ownership checks)
        $this->studentB = User::factory()->create([
            'email' => 'student.b@magang.ac.id',
            'name' => 'Bambang Sudirman',
            'identifier_number' => '2201010099',
            'study_program_id' => $prodi->id,
            'password' => Hash::make('Password123!'),
        ]);
        $this->studentB->assignRole('MHS');

        // 3. Reviewers
        $this->dosenMk = User::where('email', 'dosenmk@magang.ac.id')->firstOrFail();
        $this->dosbing = User::where('email', 'dosbing@magang.ac.id')->firstOrFail();
        $this->dosbingOther = User::factory()->create([
            'email' => 'dosbing.other@magang.ac.id',
            'name' => 'Dr. Bambang Other, M.T.',
            'password' => Hash::make('Password123!'),
        ]);
        $this->dosbingOther->assignRole('DOSBING');

        $this->wadek1 = User::where('email', 'wadek1@magang.ac.id')->firstOrFail();
        $this->kaprodi = User::where('email', 'kaprodi@magang.ac.id')->firstOrFail();

        // 4. Approved Internship with accepted advisor
        $this->approvedApplication = InternshipApplication::create([
            'user_id' => $this->student->id,
            'study_program_id' => $prodi->id,
            'partner_institution_id' => $institution->id,
            'internship_period_id' => $period->id,
            'student_name' => $this->student->name,
            'student_nim' => $this->student->identity_number,
            'student_phone' => $this->student->phone,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'proposal_title' => 'Implementasi Modul Otentikasi dan API Magang',
            'internship_plan' => 'Rencana kegiatan magang mencakup analisis sistem, perancangan database, dan coding.',
            'status' => InternshipApplication::STATUS_APPROVED,
            'advisor_id' => $this->dosbing->id,
            'advisor_status' => InternshipApplication::STATUS_ADVISOR_ACCEPTED,
        ]);
    }

    /**
     * Test valid course conversion end-to-end flow.
     */
    public function test_valid_conversion_end_to_end_flow(): void
    {
        // 1. Mahasiswa submits conversion
        $response = $this->actingAs($this->student)->post(route('conversions.store'), [
            'internship_application_id' => $this->approvedApplication->id,
            'course_id' => $this->course->id,
            'activity_plan' => 'Pengembangan fitur backend API sesuai dengan kurikulum pemrograman web lanjutan dan basis data.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('course_conversions', [
            'internship_application_id' => $this->approvedApplication->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => CourseConversion::STATUS_SUBMITTED,
        ]);

        $conversion = CourseConversion::where('internship_application_id', $this->approvedApplication->id)->firstOrFail();

        // Audit entry recorded
        $this->assertDatabaseHas('course_conversion_status_histories', [
            'course_conversion_id' => $conversion->id,
            'action' => 'SUBMIT',
            'to_status' => CourseConversion::STATUS_SUBMITTED,
        ]);

        // 2. Dosen MK reviews and approves
        $responseDosenMk = $this->actingAs($this->dosenMk)->post(route('dosen-mk.conversions.review', $conversion->id), [
            'action' => 'APPROVE',
            'notes' => 'Materi backend sesuai dengan CPMK mata kuliah.',
        ]);

        $responseDosenMk->assertRedirect();
        $conversion->refresh();
        $this->assertEquals(CourseConversion::STATUS_APPROVED_DOSEN_MK, $conversion->status);
        $this->assertEquals($this->dosenMk->id, $conversion->dosen_mk_id);
        $this->assertNotNull($conversion->dosen_mk_approved_at);

        // 3. Dosen Pembimbing verifies
        $responseDosbing = $this->actingAs($this->dosbing)->post(route('academic.conversions.review', $conversion->id), [
            'action' => 'APPROVE',
            'notes' => 'Beban jam kerja magang mahasiswa terverifikasi mencukupi 3 SKS.',
        ]);

        $responseDosbing->assertRedirect();
        $conversion->refresh();
        $this->assertEquals(CourseConversion::STATUS_VERIFIED_DOSBING, $conversion->status);
        $this->assertEquals($this->dosbing->id, $conversion->dosbing_id);
        $this->assertNotNull($conversion->dosbing_verified_at);

        // 4. Wadek 1 approves
        $responseWadek1 = $this->actingAs($this->wadek1)->post(route('wadek1.conversions.review', $conversion->id), [
            'action' => 'APPROVE',
            'notes' => 'Disetujui untuk konversi nilai akademik fakultas.',
        ]);

        $responseWadek1->assertRedirect();
        $conversion->refresh();
        $this->assertEquals(CourseConversion::STATUS_APPROVED_WADEK1, $conversion->status);
        $this->assertEquals($this->wadek1->id, $conversion->wadek1_id);
        $this->assertNotNull($conversion->wadek1_approved_at);

        // 5. Kaprodi marks "Diketahui"
        $responseKaprodi = $this->actingAs($this->kaprodi)->post(route('kaprodi.conversions.acknowledge', $conversion->id), [
            'notes' => 'Dicatat dalam rekapitulasi KRS konversi program studi.',
        ]);

        $responseKaprodi->assertRedirect();
        $conversion->refresh();
        $this->assertEquals(CourseConversion::STATUS_ACKNOWLEDGED_KAPRODI, $conversion->status);
        $this->assertEquals($this->kaprodi->id, $conversion->kaprodi_id);
        $this->assertNotNull($conversion->kaprodi_acknowledged_at);

        // Verify status histories completeness
        $this->assertEquals(5, CourseConversionStatusHistory::where('course_conversion_id', $conversion->id)->count());

        // Verify notifications received by student
        $studentNotifs = AppNotification::where('user_id', $this->student->id)->get();
        $this->assertTrue($studentNotifs->isNotEmpty());
    }

    /**
     * Test invalid conversion state transitions are strictly prevented.
     */
    public function test_invalid_conversion_transitions_prevented(): void
    {
        $conversion = CourseConversion::create([
            'internship_application_id' => $this->approvedApplication->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'activity_plan' => 'Rencana kegiatan pengujian sistem.',
            'status' => CourseConversion::STATUS_SUBMITTED, // Current: DIAJUKAN
        ]);

        // Attempt 1: Dosbing attempts to verify before Dosen MK approves -> Must fail
        $responseDosbing = $this->actingAs($this->dosbing)->post(route('academic.conversions.review', $conversion->id), [
            'action' => 'APPROVE',
        ]);
        $responseDosbing->assertSessionHasErrors(['status']);

        // Attempt 2: Wadek 1 attempts to approve before Dosbing verifies -> Must fail
        $responseWadek = $this->actingAs($this->wadek1)->post(route('wadek1.conversions.review', $conversion->id), [
            'action' => 'APPROVE',
        ]);
        $responseWadek->assertSessionHasErrors(['status']);

        // Attempt 3: Kaprodi attempts to acknowledge before Wadek 1 approves -> Must fail
        $responseKaprodi = $this->actingAs($this->kaprodi)->post(route('kaprodi.conversions.acknowledge', $conversion->id), [
            'notes' => 'Diketahui lebih awal',
        ]);
        $responseKaprodi->assertSessionHasErrors(['status']);
    }

    /**
     * Test rejection requires mandatory reason across all reviewers.
     */
    public function test_rejection_requires_mandatory_reason(): void
    {
        $conversion = CourseConversion::create([
            'internship_application_id' => $this->approvedApplication->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'activity_plan' => 'Rencana kegiatan.',
            'status' => CourseConversion::STATUS_SUBMITTED,
        ]);

        // 1. Dosen MK rejects without reason -> validation error
        $responseDosenMk = $this->actingAs($this->dosenMk)->post(route('dosen-mk.conversions.review', $conversion->id), [
            'action' => 'REJECT',
            'rejection_reason' => '', // Empty
        ]);
        $responseDosenMk->assertSessionHasErrors(['rejection_reason']);

        // 2. Dosen MK rejects with reason -> succeeds
        $responseDosenMkValid = $this->actingAs($this->dosenMk)->post(route('dosen-mk.conversions.review', $conversion->id), [
            'action' => 'REJECT',
            'rejection_reason' => 'Deskripsi kegiatan tidak mencakup topik keamanan database.',
        ]);
        $responseDosenMkValid->assertRedirect();
        $conversion->refresh();
        $this->assertEquals(CourseConversion::STATUS_REJECTED, $conversion->status);
        $this->assertEquals('DOSEN_MK', $conversion->rejection_stage);
        $this->assertEquals('Deskripsi kegiatan tidak mencakup topik keamanan database.', $conversion->rejection_reason);
    }

    /**
     * Test rejection and resubmission workflow.
     */
    public function test_rejection_and_resubmission_flow(): void
    {
        // Setup rejected conversion
        $conversion = CourseConversion::create([
            'internship_application_id' => $this->approvedApplication->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'activity_plan' => 'Rencana awal yang belum memadai.',
            'status' => CourseConversion::STATUS_REJECTED,
            'rejection_stage' => 'DOSEN_MK',
            'rejection_reason' => 'Perlu penambahan rincian implementasi REST API.',
        ]);

        // Student resubmits with revised activity plan
        $responseResubmit = $this->actingAs($this->student)->post(route('conversions.resubmit', $conversion->id), [
            'activity_plan' => 'Revisi: Penambahan rincian modul autentikasi JWT dan endpoints REST API Magang.',
        ]);

        $responseResubmit->assertRedirect();
        $conversion->refresh();
        $this->assertEquals(CourseConversion::STATUS_SUBMITTED, $conversion->status);
        $this->assertNull($conversion->rejection_reason);
        $this->assertNull($conversion->rejection_stage);

        // Audit entry recorded for resubmission
        $this->assertDatabaseHas('course_conversion_status_histories', [
            'course_conversion_id' => $conversion->id,
            'action' => 'RESUBMIT',
            'from_status' => CourseConversion::STATUS_REJECTED,
            'to_status' => CourseConversion::STATUS_SUBMITTED,
        ]);

        // Dosen MK can now review and approve
        $responseApprove = $this->actingAs($this->dosenMk)->post(route('dosen-mk.conversions.review', $conversion->id), [
            'action' => 'APPROVE',
            'notes' => 'Perbaikan sudah memadai.',
        ]);
        $responseApprove->assertRedirect();
        $conversion->refresh();
        $this->assertEquals(CourseConversion::STATUS_APPROVED_DOSEN_MK, $conversion->status);
    }

    /**
     * Test multi-role access control on queues and reviewer actions.
     */
    public function test_multi_role_access_control(): void
    {
        // 1. Mahasiswa cannot access reviewer queues
        $this->actingAs($this->student)->get(route('dosen-mk.conversions.index'))->assertForbidden();
        $this->actingAs($this->student)->get(route('wadek1.conversions.index'))->assertForbidden();
        $this->actingAs($this->student)->get(route('kaprodi.conversions.index'))->assertForbidden();

        // 2. Dosen without DOSEN_MK role cannot review Dosen MK queue
        $this->actingAs($this->dosbing)->get(route('dosen-mk.conversions.index'))->assertForbidden();

        // 3. Unassigned Dosen cannot verify student's conversion
        $conversion = CourseConversion::create([
            'internship_application_id' => $this->approvedApplication->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'activity_plan' => 'Rencana magang terverifikasi.',
            'status' => CourseConversion::STATUS_APPROVED_DOSEN_MK,
        ]);

        // Another dosbing attempts to verify -> must be forbidden (403)
        $this->actingAs($this->dosbingOther)->post(route('academic.conversions.review', $conversion->id), [
            'action' => 'APPROVE',
        ])->assertForbidden();
    }

    /**
     * Test weekly logbook creation and student ownership rules.
     */
    public function test_logbook_creation_and_ownership(): void
    {
        Storage::fake('public');
        $fakeFile = UploadedFile::fake()->create('laporan_minggu1.pdf', 500, 'application/pdf');

        // 1. Student A creates logbook
        $response = $this->actingAs($this->student)->post(route('logbooks.store'), [
            'internship_application_id' => $this->approvedApplication->id,
            'week_number' => 1,
            'activity_date' => now()->toDateString(),
            'activity_title' => 'Pengenalan Lingkungan Kerja & Setup Server',
            'description' => 'Melakukan on-boarding, pengenalan anggota tim, dan konfigurasi server staging.',
            'attachment' => $fakeFile,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('internship_logbooks', [
            'internship_application_id' => $this->approvedApplication->id,
            'user_id' => $this->student->id,
            'week_number' => 1,
            'activity_title' => 'Pengenalan Lingkungan Kerja & Setup Server',
        ]);

        $logbook = InternshipLogbook::where('internship_application_id', $this->approvedApplication->id)->firstOrFail();
        $this->assertNotNull($logbook->attachment_path);
        Storage::disk('public')->assertExists($logbook->attachment_path);

        // 2. Student B cannot view Student A's logbook
        $this->actingAs($this->studentB)->get(route('logbooks.show', $logbook->id))->assertForbidden();

        // 3. Student B cannot edit or update Student A's logbook
        $this->actingAs($this->studentB)->get(route('logbooks.edit', $logbook->id))->assertForbidden();
        $this->actingAs($this->studentB)->put(route('logbooks.update', $logbook->id), [
            'week_number' => 1,
            'activity_date' => now()->toDateString(),
            'activity_title' => 'Manipulasi Data Orang Lain',
            'description' => 'Mencoba mengubah data yang bukan miliknya.',
        ])->assertForbidden();

        // 4. Student B cannot delete Student A's logbook
        $this->actingAs($this->studentB)->delete(route('logbooks.destroy', $logbook->id))->assertForbidden();

        // 5. Student B cannot download Student A's attachment
        $this->actingAs($this->studentB)->get(route('logbooks.attachment.download', $logbook->id))->assertForbidden();

        // 6. Student A can download their own attachment
        $this->actingAs($this->student)->get(route('logbooks.attachment.download', $logbook->id))->assertOk();
    }

    /**
     * Test dosen access and review feedback on mentored student logbooks.
     */
    public function test_dosen_access_and_feedback_on_mentored_logbooks(): void
    {
        $logbook = InternshipLogbook::create([
            'internship_application_id' => $this->approvedApplication->id,
            'user_id' => $this->student->id,
            'week_number' => 2,
            'activity_date' => now()->toDateString(),
            'activity_title' => 'Implementasi Endpoint REST API',
            'description' => 'Membuat controller, route, dan automated testing untuk modul pengguna.',
        ]);

        // 1. Assigned advisor can view mentored student's logbooks
        $responseView = $this->actingAs($this->dosbing)->get(route('academic.logbooks.student', $this->approvedApplication->id));
        $responseView->assertOk();
        $responseView->assertSee('Implementasi Endpoint REST API');

        // 2. Assigned advisor can submit feedback
        $responseFeedback = $this->actingAs($this->dosbing)->post(route('academic.logbooks.feedback', $logbook->id), [
            'dosbing_feedback' => 'Bagus sekali, pertahankan standar code coverage dan dokumentasikan swagger API.',
        ]);

        $responseFeedback->assertRedirect();
        $logbook->refresh();
        $this->assertEquals('Bagus sekali, pertahankan standar code coverage dan dokumentasikan swagger API.', $logbook->dosbing_feedback);
        $this->assertEquals($this->dosbing->id, $logbook->reviewed_by);
        $this->assertNotNull($logbook->dosbing_reviewed_at);

        // 3. Unassigned dosbing cannot view or submit feedback
        $this->actingAs($this->dosbingOther)->get(route('academic.logbooks.student', $this->approvedApplication->id))->assertForbidden();
        $this->actingAs($this->dosbingOther)->post(route('academic.logbooks.feedback', $logbook->id), [
            'dosbing_feedback' => 'Komentar tidak berhak',
        ])->assertForbidden();
    }

    /**
     * Test Wadek 1 approval and rejection paths specifically.
     */
    public function test_wadek_approval_and_rejection(): void
    {
        $conversion = CourseConversion::create([
            'internship_application_id' => $this->approvedApplication->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'activity_plan' => 'Rencana konversi magang untuk pengujian Wadek 1.',
            'status' => CourseConversion::STATUS_VERIFIED_DOSBING,
        ]);

        // Wadek 1 rejection without reason -> validation error
        $responseRejectFail = $this->actingAs($this->wadek1)->post(route('wadek1.conversions.review', $conversion->id), [
            'action' => 'REJECT',
            'rejection_reason' => '',
        ]);
        $responseRejectFail->assertSessionHasErrors(['rejection_reason']);

        // Wadek 1 rejection with reason
        $responseRejectSuccess = $this->actingAs($this->wadek1)->post(route('wadek1.conversions.review', $conversion->id), [
            'action' => 'REJECT',
            'rejection_reason' => 'Perlu penyesuaian kurikulum dan persetujuan prodi lebih lanjut.',
        ]);
        $responseRejectSuccess->assertRedirect();
        $conversion->refresh();
        $this->assertEquals(CourseConversion::STATUS_REJECTED, $conversion->status);
        $this->assertEquals('WADEK1', $conversion->rejection_stage);

        // Reset to DIVERIFIKASI_DOSBING for approval test
        $conversion->update(['status' => CourseConversion::STATUS_VERIFIED_DOSBING]);

        $responseApprove = $this->actingAs($this->wadek1)->post(route('wadek1.conversions.review', $conversion->id), [
            'action' => 'APPROVE',
            'notes' => 'Disetujui Dekanat.',
        ]);
        $responseApprove->assertRedirect();
        $conversion->refresh();
        $this->assertEquals(CourseConversion::STATUS_APPROVED_WADEK1, $conversion->status);
    }

    /**
     * Test Kaprodi acknowledgement marking "Diketahui".
     */
    public function test_kaprodi_acknowledgement_workflow(): void
    {
        $conversion = CourseConversion::create([
            'internship_application_id' => $this->approvedApplication->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'activity_plan' => 'Rencana konversi yang telah disetujui Wadek 1.',
            'status' => CourseConversion::STATUS_APPROVED_WADEK1,
        ]);

        // Non-kaprodi cannot acknowledge
        $this->actingAs($this->student)->post(route('kaprodi.conversions.acknowledge', $conversion->id), [
            'notes' => 'Catatan ilegal',
        ])->assertForbidden();

        // Kaprodi acknowledges
        $response = $this->actingAs($this->kaprodi)->post(route('kaprodi.conversions.acknowledge', $conversion->id), [
            'notes' => 'Telah dicatat dalam transkrip nilai konversi prodi.',
        ]);
        $response->assertRedirect();
        $conversion->refresh();
        $this->assertEquals(CourseConversion::STATUS_ACKNOWLEDGED_KAPRODI, $conversion->status);
        $this->assertEquals($this->kaprodi->id, $conversion->kaprodi_id);
        $this->assertNotNull($conversion->kaprodi_acknowledged_at);
    }
}
