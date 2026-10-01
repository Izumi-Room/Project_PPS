<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\InternshipAdvisorAssignment;
use App\Models\InternshipApplication;
use App\Models\InternshipPeriod;
use App\Models\PartnerInstitution;
use App\Models\StudyProgram;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdvisorAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $kaprodiUser;
    protected User $dosbingA;
    protected User $dosbingB;
    protected User $regularUser;
    protected StudyProgram $prodi;
    protected PartnerInstitution $institution;
    protected InternshipPeriod $activePeriod;
    protected InternshipApplication $approvedApplication;
    protected InternshipApplication $unapprovedApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);
        $this->seed(UserSeeder::class);

        $this->prodi = StudyProgram::where('code', 'TI')->firstOrFail();
        $this->institution = PartnerInstitution::where('is_active', true)->firstOrFail();
        $this->activePeriod = InternshipPeriod::where('is_active', true)->firstOrFail();

        // 1. Mahasiswa
        $this->student = User::factory()->create([
            'email' => 'student.adv@magang.ac.id',
            'name' => 'Dimas Wicaksono',
            'identifier_number' => '2201010077',
            'phone' => '081234567890',
            'study_program_id' => $this->prodi->id,
            'password' => Hash::make('Password123!'),
        ]);
        $this->student->assignRole('MHS');

        // 2. Kaprodi
        $this->kaprodiUser = User::where('email', 'kaprodi@magang.ac.id')->firstOrFail();

        // 3. Dosbing A & Dosbing B
        $this->dosbingA = User::where('email', 'dosbing@magang.ac.id')->firstOrFail();
        $this->dosbingA->update(['identifier_number' => '0012057501']); // NIDN

        $this->dosbingB = User::factory()->create([
            'email' => 'dosbing.b@magang.ac.id',
            'name' => 'Dr. Suryadi, S.T., M.T.',
            'identifier_number' => '0015088002',
            'study_program_id' => $this->prodi->id,
            'password' => Hash::make('Password123!'),
        ]);
        $this->dosbingB->assignRole('DOSBING');

        // 4. Regular User without DOSBING role
        $this->regularUser = User::where('email', 'tu@magang.ac.id')->firstOrFail();

        // 5. Approved Application (Disetujui Wadek 1)
        $this->approvedApplication = InternshipApplication::factory()->create([
            'user_id' => $this->student->id,
            'study_program_id' => $this->prodi->id,
            'partner_institution_id' => $this->institution->id,
            'internship_period_id' => $this->activePeriod->id,
            'student_name' => $this->student->name,
            'student_nim' => $this->student->identifier_number,
            'student_phone' => $this->student->phone,
            'status' => InternshipApplication::STATUS_APPROVED,
            'advisor_status' => InternshipApplication::STATUS_ADVISOR_UNASSIGNED,
        ]);

        // 6. Unapproved Application (Still in submission / review stage)
        $this->unapprovedApplication = InternshipApplication::factory()->create([
            'user_id' => $this->student->id,
            'study_program_id' => $this->prodi->id,
            'partner_institution_id' => $this->institution->id,
            'internship_period_id' => $this->activePeriod->id,
            'student_name' => $this->student->name,
            'student_nim' => $this->student->identifier_number,
            'student_phone' => $this->student->phone,
            'status' => InternshipApplication::STATUS_SUBMITTED,
            'advisor_status' => InternshipApplication::STATUS_ADVISOR_UNASSIGNED,
        ]);
    }

    /**
     * 1. Kaprodi can view queue of students ready for advisor assignment.
     * Only applications approved by Wadek 1 must appear.
     */
    public function test_kaprodi_can_view_queue_of_students_ready_for_advisor_assignment(): void
    {
        $response = $this->actingAs($this->kaprodiUser)->get(route('kaprodi.advisors.index', ['tab' => 'ready']));

        $response->assertStatus(200);
        $response->assertSee($this->approvedApplication->student_name);
        $response->assertSee($this->approvedApplication->student_nim);
        
        // Unapproved application must NOT appear in the ready queue
        $this->assertFalse(
            InternshipApplication::readyForAdvisorAssignment()->where('id', $this->unapprovedApplication->id)->exists()
        );
    }

    /**
     * 2. Assignment valid: Kaprodi can assign a DOSBING lecturer to an approved application.
     */
    public function test_assignment_valid_for_approved_application(): void
    {
        $response = $this->actingAs($this->kaprodiUser)->post(route('kaprodi.advisors.assign', $this->approvedApplication), [
            'advisor_id' => $this->dosbingA->id,
            'notes' => 'Harap fokus membimbing topik arsitektur cloud backend.',
        ]);

        $response->assertRedirect(route('kaprodi.advisors.index'));

        $this->approvedApplication->refresh();
        $this->assertEquals($this->dosbingA->id, $this->approvedApplication->advisor_id);
        $this->assertEquals(InternshipApplication::STATUS_ADVISOR_PENDING, $this->approvedApplication->advisor_status);

        // Check assignment record
        $assignment = InternshipAdvisorAssignment::where('internship_application_id', $this->approvedApplication->id)->firstOrFail();
        $this->assertEquals($this->dosbingA->id, $assignment->advisor_id);
        $this->assertEquals($this->kaprodiUser->id, $assignment->assigned_by);
        $this->assertEquals(InternshipAdvisorAssignment::STATUS_PENDING, $assignment->status);
        $this->assertEquals('Harap fokus membimbing topik arsitektur cloud backend.', $assignment->notes);

        // Verify Notifications
        // Notification to Dosen
        $this->assertDatabaseHas('app_notifications', [
            'type' => 'SUBMISSION',
            'user_id' => $this->dosbingA->id,
        ]);

        // Notification to Mahasiswa
        $this->assertDatabaseHas('app_notifications', [
            'type' => 'SUBMISSION',
            'user_id' => $this->student->id,
        ]);
    }

    /**
     * 3. Assignment invalid: Kaprodi cannot assign advisor to unapproved application.
     */
    public function test_assignment_invalid_for_unapproved_application_fails(): void
    {
        $response = $this->actingAs($this->kaprodiUser)->post(route('kaprodi.advisors.assign', $this->unapprovedApplication), [
            'advisor_id' => $this->dosbingA->id,
        ]);

        $response->assertSessionHasErrors('application');

        $this->unapprovedApplication->refresh();
        $this->assertNull($this->unapprovedApplication->advisor_id);
        $this->assertEquals(InternshipApplication::STATUS_ADVISOR_UNASSIGNED, $this->unapprovedApplication->advisor_status);
    }

    /**
     * 4. Assignment invalid: Selected user must have DOSBING role.
     */
    public function test_assignment_fails_if_selected_user_is_not_dosbing(): void
    {
        $response = $this->actingAs($this->kaprodiUser)->post(route('kaprodi.advisors.assign', $this->approvedApplication), [
            'advisor_id' => $this->regularUser->id, // TU user, does not have DOSBING role
        ]);

        $response->assertSessionHasErrors('advisor_id');

        $this->approvedApplication->refresh();
        $this->assertNull($this->approvedApplication->advisor_id);
    }

    /**
     * 5. Duplicate assignment prevention:
     * Cannot assign another advisor while an assignment is already pending or accepted.
     */
    public function test_duplicate_assignment_prevention(): void
    {
        // First assignment
        $this->actingAs($this->kaprodiUser)->post(route('kaprodi.advisors.assign', $this->approvedApplication), [
            'advisor_id' => $this->dosbingA->id,
        ]);

        $this->approvedApplication->refresh();
        $this->assertEquals(InternshipApplication::STATUS_ADVISOR_PENDING, $this->approvedApplication->advisor_status);

        // Second assignment attempt to Dosbing B while first is active -> Must fail!
        $duplicateResponse = $this->actingAs($this->kaprodiUser)->post(route('kaprodi.advisors.assign', $this->approvedApplication), [
            'advisor_id' => $this->dosbingB->id,
        ]);

        $duplicateResponse->assertSessionHasErrors('advisor');

        // Verify advisor remains Dosbing A and only 1 assignment exists
        $this->assertEquals(1, $this->approvedApplication->advisorAssignments()->count());
        $this->assertEquals($this->dosbingA->id, $this->approvedApplication->fresh()->advisor_id);
    }

    /**
     * 6. Dosen can accept assignment.
     */
    public function test_dosen_can_accept_assignment(): void
    {
        // Assign first
        $this->actingAs($this->kaprodiUser)->post(route('kaprodi.advisors.assign', $this->approvedApplication), [
            'advisor_id' => $this->dosbingA->id,
        ]);

        $assignment = InternshipAdvisorAssignment::where('internship_application_id', $this->approvedApplication->id)->firstOrFail();

        // Dosen A accepts
        $response = $this->actingAs($this->dosbingA)->post(route('academic.advisor-assignments.respond', $assignment), [
            'decision' => 'ACCEPT',
        ]);

        $response->assertRedirect(route('academic.advisor-assignments.index'));

        $assignment->refresh();
        $this->assertEquals(InternshipAdvisorAssignment::STATUS_ACCEPTED, $assignment->status);
        $this->assertNotNull($assignment->responded_at);

        $this->approvedApplication->refresh();
        $this->assertEquals(InternshipApplication::STATUS_ADVISOR_ACCEPTED, $this->approvedApplication->advisor_status);
        $this->assertEquals($this->dosbingA->id, $this->approvedApplication->advisor_id);

        // Verify notifications sent to Student and Kaprodi
        $this->assertDatabaseHas('app_notifications', [
            'type' => 'APPROVAL',
            'user_id' => $this->student->id,
        ]);

        $this->assertDatabaseHas('app_notifications', [
            'type' => 'APPROVAL',
            'user_id' => $this->kaprodiUser->id,
        ]);
    }

    /**
     * 7. Dosen can reject assignment with mandatory reason, returning student to Kaprodi queue.
     */
    public function test_dosen_can_reject_assignment_with_mandatory_reason(): void
    {
        // Assign first
        $this->actingAs($this->kaprodiUser)->post(route('kaprodi.advisors.assign', $this->approvedApplication), [
            'advisor_id' => $this->dosbingA->id,
        ]);

        $assignment = InternshipAdvisorAssignment::where('internship_application_id', $this->approvedApplication->id)->firstOrFail();

        // Dosen A rejects with reason
        $response = $this->actingAs($this->dosbingA)->post(route('academic.advisor-assignments.respond', $assignment), [
            'decision' => 'REJECT',
            'reason' => 'Kuota bimbingan magang saya semester ini sudah mencapai batas maksimum 8 mahasiswa.',
        ]);

        $response->assertRedirect(route('academic.advisor-assignments.index'));

        $assignment->refresh();
        $this->assertEquals(InternshipAdvisorAssignment::STATUS_REJECTED, $assignment->status);
        $this->assertEquals('Kuota bimbingan magang saya semester ini sudah mencapai batas maksimum 8 mahasiswa.', $assignment->rejection_reason);

        // Application advisor state is reset to DITOLAK and advisor_id set to null
        $this->approvedApplication->refresh();
        $this->assertEquals(InternshipApplication::STATUS_ADVISOR_REJECTED, $this->approvedApplication->advisor_status);
        $this->assertNull($this->approvedApplication->advisor_id);

        // Student returns to Kaprodi queue!
        $this->assertTrue($this->approvedApplication->isReadyForAdvisorAssignment());
        $this->assertTrue(
            InternshipApplication::readyForAdvisorAssignment()->where('id', $this->approvedApplication->id)->exists()
        );

        // Notification to Kaprodi that lecturer rejected with reason
        $this->assertDatabaseHas('app_notifications', [
            'type' => 'REJECTION',
            'user_id' => $this->kaprodiUser->id,
        ]);
    }

    /**
     * 8. Dosen rejection without reason must fail validation.
     */
    public function test_dosen_rejection_without_reason_fails(): void
    {
        // Assign first
        $this->actingAs($this->kaprodiUser)->post(route('kaprodi.advisors.assign', $this->approvedApplication), [
            'advisor_id' => $this->dosbingA->id,
        ]);

        $assignment = InternshipAdvisorAssignment::where('internship_application_id', $this->approvedApplication->id)->firstOrFail();

        // Rejection without reason
        $response = $this->actingAs($this->dosbingA)->post(route('academic.advisor-assignments.respond', $assignment), [
            'decision' => 'REJECT',
            'reason' => '',
        ]);

        $response->assertSessionHasErrors('reason');

        $assignment->refresh();
        $this->assertEquals(InternshipAdvisorAssignment::STATUS_PENDING, $assignment->status);
    }

    /**
     * 9. Authorization: Dosen cannot respond to an assignment intended for another lecturer.
     */
    public function test_dosen_cannot_respond_to_assignment_for_another_dosen(): void
    {
        // Assigned to Dosbing A
        $this->actingAs($this->kaprodiUser)->post(route('kaprodi.advisors.assign', $this->approvedApplication), [
            'advisor_id' => $this->dosbingA->id,
        ]);

        $assignment = InternshipAdvisorAssignment::where('internship_application_id', $this->approvedApplication->id)->firstOrFail();

        // Dosbing B attempts to respond to Dosbing A's assignment -> 403 Forbidden!
        $response = $this->actingAs($this->dosbingB)->post(route('academic.advisor-assignments.respond', $assignment), [
            'decision' => 'ACCEPT',
        ]);

        $response->assertStatus(403);
    }

    /**
     * 10. Authorization: Unauthorized roles cannot access Kaprodi or Dosbing queues.
     */
    public function test_unauthorized_user_cannot_access_queues(): void
    {
        // Student cannot access Kaprodi advisor assignment queue -> 403
        $this->actingAs($this->student)->get(route('kaprodi.advisors.index'))->assertStatus(403);

        // Student cannot access Academic advisor assignments queue -> 403
        $this->actingAs($this->student)->get(route('academic.advisor-assignments.index'))->assertStatus(403);
    }

    /**
     * 11. Mahasiswa can view assigned Dosen's name, NIDN, and status.
     */
    public function test_mahasiswa_can_view_assigned_dosen_details(): void
    {
        // Kaprodi assigns Dosbing A
        $this->actingAs($this->kaprodiUser)->post(route('kaprodi.advisors.assign', $this->approvedApplication), [
            'advisor_id' => $this->dosbingA->id,
        ]);

        // Student views their own application
        $response = $this->actingAs($this->student)->get(route('internships.show', $this->approvedApplication));

        $response->assertStatus(200);
        $response->assertSee($this->dosbingA->name);
        $response->assertSee('0012057501'); // NIDN
        $response->assertSee('Menunggu Konfirmasi Dosen');
    }
}
