<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
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

        if (! $user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Akses ditolak: Anda tidak memiliki permission '{$permission}'.",
                ], 403);
            }
            abort(403, "Akses ditolak: Anda tidak memiliki permission '{$permission}'.");
        }

        return $next($request);
    }
}
