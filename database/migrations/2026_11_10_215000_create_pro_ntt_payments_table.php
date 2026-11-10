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
        if (!Schema::hasTable('pro_ntt_payments')) {
            Schema::create('pro_ntt_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('username', 50)->nullable()->index();
                $table->string('nopol', 20)->index();
                $table->string('nopol_display', 25)->nullable();
                $table->string('nomor_rangka', 50)->nullable();
                $table->string('digit_5_rangka', 10)->nullable();
                $table->string('sumber_rangka', 50)->nullable();

                $table->string('nama_pemilik', 150)->nullable();
                $table->string('jenis', 60)->nullable();
                $table->string('merk', 80)->nullable();
                $table->string('tipe', 120)->nullable();
                $table->string('tahun_buat', 10)->nullable();
                $table->string('unit', 200)->nullable();

                $table->string('kode_bayar', 50)->nullable()->index();
                $table->unsignedBigInteger('total_bayar')->default(0);
                $table->string('batas_bayar', 60)->nullable();
                $table->string('status_bayar', 50)->default('BELUM BAYAR');
                $table->string('status_pesan', 100)->nullable();
                $table->boolean('is_paid')->default(false);
                $table->boolean('is_expired')->default(false);
                $table->boolean('is_pending')->default(true);

                $table->string('kanal', 50)->nullable();
                $table->string('kanal_label', 100)->nullable();
                $table->string('metode', 255)->nullable();
                $table->string('tanggal_transaksi', 80)->nullable();
                $table->string('waktu_pembayaran', 80)->nullable();
                $table->string('ref_bank', 100)->nullable();
                $table->text('keterangan')->nullable();
                $table->json('raw_response')->nullable();
                $table->timestamp('last_checked_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pro_ntt_payments');
    }
};

