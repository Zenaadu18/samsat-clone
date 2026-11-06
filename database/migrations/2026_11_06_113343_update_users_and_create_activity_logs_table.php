<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modifikasi tabel users agar mendukung login via username & fitur blokir
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 50)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('users', 'is_blocked')) {
                $table->boolean('is_blocked')->default(false)->after('password');
            }
            if (!Schema::hasColumn('users', 'api_token')) {
                $table->string('api_token', 80)->nullable()->unique()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('api_token');
            }
        });

        // Buat kolom email menjadi nullable jika sebelumnya wajib
        try {
            DB::statement('ALTER TABLE users MODIFY COLUMN email VARCHAR(255) NULL');
        } catch (\Throwable $e) {}

        // 2. Buat tabel activity_logs untuk pencatatan riwayat aktivitas user & audit admin
        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('username', 50)->index();
                $table->string('user_name', 100)->nullable();
                $table->string('activity', 100);
                $table->text('detail')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamp('created_at')->useCurrent()->index();
            });
        }

        // 3. Inisialisasi default konfigurasi banner di tabel konfigurasi
        $defaultBanners = [
            ['kunci' => 'banner_active', 'nilai' => '0', 'keterangan' => 'Status Aktif Banner Informasi User (1 = Aktif, 0 = Nonaktif)'],
            ['kunci' => 'banner_title', 'nilai' => '', 'keterangan' => 'Judul Banner Informasi'],
            ['kunci' => 'banner_content', 'nilai' => '', 'keterangan' => 'Isi Pesan Banner Informasi Pengguna'],
            ['kunci' => 'banner_type', 'nilai' => 'info', 'keterangan' => 'Tipe Banner (info, warning, promo)'],
        ];

        foreach ($defaultBanners as $item) {
            $exists = DB::table('konfigurasi')->where('kunci', $item['kunci'])->exists();
            if (!$exists) {
                DB::table('konfigurasi')->insert($item);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'username')) $table->dropColumn('username');
            if (Schema::hasColumn('users', 'is_blocked')) $table->dropColumn('is_blocked');
            if (Schema::hasColumn('users', 'api_token')) $table->dropColumn('api_token');
            if (Schema::hasColumn('users', 'last_login_at')) $table->dropColumn('last_login_at');
        });
    }
};
