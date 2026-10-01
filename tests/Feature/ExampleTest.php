<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Unauthenticated user is redirected to login page.
     */
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /**
     * Authenticated user with role can access home dashboard.
     */
    public function test_authenticated_user_with_role_can_access_dashboard(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('MHS');

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }
}
