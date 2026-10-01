<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class AuthorizationService
{
    /**
     * Assert that the user has at least one of the given roles.
     * Throws AuthorizationException if not authorized.
     *
     * @param  string|array<string>  $roles
     *
     * @throws AuthorizationException
     */
    public function assertRole(User $user, string|array $roles): void
    {
        $roleList = is_array($roles) ? $roles : explode(',', $roles);
        $roleList = array_map('trim', $roleList);

        if (! $user->hasAnyRole($roleList)) {
            throw new AuthorizationException('Akses ditolak: Anda tidak memiliki role yang diperlukan untuk operasi ini.');
        }
    }

    /**
     * Assert that the user has a specific permission.
     * Throws AuthorizationException if not authorized.
     *
     * @throws AuthorizationException
     */
    public function assertPermission(User $user, string $permission): void
    {
        if (! $user->hasPermission($permission)) {
            throw new AuthorizationException("Akses ditolak: Anda tidak memiliki izin '{$permission}' untuk operasi ini.");
        }
    }

    /**
     * Check if user is authorized for superadmin panel or capabilities.
     * KAPRODI automatically satisfies this condition.
     */
    public function canAccessSuperadmin(User $user): bool
    {
        return $user->hasRole('SUPERADMIN');
    }

    /**
     * Get list of role names for the user, including inherited roles.
     *
     * @return array<string>
     */
    public function getEffectiveRoles(User $user): array
    {
        $roles = $user->roles->pluck('name')->map(fn ($r) => strtoupper($r))->toArray();
        if (in_array('KAPRODI', $roles, true) && ! in_array('SUPERADMIN', $roles, true)) {
            $roles[] = 'SUPERADMIN';
        }

        return array_values(array_unique($roles));
    }
}
