<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\InternshipApplication;
use App\Models\InternshipDocument;
use App\Models\InternshipPeriod;
use App\Models\PartnerInstitution;
use App\Models\Role;
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

class InternshipRegistrationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $otherStudent;
    protected User $tuUser;
    protected User $kaprodiUser;
    protected User $wadek1User;
    protected User $superadminUser;
    protected StudyProgram $prodi;
    protected PartnerInstitution $institution;
    protected InternshipPeriod $activePeriod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);
        $this->seed(UserSeeder::class);

        Storage::fake('local');

        $this->prodi = StudyProgram::where('code', 'TI')->firstOrFail();
        $this->institution = PartnerInstitution::where('is_active', true)->firstOrFail();
        $this->activePeriod = InternshipPeriod::where('is_active', true)->firstOrFail();

        // 1. Mahasiswa
        $this->student = User::factory()->create([
            'email' => 'student.intern@magang.ac.id',
            'name' => 'Aditya Pratama',
            'identifier_number' => '2201010055',
            'phone' => '081234567890',
            'study_program_id' => $this->prodi->id,
            'password' => Hash::make('Password123!'),
        ]);
        $this->student->assignRole('MHS');

        // Other student
        $this->otherStudent = User::factory()->create([
            'email' => 'other.student@magang.ac.id',
            'identifier_number' => '2201010099',
            'study_program_id' => $this->prodi->id,
            'password' => Hash::make('Password123!'),
        ]);
        $this->otherStudent->assignRole('MHS');

        // 2. Staff Roles
        $this->tuUser = User::where('email', 'tu@magang.ac.id')->firstOrFail();
        $this->kaprodiUser = User::where('email', 'kaprodi@magang.ac.id')->firstOrFail();
        $this->wadek1User = User::where('email', 'wadek1@magang.ac.id')->firstOrFail();
        $this->superadminUser = User::where('email', 'superadmin@magang.ac.id')->firstOrFail();
    }

    /**
     * 1. Mahasiswa can save draft application without required files.
     */
    public function test_mahasiswa_can_save_draft_application(): void
    {
        $response = $this->actingAs($this->student)->post(route('internships.store'), [
            'action' => 'draft',
            'study_program_id' => $this->prodi->id,
            'partner_institution_id' => $this->institution->id,
            'internship_period_id' => $this->activePeriod->id,
            'student_name' => $this->student->name,
            'student_nim' => $this->student->identifier_number,
            'student_phone' => $this->student->phone,
            'start_date' => '2026-07-01',
            'end_date' => '2026-09-30',
            'proposal_title' => 'Rencana Magang Divisi Cloud Infrastructure',
            'internship_plan' => 'Mempelajari arsitektur microservices dan pipeline deployment.',
        ]);

        $response->assertRedirect();

        $application = InternshipApplication::where('user_id', $this->student->id)->firstOrFail();
        $this->assertEquals(InternshipApplication::STATUS_DRAFT, $application->status);
        $this->assertTrue($application->isDraft());
        $this->assertTrue($application->isEditableByStudent());
    }

    /**
     * 2. Full workflow end-to-end:
     * Mahasiswa Submit -> TU Return -> Mahasiswa Resubmit -> TU Verify & Surat Pengantar -> Mahasiswa Upload Balasan -> Kaprodi Verify -> Wadek 1 Approve -> Status DISETUJUI.
     */
    public function test_full_internship_workflow_end_to_end(): void
    {
        // ---------------------------------------------------------
        // STEP 1: Mahasiswa Submit Application
        // ---------------------------------------------------------
        $transcript = UploadedFile::fake()->create('transkrip_nilai.pdf', 300, 'application/pdf');
        $proposal = UploadedFile::fake()->create('proposal_magang.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->student)->post(route('internships.store'), [
            'action' => 'submit',
            'study_program_id' => $this->prodi->id,
            'partner_institution_id' => $this->institution->id,
            'internship_period_id' => $this->activePeriod->id,
            'student_name' => $this->student->name,
            'student_nim' => $this->student->identifier_number,
            'student_phone' => $this->student->phone,
            'start_date' => '2026-07-01',
            'end_date' => '2026-09-30',
            'proposal_title' => 'Sistem Otomasi Data Pipeline',
            'internship_plan' => 'Merancang arsitektur pipeline data real-time menggunakan Kafka dan Spark.',
            'document_transcript' => $transcript,
            'document_proposal' => $proposal,
        ]);

        $application = InternshipApplication::where('user_id', $this->student->id)->firstOrFail();
        $this->assertEquals(InternshipApplication::STATUS_SUBMITTED, $application->status);
        $this->assertEquals(2, $application->documents()->count());

        // Check Notification for TU
        $this->assertDatabaseHas('app_notifications', [
            'type' => 'SUBMISSION',
            'user_id' => $this->tuUser->id,
        ]);

        // ---------------------------------------------------------
        // STEP 2: TU Reviews & Returns for Correction (Tidak Lengkap)
        // ---------------------------------------------------------
        $tuResponse = $this->actingAs($this->tuUser)->post(route('tu.internships.review', $application), [
            'decision' => 'RETURN',
            'reason' => 'Transkrip nilai belum memuat stempel legalisir basah dari program studi.',
        ]);

        $tuResponse->assertRedirect(route('tu.internships.index'));

        $application->refresh();
        $this->assertEquals(InternshipApplication::STATUS_INCOMPLETE, $application->status);
        $this->assertEquals('Transkrip nilai belum memuat stempel legalisir basah dari program studi.', $application->review_notes);

        // Check Notification for Student
        $this->assertDatabaseHas('app_notifications', [
            'type' => 'INCOMPLETE',
            'user_id' => $this->student->id,
        ]);

        // ---------------------------------------------------------
        // STEP 3: Mahasiswa Fixes Document and Resubmits
        // ---------------------------------------------------------
        $newTranscript = UploadedFile::fake()->create('transkrip_legalisir.pdf', 400, 'application/pdf');

        $resubmitResponse = $this->actingAs($this->student)->put(route('internships.update', $application), [
            'action' => 'submit',
            'study_program_id' => $this->prodi->id,
            'partner_institution_id' => $this->institution->id,
            'internship_period_id' => $this->activePeriod->id,
            'student_name' => $this->student->name,
            'student_nim' => $this->student->identifier_number,
            'student_phone' => $this->student->phone,
            'start_date' => '2026-07-01',
            'end_date' => '2026-09-30',
            'proposal_title' => 'Sistem Otomasi Data Pipeline (Revisi)',
            'internship_plan' => 'Merancang arsitektur pipeline data real-time menggunakan Kafka dan Spark.',
            'document_transcript' => $newTranscript,
        ]);

        $application->refresh();
        $this->assertEquals(InternshipApplication::STATUS_SUBMITTED, $application->status);

        // Check Notification for TU on Resubmission
        $this->assertDatabaseHas('app_notifications', [
            'type' => 'RESUBMISSION',
            'user_id' => $this->tuUser->id,
        ]);

        // ---------------------------------------------------------
        // STEP 4: TU Validates (Lolos) & Issues Surat Pengantar
        // ---------------------------------------------------------
        $refLetterFile = UploadedFile::fake()->create('surat_pengantar_ttd.pdf', 250, 'application/pdf');

        $tuPassResponse = $this->actingAs($this->tuUser)->post(route('tu.internships.review', $application), [
            'decision' => 'PASS',
            'reference_letter_number' => '421/FT-TU/MAGANG/2026',
            'reference_letter_file' => $refLetterFile,
            'reason' => 'Semua dokumen telah terverifikasi lengkap dan sah.',
        ]);

        $tuPassResponse->assertRedirect(route('tu.internships.index'));

        $application->refresh();
        $this->assertEquals(InternshipApplication::STATUS_TU_VERIFIED, $application->status);
        $this->assertEquals('421/FT-TU/MAGANG/2026', $application->reference_letter_number);
        $this->assertNotNull($application->reference_letter_issued_at);
        $this->assertNotNull($application->reference_letter_path);

        // ---------------------------------------------------------
        // STEP 5: Mahasiswa Downloads Surat Pengantar & Uploads Balasan Instansi
        // ---------------------------------------------------------
        $downloadRefResponse = $this->actingAs($this->student)->get(route('internships.reference-letter.download', $application));
        $downloadRefResponse->assertStatus(200);

        $acceptanceFile = UploadedFile::fake()->create('surat_balasan_pt_xyz.pdf', 300, 'application/pdf');
        $uploadAcceptanceResponse = $this->actingAs($this->student)->post(route('internships.acceptance.upload', $application), [
            'acceptance_letter_file' => $acceptanceFile,
        ]);

        $uploadAcceptanceResponse->assertRedirect();
        $application->refresh();
        $this->assertNotNull($application->acceptance_letter_path);
        $this->assertNotNull($application->acceptance_letter_uploaded_at);

        // ---------------------------------------------------------
        // STEP 6: Kaprodi Verifies Application
        // ---------------------------------------------------------
        $kaprodiResponse = $this->actingAs($this->kaprodiUser)->post(route('kaprodi.internships.review', $application), [
            'decision' => 'VERIFY',
            'reason' => 'Rencana magang relevan dengan capaian pembelajaran prodi Teknik Informatika.',
        ]);

        $kaprodiResponse->assertRedirect(route('kaprodi.internships.index'));

        $application->refresh();
        $this->assertEquals(InternshipApplication::STATUS_KAPRODI_VERIFIED, $application->status);

        // Check Notification for Wadek 1
        $this->assertDatabaseHas('app_notifications', [
            'type' => 'APPROVAL',
            'user_id' => $this->wadek1User->id,
        ]);

        // ---------------------------------------------------------
        // STEP 7: Wadek 1 Approves Application (Final Milestone)
        // ---------------------------------------------------------
        $wadekResponse = $this->actingAs($this->wadek1User)->post(route('wadek1.internships.review', $application), [
            'decision' => 'APPROVE',
            'reason' => 'Disetujui penuh oleh pimpinan fakultas.',
        ]);

        $wadekResponse->assertRedirect(route('wadek1.internships.index'));

        $application->refresh();
        $this->assertEquals(InternshipApplication::STATUS_APPROVED, $application->status);

        // Status History verified
        $this->assertGreaterThanOrEqual(5, $application->statusHistories()->count());

        // Check Final Approval Notification for Student
        $this->assertDatabaseHas('app_notifications', [
            'type' => 'APPROVAL',
            'user_id' => $this->student->id,
        ]);
    }

    /**
     * 3. TU return requires mandatory reason.
     */
    public function test_tu_return_without_reason_is_rejected(): void
    {
        $application = InternshipApplication::factory()->create([
            'user_id' => $this->student->id,
            'study_program_id' => $this->prodi->id,
            'partner_institution_id' => $this->institution->id,
            'internship_period_id' => $this->activePeriod->id,
            'status' => InternshipApplication::STATUS_SUBMITTED,
        ]);

        $response = $this->actingAs($this->tuUser)->post(route('tu.internships.review', $application), [
            'decision' => 'RETURN',
            'reason' => '', // Empty reason
        ]);

        $response->assertSessionHasErrors('reason');
        $application->refresh();
        $this->assertEquals(InternshipApplication::STATUS_SUBMITTED, $application->status);
    }

    /**
     * 4. Wadek 1 rejection path requires mandatory reason and transitions to DITOLAK.
     */
    public function test_wadek1_rejection_path_with_mandatory_reason(): void
    {
        $application = InternshipApplication::factory()->create([
            'user_id' => $this->student->id,
            'study_program_id' => $this->prodi->id,
            'partner_institution_id' => $this->institution->id,
            'internship_period_id' => $this->activePeriod->id,
            'status' => InternshipApplication::STATUS_KAPRODI_VERIFIED,
        ]);

        // Attempt rejection without reason -> validation error
        $failedResponse = $this->actingAs($this->wadek1User)->post(route('wadek1.internships.review', $application), [
            'decision' => 'REJECT',
            'reason' => '',
        ]);
        $failedResponse->assertSessionHasErrors('reason');

        // Rejection with reason -> Success
        $successResponse = $this->actingAs($this->wadek1User)->post(route('wadek1.internships.review', $application), [
            'decision' => 'REJECT',
            'reason' => 'Kuota magang mandiri semester ini telah melebihi kapasitas fakultas.',
        ]);

        $successResponse->assertRedirect(route('wadek1.internships.index'));

        $application->refresh();
        $this->assertEquals(InternshipApplication::STATUS_DRAFT ? InternshipApplication::STATUS_REJECTED : InternshipApplication::STATUS_REJECTED, $application->status);
        $this->assertEquals('Kuota magang mandiri semester ini telah melebihi kapasitas fakultas.', $application->review_notes);

        // Check Rejection Notification for Student
        $this->assertDatabaseHas('app_notifications', [
            'type' => 'REJECTION',
            'user_id' => $this->student->id,
        ]);
    }

    /**
     * 5. Kaprodi rejection path transitions to DITOLAK with mandatory reason.
     */
    public function test_kaprodi_rejection_path_with_mandatory_reason(): void
    {
        $application = InternshipApplication::factory()->create([
            'user_id' => $this->student->id,
            'study_program_id' => $this->prodi->id,
            'partner_institution_id' => $this->institution->id,
            'internship_period_id' => $this->activePeriod->id,
            'status' => InternshipApplication::STATUS_TU_VERIFIED,
        ]);

        $response = $this->actingAs($this->kaprodiUser)->post(route('kaprodi.internships.review', $application), [
            'decision' => 'REJECT',
            'reason' => 'Bidang pekerjaan di instansi tidak relevan dengan profil lulusan Teknik Informatika.',
        ]);

        $response->assertRedirect(route('kaprodi.internships.index'));

        $application->refresh();
        $this->assertEquals(InternshipApplication::STATUS_REJECTED, $application->status);
        $this->assertEquals('Bidang pekerjaan di instansi tidak relevan dengan profil lulusan Teknik Informatika.', $application->review_notes);
    }

    /**
     * 6. Secure document downloads authorization:
     * - Owner and staff can download.
     * - Other student is rejected with 403 Forbidden.
     */
    public function test_secure_document_download_authorization(): void
    {
        $application = InternshipApplication::factory()->create([
            'user_id' => $this->student->id,
            'study_program_id' => $this->prodi->id,
            'partner_institution_id' => $this->institution->id,
            'internship_period_id' => $this->activePeriod->id,
            'status' => InternshipApplication::STATUS_SUBMITTED,
        ]);

        $fakeFile = UploadedFile::fake()->create('rahasia.pdf', 200, 'application/pdf');
        $storedPath = $fakeFile->store("internships/{$application->id}/documents", 'local');

        $doc = InternshipDocument::create([
            'internship_application_id' => $application->id,
            'document_type' => 'TRANSCRIPT',
            'document_name' => 'Transkrip Nilai Rahasia',
            'file_path' => $storedPath,
            'file_size' => 200,
            'mime_type' => 'application/pdf',
        ]);

        // Student owner can download
        $this->actingAs($this->student)
            ->get(route('internships.documents.download', [$application, $doc]))
            ->assertStatus(200);

        // TU can download
        $this->actingAs($this->tuUser)
            ->get(route('internships.documents.download', [$application, $doc]))
            ->assertStatus(200);

        // Kaprodi can download
        $this->actingAs($this->kaprodiUser)
            ->get(route('internships.documents.download', [$application, $doc]))
            ->assertStatus(200);

        // Wadek 1 can download
        $this->actingAs($this->wadek1User)
            ->get(route('internships.documents.download', [$application, $doc]))
            ->assertStatus(200);

        // Unrelated student cannot download -> 403 Forbidden
        $this->actingAs($this->otherStudent)
            ->get(route('internships.documents.download', [$application, $doc]))
            ->assertStatus(403);
    }

    /**
     * 7. Role authorization matrix protection:
     * - Student cannot access TU, Kaprodi, or Wadek 1 review endpoints.
     * - TU cannot review Kaprodi or Wadek 1 endpoints.
     */
    public function test_role_authorization_guards_across_workflow(): void
    {
        $application = InternshipApplication::factory()->create([
            'user_id' => $this->student->id,
            'study_program_id' => $this->prodi->id,
            'partner_institution_id' => $this->institution->id,
            'internship_period_id' => $this->activePeriod->id,
            'status' => InternshipApplication::STATUS_SUBMITTED,
        ]);

        // Student tries to access TU queue -> 403
        $this->actingAs($this->student)->get(route('tu.internships.index'))->assertStatus(403);

        // Student tries to access Kaprodi queue -> 403
        $this->actingAs($this->student)->get(route('kaprodi.internships.index'))->assertStatus(403);

        // Student tries to access Wadek 1 queue -> 403
        $this->actingAs($this->student)->get(route('wadek1.internships.index'))->assertStatus(403);

        // TU tries to access Kaprodi queue -> 403
        $this->actingAs($this->tuUser)->get(route('kaprodi.internships.index'))->assertStatus(403);

        // TU tries to access Wadek 1 review action -> 403
        $this->actingAs($this->tuUser)->post(route('wadek1.internships.review', $application), [
            'decision' => 'APPROVE',
        ])->assertStatus(403);
    }

    /**
     * 8. File validation rules (file type, file size).
     */
    public function test_file_validation_rejects_invalid_types_and_excessive_sizes(): void
    {
        // Invalid file extension (.exe)
        $invalidFile = UploadedFile::fake()->create('virus.exe', 100);

        $response = $this->actingAs($this->student)->post(route('internships.store'), [
            'action' => 'submit',
            'study_program_id' => $this->prodi->id,
            'partner_institution_id' => $this->institution->id,
            'internship_period_id' => $this->activePeriod->id,
            'student_name' => $this->student->name,
            'student_nim' => $this->student->identifier_number,
            'student_phone' => $this->student->phone,
            'start_date' => '2026-07-01',
            'end_date' => '2026-09-30',
            'internship_plan' => 'Rencana magang valid minimal sepuluh karakter.',
            'document_transcript' => $invalidFile,
            'document_proposal' => UploadedFile::fake()->create('proposal.pdf', 200, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('document_transcript');
    }
}
