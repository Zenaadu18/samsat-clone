<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activity_logs') && !Schema::hasColumn('activity_logs', 'total_biaya')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->decimal('total_biaya', 15, 2)->nullable()->after('detail');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('activity_logs') && Schema::hasColumn('activity_logs', 'total_biaya')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->dropColumn('total_biaya');
            });
        }
    }
};

