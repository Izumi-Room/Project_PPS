<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Services\Auth\AuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CurrentUserApiController extends Controller
{
    protected AuthorizationService $authService;

    public function __construct(AuthorizationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Get current authenticated user state, roles, and permissions.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'authenticated' => false,
                'user' => null,
            ], 401);
        }

        $user->load('roles.permissions');

        $assignedRoles = $user->roles->pluck('name')->toArray();
        $effectiveRoles = $this->authService->getEffectiveRoles($user);

        // Collect all distinct permissions from all assigned roles (or all permissions if Superadmin)
        $permissions = [];
        if ($user->hasRole('SUPERADMIN')) {
            $permissions = Permission::pluck('name')->toArray();
        } else {
            foreach ($user->roles as $role) {
                foreach ($role->permissions as $perm) {
                    $permissions[] = $perm->name;
                }
            }
            $permissions = array_values(array_unique($permissions));
        }

        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at?->toISOString(),
            ],
            'assigned_roles' => $assignedRoles,
            'effective_roles' => $effectiveRoles,
            'is_superadmin' => $user->hasRole('SUPERADMIN'),
            'permissions' => $permissions,
        ]);
    }

    /**
     * API test endpoint for superadmin authorization.
     */
    public function superadminCheck(Request $request): JsonResponse
    {
        $user = $request->user();

        Gate::authorize('access-superadmin');
        $this->authService->assertRole($user, 'SUPERADMIN');

        return response()->json([
            'success' => true,
            'message' => 'Otorisasi API Superadmin berhasil.',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
