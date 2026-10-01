<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\UserRoleAssignRequest;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Services\Auth\AuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserRoleController extends Controller
{
    protected AuthorizationService $authService;

    public function __construct(AuthorizationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Display listing of users and their role assignments.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = User::query()->with(['roles.permissions', 'studyProgram']);

        if ($request->filled('search')) {
            $query->search($request->string('search'));
        }

        if ($request->filled('role')) {
            $query->filterRole($request->string('role'));
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();
        $roles = Role::orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $users,
            ]);
        }

        return view('master.user-roles.index', compact('users', 'roles'));
    }

    /**
     * Show form / detail to manage roles for a specific user.
     */
    public function edit(User $user): View
    {
        $user->load('roles');
        $allRoles = Role::orderBy('name')->get();
        $effectiveRoles = $this->authService->getEffectiveRoles($user);

        return view('master.user-roles.edit', compact('user', 'allRoles', 'effectiveRoles'));
    }

    /**
     * Update/sync roles for a specific user.
     */
    public function update(UserRoleAssignRequest $request, User $user): RedirectResponse|JsonResponse
    {
        $roleIds = $request->validated('roles');

        // Security check: User cannot remove SUPERADMIN role from self
        if ($user->id === auth()->id() && $user->hasRole('SUPERADMIN')) {
            $superadminRoleId = Role::where('name', 'SUPERADMIN')->value('id');
            if (!in_array($superadminRoleId, $roleIds)) {
                $msg = 'Anda tidak boleh mencopot hak akses SUPERADMIN dari akun Anda sendiri demi keamanan sistem.';
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 403);
                }
                return back()->with('error', $msg);
            }
        }

        $oldRoles = $user->roles->pluck('name')->sort()->values()->toArray();

        $user->roles()->sync($roleIds);
        $user->unsetRelation('roles');

        $newRoles = $user->roles->pluck('name')->sort()->values()->toArray();

        if ($oldRoles !== $newRoles) {
            AuditLog::record(
                actor: auth()->user(),
                targetUser: $user,
                action: 'ROLES_SYNCED',
                oldValues: $oldRoles,
                newValues: $newRoles,
                description: "Role pengguna {$user->name} diperbarui dari [" . implode(', ', $oldRoles) . "] ke [" . implode(', ', $newRoles) . "]"
            );
        }

        $effectiveRoles = $this->authService->getEffectiveRoles($user);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role pengguna berhasil diperbarui.',
                'roles' => $user->roles,
                'effective_roles' => $effectiveRoles,
            ]);
        }

        return redirect()
            ->route('master.user-roles.index')
            ->with('success', "Penugasan role untuk pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Detach a specific role from a user.
     */
    public function detach(Request $request, User $user, Role $role): RedirectResponse|JsonResponse
    {
        // Security check: Cannot remove SUPERADMIN from self
        if ($user->id === auth()->id() && strtoupper($role->name) === 'SUPERADMIN') {
            $msg = 'Anda tidak boleh mencopot peran SUPERADMIN dari akun Anda sendiri.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        if ($user->roles()->count() <= 1) {
            $msg = 'Pengguna harus memiliki minimal satu role aktif.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $user->roles()->detach($role->id);

        AuditLog::record(
            actor: auth()->user(),
            targetUser: $user,
            action: 'ROLE_REMOVED',
            oldValues: [$role->name],
            newValues: $user->fresh()->roles->pluck('name')->toArray(),
            description: "Role '{$role->name}' dicopot dari pengguna {$user->name}"
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Role {$role->name} berhasil dilepas dari {$user->name}.",
            ]);
        }

        return back()->with('success', "Role {$role->name} berhasil dilepas dari pengguna {$user->name}.");
    }
}
