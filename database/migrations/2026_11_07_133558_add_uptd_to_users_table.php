<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'uptd')) {
                $listUptd = [
                    'Kantor Pusat',
                    'UPTD Kota Kupang',
                    'UPTD Kabupaten Kupang',
                    'UPTD Kabupaten Timor Tengah Selatan',
                    'UPTD Kabupaten Timor Tengah Utara',
                    'UPTD Kabupaten Belu',
                    'UPTD Kabupaten Malaka',
                    'UPTD Kabupaten Rote Ndao',
                    'UPTD Kabupaten Sabu Raijua',
                    'UPTD Kabupaten Alor',
                    'UPTD Kabupaten Lembata',
                    'UPTD Kabupaten Flores Timur',
                    'UPTD Kabupaten Sikka',
                    'UPTD Kabupaten Ende',
                    'UPTD Kabupaten Ngada',
                    'UPTD Kabupaten Nagekeo',
                    'UPTD Kabupaten Manggarai',
                    'UPTD Kabupaten Manggarai Timur',
                    'UPTD Kabupaten Manggarai Barat',
                    'UPTD Kabupaten Sumba Timur',
                    'UPTD Kabupaten Sumba Barat',
                    'UPTD Kabupaten Sumba Tengah',
                    'UPTD Kabupaten Sumba Barat Daya',
                ];

                $table->enum('uptd', $listUptd)->nullable()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'uptd')) {
                $table->dropColumn('uptd');
            }
        });
    }
};
