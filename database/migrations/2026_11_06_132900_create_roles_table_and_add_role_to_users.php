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
        // 1. Buat tabel roles jika belum ada
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name', 30)->unique();
                $table->string('display_name', 100);
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });

            // Seed role awal: admin dan user
            DB::table('roles')->insert([
                [
                    'id' => 1,
                    'name' => 'admin',
                    'display_name' => 'Administrator',
                    'description' => 'Akses penuh seluruh fitur termasuk Simulasi, Katalog NJKB, dan Audit Log',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 2,
                    'name' => 'user',
                    'display_name' => 'Pengguna Biasa',
                    'description' => 'Akses standar PWA: Cek Pajak, Balik Nama (BBN 2), Riwayat, dan Ketentuan',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // 2. Tambahkan kolom role dan role_id pada tabel users jika belum ada
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 30)->default('user')->after('username')->index();
            }
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->unsignedBigInteger('role_id')->default(2)->after('role')->index();
            }
        });

        // 3. Update pengguna sena menjadi admin
        DB::table('users')->where('username', 'sena')->update([
            'role' => 'admin',
            'role_id' => 1,
        ]);

        // Default pengguna lainnya menjadi user jika kosong
        DB::table('users')->where('username', '!=', 'sena')->whereNull('role')->update([
            'role' => 'user',
            'role_id' => 2,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role_id')) $table->dropColumn('role_id');
            if (Schema::hasColumn('users', 'role')) $table->dropColumn('role');
        });

        Schema::dropIfExists('roles');
    }
};

