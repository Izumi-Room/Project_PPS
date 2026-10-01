<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\InternshipPeriod;
use App\Models\PartnerInstitution;
use App\Models\Role;
use App\Models\StudyProgram;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $kaprodi;
    protected User $regularStudent;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles, permissions, and initial master data
        $this->seed(RolePermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        // Create Superadmin user
        $this->superadmin = User::factory()->create([
            'email' => 'superadmin.test@magang.ac.id',
            'password' => Hash::make('AdminPass123!'),
        ]);
        $this->superadmin->assignRole('SUPERADMIN');

        // Create Kaprodi user (inherits Superadmin)
        $this->kaprodi = User::factory()->create([
            'email' => 'kaprodi.test@magang.ac.id',
            'password' => Hash::make('KaprodiPass123!'),
        ]);
        $this->kaprodi->assignRole('KAPRODI');

        // Create Regular Student (MHS)
        $this->regularStudent = User::factory()->create([
            'email' => 'student.test@magang.ac.id',
            'password' => Hash::make('StudentPass123!'),
        ]);
        $this->regularStudent->assignRole('MHS');
    }

    /**
     * 1. Authorization: Regular user (MHS) cannot access master data management routes.
     */
    public function test_regular_user_cannot_access_master_data_management(): void
    {
        // GET /master/study-programs returns 403
        $response = $this->actingAs($this->regularStudent)->get(route('master.study-programs.index'));
        $response->assertStatus(403);

        // POST /master/study-programs returns 403
        $response = $this->actingAs($this->regularStudent)->post(route('master.study-programs.store'), [
            'code' => 'TEST',
            'name' => 'Test Prodi',
            'degree_level' => 'S1',
            'faculty' => 'Fakultas Teknik',
        ]);
        $response->assertStatus(403);

        // POST /master/partner-institutions returns 403
        $response = $this->actingAs($this->regularStudent)->post(route('master.partner-institutions.store'), [
            'name' => 'Fake Company',
            'address' => 'Fake Address',
            'contact_person' => 'Fake PIC',
            'email' => 'fake@company.com',
            'phone' => '08123456789',
            'sector' => 'Swasta',
        ]);
        $response->assertStatus(403);

        // POST /master/courses returns 403
        $response = $this->actingAs($this->regularStudent)->post(route('master.courses.store'), [
            'study_program_id' => 1,
            'code' => 'IF999',
            'name' => 'Test Course',
            'credits' => 3,
            'semester' => 6,
        ]);
        $response->assertStatus(403);

        // POST /master/internship-periods returns 403
        $response = $this->actingAs($this->regularStudent)->post(route('master.internship-periods.store'), [
            'name' => 'Test Period',
            'academic_year' => '2026/2027',
            'semester_type' => 'GENAP',
            'start_date' => '2027-02-01',
            'end_date' => '2027-07-31',
        ]);
        $response->assertStatus(403);

        // PUT /master/user-roles/{user} returns 403
        $response = $this->actingAs($this->regularStudent)->put(route('master.user-roles.update', $this->regularStudent), [
            'roles' => [Role::where('name', 'SUPERADMIN')->first()->id],
        ]);
        $response->assertStatus(403);
    }

    /**
     * 2. Regular user CAN read active catalog endpoints.
     */
    public function test_regular_user_can_read_active_master_catalog(): void
    {
        $response = $this->actingAs($this->regularStudent)->get(route('master.catalog.periods.active'));
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('has_active_period', true);

        $response = $this->actingAs($this->regularStudent)->get(route('master.catalog.study-programs.list', ['format' => 'json']), [
            'Accept' => 'application/json',
        ]);
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    /**
     * 3. Superadmin can CRUD Study Programs.
     */
    public function test_superadmin_can_crud_study_programs(): void
    {
        // Create
        $response = $this->actingAs($this->superadmin)->post(route('master.study-programs.store'), [
            'code' => 'PWK',
            'name' => 'Perencanaan Wilayah dan Kota',
            'degree_level' => 'S1',
            'faculty' => 'Fakultas Teknik',
            'is_active' => '1',
            'description' => 'Program studi PWK.',
        ]);
        $response->assertRedirect(route('master.study-programs.index'));
        $this->assertDatabaseHas('study_programs', ['code' => 'PWK']);

        $prodi = StudyProgram::where('code', 'PWK')->firstOrFail();

        // Show
        $response = $this->actingAs($this->superadmin)->get(route('master.study-programs.show', $prodi));
        $response->assertStatus(200);
        $response->assertSee('Perencanaan Wilayah dan Kota');

        // Update
        $response = $this->actingAs($this->superadmin)->put(route('master.study-programs.update', $prodi), [
            'code' => 'PWK',
            'name' => 'Perencanaan Wilayah & Kota Terpadu',
            'degree_level' => 'S1',
            'faculty' => 'Fakultas Teknik',
            'is_active' => '1',
        ]);
        $response->assertRedirect(route('master.study-programs.index'));
        $this->assertDatabaseHas('study_programs', ['name' => 'Perencanaan Wilayah & Kota Terpadu']);

        // Toggle Status
        $response = $this->actingAs($this->superadmin)->post(route('master.study-programs.toggle', $prodi));
        $this->assertDatabaseHas('study_programs', ['id' => $prodi->id, 'is_active' => false]);

        // Delete
        $response = $this->actingAs($this->superadmin)->delete(route('master.study-programs.destroy', $prodi));
        $this->assertDatabaseMissing('study_programs', ['id' => $prodi->id]);
    }

    /**
     * 4. Superadmin can CRUD Partner Institutions.
     */
    public function test_superadmin_can_crud_partner_institutions(): void
    {
        $response = $this->actingAs($this->superadmin)->post(route('master.partner-institutions.store'), [
            'name' => 'PT Astra International Tbk',
            'address' => 'Menara Astra, Jl. Jend. Sudirman Kav. 5-6, Jakarta',
            'contact_person' => 'Dian Sastro (People Development)',
            'email' => 'internship@astra.co.id',
            'phone' => '021-50843888',
            'website' => 'https://www.astra.co.id',
            'sector' => 'Swasta',
            'is_active' => '1',
            'description' => 'Konglomerasi multinasional manufaktur dan otomotif.',
        ]);
        $response->assertRedirect(route('master.partner-institutions.index'));
        $this->assertDatabaseHas('partner_institutions', ['name' => 'PT Astra International Tbk']);

        $institution = PartnerInstitution::where('name', 'PT Astra International Tbk')->firstOrFail();

        // Update
        $response = $this->actingAs($this->superadmin)->put(route('master.partner-institutions.update', $institution), [
            'name' => 'PT Astra International Tbk (Head Office)',
            'address' => 'Menara Astra, Jl. Jend. Sudirman Kav. 5-6, Jakarta',
            'contact_person' => 'Dian Sastro (People Development)',
            'email' => 'internship@astra.co.id',
            'phone' => '021-50843888',
            'sector' => 'Swasta',
            'is_active' => '1',
        ]);
        $this->assertDatabaseHas('partner_institutions', ['name' => 'PT Astra International Tbk (Head Office)']);

        // Toggle Status
        $response = $this->actingAs($this->superadmin)->post(route('master.partner-institutions.toggle', $institution));
        $this->assertDatabaseHas('partner_institutions', ['id' => $institution->id, 'is_active' => false]);
    }

    /**
     * 5. Superadmin can CRUD Courses with Study Program relation.
     */
    public function test_superadmin_can_crud_courses(): void
    {
        $prodi = StudyProgram::where('code', 'TI')->firstOrFail();

        $response = $this->actingAs($this->superadmin)->post(route('master.courses.store'), [
            'study_program_id' => $prodi->id,
            'code' => 'IF699',
            'name' => 'Magang MBKM Terstruktur',
            'credits' => 4,
            'semester' => 7,
            'is_active' => '1',
            'description' => 'Program MBKM magang bersertifikat selama 1 semester.',
        ]);
        $response->assertRedirect(route('master.courses.index'));
        $this->assertDatabaseHas('courses', [
            'code' => 'IF699',
            'study_program_id' => $prodi->id,
            'credits' => 4,
        ]);

        $course = Course::where('code', 'IF699')->firstOrFail();

        // Update
        $response = $this->actingAs($this->superadmin)->put(route('master.courses.update', $course), [
            'study_program_id' => $prodi->id,
            'code' => 'IF699',
            'name' => 'Magang MBKM Terstruktur & Riset',
            'credits' => 4,
            'semester' => 7,
            'is_active' => '1',
        ]);
        $this->assertDatabaseHas('courses', ['name' => 'Magang MBKM Terstruktur & Riset']);

        // Delete
        $response = $this->actingAs($this->superadmin)->delete(route('master.courses.destroy', $course));
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    /**
     * 6. Periode Magang rule: only active period is selected for new registrations.
     */
    public function test_internship_period_active_selection_rule(): void
    {
        // Initial state has active period from seeder
        $activePeriod = InternshipPeriod::active()->first();
        $this->assertNotNull($activePeriod);
        $this->assertEquals('2026/2027', $activePeriod->academic_year);

        // Create a new period marked as active
        $response = $this->actingAs($this->superadmin)->post(route('master.internship-periods.store'), [
            'name' => 'Magang Semester Ganjil 2027/2028 (Baru)',
            'academic_year' => '2027/2028',
            'semester_type' => 'GANJIL',
            'start_date' => '2027-08-01',
            'end_date' => '2027-12-31',
            'is_active' => '1',
            'description' => 'Periode pendaftaran baru tahun 2027/2028.',
        ]);
        $response->assertRedirect(route('master.internship-periods.index'));

        // The newly created period should now be the only active period!
        $newActivePeriod = InternshipPeriod::where('academic_year', '2027/2028')->firstOrFail();
        $this->assertTrue($newActivePeriod->is_active);

        // Previous active period should be automatically deactivated
        $previousPeriod = InternshipPeriod::find($activePeriod->id);
        $this->assertFalse($previousPeriod->is_active);

        // Active catalog endpoint must return the new active period
        $response = $this->actingAs($this->regularStudent)->get(route('master.catalog.periods.active'));
        $response->assertStatus(200);
        $response->assertJsonPath('data.academic_year', '2027/2028');
    }

    /**
     * 7. Superadmin can CRUD Users and assign multiple roles.
     */
    public function test_superadmin_can_crud_users_and_assign_roles(): void
    {
        $dosbingRole = Role::where('name', 'DOSBING')->firstOrFail();
        $dosenMkRole = Role::where('name', 'DOSEN_MK')->firstOrFail();
        $prodi = StudyProgram::where('code', 'TI')->firstOrFail();

        // Create User with multiple roles
        $response = $this->actingAs($this->superadmin)->post(route('master.users.store'), [
            'name' => 'Dr. Hendra Gunawan, M.T.',
            'email' => 'hendra.gunawan@magang.ac.id',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'study_program_id' => $prodi->id,
            'identifier_number' => '197503122001121002',
            'phone' => '081345678999',
            'roles' => [$dosbingRole->id, $dosenMkRole->id],
            'is_active' => '1',
        ]);
        $response->assertRedirect(route('master.users.index'));

        $newUser = User::where('email', 'hendra.gunawan@magang.ac.id')->firstOrFail();
        $this->assertTrue($newUser->hasRole('DOSBING'));
        $this->assertTrue($newUser->hasRole('DOSEN_MK'));
        $this->assertEquals(2, $newUser->roles()->count());

        // Update User Roles via user-roles management route
        $tuRole = Role::where('name', 'TU')->firstOrFail();
        $response = $this->actingAs($this->superadmin)->put(route('master.user-roles.update', $newUser), [
            'roles' => [$dosbingRole->id, $tuRole->id],
        ]);
        $response->assertRedirect(route('master.user-roles.index'));

        $newUser->refresh();
        $this->assertTrue($newUser->hasRole('DOSBING'));
        $this->assertTrue($newUser->hasRole('TU'));
        $this->assertFalse($newUser->hasRole('DOSEN_MK'));
    }

    /**
     * 8. Kaprodi can manage master data because Kaprodi inherits Superadmin.
     */
    public function test_kaprodi_can_access_master_data_management(): void
    {
        $response = $this->actingAs($this->kaprodi)->get(route('master.study-programs.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->kaprodi)->get(route('master.courses.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->kaprodi)->get(route('master.internship-periods.index'));
        $response->assertStatus(200);
    }
}
