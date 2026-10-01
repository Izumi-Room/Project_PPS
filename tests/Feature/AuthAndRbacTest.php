<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Auth\AuthorizationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAndRbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles and permissions for tests
        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * Requirement 1: User dapat login.
     */
    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@magang.ac.id',
            'password' => Hash::make('Password123!'),
        ]);
        $user->assignRole('MHS');

        $response = $this->post('/login', [
            'email' => 'testuser@magang.ac.id',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Requirement 2: User tidak dapat login dengan password salah.
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@magang.ac.id',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'testuser@magang.ac.id',
            'password' => 'WrongPassword999!',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Requirement 3: User dapat logout.
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $user->assignRole('MHS');

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * Requirement 4: User tanpa role tidak dapat mengakses protected resource.
     */
    public function test_user_without_role_cannot_access_protected_resource(): void
    {
        $userWithoutRole = User::factory()->create([
            'email' => 'norole@magang.ac.id',
        ]);

        $this->assertTrue($userWithoutRole->roles->isEmpty());

        $response = $this->actingAs($userWithoutRole)->get('/dashboard');

        // Middleware EnsureHasRole must abort with 403 Forbidden
        $response->assertStatus(403);
    }

    /**
     * Requirement 5: Mahasiswa tidak dapat mengakses halaman Superadmin.
     */
    public function test_mahasiswa_cannot_access_superadmin_page(): void
    {
        $mhs = User::factory()->create([
            'email' => 'mhs@magang.ac.id',
        ]);
        $mhs->assignRole('MHS');

        $response = $this->actingAs($mhs)->get('/admin/superadmin-panel');

        // Middleware CheckRole must abort with 403 Forbidden
        $response->assertStatus(403);
    }

    /**
     * Requirement 6: User dengan dua role mendapatkan akses dari kedua role.
     */
    public function test_user_with_dual_role_receives_access_from_both_roles(): void
    {
        $dualUser = User::factory()->create([
            'email' => 'dual@magang.ac.id',
        ]);
        $dualUser->assignRole('DOSBING');
        $dualUser->assignRole('DOSEN_MK');

        // Check model RBAC queries
        $this->assertTrue($dualUser->hasRole('DOSBING'));
        $this->assertTrue($dualUser->hasRole('DOSEN_MK'));
        $this->assertTrue($dualUser->hasAnyRole(['DOSBING', 'DOSEN_MK']));
        $this->assertTrue($dualUser->hasAllRoles(['DOSBING', 'DOSEN_MK']));

        // Check specific permissions from both roles
        $this->assertTrue($dualUser->hasPermission('guidance:logbook')); // from DOSBING
        $this->assertTrue($dualUser->hasPermission('grade:academic'));   // from DOSEN_MK

        // Check protected route access
        $response = $this->actingAs($dualUser)->get('/academic/portal');
        $response->assertStatus(200);
    }

    /**
     * Requirement 7: Kaprodi mendapatkan permission Superadmin.
     */
    public function test_kaprodi_automatically_receives_superadmin_permission_and_access(): void
    {
        $kaprodi = User::factory()->create([
            'email' => 'kaprodi@magang.ac.id',
        ]);
        $kaprodi->assignRole('KAPRODI');

        // Must inherit SUPERADMIN role check
        $this->assertTrue($kaprodi->hasRole('KAPRODI'));
        $this->assertTrue($kaprodi->hasRole('SUPERADMIN'), 'KAPRODI must automatically have SUPERADMIN role access.');

        // Must have superadmin permissions
        $this->assertTrue($kaprodi->hasPermission('access:superadmin'));
        $this->assertTrue($kaprodi->hasPermission('manage:users'));
        $this->assertTrue($kaprodi->hasPermission('approve:kaprodi'));

        // Access superadmin route
        $response = $this->actingAs($kaprodi)->get('/admin/superadmin-panel');
        $response->assertStatus(200);

        // Access superadmin API
        $apiResponse = $this->actingAs($kaprodi)->getJson('/api/superadmin/check');
        $apiResponse->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Requirement 8: Backend menolak unauthorized request walaupun frontend dimanipulasi.
     */
    public function test_backend_rejects_unauthorized_request_even_if_frontend_is_manipulated(): void
    {
        $mhs = User::factory()->create();
        $mhs->assignRole('MHS');

        // Direct service-level assertion check
        $authService = app(AuthorizationService::class);

        $this->expectException(AuthorizationException::class);
        $authService->assertRole($mhs, 'SUPERADMIN');
    }

    /**
     * Requirement 8 (Part B - HTTP level): Backend endpoint rejects unauthorized user with 403.
     */
    public function test_backend_service_action_endpoint_rejects_unauthorized_users(): void
    {
        $mhs = User::factory()->create();
        $mhs->assignRole('MHS');

        // Attacking endpoint directly via API/AJAX
        $response = $this->actingAs($mhs)->getJson('/test-service-action?role=SUPERADMIN');

        $response->assertStatus(403);
    }

    /**
     * Test Current User State Endpoint /api/me
     */
    public function test_current_user_api_endpoint_returns_user_state_and_roles(): void
    {
        $tu = User::factory()->create([
            'name' => 'Staf Tata Usaha',
            'email' => 'tu@magang.ac.id',
        ]);
        $tu->assignRole('TU');

        $response = $this->actingAs($tu)->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'authenticated' => true,
                'user' => [
                    'email' => 'tu@magang.ac.id',
                    'name' => 'Staf Tata Usaha',
                ],
                'assigned_roles' => ['TU'],
                'is_superadmin' => false,
            ]);

        $this->assertContains('verify:documents', $response->json('permissions'));
    }

    /**
     * Test Profile update and password change
     */
    public function test_user_can_update_profile_and_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Nama Awal',
            'email' => 'nama.awal@magang.ac.id',
            'password' => Hash::make('OldPassword123!'),
        ]);
        $user->assignRole('MHS');

        // Update profile
        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Nama Baru',
            'email' => 'nama.baru@magang.ac.id',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru',
            'email' => 'nama.baru@magang.ac.id',
        ]);

        // Update password
        $pwdResponse = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'OldPassword123!',
            'password' => 'NewSecretPassword123!',
            'password_confirmation' => 'NewSecretPassword123!',
        ]);

        $pwdResponse->assertRedirect();
        $user->refresh();
        $this->assertTrue(Hash::check('NewSecretPassword123!', $user->password));
    }
}
