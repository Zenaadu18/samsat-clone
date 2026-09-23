<?php
// config_loader.php
// Memuat semua konfigurasi dari database ke dalam variabel global PHP.
// Harus di-require SETELAH db.php (karena butuh $conn).
//
// Variabel global yang dihasilkan:
//   $dbKonfigurasi          — array [kunci => nilai]
//   $dbTarifPkb             — array [K_GUNA => ['lama' => float, 'baru' => float]]
//   $dbTarifSwdkllj         — array [GOLONGAN => ['tarif' => int, 'admin' => int]]
//   $dbTarifStnkTnkb        — array [JENIS => ['biaya_stnk' => int, 'biaya_tnkb' => int]]
//   $dbKlasifikasiKendaraan — array [['keyword'=>..., 'golongan'=>..., 'jenis'=>...], ...]


/* ============================================================================
 * FALLBACK DEFAULT
 * Digunakan jika tabel konfigurasi belum ada / belum diisi.
 * ========================================================================== */

$dbKonfigurasi = [
    'tarif_baru_berlaku'         => '2025-01-05',
    'maks_bulan_denda'           => '24',
    'rasio_opsen_pkb'            => '0.66',
    'persen_denda_pkb_per_bulan' => '0.01',
    'api_url_samsat'             =>
        'https://dash.bpad.nttprov.go.id/pajak/webdtd/pendataan/core/php/getnopol.php',
    'amnesti_aktif'              => '1',
    'amnesti_mulai'              => '2026-09-01',
    'amnesti_selesai'            => '2026-10-31',
];

$dbTarifPkb = [
    'PRIBADI' => ['lama' => 0.0150, 'baru' => 0.0120],
    'UMUM'    => ['lama' => 0.0100, 'baru' => 0.0050],
    'DEFAULT' => ['lama' => 0.0050, 'baru' => 0.0050],
];

$dbTarifSwdkllj = [
    'A'  => ['tarif' => 0,      'admin' => 3000],
    'C1' => ['tarif' => 32000,  'admin' => 3000],
    'C2' => ['tarif' => 80000,  'admin' => 3000],
    'B'  => ['tarif' => 20000,  'admin' => 3000],
    'EU' => ['tarif' => 87000,  'admin' => 3000],
    'EP' => ['tarif' => 150000, 'admin' => 3000],
    'F'  => ['tarif' => 160000, 'admin' => 3000],
    'DP' => ['tarif' => 140000, 'admin' => 3000],
    'DU' => ['tarif' => 70000,  'admin' => 3000],
];

$dbTarifStnkTnkb = [
    'MOTOR' => ['biaya_stnk' => 100000, 'biaya_tnkb' => 60000],
    'MOBIL' => ['biaya_stnk' => 200000, 'biaya_tnkb' => 100000],
];

$dbKlasifikasiKendaraan = [
    ['keyword' => 'MOBIL DEREK',  'golongan' => 'B',  'jenis' => 'ALAT_BERAT'],
    ['keyword' => 'MOBIL BARANG', 'golongan' => 'DP', 'jenis' => 'PICKUP'],
    ['keyword' => 'TRACTOR HEAD', 'golongan' => 'F',  'jenis' => 'TRUCK'],
    ['keyword' => 'LIGHT TRUCK',  'golongan' => 'DP', 'jenis' => 'PICKUP'],
    ['keyword' => 'BLIND VAN',    'golongan' => 'DP', 'jenis' => 'PICKUP'],
    ['keyword' => 'PICK UP',      'golongan' => 'DP', 'jenis' => 'PICKUP'],
    ['keyword' => 'MICROBUS',     'golongan' => 'EU', 'jenis' => 'BUS'],
    ['keyword' => 'KONTAINER',    'golongan' => 'F',  'jenis' => 'TRUCK'],
    ['keyword' => 'CONTAINER',    'golongan' => 'F',  'jenis' => 'TRUCK'],
    ['keyword' => 'EXCAVATOR',    'golongan' => 'B',  'jenis' => 'ALAT_BERAT'],
    ['keyword' => 'FORKLIFT',     'golongan' => 'B',  'jenis' => 'ALAT_BERAT'],
    ['keyword' => 'FORKLIT',      'golongan' => 'B',  'jenis' => 'ALAT_BERAT'],
    ['keyword' => 'TRAKTOR',      'golongan' => 'B',  'jenis' => 'ALAT_BERAT'],
    ['keyword' => 'BULDOZER',     'golongan' => 'B',  'jenis' => 'ALAT_BERAT'],
    ['keyword' => 'GANDENG',      'golongan' => 'F',  'jenis' => 'TRUCK'],
    ['keyword' => 'TANGKI',       'golongan' => 'F',  'jenis' => 'TRUCK'],
    ['keyword' => 'DEREK',        'golongan' => 'B',  'jenis' => 'ALAT_BERAT'],
    ['keyword' => 'CRANE',        'golongan' => 'B',  'jenis' => 'ALAT_BERAT'],
    ['keyword' => 'PICKUP',       'golongan' => 'DP', 'jenis' => 'PICKUP'],
    ['keyword' => 'MIKRO',        'golongan' => 'EU', 'jenis' => 'BUS'],
    ['keyword' => 'TRUCK',        'golongan' => 'F',  'jenis' => 'TRUCK'],
    ['keyword' => 'TRUK',         'golongan' => 'F',  'jenis' => 'TRUCK'],
    ['keyword' => 'CRANE',        'golongan' => 'B',  'jenis' => 'ALAT_BERAT'],
    ['keyword' => 'BUS',          'golongan' => 'EU', 'jenis' => 'BUS'],
];


