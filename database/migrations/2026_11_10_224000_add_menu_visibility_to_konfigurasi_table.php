<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            [
                'kunci'      => 'menu_cek',
                'nilai'      => '1',
                'keterangan' => 'Tampilkan menu Cek Pajak Nopol untuk user',
            ],
            [
                'kunci'      => 'menu_prontt',
                'nilai'      => '1',
                'keterangan' => 'Tampilkan menu & tab pembayaran online PRO NTT untuk user',
            ],
            [
                'kunci'      => 'fitur_tombol_bayar_prontt',
                'nilai'      => '1',
                'keterangan' => 'Tampilkan tombol "Bayar lewat PRO NTT" pada halaman hasil Cek Pajak',
            ],
            [
                'kunci'      => 'menu_bbn2',
                'nilai'      => '1',
                'keterangan' => 'Tampilkan menu Simulasi Balik Nama (BBN 2) untuk user',
            ],
            [
                'kunci'      => 'menu_simulasi',
                'nilai'      => '0',
                'keterangan' => 'Tampilkan menu Simulasi Pajak & Amnesti untuk user non-admin',
            ],
            [
                'kunci'      => 'menu_njkb',
                'nilai'      => '0',
                'keterangan' => 'Tampilkan menu Katalog NJKB untuk user non-admin',
            ],
            [
                'kunci'      => 'menu_riwayat',
                'nilai'      => '1',
                'keterangan' => 'Tampilkan menu Riwayat Aktivitas untuk user',
            ],
            [
                'kunci'      => 'menu_info',
                'nilai'      => '1',
                'keterangan' => 'Tampilkan menu Ketentuan Relaksasi / Info untuk user',
            ],
        ];

        DB::table('konfigurasi')->insertOrIgnore($defaults);
    }

    public function down(): void
    {
        DB::table('konfigurasi')->whereIn('kunci', [
            'menu_cek',
            'menu_prontt',
            'fitur_tombol_bayar_prontt',
            'menu_bbn2',
            'menu_simulasi',
            'menu_njkb',
            'menu_riwayat',
            'menu_info',
        ])->delete();
    }
};

