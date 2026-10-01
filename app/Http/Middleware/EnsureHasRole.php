<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasRole
{
    /**
     * Handle an incoming request.
     * Ensure the user has at least one role assigned.
     */
    public function handle(Request $request, Closure $next): Response
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

        if ($user->roles->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Akun Anda tidak memiliki role yang aktif dalam sistem.',
                ], 403);
            }
            abort(403, 'Akses ditolak: Akun Anda tidak memiliki role yang aktif dalam sistem.');
        }

        return $next($request);
    }
}
