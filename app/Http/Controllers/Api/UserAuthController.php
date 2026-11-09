<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserAuthController extends Controller
{
    /**
     * POST /api/pwa/auth/login
     * Login pengguna level rendah dengan username & password
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string|max:50',
            'password' => 'required|string',
        ]);

        $username = trim($request->input('username'));
        $password = (string)$request->input('password');

        $user = DB::table('users')->where('username', $username)->first();

        // 1. Jika user tidak ditemukan di tabel users, periksa di tabel admin_users (auto-sync admin)
        if (!$user) {
            $adminUser = DB::table('admin_users')->where('username', $username)->first();
            if ($adminUser && (password_verify($password, $adminUser->password) || $password === $adminUser->password)) {
                $newId = DB::table('users')->insertGetId([
                    'username'   => $adminUser->username,
                    'name'       => 'Administrator ' . ucfirst($adminUser->username),
                    'password'   => $adminUser->password,
                    'role'       => 'admin',
                    'role_id'    => 1,
                    'is_blocked' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $user = DB::table('users')->where('id', $newId)->first();
            } else {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Username atau Password tidak valid.',
                ], 401);
            }
        }

        // Cek status blokir
        if (!empty($user->is_blocked)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun Anda sedang diblokir oleh Administrator. Silakan hubungi Developer via WhatsApp.',
            ], 403);
        }

        // 2. Verifikasi password (Bcrypt atau fallback plain)
        $isPwValid = (password_verify($password, $user->password) || $password === $user->password);

        // Jika tidak valid, cek apakah ada di admin_users dengan password tersebut (sinkronisasi password admin)
        if (!$isPwValid) {
            $adminUser = DB::table('admin_users')->where('username', $user->username)->first();
            if ($adminUser && (password_verify($password, $adminUser->password) || $password === $adminUser->password)) {
                $isPwValid = true;
                DB::table('users')->where('id', $user->id)->update([
                    'password'   => $adminUser->password,
                    'updated_at' => now(),
                ]);
            }
        }

        // Fallback kemudahan untuk akun demo 'coba' (mendukung password umum: 123456, password123, coba, coba123)
        if (!$isPwValid && $username === 'coba') {
            if (in_array($password, ['123456', 'password123', 'coba', 'coba123'])) {
                $isPwValid = true;
                DB::table('users')->where('id', $user->id)->update([
                    'password'   => Hash::make($password),
                    'updated_at' => now(),
                ]);
            }
        }

        if (!$isPwValid) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Username atau Password tidak valid.',
            ], 401);
        }

        $deviceId = trim((string)$request->input('device_id', ''));
        $deviceName = trim((string)$request->input('device_name', ''));
        $force = (bool)$request->input('force', false);
        $isAdminRole = ($user->role === 'admin' || $user->role_id == 1);

        // Durasi idle timeout sesi aktif (30 menit)
        $idleTimeoutMinutes = 30;

        // Pengecekan Sesi Aktif di Perangkat Lain (Active Session Check):
        // Jika bukan admin dan ada sesi aktif di perangkat lain yang belum logout / belum idle > 30 menit
        if (!$isAdminRole) {
            $hasActiveToken = !empty($user->api_token);
            $hasDifferentDevice = (!empty($user->device_id) && !empty($deviceId) && $user->device_id !== $deviceId);

            $lastActivity = $user->last_activity_at ?: $user->last_login_at;
            $isSessionRecent = false;
            if ($lastActivity) {
                $diffInMinutes = now()->diffInMinutes(\Carbon\Carbon::parse($lastActivity));
                if ($diffInMinutes < $idleTimeoutMinutes) {
                    $isSessionRecent = true;
                }
            }

            // Jika ada sesi aktif di perangkat lain dan belum idle timeout, dan user belum konfirmasi force logout
            if ($hasActiveToken && $hasDifferentDevice && $isSessionRecent && !$force) {
                $activeDevice = $user->device_name ?: 'perangkat lain';
                $diffMinutes = $lastActivity ? now()->diffInMinutes(\Carbon\Carbon::parse($lastActivity)) : 0;
                $activeDesc = $diffMinutes <= 1 ? 'baru saja' : "{$diffMinutes} menit yang lalu";

                return response()->json([
                    'status'        => 'error',
                    'error_code'    => 'SESSION_ACTIVE_ELSEWHERE',
                    'message'       => "Akun sedang aktif digunakan di {$activeDevice} (terakhir aktif {$activeDesc}). Silakan logout terlebih dahulu dari perangkat tersebut sebelum masuk di sini.",
                    'active_device' => $user->device_name,
                    'can_force'     => true,
                ], 409);
            }
        }

        // Generate token baru atau pertahankan token jika admin (agar admin bisa login di banyak perangkat bersamaan tanpa saling menendang)
        if ($isAdminRole && !empty($user->api_token)) {
            $token = $user->api_token;
        } else {
            $token = Str::random(60);
        }

        $updateData = [
            'api_token'        => $token,
            'last_login_at'    => now(),
            'last_login_ip'    => $request->ip(),
            'last_activity_at' => now(),
            'updated_at'       => now(),
        ];

        // Daftarkan perangkat aktif saat ini
        if (!$isAdminRole && !empty($deviceId)) {
            $updateData['device_id'] = $deviceId;
            $updateData['device_name'] = $deviceName ?: 'Perangkat Aktif';
            $updateData['device_locked_at'] = now();
        }

        DB::table('users')->where('id', $user->id)->update($updateData);

        return response()->json([
            'status'  => 'success',
            'message' => 'Login berhasil.',
            'data'    => [
                'token'     => $token,
                'user'      => [
                    'id'          => $user->id,
                    'username'    => $user->username,
                    'name'        => $user->name,
                    'uptd'        => $user->uptd ?? 'Kantor Pusat',
                    'role'        => $user->role ?? ($user->role_id == 1 ? 'admin' : 'user'),
                    'device_name' => $updateData['device_name'] ?? $user->device_name ?? null,
                ],
            ],
        ]);
    }

    /**
     * GET /api/pwa/auth/me
     * Cek validitas token dan data user aktif
     */
    public function me(Request $request): JsonResponse
    {
        $token = $this->extractToken($request);
        if (!$token) {
            return response()->json(['status' => 'error', 'message' => 'Sesi tidak ditemukan.'], 401);
        }

        $user = DB::table('users')->where('api_token', $token)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Sesi telah berakhir.'], 401);
        }

        if (!empty($user->is_blocked)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun Anda telah dinonaktifkan/diblokir oleh Administrator.',
            ], 403);
        }

        $deviceId = trim((string)($request->header('X-Device-Id') ?: $request->input('device_id', '')));
        $isAdminRole = ($user->role === 'admin' || $user->role_id == 1);

        // Jika bukan admin dan perangkat berbeda dari perangkat sesi aktif saat ini
        if (!$isAdminRole && !empty($user->device_id) && !empty($deviceId)) {
            if ($user->device_id !== $deviceId) {
                return response()->json([
                    'status'     => 'error',
                    'error_code' => 'SESSION_ACTIVE_ELSEWHERE',
                    'message'    => 'Sesi Anda telah beralih ke perangkat lain.',
                ], 401);
            }
        }

        // Catat heartbeat aktivitas terakhir
        DB::table('users')->where('id', $user->id)->update([
            'last_activity_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'          => $user->id,
                'username'    => $user->username,
                'name'        => $user->name,
                'uptd'        => $user->uptd ?? 'Kantor Pusat',
                'role'        => $user->role ?? ($user->role_id == 1 ? 'admin' : 'user'),
                'device_name' => $user->device_name,
            ],
        ]);
    }

    /**
     * POST /api/pwa/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $this->extractToken($request);
        if ($token) {
            $user = DB::table('users')->where('api_token', $token)->first();
            if ($user) {
                $isAdminRole = ($user->role === 'admin' || $user->role_id == 1);
                // Hanya reset token dan perangkat di database jika bukan admin
                // Ini memastikan begitu pengguna menekan 'Logout', akun langsung bebas digunakan di PC/HP lain
                if (!$isAdminRole) {
                    DB::table('users')->where('id', $user->id)->update([
                        'api_token'        => null,
                        'device_id'        => null,
                        'device_name'      => null,
                        'device_locked_at' => null,
                        'last_activity_at' => null,
                        'updated_at'       => now(),
                    ]);
                }
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil keluar.',
        ]);
    }

    /**
     * GET /api/pwa/user/activities
     * Mengambil riwayat aktivitas khusus user yang sedang login
     * Hanya mencatat aktivitas transaksi nyata (Cek Pajak, Simulasi, BBN 2, NJKB)
     * Tidak menyertakan login/logout agar riwayat tetap bersih
     */
    public function activities(Request $request): JsonResponse
    {
        $token = $this->extractToken($request);
        if (!$token) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        $user = DB::table('users')->where('api_token', $token)->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User tidak valid.'], 401);
        }

        $isAll = $request->query('all') === '1' || $request->query('all') === 'true';
        $limit = $isAll ? 50 : 5;

        $logs = DB::table('activity_logs')
            ->where('user_id', $user->id)
            ->whereNotIn('activity', ['Login Aplikasi', 'Logout Aplikasi', 'Login', 'Logout'])
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();

        $total = DB::table('activity_logs')
            ->where('user_id', $user->id)
            ->whereNotIn('activity', ['Login Aplikasi', 'Logout Aplikasi', 'Login', 'Logout'])
            ->count();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'logs'       => $logs,
                'total'      => $total,
                'is_all'     => $isAll,
                'has_more'   => !$isAll && $total > 5,
            ],
        ]);
    }

    /**
     * GET /api/pwa/banner
     * Mengambil daftar banner aktif (bisa gambar, teks, atau kombinasi keduanya)
     * Terurut berdasarkan order_num ASC
     */
    public function banner(): JsonResponse
    {
        $banners = DB::table('banners')
            ->where('is_active', 1)
            ->where(function ($q) {
                $q->whereNotNull('image_path')->where('image_path', '!=', '')
                  ->orWhereNotNull('content')->where('content', '!=', '')
                  ->orWhereNotNull('title')->where('title', '!=', '');
            })
            ->orderBy('order_num', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        // Fallback backward-compatible: jika tabel banners kosong, periksa tabel konfigurasi lama
        if ($banners->isEmpty()) {
            $bannerActive = DB::table('konfigurasi')->where('kunci', 'banner_active')->value('nilai');
            $bannerTitle = DB::table('konfigurasi')->where('kunci', 'banner_title')->value('nilai');
            $bannerContent = DB::table('konfigurasi')->where('kunci', 'banner_content')->value('nilai');
            $bannerType = DB::table('konfigurasi')->where('kunci', 'banner_type')->value('nilai') ?: 'info';

            if ((string)$bannerActive === '1' && !empty(trim((string)$bannerContent))) {
                $banners = collect([[
                    'id'         => 0,
                    'title'      => $bannerTitle,
                    'content'    => $bannerContent,
                    'image_path' => null,
                    'link_url'   => null,
                    'type'       => $bannerType,
                ]]);
            }
        }

        $banners = $banners->map(function ($b) {
            $b = (object)$b;
            $b->image_url = !empty($b->image_path) ? asset(ltrim($b->image_path, '/')) : null;
            return $b;
        });

        $first = $banners->first();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'active'     => $banners->isNotEmpty(),
                'banners'    => $banners,
                'count'      => $banners->count(),
                'title'      => $first ? ($first->title ?? '') : '',
                'content'    => $first ? ($first->content ?? '') : '',
                'image_path' => $first ? ($first->image_path ?? null) : null,
                'image_url'  => $first ? ($first->image_url ?? null) : null,
                'type'       => $first ? ($first->type ?? 'info') : 'info',
            ],
        ]);
    }

    /**
     * Helper untuk extract Bearer token
     */
    protected function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');
        if (preg_match('/Bearer\s+(\S+)/i', $header, $matches)) {
            return $matches[1];
        }
        return $request->header('X-Api-Token')
            ?: $request->header('X-Token')
            ?: $request->input('api_token');
    }

    /**
     * Helper pencatatan log
     */
    protected function logUserActivity(?int $userId, string $username, ?string $userName, string $activity, ?string $detail, Request $request): void
    {
        try {
            DB::table('activity_logs')->insert([
                'user_id'    => $userId,
                'username'   => $username,
                'user_name'  => $userName,
                'activity'   => $activity,
                'detail'     => $detail,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string)$request->userAgent(), 0, 250),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {}
    }
}

