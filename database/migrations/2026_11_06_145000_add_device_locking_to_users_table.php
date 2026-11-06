<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'device_id')) {
                $table->string('device_id', 100)->nullable()->after('api_token');
            }
            if (!Schema::hasColumn('users', 'device_name')) {
                $table->string('device_name', 150)->nullable()->after('device_id');
            }
            if (!Schema::hasColumn('users', 'device_locked_at')) {
                $table->dateTime('device_locked_at')->nullable()->after('device_name');
            }
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('device_locked_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['device_id', 'device_name', 'device_locked_at', 'last_login_ip']);
        });
    }
};

