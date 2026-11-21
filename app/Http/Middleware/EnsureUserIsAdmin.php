<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak. Fitur ini memerlukan hak akses Admin.'], 403);
            }
            abort(403, 'Akses ditolak. Halaman atau aksi ini hanya diperuntukkan bagi Administrator dan Master Admin.');
        }

        return $next($request);
    }
}
