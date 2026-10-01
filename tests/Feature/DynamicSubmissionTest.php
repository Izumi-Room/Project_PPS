<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseConversion;
use App\Models\InternshipApplication;
use App\Models\InternshipPeriod;
use App\Models\PartnerInstitution;
use App\Models\StudentSubmission;
use App\Models\SubmissionComponent;
use App\Models\SubmissionVersion;
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

class DynamicSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $studentB;
    protected User $dosenMk;
    protected User $dosbing;
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

        // 1. Mahasiswa A
        $this->student = User::factory()->create([
            'email' => 'student.p5@magang.ac.id',
            'name' => 'Bintang Perkasa',
            'identifier_number' => '2201010111',
            'study_program_id' => $prodi->id,
            'password' => Hash::make('Password123!'),
        ]);
        $this->student->assignRole('MHS');

        // 2. Mahasiswa B (different student)
        $this->studentB = User::factory()->create([
            'email' => 'student.other@magang.ac.id',
            'name' => 'Doni Kusuma',
            'identifier_number' => '2201010222',
            'study_program_id' => $prodi->id,
            'password' => Hash::make('Password123!'),
        ]);
        $this->studentB->assignRole('MHS');

        // 3. Reviewers
        $this->dosenMk = User::where('email', 'dosenmk@magang.ac.id')->firstOrFail();
        $this->dosbing = User::where('email', 'dosbing@magang.ac.id')->firstOrFail();

        // 4. Approved Internship Application
        $this->application = InternshipApplication::create([
            'user_id' => $this->student->id,
            'study_program_id' => $prodi->id,
            'partner_institution_id' => $institution->id,
            'internship_period_id' => $period->id,
            'student_name' => $this->student->name,
            'student_nim' => $this->student->identifier_number,
            'student_phone' => '081234567891',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'proposal_title' => 'Sistem Pemantauan Submission Magang',
            'internship_plan' => 'Rencana kegiatan magang.',
            'status' => InternshipApplication::STATUS_APPROVED,
            'advisor_id' => $this->dosbing->id,
            'advisor_status' => InternshipApplication::STATUS_ADVISOR_ACCEPTED,
        ]);

        // 5. Approved Course Conversion
        $this->conversion = CourseConversion::create([
            'internship_application_id' => $this->application->id,
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'activity_plan' => 'Pekerjaan proyek backend yang relevan dengan silabus MK.',
            'status' => CourseConversion::STATUS_ACKNOWLEDGED_KAPRODI,
        ]);
    }

    /**
     * Test Dosen MK can create dynamic submission components.
     */
    public function test_dosen_mk_can_create_dynamic_components(): void
    {
        $response = $this->actingAs($this->dosenMk)->post(route('dosen-mk.components.store'), [
            'course_id' => $this->course->id,
            'name' => 'Laporan Akhir & Dokumentasi API',
            'submission_type' => 'FILE',
            'deadline' => now()->addDays(14)->format('Y-m-d H:i'),
            'weight' => 40,
            'is_required' => 1,
            'instructions' => 'Format berkas PDF maksimal 20 halaman.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('submission_components', [
            'course_id' => $this->course->id,
            'created_by' => $this->dosenMk->id,
            'name' => 'Laporan Akhir & Dokumentasi API',
            'submission_type' => 'FILE',
            'weight' => 40,
            'is_required' => 1,
        ]);

        // Test non-hardcoded type (e.g. LINK or PROJECT_URL)
        $response2 = $this->actingAs($this->dosenMk)->post(route('dosen-mk.components.store'), [
            'course_id' => $this->course->id,
            'name' => 'Source Code GitHub & Deployment URL',
            'submission_type' => 'LINK',
            'deadline' => now()->addDays(20)->format('Y-m-d H:i'),
            'weight' => 60,
            'is_required' => 1,
            'instructions' => 'Tautan repository publik GitHub dan URL staging.',
        ]);

        $response2->assertRedirect();
        $this->assertDatabaseHas('submission_components', [
            'name' => 'Source Code GitHub & Deployment URL',
            'submission_type' => 'LINK',
            'weight' => 60,
        ]);
    }

    /**
     * Test submission deadline validation: cannot submit after deadline.
     */
    public function test_submission_deadline_validation(): void
    {
        Storage::fake('public');

        // Create expired component
        $expiredComponent = SubmissionComponent::create([
            'course_id' => $this->course->id,
            'created_by' => $this->dosenMk->id,
            'name' => 'Tugas Sudah Kedaluwarsa',
            'submission_type' => 'FILE',
            'deadline' => now()->subDay(), // Expired yesterday
            'weight' => 20,
            'is_required' => true,
        ]);

        $fakeFile = UploadedFile::fake()->create('laporan.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->student)->post(route('submissions.submit', $expiredComponent->id), [
            'course_conversion_id' => $this->conversion->id,
            'file' => $fakeFile,
        ]);

        $response->assertSessionHasErrors(['deadline']);
        $this->assertDatabaseMissing('student_submissions', [
            'submission_component_id' => $expiredComponent->id,
            'user_id' => $this->student->id,
        ]);
    }

    /**
     * Test required component validation.
     */
    public function test_required_component_validation(): void
    {
        $component = SubmissionComponent::create([
            'course_id' => $this->course->id,
            'created_by' => $this->dosenMk->id,
            'name' => 'Berkas Wajib Laporan',
            'submission_type' => 'FILE',
            'deadline' => now()->addDays(7),
            'weight' => 30,
            'is_required' => true, // Required
        ]);

        // Attempting to submit without file and without link
        $response = $this->actingAs($this->student)->post(route('submissions.submit', $component->id), [
            'course_conversion_id' => $this->conversion->id,
            'student_notes' => 'Hanya catatan tanpa berkas',
        ]);

        $response->assertSessionHasErrors();
    }

    /**
     * Test upload, versioning, resubmission, and file preservation.
     */
    public function test_upload_versioning_and_resubmission(): void
    {
        Storage::fake('public');

        $component = SubmissionComponent::create([
            'course_id' => $this->course->id,
            'created_by' => $this->dosenMk->id,
            'name' => 'Laporan Akhir Magang',
            'submission_type' => 'FILE_OR_LINK',
            'deadline' => now()->addDays(10),
            'weight' => 50,
            'is_required' => true,
        ]);

        // 1. First Submission (Version 1)
        $fileV1 = UploadedFile::fake()->create('laporan_v1.pdf', 500, 'application/pdf');
        $responseV1 = $this->actingAs($this->student)->post(route('submissions.submit', $component->id), [
            'course_conversion_id' => $this->conversion->id,
            'file' => $fileV1,
            'student_notes' => 'Pengumpulan draf awal laporan.',
        ]);

        $responseV1->assertRedirect();

        $submission = StudentSubmission::where('submission_component_id', $component->id)
            ->where('user_id', $this->student->id)
            ->firstOrFail();

        $this->assertEquals(StudentSubmission::STATUS_SUBMITTED, $submission->status);
        $this->assertEquals(1, $submission->current_version);

        $version1 = SubmissionVersion::where('student_submission_id', $submission->id)
            ->where('version_number', 1)
            ->firstOrFail();

        $pathV1 = $version1->file_path;
        $this->assertNotNull($pathV1);
        Storage::disk('public')->assertExists($pathV1);

        // 2. Dosen MK requests revision with mandatory feedback
        $responseRevFail = $this->actingAs($this->dosenMk)->post(route('dosen-mk.submissions.review', $submission->id), [
            'action' => 'REVISION',
            'feedback' => '', // Empty feedback should fail
        ]);
        $responseRevFail->assertSessionHasErrors(['feedback']);

        $responseRevOk = $this->actingAs($this->dosenMk)->post(route('dosen-mk.submissions.review', $submission->id), [
            'action' => 'REVISION',
            'feedback' => 'Tolong lengkapi bab 4 terkait arsitektur database dan lampirkan link demo.',
        ]);
        $responseRevOk->assertRedirect();
        $submission->refresh();
        $this->assertEquals(StudentSubmission::STATUS_REVISION_NEEDED, $submission->status);

        // 3. Resubmission (Version 2)
        $fileV2 = UploadedFile::fake()->create('laporan_v2_final.pdf', 650, 'application/pdf');
        $responseV2 = $this->actingAs($this->student)->post(route('submissions.submit', $component->id), [
            'course_conversion_id' => $this->conversion->id,
            'file' => $fileV2,
            'link_url' => 'https://github.com/student/magang-repo',
            'student_notes' => 'Telah diperbaiki bab 4 dan ditambahkan repository link.',
        ]);

        $responseV2->assertRedirect();
        $submission->refresh();

        // Must update to DIKIRIM_ULANG and version 2
        $this->assertEquals(StudentSubmission::STATUS_RESUBMITTED, $submission->status);
        $this->assertEquals(2, $submission->current_version);

        $version2 = SubmissionVersion::where('student_submission_id', $submission->id)
            ->where('version_number', 2)
            ->firstOrFail();

        $pathV2 = $version2->file_path;
        $this->assertNotNull($pathV2);
        Storage::disk('public')->assertExists($pathV2);

        // CRUCIAL REQUIREMENT: File from Version 1 must NOT be deleted!
        Storage::disk('public')->assertExists($pathV1);
        $this->assertEquals(2, $submission->versions()->count());

        // 4. Dosen MK reviews Version 2 and Approves
        $responseApprove = $this->actingAs($this->dosenMk)->post(route('dosen-mk.submissions.review', $submission->id), [
            'action' => 'APPROVE',
            'feedback' => 'Sangat baik, revisi bab 4 dan repository telah sesuai.',
        ]);
        $responseApprove->assertRedirect();
        $submission->refresh();
        $this->assertEquals(StudentSubmission::STATUS_APPROVED, $submission->status);
        $this->assertEquals($this->dosenMk->id, $submission->reviewed_by);
        $this->assertNotNull($submission->reviewed_at);
    }

    /**
     * Test authorization & access rules for submissions.
     */
    public function test_submission_authorization(): void
    {
        Storage::fake('public');

        $component = SubmissionComponent::create([
            'course_id' => $this->course->id,
            'created_by' => $this->dosenMk->id,
            'name' => 'Presentasi Akhir',
            'submission_type' => 'LINK',
            'deadline' => now()->addDays(5),
            'weight' => 20,
            'is_required' => true,
        ]);

        // 1. Mahasiswa cannot create submission components
        $this->actingAs($this->student)->post(route('dosen-mk.components.store'), [
            'course_id' => $this->course->id,
            'name' => 'Komponen Ilegal',
            'submission_type' => 'FILE',
            'deadline' => now()->addDays(5),
            'weight' => 20,
        ])->assertForbidden();

        // 2. Mahasiswa cannot review submissions
        $submission = StudentSubmission::create([
            'submission_component_id' => $component->id,
            'user_id' => $this->student->id,
            'course_conversion_id' => $this->conversion->id,
            'status' => StudentSubmission::STATUS_SUBMITTED,
            'current_version' => 1,
        ]);

        $this->actingAs($this->student)->post(route('dosen-mk.submissions.review', $submission->id), [
            'action' => 'APPROVE',
        ])->assertForbidden();

        // 3. Student B cannot submit on Student A's conversion
        $this->actingAs($this->studentB)->post(route('submissions.submit', $component->id), [
            'course_conversion_id' => $this->conversion->id,
            'link_url' => 'https://drive.google.com/test',
        ])->assertForbidden();

        // 4. Student B cannot download Student A's submission file
        $version = SubmissionVersion::create([
            'student_submission_id' => $submission->id,
            'version_number' => 1,
            'file_path' => 'submissions/test/file.pdf',
            'file_name' => 'file.pdf',
            'submitted_at' => now(),
            'status' => StudentSubmission::STATUS_SUBMITTED,
        ]);

        $this->actingAs($this->studentB)->get(route('submissions.download', $version->id))->assertForbidden();
    }
}
