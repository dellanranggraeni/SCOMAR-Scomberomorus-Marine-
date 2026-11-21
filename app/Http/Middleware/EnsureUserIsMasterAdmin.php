<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsMasterAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isMasterAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak. Fitur ini memerlukan hak akses Master Admin.'], 403);
            }
            abort(403, 'Akses ditolak. Halaman atau aksi ini hanya diperuntukkan bagi Master Admin SCOMAR.');
        }

        return $next($request);
    }
}
