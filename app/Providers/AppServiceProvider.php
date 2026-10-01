<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Global Gate rule: Superadmin (and Kaprodi via inheritance) has all permissions
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('SUPERADMIN')) {
                return true;
            }

            return null; // fall through to other gates
        });

        // Role-based gates
        Gate::define('access-mhs', fn (User $user) => $user->hasRole('MHS'));
        Gate::define('access-tu', fn (User $user) => $user->hasRole('TU'));
        Gate::define('access-kaprodi', fn (User $user) => $user->hasRole('KAPRODI'));
        Gate::define('access-dosbing', fn (User $user) => $user->hasRole('DOSBING'));
        Gate::define('access-dosen-mk', fn (User $user) => $user->hasRole('DOSEN_MK'));
        Gate::define('access-wadek1', fn (User $user) => $user->hasRole('WADEK1'));
        Gate::define('access-superadmin', fn (User $user) => $user->hasRole('SUPERADMIN'));

        // Granular permission gate
        Gate::define('has-permission', fn (User $user, string $permission) => $user->hasPermission($permission));
    }
}
