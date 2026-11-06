<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Konfigurasi BAPENDA NTT Samsat Clone
    |--------------------------------------------------------------------------
    |
    | File ini menyimpan konfigurasi spesifik aplikasi yang tidak ada
    | di config Laravel default.
    |
    */

    /*
    | API Key untuk mengakses endpoint /api/v1/*
    | Set nilai ini di .env sebagai: SAMSAT_API_KEY=your-secret-key
    | Kosongkan untuk menonaktifkan autentikasi API (TIDAK DISARANKAN di production)
    */
    'api_key' => env('SAMSAT_API_KEY', ''),

    /*
    | Origin yang diizinkan untuk CORS pada endpoint API.
    | Set di .env sebagai: SAMSAT_CORS_ORIGINS=https://bapenda.nttprov.go.id
    | Untuk multiple origins, pisahkan dengan koma.
    | Set '*' untuk mengizinkan semua (HANYA untuk development)
    */
    'cors_origins' => env('SAMSAT_CORS_ORIGINS', '*'),

    /*
    | Durasi cache konfigurasi tarif (dalam detik).
    | Default: 300 (5 menit). Setelah simpan di admin, cache otomatis dihapus.
    */
    'config_cache_ttl' => (int) env('SAMSAT_CONFIG_CACHE_TTL', 300),

    /*
    | Durasi cache hasil query nopol ke API Samsat pusat (dalam detik).
    | Default: 300 (5 menit).
    */
    'nopol_cache_ttl' => (int) env('SAMSAT_NOPOL_CACHE_TTL', 300),

    /*
    | URL API Samsat pusat (fallback jika tidak ada di tabel konfigurasi DB)
    */
    'api_url_samsat' => env(
        'SAMSAT_API_URL',
        'https://dash.bpad.nttprov.go.id/pajak/webdtd/pendataan/core/php/getnopol.php'
    ),

    /*
    | Aktifkan SSL Verification untuk cURL ke server Samsat pusat.
    | Set false HANYA jika server Samsat menggunakan sertifikat self-signed.
    */
    'ssl_verify' => (bool) env('SAMSAT_SSL_VERIFY', true),
];
