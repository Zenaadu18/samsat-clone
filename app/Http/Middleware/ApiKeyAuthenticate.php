<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware autentikasi sederhana untuk API BAPENDA NTT.
 *
 * Cara penggunaan:
 *   Tambahkan header: Authorization: Bearer <API_KEY>
 *   API key dikonfigurasi di .env sebagai SAMSAT_API_KEY.
 *
 * Jika SAMSAT_API_KEY tidak di-set di .env, akses API akan ditolak.
 */
class ApiKeyAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = config('samsat.api_key');

        // Jika tidak ada key yang dikonfigurasi, tolak semua akses API
        if (empty($configuredKey)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'API key belum dikonfigurasi di server.',
            ], 503);
        }

        $providedKey = null;

        // Ambil key dari header Authorization: Bearer <key>
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $providedKey = substr($authHeader, 7);
        }

        // Fallback: dari query param ?api_key= (untuk testing)
        if (!$providedKey) {
            $providedKey = $request->query('api_key');
        }

        if (!$providedKey || !hash_equals($configuredKey, $providedKey)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'API key tidak valid atau tidak diberikan.',
            ], 401);
        }

        return $next($request);
    }
}
