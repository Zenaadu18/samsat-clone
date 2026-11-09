<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    /* =========================================================================
     * AUTENTIKASI
     * ======================================================================= */

    public function index(Request $request)
    {
        // --- LOGOUT ---
        if ($request->has('logout')) {
            return $this->logout();
        }

        // --- PROSES LOGIN ---
        $errorLogin = '';
        if ($request->isMethod('POST') && $request->has('login')) {
            $inputUsername = trim($request->input('username', ''));
            $inputPassword = $request->input('password', '');

            $user = DB::selectOne(
                'SELECT id, username, password FROM admin_users WHERE username = ? LIMIT 1',
                [$inputUsername]
            );

            if ($user && (password_verify($inputPassword, $user->password) || $inputPassword === $user->password)) {
                Session::put('admin_logged_in', true);
                Session::put('admin_username', $user->username);
                Session::put('admin_last_activity', time());
                return redirect()->route('admin');
            } else {
                $errorLogin = 'Username atau Password salah!';
            }
        }

        // --- BELUM LOGIN → tampilkan form login modern ---
        if (!Session::get('admin_logged_in')) {
            $pesanTimeout = session('errorLogin', $errorLogin);
            return view('admin.login', ['errorLogin' => $pesanTimeout]);
        }

        /* =====================================================================
         * AMBIL DATA DARI DATABASE LENGKAP
         * =================================================================== */
        $grupAmnesti    = [];
        $grupSistem     = [];
        $grupBobot      = [];
        $grupTarifDasar = [];

        $konfigurasi = DB::select('SELECT kunci, nilai FROM konfigurasi ORDER BY kunci ASC');
        foreach ($konfigurasi as $row) {
            $k = $row->kunci;
            if (str_contains($k, 'amnesti')) {
                $grupAmnesti[] = ['kunci' => $k, 'nilai' => $row->nilai];
            } elseif (str_contains($k, 'bobot')) {
                $grupBobot[] = ['kunci' => $k, 'nilai' => $row->nilai];
            } elseif (str_contains($k, 'api') || str_contains($k, 'jam') || str_contains($k, 'pembatasan')) {
                $grupSistem[] = ['kunci' => $k, 'nilai' => $row->nilai];
            } else {
                $grupTarifDasar[] = ['kunci' => $k, 'nilai' => $row->nilai];
            }
        }

        $dataPkb         = DB::select('SELECT k_guna, tarif_lama, tarif_baru, keterangan FROM tarif_pkb');
        $dataStnk        = DB::select('SELECT jenis, biaya_stnk, biaya_tnkb, keterangan FROM tarif_stnk_tnkb');
        $dataSwdkllj     = DB::select('SELECT golongan, tarif, admin, denda_t1, denda_t2, denda_t3, denda_t4, keterangan FROM tarif_swdkllj');
        $dataKlasifikasi = DB::select('SELECT id, keyword, golongan_swdkllj, jenis_kend FROM klasifikasi_kendaraan ORDER BY id ASC');
        
        // Ringkasan Statistik Database
        $stats = [
            'total_users'       => DB::table('users')->count(),
            'total_activities'  => DB::table('activity_logs')->whereNotIn('activity', ['Login Aplikasi', 'Logout Aplikasi', 'Login', 'Logout'])->count(),
            'total_njkb'        => DB::table('njkb_data')->count(),
            'total_njub'        => DB::table('njmkb_data')->count(),
            'total_klasifikasi' => count($dataKlasifikasi),
            'total_admin'       => DB::table('admin_users')->count(),
        ];

        // Ambil Data Pengguna Aplikasi
        $usersList = DB::table('users')->orderBy('id', 'desc')->get();

        // Ambil Seluruh Data Riwayat Aktivitas Pengguna (Audit Logs)
        $allActivities = DB::table('activity_logs')
            ->whereNotIn('activity', ['Login Aplikasi', 'Logout Aplikasi', 'Login', 'Logout'])
            ->orderBy('id', 'desc')
            ->limit(2000)
            ->get()
            ->map(function ($item) {
                $item->created_at_formatted = !empty($item->created_at)
                    ? date('d/m/Y H:i:s', strtotime($item->created_at)) . ' WITA'
                    : '-';
                return $item;
            });

        // Kategori Aktivitas Unik untuk Filter Dropdown
        $distinctActivities = DB::table('activity_logs')
            ->whereNotIn('activity', ['Login Aplikasi', 'Logout Aplikasi', 'Login', 'Logout'])
            ->distinct()
            ->pluck('activity')
            ->filter()
            ->values();

        // Ambil Konfigurasi Banner
        $banner = [
            'active'  => DB::table('konfigurasi')->where('kunci', 'banner_active')->value('nilai') ?? '0',
            'title'   => DB::table('konfigurasi')->where('kunci', 'banner_title')->value('nilai') ?? '',
            'content' => DB::table('konfigurasi')->where('kunci', 'banner_content')->value('nilai') ?? '',
            'type'    => DB::table('konfigurasi')->where('kunci', 'banner_type')->value('nilai') ?? 'info',
        ];

        // Ambil Daftar Seluruh Banner
        $bannersList = DB::table('banners')->orderBy('order_num', 'asc')->orderBy('id', 'desc')->get();

        return view('admin.dashboard', [
            'adminUsername'      => Session::get('admin_username', 'Admin'),
            'pesanStatus'        => session('pesanStatus', ''),
            'pesanError'         => session('pesanError', ''),
            'usersList'          => $usersList,
            'listUptd'           => User::LIST_UPTD,
            'allActivities'      => $allActivities,
            'recentActivities'   => $allActivities,
            'distinctActivities' => $distinctActivities,
            'bannersList'        => $bannersList,
            'banner'             => $banner,
            'grupAmnesti'        => $grupAmnesti,
            'grupSistem'         => $grupSistem,
            'grupBobot'          => $grupBobot,
            'grupTarifDasar'     => $grupTarifDasar,
            'dataPkb'            => $dataPkb,
            'dataStnk'           => $dataStnk,
            'dataSwdkllj'        => $dataSwdkllj,
            'dataKlasifikasi'    => $dataKlasifikasi,
            'stats'              => $stats,
        ]);
    }

    public function logout()
    {
        Session::forget(['admin_logged_in', 'admin_username', 'admin_last_activity']);
        Session::invalidate();
        Session::regenerateToken();
        return redirect()->route('admin');
    }

    /* =========================================================================
     * SIMPAN KONFIGURASI SISTEM & AMNESTI
     * ======================================================================= */
    public function saveConfig(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return redirect()->route('admin');
        }

        try {
            DB::beginTransaction();

            if ($request->has('config') && is_array($request->input('config'))) {
                $stmt = DB::connection()->getPdo()->prepare(
                    'UPDATE konfigurasi SET nilai = ? WHERE kunci = ?'
                );
                foreach ($request->input('config') as $kunci => $nilai) {
                    $stmt->execute([trim((string) $nilai), trim((string) $kunci)]);
                }
            }

            DB::commit();
            $this->clearCacheInternal();

            return redirect()->route('admin')->with('pesanStatus', 'Konfigurasi sistem & amnesti berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('admin')->with('pesanError', 'Gagal menyimpan konfigurasi: ' . $e->getMessage());
        }
    }

    /* =========================================================================
     * SIMPAN TARIF PKB
     * ======================================================================= */
    public function savePkb(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return redirect()->route('admin');
        }

        try {
            DB::beginTransaction();
            if ($request->has('tarif_pkb') && is_array($request->input('tarif_pkb'))) {
                foreach ($request->input('tarif_pkb') as $kguna => $vals) {
                    DB::update(
                        'UPDATE tarif_pkb SET tarif_lama = ?, tarif_baru = ?, keterangan = ? WHERE k_guna = ?',
                        [(float)$vals['lama'], (float)$vals['baru'], trim($vals['keterangan'] ?? ''), $kguna]
                    );
                }
            }
            DB::commit();
            $this->clearCacheInternal();
            return redirect()->route('admin')->with('pesanStatus', 'Tarif PKB berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('admin')->with('pesanError', 'Gagal memperbarui Tarif PKB: ' . $e->getMessage());
        }
    }

    /* =========================================================================
     * SIMPAN TARIF STNK & TNKB
     * ======================================================================= */
    public function saveStnk(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return redirect()->route('admin');
        }

        try {
            DB::beginTransaction();
            if ($request->has('tarif_stnk') && is_array($request->input('tarif_stnk'))) {
                foreach ($request->input('tarif_stnk') as $jenis => $vals) {
                    DB::update(
                        'UPDATE tarif_stnk_tnkb SET biaya_stnk = ?, biaya_tnkb = ?, keterangan = ? WHERE jenis = ?',
                        [(int)$vals['stnk'], (int)$vals['tnkb'], trim($vals['keterangan'] ?? ''), $jenis]
                    );
                }
            }
            DB::commit();
            $this->clearCacheInternal();
            return redirect()->route('admin')->with('pesanStatus', 'Tarif STNK & TNKB berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('admin')->with('pesanError', 'Gagal memperbarui Tarif STNK: ' . $e->getMessage());
        }
    }

    /* =========================================================================
     * SIMPAN TARIF SWDKLLJ
     * ======================================================================= */
    public function saveSwdkllj(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return redirect()->route('admin');
        }

        try {
            DB::beginTransaction();
            if ($request->has('tarif_swdkllj') && is_array($request->input('tarif_swdkllj'))) {
                foreach ($request->input('tarif_swdkllj') as $gol => $v) {
                    DB::update(
                        'UPDATE tarif_swdkllj SET tarif = ?, admin = ?, denda_t1 = ?, denda_t2 = ?, denda_t3 = ?, denda_t4 = ?, keterangan = ? WHERE golongan = ?',
                        [
                            (int)$v['tarif'], (int)$v['admin'],
                            (int)$v['t1'], (int)$v['t2'], (int)$v['t3'], (int)$v['t4'],
                            trim($v['keterangan'] ?? ''),
                            $gol,
                        ]
                    );
                }
            }
            DB::commit();
            $this->clearCacheInternal();
            return redirect()->route('admin')->with('pesanStatus', 'Tarif SWDKLLJ berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('admin')->with('pesanError', 'Gagal memperbarui SWDKLLJ: ' . $e->getMessage());
        }
    }

    /* =========================================================================
     * MANAJEMEN KLASIFIKASI KENDARAAN (CRUD)
     * ======================================================================= */
    public function saveKlasifikasi(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return redirect()->route('admin');
        }

        $id        = $request->input('id');
        $keyword   = strtoupper(trim((string)$request->input('keyword', '')));
        $golongan  = strtoupper(trim((string)$request->input('golongan_swdkllj', 'DP')));
        $jenisKend = strtoupper(trim((string)$request->input('jenis_kend', 'MINIBUS')));

        if (empty($keyword)) {
            return redirect()->route('admin')->with('pesanError', 'Keyword klasifikasi wajib diisi.');
        }

        try {
            if ($id) {
                DB::table('klasifikasi_kendaraan')->where('id', $id)->update([
                    'keyword'          => $keyword,
                    'golongan_swdkllj' => $golongan,
                    'jenis_kend'       => $jenisKend,
                ]);
                $msg = "Klasifikasi '$keyword' berhasil diperbarui.";
            } else {
                DB::table('klasifikasi_kendaraan')->insert([
                    'keyword'          => $keyword,
                    'golongan_swdkllj' => $golongan,
                    'jenis_kend'       => $jenisKend,
                ]);
                $msg = "Klasifikasi baru '$keyword' berhasil ditambahkan.";
            }
            $this->clearCacheInternal();
            return redirect()->route('admin')->with('pesanStatus', $msg);
        } catch (\Throwable $e) {
            return redirect()->route('admin')->with('pesanError', 'Gagal menyimpan klasifikasi: ' . $e->getMessage());
        }
    }

    public function deleteKlasifikasi(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return redirect()->route('admin');
        }

        $id = $request->input('id');
        try {
            DB::table('klasifikasi_kendaraan')->where('id', $id)->delete();
            $this->clearCacheInternal();
            return redirect()->route('admin')->with('pesanStatus', 'Klasifikasi kendaraan berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->route('admin')->with('pesanError', 'Gagal menghapus klasifikasi: ' . $e->getMessage());
        }
    }

    /* =========================================================================
     * PENCARIAN & MANAJEMEN NJKB DATA
     * ======================================================================= */
    public function searchNjkb(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $q = trim((string)$request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $items = DB::table('njkb_data')
            ->where(function ($query) use ($q) {
                $query->where('tipe', 'LIKE', "%$q%")
                      ->orWhere('merk', 'LIKE', "%$q%")
                      ->orWhere('tahun_buat', 'LIKE', "%$q%");
            })
            ->orderBy('tahun_buat', 'desc')
            ->limit(25)
            ->get();

        return response()->json(['status' => 'success', 'data' => $items]);
    }

    public function saveNjkbItem(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $id    = $request->input('id');
        $merk  = strtoupper(trim((string)$request->input('merk', '')));
        $tipe  = strtoupper(trim((string)$request->input('tipe', '')));
        $tahun = (int)$request->input('tahun_buat', date('Y'));
        $njkb  = (float)$request->input('njkb', 0);
        $bobot = (float)$request->input('bobot', 1.0);

        if (empty($merk) || empty($tipe) || $njkb <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Merk, Tipe, dan NJKB wajib diisi dengan benar.'], 422);
        }

        try {
            if ($id) {
                DB::table('njkb_data')->where('id', $id)->update([
                    'merk'       => $merk,
                    'tipe'       => $tipe,
                    'tahun_buat' => $tahun,
                    'njkb'       => $njkb,
                    'bobot'      => $bobot,
                ]);
                $item = DB::table('njkb_data')->where('id', $id)->first();
            } else {
                $insertedId = DB::table('njkb_data')->insertGetId([
                    'merk'       => $merk,
                    'tipe'       => $tipe,
                    'tahun_buat' => $tahun,
                    'njkb'       => $njkb,
                    'bobot'      => $bobot,
                ]);
                $item = DB::table('njkb_data')->where('id', $insertedId)->first();
            }

            return response()->json(['status' => 'success', 'message' => 'Data NJKB berhasil disimpan.', 'data' => $item]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteNjkbItem(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $id = $request->input('id');
        try {
            DB::table('njkb_data')->where('id', $id)->delete();
            return response()->json(['status' => 'success', 'message' => 'Data NJKB berhasil dihapus.']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /* =========================================================================
     * GANTI PASSWORD ADMIN
     * ======================================================================= */
    public function changePassword(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return redirect()->route('admin');
        }

        $currentPass = $request->input('current_password');
        $newPass     = $request->input('new_password');
        $confirmPass = $request->input('confirm_password');

        if (empty($newPass) || strlen($newPass) < 5) {
            return redirect()->route('admin')->with('pesanError', 'Password baru minimal 5 karakter.');
        }

        if ($newPass !== $confirmPass) {
            return redirect()->route('admin')->with('pesanError', 'Konfirmasi password baru tidak cocok.');
        }

        $username = Session::get('admin_username');
        $user = DB::table('admin_users')->where('username', $username)->first();

        if ($user && (password_verify($currentPass, $user->password) || $currentPass === $user->password)) {
            DB::table('admin_users')->where('username', $username)->update([
                'password' => Hash::make($newPass),
            ]);
            return redirect()->route('admin')->with('pesanStatus', 'Password admin berhasil diperbarui.');
        } else {
            return redirect()->route('admin')->with('pesanError', 'Password saat ini salah.');
        }
    }

    /* =========================================================================
     * MANAJEMEN PENGGUNA (USERS)
     * ======================================================================= */
    public function saveUser(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return redirect()->route('admin');
        }

        $userId = $request->input('user_id');
        $username = trim((string)$request->input('username'));
        $name = trim((string)$request->input('name'));
        $password = $request->input('password');
        $role = $request->input('role', 'user') === 'admin' ? 'admin' : 'user';
        $roleId = $role === 'admin' ? 1 : 2;
        $isBlocked = $request->has('is_blocked') ? 1 : 0;
        $uptd = trim((string)$request->input('uptd', 'Kantor Pusat'));

        if (!in_array($uptd, User::LIST_UPTD, true)) {
            $uptd = 'Kantor Pusat';
        }

        // Auto-healing: pastikan kolom uptd tersedia di tabel users jika belum termigrasi
        if (!Schema::hasColumn('users', 'uptd')) {
            try {
                Schema::table('users', function ($table) {
                    $table->string('uptd', 100)->nullable()->default('Kantor Pusat')->after('name');
                });
            } catch (\Throwable $e) {}
        }

        if (empty($username) || empty($name)) {
            return redirect()->route('admin')->with('pesanError', 'Username dan Nama Lengkap wajib diisi.');
        }

        if ($userId) {
            // Update
            $checkUnique = DB::table('users')->where('username', $username)->where('id', '!=', $userId)->first();
            if ($checkUnique) {
                return redirect()->route('admin')->with('pesanError', 'Username sudah digunakan oleh akun lain.');
            }

            $updateData = [
                'username'   => $username,
                'name'       => $name,
                'uptd'       => $uptd,
                'role'       => $role,
                'role_id'    => $roleId,
                'is_blocked' => $isBlocked,
                'updated_at' => now(),
            ];

            if (!empty($password)) {
                if (strlen($password) < 4) {
                    return redirect()->route('admin')->with('pesanError', 'Password minimal 4 karakter.');
                }
                $updateData['password'] = Hash::make($password);
            }

            DB::table('users')->where('id', $userId)->update($updateData);
            return redirect()->route('admin')->with('pesanStatus', "Pengguna '$username' berhasil diperbarui.");
        } else {
            // Create
            if (empty($password) || strlen($password) < 4) {
                return redirect()->route('admin')->with('pesanError', 'Password baru wajib diisi minimal 4 karakter.');
            }

            $checkUnique = DB::table('users')->where('username', $username)->first();
            if ($checkUnique) {
                return redirect()->route('admin')->with('pesanError', 'Username sudah terdaftar.');
            }

            DB::table('users')->insert([
                'username'   => $username,
                'name'       => $name,
                'uptd'       => $uptd,
                'role'       => $role,
                'role_id'    => $roleId,
                'password'   => Hash::make($password),
                'is_blocked' => $isBlocked,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('admin')->with('pesanStatus', "Pengguna baru '$username' berhasil ditambahkan.");
        }
    }

    public function toggleBlockUser(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $userId = $request->input('id');
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Pengguna tidak ditemukan.'], 404);
        }

        $newStatus = $user->is_blocked ? 0 : 1;
        DB::table('users')->where('id', $userId)->update([
            'is_blocked' => $newStatus,
            'api_token'  => $newStatus ? null : $user->api_token, // Langsung putuskan sesi jika diblokir
            'updated_at' => now(),
        ]);

        return response()->json([
            'status'     => 'success',
            'is_blocked' => $newStatus,
            'message'    => $newStatus ? "Pengguna '$user->username' telah diblokir." : "Pengguna '$user->username' telah diaktifkan kembali.",
        ]);
    }

    public function deleteUser(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $userId = $request->input('id');
        DB::table('users')->where('id', $userId)->delete();
        DB::table('activity_logs')->where('user_id', $userId)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Akun pengguna berhasil dihapus.',
        ]);
    }

    public function resetUserDevice(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $userId = $request->input('id');
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Pengguna tidak ditemukan.'], 404);
        }

        DB::table('users')->where('id', $userId)->update([
            'device_id'        => null,
            'device_name'      => null,
            'device_locked_at' => null,
            'last_activity_at' => null,
            'api_token'        => null, // Putuskan sesi aktif
            'updated_at'       => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Sesi aktif pengguna '$user->username' berhasil di-logout/direset.",
        ]);
    }

    public function resetAllUserSessions(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        // Target seluruh pengguna dengan role user / non-admin
        $targetUserIds = DB::table('users')
            ->where(function ($q) {
                $q->where('role', 'user')
                  ->orWhere('role', '!=', 'admin');
            })
            ->where(function ($q) {
                $q->whereNull('role_id')
                  ->orWhere('role_id', '!=', 1);
            })
            ->pluck('id')
            ->toArray();

        $count = count($targetUserIds);

        if ($count > 0) {
            DB::table('users')
                ->whereIn('id', $targetUserIds)
                ->update([
                    'device_id'        => null,
                    'device_name'      => null,
                    'device_locked_at' => null,
                    'last_activity_at' => null,
                    'api_token'        => null, // Putuskan sesi login aktif di PWA
                    'remember_token'   => null,
                    'updated_at'       => now(),
                ]);

            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->whereIn('user_id', $targetUserIds)->delete();
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Sesi aktif seluruh pengguna (role user) berhasil direset ({$count} akun). Pengguna non-admin akan otomatis logout dan dapat melihat pop-up banner informasi terbaru saat login kembali.",
            'count'   => $count,
        ]);
    }

    /* =========================================================================
     * KONTROL BANNER INFORMASI (MULTIPLE BANNERS: GAMBAR / TEKS / KOMBINASI)
     * ======================================================================= */
    public function saveBannerItem(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return redirect()->route('admin');
        }

        $id = $request->input('banner_id');
        $title = trim((string)$request->input('title', ''));
        $content = trim((string)$request->input('content', ''));
        $type = $request->input('type', 'info');
        $linkUrl = trim((string)$request->input('link_url', ''));
        $orderNum = (int)$request->input('order_num', 0);
        $isActive = $request->has('is_active') ? 1 : 0;

        $imagePath = null;
        if ($id) {
            $existing = DB::table('banners')->where('id', $id)->first();
            $imagePath = $existing->image_path ?? null;
        }

        if ($request->input('remove_image') == '1') {
            if ($imagePath && file_exists(public_path($imagePath))) {
                @unlink(public_path($imagePath));
            }
            $imagePath = null;
        }

        // Upload Gambar jika ada
        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'nullable|file|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            ], [
                'image.image' => 'File yang diunggah harus berupa gambar yang valid.',
                'image.mimes' => 'Format gambar harus berupa jpeg, png, jpg, webp, atau gif.',
                'image.max'   => 'Ukuran gambar maksimal 5 MB.',
            ]);

            $file = $request->file('image');
            $ext = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array($ext, $allowedExtensions)) {
                return redirect()->route('admin')->with('pesanError', 'Ekstensi file tidak diizinkan. Hanya file gambar (.jpg, .jpeg, .png, .webp, .gif) yang diperbolehkan.');
            }

            $filename = 'banner_' . time() . '_' . uniqid() . '.' . $ext;
            $destDir = public_path('storage/banners');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0755, true);
            }
            if ($imagePath && file_exists(public_path($imagePath))) {
                @unlink(public_path($imagePath));
            }
            $file->move($destDir, $filename);
            $imagePath = '/storage/banners/' . $filename;
        }

        if (empty($content) && empty($imagePath) && empty($title)) {
            return redirect()->route('admin')->with('pesanError', 'Banner harus memiliki minimal Judul, Pesan Teks, atau Gambar.');
        }

        $data = [
            'title'      => $title,
            'content'    => $content,
            'image_path' => $imagePath,
            'link_url'   => $linkUrl,
            'type'       => $type,
            'is_active'  => $isActive,
            'order_num'  => $orderNum,
            'updated_at' => now(),
        ];

        if ($id) {
            DB::table('banners')->where('id', $id)->update($data);
            $pesan = 'Banner berhasil diperbarui.';
        } else {
            $data['created_at'] = now();
            DB::table('banners')->insert($data);
            $pesan = 'Banner baru berhasil ditambahkan.';
        }

        return redirect()->route('admin')->with('pesanStatus', $pesan);
    }

    public function toggleBannerStatus(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $id = $request->input('id');
        $banner = DB::table('banners')->where('id', $id)->first();
        if (!$banner) {
            return response()->json(['status' => 'error', 'message' => 'Banner tidak ditemukan.'], 404);
        }

        $newStatus = $banner->is_active ? 0 : 1;
        DB::table('banners')->where('id', $id)->update(['is_active' => $newStatus, 'updated_at' => now()]);

        return response()->json([
            'status'    => 'success',
            'is_active' => $newStatus,
            'message'   => $newStatus ? 'Banner diaktifkan.' : 'Banner dinonaktifkan.',
        ]);
    }

    public function deleteBannerItem(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $id = $request->input('id');
        $banner = DB::table('banners')->where('id', $id)->first();
        if ($banner && $banner->image_path && file_exists(public_path($banner->image_path))) {
            @unlink(public_path($banner->image_path));
        }

        DB::table('banners')->where('id', $id)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Banner berhasil dihapus.',
        ]);
    }

    /* =========================================================================
     * ACTIVITY LOGS / AUDIT LOGS (ADMIN VIEW)
     * ======================================================================= */
    public function getActivityLogs(Request $request)
    {
        if (!Session::get('admin_logged_in')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $q        = trim((string)$request->query('q', ''));
        $activity = trim((string)$request->query('activity', ''));
        $showAll  = $request->boolean('show_all') || $request->query('per_page') === 'all';

        $query = DB::table('activity_logs')
            ->whereNotIn('activity', ['Login Aplikasi', 'Logout Aplikasi', 'Login', 'Logout'])
            ->orderBy('id', 'desc');

        if ($activity && $activity !== 'all') {
            $query->where('activity', $activity);
        }

        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('username', 'like', "%$q%")
                  ->orWhere('user_name', 'like', "%$q%")
                  ->orWhere('activity', 'like', "%$q%")
                  ->orWhere('detail', 'like', "%$q%")
                  ->orWhere('ip_address', 'like', "%$q%");
            });
        }

        if ($showAll) {
            $allLogs = $query->limit(2000)->get();
            $total   = $allLogs->count();
            
            // Format waktu WITA untuk konsistensi
            $transformed = $allLogs->map(function ($item) {
                $item->created_at_formatted = date('d/m/Y H:i:s', strtotime($item->created_at)) . ' WITA';
                return $item;
            });

            $logs = [
                'data'         => $transformed,
                'current_page' => 1,
                'last_page'    => 1,
                'per_page'     => $total,
                'total'        => $total,
                'from'         => $total > 0 ? 1 : 0,
                'to'           => $total,
            ];
        } else {
            $perPage = min(max((int)$request->query('per_page', 15), 5), 100);
            $paginator = $query->paginate($perPage);

            // Format waktu WITA untuk setiap item
            $paginator->getCollection()->transform(function ($item) {
                $item->created_at_formatted = date('d/m/Y H:i:s', strtotime($item->created_at)) . ' WITA';
                return $item;
            });

            $logs = $paginator;
        }

        return response()->json([
            'status' => 'success',
            'data'   => $logs,
        ]);
    }

    /* =========================================================================
     * CLEAR CACHE SISTEM
     * ======================================================================= */
    public function clearSystemCache()
    {
        if (!Session::get('admin_logged_in')) {
            return redirect()->route('admin');
        }

        $this->clearCacheInternal();
        return redirect()->route('admin')->with('pesanStatus', 'Seluruh Cache Sistem, Konfigurasi, dan OPcache berhasil di-refresh!');
    }

    protected function clearCacheInternal()
    {
        Cache::flush();
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
        } catch (\Throwable $e) {
            // Abaikan jika artisan tidak dapat dipanggil langsung
        }
    }
}