/* ============================================================================
 * BACA DARI DATABASE (override fallback jika tabel sudah ada)
 * ========================================================================== */

global $conn;


/* -- konfigurasi ----------------------------------------------------------- */
try {
    $res = $conn->query('SELECT kunci, nilai FROM konfigurasi');
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $dbKonfigurasi[$row['kunci']] = $row['nilai'];
        }
        $res->free();
    }
} catch (Throwable $e) { /* tabel belum ada — pakai fallback */ }


/* -- tarif_pkb ------------------------------------------------------------- */
try {
    $res = $conn->query('SELECT k_guna, tarif_lama, tarif_baru FROM tarif_pkb');
    if ($res) {
        $dbTarifPkb = []; // reset fallback, gunakan data DB
        while ($row = $res->fetch_assoc()) {
            $dbTarifPkb[strtoupper(trim($row['k_guna']))] = [
                'lama' => (float)$row['tarif_lama'],
                'baru' => (float)$row['tarif_baru'],
            ];
        }
        $res->free();
    }
} catch (Throwable $e) { /* pakai fallback */ }


/* -- tarif_swdkllj --------------------------------------------------------- */
try {
    $res = $conn->query('SELECT golongan, tarif, admin FROM tarif_swdkllj');
    if ($res) {
        $dbTarifSwdkllj = []; // reset fallback
        while ($row = $res->fetch_assoc()) {
            $dbTarifSwdkllj[$row['golongan']] = [
                'tarif' => (int)$row['tarif'],
                'admin' => (int)$row['admin'],
            ];
        }
        $res->free();
    }
} catch (Throwable $e) { /* pakai fallback */ }


/* -- tarif_stnk_tnkb ------------------------------------------------------- */
try {
    $res = $conn->query('SELECT jenis, biaya_stnk, biaya_tnkb FROM tarif_stnk_tnkb');
    if ($res) {
        $dbTarifStnkTnkb = []; // reset fallback
        while ($row = $res->fetch_assoc()) {
            $dbTarifStnkTnkb[strtoupper($row['jenis'])] = [
                'biaya_stnk' => (int)$row['biaya_stnk'],
                'biaya_tnkb' => (int)$row['biaya_tnkb'],
            ];
        }
        $res->free();
    }
} catch (Throwable $e) { /* pakai fallback */ }


/* -- klasifikasi_kendaraan ------------------------------------------------- */
/*
 * Urut dari keyword TERPANJANG ke terpendek agar
 * 'MOBIL DEREK' lebih dulu di-match daripada 'DEREK'.
 */
try {
    $res = $conn->query(
        'SELECT keyword, golongan_swdkllj, jenis_kend
         FROM   klasifikasi_kendaraan
         ORDER BY LENGTH(keyword) DESC'
    );
    if ($res) {
        $dbKlasifikasiKendaraan = []; // reset fallback
        while ($row = $res->fetch_assoc()) {
            $dbKlasifikasiKendaraan[] = [
                'keyword'  => strtoupper($row['keyword']),
                'golongan' => $row['golongan_swdkllj'],
                'jenis'    => strtoupper($row['jenis_kend']),
            ];
        }
        $res->free();
    }
} catch (Throwable $e) { /* pakai fallback */ }
