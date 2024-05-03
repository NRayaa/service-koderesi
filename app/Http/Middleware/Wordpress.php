<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Wordpress
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('key');

        // Periksa apakah kunci pengguna ada dalam tabel pengguna
        if(User::where('key', $key)->exists()) {
            // Jika kunci pengguna valid, lanjutkan ke lapisan berikutnya dalam aplikasi
            return $next($request);
        }

        // Jika kunci pengguna tidak valid, kembalikan respons "Unauthorized"
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ]);
    }
}
