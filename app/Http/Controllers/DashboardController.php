<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\Auth\AuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected AuthorizationService $authService;

    public function __construct(AuthorizationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Main application dashboard.
     */
    public function index(): View
    {
        $user = Auth::user()->load('roles.permissions');
        $effectiveRoles = $this->authService->getEffectiveRoles($user);
        $allRoles = Role::with('permissions')->get();

        return view('dashboard.index', compact('user', 'effectiveRoles', 'allRoles'));
    }

    /**
     * Superadmin management panel.
     * Accessible by SUPERADMIN and KAPRODI.
     */
    public function superadmin(): View
    {
        $user = Auth::user();

        // Backend gate authorization check (Defense in depth)
        Gate::authorize('access-superadmin');
        $this->authService->assertRole($user, 'SUPERADMIN');

        $users = User::with('roles')->paginate(10);
        $roles = Role::with('permissions')->get();

        return view('admin.superadmin', compact('users', 'roles'));
    }

    /**
     * Academic portal page for DOSBING and DOSEN_MK.
     */
    public function academic(): View
    {
        $user = Auth::user();

        // Service-level assertion
        $this->authService->assertRole($user, ['DOSBING', 'DOSEN_MK']);

        return view('academic.index', compact('user'));
    }

    /**
     * Backend service-level test endpoint.
     * Enforces backend authorization rejection.
     */
    public function testServiceAction(Request $request): JsonResponse
    {
        $user = Auth::user();
        $requiredRole = $request->query('role', 'SUPERADMIN');

        // Service / Action layer assertion
        $this->authService->assertRole($user, $requiredRole);

        return response()->json([
            'success' => true,
            'message' => "Operasi backend berhasil divalidasi untuk role: {$requiredRole}",
            'user' => $user->name,
        ]);
    }
}
