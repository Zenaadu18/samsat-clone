<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AdminSessionTimeout
{
    /**
     * Sesi login admin timeout jika tidak ada aktivitas selama 30 menit (1800 detik).
     */
    public const TIMEOUT_SECONDS = 1800;

    public function handle(Request $request, Closure $next): Response
    {
        // Jika belum login, lanjutkan (biarkan route/controller yang menangani login form)
        if (!Session::get('admin_logged_in')) {
            return $next($request);
        }

        $now = time();
        $lastActivity = Session::get('admin_last_activity');

        // Periksa apakah waktu tidak aktif melebihi batas (30 menit)
        if ($lastActivity && ($now - $lastActivity > self::TIMEOUT_SECONDS)) {
            Session::forget(['admin_logged_in', 'admin_username', 'admin_last_activity']);
            Session::invalidate();
            Session::regenerateToken();

            return redirect()->route('admin')->with('errorLogin', 'Sesi login telah berakhir karena tidak ada aktivitas selama 30 menit. Silakan login kembali.');
        }

        // Perbarui timestamp aktivitas terakhir
        Session::put('admin_last_activity', $now);

        return $next($request);
    }
}

