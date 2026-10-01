<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\RoleMasterRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    protected array $systemRoles = ['MHS', 'TU', 'KAPRODI', 'DOSBING', 'DOSEN_MK', 'WADEK1', 'SUPERADMIN'];

    /**
     * Display a listing of roles.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Role::query()->withCount(['users', 'permissions']);

        if ($request->filled('search')) {
            $query->search($request->string('search'));
        }

        $roles = $query->orderBy('name')->paginate(10)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $roles,
            ]);
        }

        return view('master.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): View
    {
        $permissions = Permission::orderBy('name')->get();
        return view('master.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role.
     */
    public function store(RoleMasterRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $permissionIds = $validated['permissions'] ?? [];
        unset($validated['permissions']);

        $role = Role::create($validated);

        if (!empty($permissionIds)) {
            $role->permissions()->sync($permissionIds);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role berhasil ditambahkan.',
                'data' => $role->load('permissions'),
            ], 201);
        }

        return redirect()
            ->route('master.roles.index')
            ->with('success', 'Role ' . $role->name . ' (' . $role->label . ') berhasil ditambahkan.');
    }

    /**
     * Display the specified role.
     */
    public function show(Request $request, Role $role): View|JsonResponse
    {
        $role->load(['permissions', 'users.studyProgram']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $role,
            ]);
        }

        return view('master.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): View
    {
        $role->load('permissions');
        $permissions = Permission::orderBy('name')->get();

        return view('master.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role.
     */
    public function update(RoleMasterRequest $request, Role $role): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $permissionIds = $validated['permissions'] ?? [];
        unset($validated['permissions']);

        // Don't allow renaming system roles
        if (in_array($role->name, $this->systemRoles) && $validated['name'] !== $role->name) {
            return back()->with('error', 'Nama role bawaan sistem (' . $role->name . ') tidak boleh diubah kodenya.');
        }

        $role->update($validated);
        $role->permissions()->sync($permissionIds);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role berhasil diperbarui.',
                'data' => $role->load('permissions'),
            ]);
        }

        return redirect()
            ->route('master.roles.index')
            ->with('success', 'Role ' . $role->name . ' berhasil diperbarui.');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Request $request, Role $role): RedirectResponse|JsonResponse
    {
        if (in_array($role->name, $this->systemRoles)) {
            $msg = 'Role bawaan sistem (' . $role->name . ') dilindungi dan tidak dapat dihapus.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        if ($role->users()->exists()) {
            $msg = 'Role masih digunakan oleh ' . $role->users()->count() . ' pengguna, tidak dapat dihapus.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $roleName = $role->name;
        $role->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role berhasil dihapus.',
            ]);
        }

        return redirect()
            ->route('master.roles.index')
            ->with('success', "Role {$roleName} berhasil dihapus.");
    }
}
