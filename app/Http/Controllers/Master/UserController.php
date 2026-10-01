<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\UserMasterRequest;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = User::query()->with(['roles', 'studyProgram']);

        if ($request->filled('search')) {
            $query->search($request->string('search'));
        }

        if ($request->filled('role')) {
            $query->filterRole($request->string('role'));
        }

        if ($request->filled('study_program_id')) {
            $query->filterStudyProgram($request->input('study_program_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();
        $roles = Role::orderBy('name')->get();
        $studyPrograms = StudyProgram::active()->orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $users,
            ]);
        }

        return view('master.users.index', compact('users', 'roles', 'studyPrograms'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $roles = Role::orderBy('name')->get();
        $studyPrograms = StudyProgram::active()->orderBy('name')->get();

        return view('master.users.create', compact('roles', 'studyPrograms'));
    }

    /**
     * Store a newly created user.
     */
    public function store(UserMasterRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $roleIds = $validated['roles'] ?? [];
        unset($validated['roles']);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        if (!empty($roleIds)) {
            $user->roles()->sync($roleIds);
        }

        $user->load(['roles', 'studyProgram']);

        // Record Audit Log
        AuditLog::record(
            actor: auth()->user(),
            targetUser: $user,
            action: 'USER_CREATED',
            oldValues: null,
            newValues: [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray(),
                'is_active' => $user->is_active,
            ],
            description: "Pengguna '{$user->name}' ({$user->email}) didaftarkan dengan peran: " . implode(', ', $user->roles->pluck('name')->toArray())
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil didaftarkan.',
                'data' => $user,
            ], 201);
        }

        return redirect()
            ->route('master.users.index')
            ->with('success', 'Pengguna ' . $user->name . ' (' . $user->email . ') berhasil dibuat.');
    }

    /**
     * Display the specified user.
     */
    public function show(Request $request, User $user): View|JsonResponse
    {
        $user->load(['roles.permissions', 'studyProgram', 'auditLogs' => fn ($q) => $q->latest()->take(20)]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $user,
            ]);
        }

        return view('master.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $user->load('roles');
        $roles = Role::orderBy('name')->get();
        $studyPrograms = StudyProgram::active()->orderBy('name')->get();

        return view('master.users.edit', compact('user', 'roles', 'studyPrograms'));
    }

    /**
     * Update the specified user.
     */
    public function update(UserMasterRequest $request, User $user): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $roleIds = $validated['roles'] ?? null;
        unset($validated['roles']);

        // Security check: User cannot remove SUPERADMIN role from self
        if ($user->id === auth()->id()) {
            if (isset($validated['is_active']) && !$validated['is_active']) {
                $msg = 'Anda tidak boleh menonaktifkan akun Anda sendiri.';
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 403);
                }
                return back()->with('error', $msg);
            }

            if ($roleIds !== null && $user->hasRole('SUPERADMIN')) {
                $superadminRoleId = Role::where('name', 'SUPERADMIN')->value('id');
                if (!in_array($superadminRoleId, $roleIds)) {
                    $msg = 'Anda tidak boleh mencopot hak akses SUPERADMIN dari akun Anda sendiri demi keamanan sistem.';
                    if ($request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $msg], 403);
                    }
                    return back()->with('error', $msg);
                }
            }
        }

        $oldRoles = $user->roles->pluck('name')->sort()->values()->toArray();
        $oldData = $user->only(['name', 'email', 'study_program_id', 'identifier_number', 'phone', 'is_active']);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        if ($roleIds !== null) {
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
                    description: "Role pengguna {$user->name} diubah dari [" . implode(', ', $oldRoles) . "] ke [" . implode(', ', $newRoles) . "]"
                );
            }
        }

        $newData = $user->only(['name', 'email', 'study_program_id', 'identifier_number', 'phone', 'is_active']);
        if ($oldData !== $newData) {
            AuditLog::record(
                actor: auth()->user(),
                targetUser: $user,
                action: 'USER_UPDATED',
                oldValues: $oldData,
                newValues: $newData,
                description: "Data profil pengguna {$user->name} diperbarui"
            );
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pengguna berhasil diperbarui.',
                'data' => $user->load(['roles', 'studyProgram']),
            ]);
        }

        return redirect()
            ->route('master.users.index')
            ->with('success', 'Data pengguna ' . $user->name . ' berhasil diperbarui.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $user): RedirectResponse|JsonResponse
    {
        if ($user->id === auth()->id()) {
            $msg = 'Anda tidak dapat menghapus akun Anda sendiri.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        $userName = $user->name;
        $userEmail = $user->email;

        AuditLog::record(
            actor: auth()->user(),
            targetUser: $user,
            action: 'USER_DELETED',
            oldValues: ['name' => $userName, 'email' => $userEmail],
            newValues: null,
            description: "Pengguna {$userName} ({$userEmail}) dihapus dari sistem"
        );

        $user->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil dihapus.',
            ]);
        }

        return redirect()
            ->route('master.users.index')
            ->with('success', "Pengguna {$userName} berhasil dihapus.");
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse|JsonResponse
    {
        if ($user->id === auth()->id()) {
            $msg = 'Anda tidak dapat menonaktifkan akun Anda sendiri.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        $oldStatus = $user->is_active;
        $user->update(['is_active' => !$user->is_active]);
        $statusStr = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        AuditLog::record(
            actor: auth()->user(),
            targetUser: $user,
            action: 'STATUS_TOGGLED',
            oldValues: ['is_active' => $oldStatus],
            newValues: ['is_active' => $user->is_active],
            description: "Status akun {$user->name} {$statusStr}"
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Pengguna berhasil {$statusStr}.",
                'is_active' => $user->is_active,
            ]);
        }

        return back()->with('success', "Akun pengguna {$user->name} berhasil {$statusStr}.");
    }
}
