<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\StudyProgram;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $regularStudent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\UserSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->superadmin = User::factory()->create([
            'email' => 'superadmin.user-mgmt@magang.ac.id',
            'password' => Hash::make('AdminPass123!'),
        ]);
        $this->superadmin->assignRole('SUPERADMIN');

        $this->regularStudent = User::factory()->create([
            'email' => 'student.user-mgmt@magang.ac.id',
            'password' => Hash::make('StudentPass123!'),
        ]);
        $this->regularStudent->assignRole('MHS');
    }

    /**
     * 1. Superadmin can view users list with search and filter.
     */
    public function test_superadmin_can_view_users_with_search_and_filter(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('master.users.index', [
            'search' => 'kaprodi',
            'role' => 'KAPRODI',
        ]));

        $response->assertStatus(200);
        $response->assertSee('kaprodi@magang.ac.id');
    }

    /**
     * 2. Superadmin can create user with multiple roles, and password must be hashed.
     */
    public function test_superadmin_can_create_user_with_hashed_password_and_multiple_roles(): void
    {
        $dosenRole = Role::where('name', 'DOSEN_MK')->firstOrFail();
        $kaprodiRole = Role::where('name', 'KAPRODI')->firstOrFail();
        $superadminRole = Role::where('name', 'SUPERADMIN')->firstOrFail();
        $prodi = StudyProgram::where('code', 'TI')->firstOrFail();

        $plainPassword = 'SecretPassword2026!';

        $response = $this->actingAs($this->superadmin)->post(route('master.users.store'), [
            'name' => 'Prof. Dr. Ir. Budi Santoso',
            'email' => 'budi.santoso@magang.ac.id',
            'password' => $plainPassword,
            'password_confirmation' => $plainPassword,
            'study_program_id' => $prodi->id,
            'identifier_number' => '196805121993031001',
            'phone' => '081299887766',
            'roles' => [$dosenRole->id, $kaprodiRole->id, $superadminRole->id], // Multiple roles as per example
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('master.users.index'));

        // Verify user created
        $user = User::where('email', 'budi.santoso@magang.ac.id')->firstOrFail();

        // Verify password is hashed and matches
        $this->assertTrue(Hash::check($plainPassword, $user->password));
        $this->assertNotEquals($plainPassword, $user->password);

        // Verify multiple roles
        $this->assertTrue($user->hasRole('DOSEN_MK'));
        $this->assertTrue($user->hasRole('KAPRODI'));
        $this->assertTrue($user->hasRole('SUPERADMIN'));
        $this->assertEquals(3, $user->roles()->count());

        // Verify audit log recorded for user creation
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'USER_CREATED',
        ]);
    }

    /**
     * 3. Password is never exposed in API responses (hidden attribute).
     */
    public function test_password_is_never_exposed_in_api_response(): void
    {
        // Request JSON from users index
        $response = $this->actingAs($this->superadmin)->get(route('master.users.index'), [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);
        $json = $response->json();

        // Check each user in payload
        foreach ($json['data']['data'] as $userData) {
            $this->assertArrayNotHasKey('password', $userData);
            $this->assertArrayNotHasKey('remember_token', $userData);
        }

        // Request JSON from user detail
        $targetUser = $this->regularStudent;
        $response = $this->actingAs($this->superadmin)->get(route('master.users.show', $targetUser), [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);
        $this->assertArrayNotHasKey('password', $response->json('data'));
    }

    /**
     * 4. Superadmin can assign multiple roles and remove roles.
     */
    public function test_superadmin_can_assign_and_remove_roles(): void
    {
        $targetUser = User::factory()->create([
            'email' => 'staff.multirole@magang.ac.id',
            'password' => Hash::make('Password123!'),
        ]);

        $tuRole = Role::where('name', 'TU')->firstOrFail();
        $dosbingRole = Role::where('name', 'DOSBING')->firstOrFail();

        // Assign TU & DOSBING
        $response = $this->actingAs($this->superadmin)->put(route('master.user-roles.update', $targetUser), [
            'roles' => [$tuRole->id, $dosbingRole->id],
        ]);
        $response->assertRedirect(route('master.user-roles.index'));

        $targetUser->refresh();
        $this->assertTrue($targetUser->hasRole('TU'));
        $this->assertTrue($targetUser->hasRole('DOSBING'));
        $this->assertEquals(2, $targetUser->roles()->count());

        // Remove single role (detach DOSBING)
        $response = $this->actingAs($this->superadmin)->delete(route('master.user-roles.detach', [$targetUser, $dosbingRole]));
        $targetUser->refresh();

        $this->assertTrue($targetUser->hasRole('TU'));
        $this->assertFalse($targetUser->hasRole('DOSBING'));
        $this->assertEquals(1, $targetUser->roles()->count());

        // Audit log should record ROLES_SYNCED and ROLE_REMOVED
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $targetUser->id,
            'action' => 'ROLE_REMOVED',
        ]);
    }

    /**
     * 5. Active/inactive status toggle and authorization.
     */
    public function test_superadmin_can_toggle_user_active_status(): void
    {
        $targetUser = User::factory()->create([
            'email' => 'active.toggle@magang.ac.id',
            'is_active' => true,
        ]);

        // Toggle to inactive
        $response = $this->actingAs($this->superadmin)->post(route('master.users.toggle', $targetUser));
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'is_active' => false,
        ]);

        // Audit log should record STATUS_TOGGLED
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $targetUser->id,
            'action' => 'STATUS_TOGGLED',
        ]);

        // Toggle back to active
        $response = $this->actingAs($this->superadmin)->post(route('master.users.toggle', $targetUser));
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'is_active' => true,
        ]);
    }

    /**
     * 6. Self-privilege protection: User cannot remove SUPERADMIN role from self.
     */
    public function test_user_cannot_remove_superadmin_from_self(): void
    {
        $mhsRole = Role::where('name', 'MHS')->firstOrFail();

        // Attempt to remove SUPERADMIN from self by updating user roles to only MHS
        $response = $this->actingAs($this->superadmin)->put(route('master.user-roles.update', $this->superadmin), [
            'roles' => [$mhsRole->id],
        ]);

        // Should be blocked with error and self still has SUPERADMIN
        $this->superadmin->refresh();
        $this->assertTrue($this->superadmin->hasRole('SUPERADMIN'));

        // Attempt via detach route
        $superadminRole = Role::where('name', 'SUPERADMIN')->firstOrFail();
        $response = $this->actingAs($this->superadmin)->delete(route('master.user-roles.detach', [$this->superadmin, $superadminRole]));

        $this->superadmin->refresh();
        $this->assertTrue($this->superadmin->hasRole('SUPERADMIN'));
    }

    /**
     * 7. Self-deactivation and self-deletion are blocked.
     */
    public function test_user_cannot_deactivate_or_delete_self(): void
    {
        // Attempt self-deactivation
        $response = $this->actingAs($this->superadmin)->post(route('master.users.toggle', $this->superadmin));
        $this->superadmin->refresh();
        $this->assertTrue($this->superadmin->is_active);

        // Attempt self-deletion
        $response = $this->actingAs($this->superadmin)->delete(route('master.users.destroy', $this->superadmin));
        $this->assertDatabaseHas('users', ['id' => $this->superadmin->id]);
    }

    /**
     * 8. Regular user cannot access user management or audit logs.
     */
    public function test_regular_user_cannot_access_user_management(): void
    {
        // Users list -> 403
        $response = $this->actingAs($this->regularStudent)->get(route('master.users.index'));
        $response->assertStatus(403);

        // User creation -> 403
        $response = $this->actingAs($this->regularStudent)->post(route('master.users.store'), [
            'name' => 'Hacker User',
            'email' => 'hacker@magang.ac.id',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);
        $response->assertStatus(403);

        // Audit logs -> 403
        $response = $this->actingAs($this->regularStudent)->get(route('master.audit-logs.index'));
        $response->assertStatus(403);
    }
}
