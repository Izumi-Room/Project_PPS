<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()->guest(route('login'));
        }

        // Parse comma-separated roles if passed as a single string like "KAPRODI,SUPERADMIN"
        $parsedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $subRole) {
                $trimmed = trim($subRole);
                if ($trimmed !== '') {
                    $parsedRoles[] = $trimmed;
                }
            }
        }

        if (empty($parsedRoles)) {
            return $next($request);
        }

        // If user has no roles assigned at all
        if ($user->roles->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Akun Anda belum memiliki role aktif.',
                ], 403);
            }
            abort(403, 'Akses ditolak: Akun Anda belum memiliki role aktif.');
        }

        if (! $user->hasAnyRole($parsedRoles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Anda tidak memiliki role yang diizinkan untuk mengakses resource ini.',
                ], 403);
            }
            abort(403, 'Akses ditolak: Anda tidak memiliki role yang diizinkan untuk mengakses resource ini.');
        }

        return $next($request);
    }
}
