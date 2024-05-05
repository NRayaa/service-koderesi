<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class SuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pastikan pengguna tidak terautentikasi atau belum login
        if (!Auth::check() || !Auth::user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Jika pengguna sudah terautentikasi, lanjutkan pengecekan role admin
        if (Auth::user()->role === 'superadmin') {
            return $next($request);
        }

        // Jika user tidak memiliki role admin
        return response()->json(['error' => 'Forbidden'], 403);
    }
}
