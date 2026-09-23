<?php
// tax_helper.php
// Helper kalkulasi pajak kendaraan bermotor NTT.
//
// Sejak migrasi konfigurasi:
//   Seluruh tarif, konstanta, dan keyword klasifikasi kendaraan dibaca
//   dari variabel global yang diisi oleh config_loader.php
//   ($dbKonfigurasi, $dbTarifPkb, $dbTarifSwdkllj, $dbTarifStnkTnkb,
//    $dbKlasifikasiKendaraan).
//
//   Jika config_loader.php belum di-require, variabel tersebut belum ada
//   dan fungsi-fungsi di sini akan menggunakan nilai fallback yang aman.


/* ============================================================================
 * BAGIAN 1 - TARIF PKB
 * ========================================================================== */

/**
 * Mengambil tarif PKB berdasarkan penggunaan kendaraan.
 *
 * Data dibaca dari $dbTarifPkb (dimuat oleh config_loader.php).
 *
 * Urutan pencarian:
 *   1. Exact match dengan $kGuna (mis. 'PRIBADI')
 *   2. Substring UMUM (mis. 'UMUM PENUMPANG' → baris 'UMUM')
 *   3. Baris 'DEFAULT'
 *   4. Nilai hardcoded sebagai last-resort fallback
 */
function getTarifPkb(string $kGuna, bool $tarifBaru): float
{
    global $dbTarifPkb;

    $kGuna = strtoupper(trim($kGuna));

    if (!empty($dbTarifPkb)) {

        // 1. Exact match
        if (isset($dbTarifPkb[$kGuna])) {
            $r = $dbTarifPkb[$kGuna];
            return $tarifBaru ? (float)$r['baru'] : (float)$r['lama'];
        }

        // 2. UMUM substring
        if (
            strpos($kGuna, 'UMUM') !== false &&
            isset($dbTarifPkb['UMUM'])
        ) {
            $r = $dbTarifPkb['UMUM'];
            return $tarifBaru ? (float)$r['baru'] : (float)$r['lama'];
        }

        // 3. DEFAULT row
        if (isset($dbTarifPkb['DEFAULT'])) {
            $r = $dbTarifPkb['DEFAULT'];
            return $tarifBaru ? (float)$r['baru'] : (float)$r['lama'];
        }
    }

    // 4. Hardcoded last-resort fallback
    if ($tarifBaru) {
        return ($kGuna === 'PRIBADI') ? 0.012 : 0.005;
    }

    if ($kGuna === 'PRIBADI')              return 0.015;
    if (strpos($kGuna, 'UMUM') !== false)  return 0.010;

    return 0.005;
}


/**
 * Mengecek apakah tanggal menggunakan tarif baru.
 * Tanggal cutoff dibaca dari tabel konfigurasi (kunci: tarif_baru_berlaku).
 */
function menggunakanTarifBaru(DateTime $tanggal): bool
{
    global $dbKonfigurasi;

    $cutoffStr = $dbKonfigurasi['tarif_baru_berlaku'] ?? '2025-01-05';
    $cutoff    = new DateTime($cutoffStr);

    return $tanggal >= $cutoff;
}


/* ============================================================================
 * BAGIAN 2 - UTILITAS TANGGAL
 * ========================================================================== */

/**
 * Validasi tanggal dari API.
 */
function tanggalValid(string $tanggal): bool
{
    return !empty($tanggal)
        && $tanggal !== '-'
        && $tanggal !== '0000-00-00';
}


/**
 * Membuat DateTime dengan aman.
 */
function buatTanggal(string $tanggal): ?DateTime
{
    if (!tanggalValid($tanggal)) {
        return null;
    }

    try {
        return new DateTime($tanggal);
    } catch (Exception $e) {
        return null;
    }
}


/**
 * Menghitung bulan keterlambatan.
 *
 * Contoh:
 * 1 hari   -> 1 bulan
 * 30 hari  -> 1 bulan
 * 31 hari  -> 2 bulan
 *
 * Maksimal dikonfigurasi melalui tabel konfigurasi (kunci: maks_bulan_denda).
 */
function hitungBulanTerlambatDariTanggal(DateTime $jatuhTempo): int
{
    global $dbKonfigurasi;

    $sekarang  = new DateTime('today');
    $maksbulan = (int)($dbKonfigurasi['maks_bulan_denda'] ?? 24);

    if ($sekarang <= $jatuhTempo) {
        return 0;
    }

    $hari = (int)$sekarang->diff($jatuhTempo)->days;

    return min(
        (int)ceil($hari / 30),
        $maksbulan
    );
}


/**
 * Kompatibilitas dengan pemanggilan lama.
 */
function hitungBulanTerlambat(string $sdNotice): int
{
    $jatuhTempo = buatTanggal($sdNotice);

    if (!$jatuhTempo) {
        return 0;
    }

    return hitungBulanTerlambatDariTanggal($jatuhTempo);
}


/* ============================================================================
 * BAGIAN 3 - PERHITUNGAN PKB
 * ========================================================================== */

/**
 * Menghitung PKB lengkap berdasarkan periode.
 *
 * LOGIKA UTAMA:
 *
 * Jika kendaraan memiliki beberapa periode:
 *
 *   2023-2024 -> TUNGGAKAN
 *   2024-2025 -> TUNGGAKAN
 *   2025-2026 -> PKB BERJALAN
 *
 * Jadi pengguna tidak perlu melihat pembagian tarif lama/tarif baru.
 *
 * Namun secara internal tarif lama dan baru tetap dihitung karena
 * diperlukan untuk menentukan nilai PKB dan Opsen.
 *
 * Konfigurasi yang dibaca dari DB:
 *   - rasio_opsen_pkb            (default 0.66)
 *   - persen_denda_pkb_per_bulan (default 0.01)
 */
function hitungPkbLengkap(float $dpPkb, string $kGuna, string $sdNotice, array $kendaraan = []): array {
    global $dbKonfigurasi;

    $today      = new DateTime('today');
    $rasioOpsen = (float)($dbKonfigurasi['rasio_opsen_pkb']            ?? 0.66);
    $pctDenda   = (float)($dbKonfigurasi['persen_denda_pkb_per_bulan'] ?? 0.01);
    
    // Amnesti config
    $amnestiAktif = !empty($dbKonfigurasi['amnesti_aktif']) && (int)$dbKonfigurasi['amnesti_aktif'] === 1;
    $amnestiMulai = $dbKonfigurasi['amnesti_mulai'] ?? '2026-09-01';
    $amnestiSelesai = $dbKonfigurasi['amnesti_selesai'] ?? '2026-10-31';
    
    if ($amnestiAktif) {
        $startAmnesti = buatTanggal($amnestiMulai);
        $endAmnesti = buatTanggal($amnestiSelesai);
        if (!$startAmnesti || !$endAmnesti || $today < $startAmnesti || $today > $endAmnesti) {
            $amnestiAktif = false;
        }
    }

    if (!tanggalValid($sdNotice)) {
        $tarif = getTarifPkb($kGuna, true);
        $pkbBerjalan   = round($dpPkb * $tarif);
        $opsenBerjalan = round($pkbBerjalan * $rasioOpsen);

        return [
            'pkb_berjalan'        => $pkbBerjalan,
            'pkb_tunggakan'       => 0,
            'pkb_total'           => $pkbBerjalan,
            'opsen_pkb_berjalan'  => $opsenBerjalan,
            'opsen_pkb_tunggakan' => 0,
            'opsen_pkb'           => $opsenBerjalan,
            'denda_pkb_berjalan'  => 0,
            'denda_pkb_tunggakan' => 0,
            'denda_pkb_total'     => 0,
            'denda_opsen_berjalan'=> 0,
            'denda_opsen_tunggakan'=> 0,
            'denda_opsen'         => 0,
            'tahun_berjalan'  => 1,
            'tahun_tunggakan' => 0,
            'tahun_total'     => 1,
            'pkb_lama'       => 0,
            'pkb_baru'       => $pkbBerjalan,
            'denda_pkb_lama' => 0,
            'denda_pkb_baru' => 0,
            'tahun_lama'     => 0,
            'tahun_baru'     => 1,
            'peringatan' => 'Data SD Notice (masa pajak) tidak tersedia. Perhitungan hanya untuk 1 tahun dengan tarif terkini.',
            'amnesti_aktif' => false,
            'amnesti_diskon_pkb_berjalan' => 0,
            'amnesti_diskon_pkb_tunggakan' => 0,
            'amnesti_denda_dibebaskan' => 0,
            'amnesti_total_potongan' => 0
        ];
    }

    $periodStart = buatTanggal($sdNotice);
    if (!$periodStart) {
        $tarif = getTarifPkb($kGuna, true);
        $pkbBerjalan   = round($dpPkb * $tarif);
        $opsenBerjalan = round($pkbBerjalan * $rasioOpsen);

        return [
            'pkb_berjalan'        => $pkbBerjalan,
            'pkb_tunggakan'       => 0,
            'pkb_total'           => $pkbBerjalan,
            'opsen_pkb_berjalan'  => $opsenBerjalan,
            'opsen_pkb_tunggakan' => 0,
            'opsen_pkb'           => $opsenBerjalan,
            'denda_pkb_berjalan'  => 0,
            'denda_pkb_tunggakan' => 0,
            'denda_pkb_total'     => 0,
            'denda_opsen_berjalan'=> 0,
            'denda_opsen_tunggakan'=> 0,
            'denda_opsen'         => 0,
            'tahun_berjalan'  => 1,
            'tahun_tunggakan' => 0,
            'tahun_total'     => 1,
            'pkb_lama'       => 0,
            'pkb_baru'       => $pkbBerjalan,
            'denda_pkb_lama' => 0,
            'denda_pkb_baru' => 0,
            'tahun_lama'     => 0,
            'tahun_baru'     => 1,
            'peringatan' => 'Data SD Notice tidak valid. Perhitungan hanya untuk 1 tahun dengan tarif terkini.',
            'amnesti_aktif' => false,
            'amnesti_diskon_pkb_berjalan' => 0,
            'amnesti_diskon_pkb_tunggakan' => 0,
            'amnesti_denda_dibebaskan' => 0,
            'amnesti_total_potongan' => 0
        ];
    }

    $periods = [];
    $current = clone $periodStart;
    $guard   = 0;
    
    // Jika masih di masa depan, periodenya hanya 1 (berjalan)
    if ($periodStart >= $today) {
        $periodEnd = clone $current;
        $periodEnd->modify('+1 year');
        $periods[] = ['start' => clone $current, 'end' => clone $periodEnd];
    } else {
        while ($current < $today && $guard < 25) {
            $periodEnd = clone $current;
            $periodEnd->modify('+1 year');
            $periods[] = ['start' => clone $current, 'end' => clone $periodEnd];
            $current = $periodEnd;
            $guard++;
        }
    }

    if (empty($periods)) {
        $tarif = getTarifPkb($kGuna, true);
        $pkb   = round($dpPkb * $tarif);
        $opsen = round($pkb * $rasioOpsen);
        return [
            'pkb_berjalan'        => $pkb,
            'pkb_tunggakan'       => 0,
            'pkb_total'           => $pkb,
            'opsen_pkb_berjalan'  => $opsen,
            'opsen_pkb_tunggakan' => 0,
            'opsen_pkb'           => $opsen,
            'denda_pkb_berjalan'  => 0,
            'denda_pkb_tunggakan' => 0,
            'denda_pkb_total'     => 0,
            'denda_opsen_berjalan'=> 0,
            'denda_opsen_tunggakan'=> 0,
            'denda_opsen'         => 0,
            'tahun_berjalan'  => 1,
            'tahun_tunggakan' => 0,
            'tahun_total'     => 1,
            'pkb_lama'       => 0,
            'pkb_baru'       => $pkb,
            'denda_pkb_lama' => 0,
            'denda_pkb_baru' => 0,
            'tahun_lama'     => 0,
            'tahun_baru'     => 1,
            'peringatan' => '',
            'amnesti_aktif' => false,
            'amnesti_diskon_pkb_berjalan' => 0,
            'amnesti_diskon_pkb_tunggakan' => 0,
            'amnesti_denda_dibebaskan' => 0,
            'amnesti_total_potongan' => 0
        ];
    }

    $pkbBerjalan = 0; $pkbTunggakan = 0;
    $opsenBerjalan = 0; $opsenTunggakan = 0;
    $dendaPkbBerjalan = 0; $dendaPkbTunggakan = 0;
    $dendaOpsenBerjalan = 0; $dendaOpsenTunggakan = 0;
    $pkbLama = 0; $pkbBaru = 0;
    $dendaLama = 0; $dendaBaru = 0;
    $tahunLama = 0; $tahunBaru = 0;

    $indexBerjalan = count($periods) - 1;
    $hasTunggakan = $indexBerjalan > 0;
    $hariKeterlambatan = 0;

    foreach ($periods as $index => $period) {
        $start = $period['start'];
        $end   = $period['end'];
        $isBerjalan = ($index === $indexBerjalan);
        $isNew      = menggunakanTarifBaru($start);
        $tarif = getTarifPkb($kGuna, $isNew);
        $pkbTahun = round($dpPkb * $tarif);
        $opsenTahun = $isNew ? round($pkbTahun * $rasioOpsen) : 0;

        $bulanLambat = 0;
        if ($today > $end) {
            $bulanLambat = hitungBulanTerlambatDariTanggal($end);
            if ($isBerjalan) {
                $hasTunggakan = true;
                $hariKeterlambatan = $end->diff($today)->days;
            } else {
                $hasTunggakan = true;
            }
        } elseif ($isBerjalan) {
            $hariKeterlambatan = -($today->diff($start)->days);
        }

        $dendaPkbTahun = round($bulanLambat * $pctDenda * $pkbTahun);
        $dendaOpsenTahun = $isNew ? round($bulanLambat * $pctDenda * $opsenTahun) : 0;

        if ($isNew) {
            $pkbBaru   += $pkbTahun;
            $dendaBaru += $dendaPkbTahun;
            $tahunBaru++;
        } else {
            $pkbLama   += $pkbTahun;
            $dendaLama += $dendaPkbTahun;
            $tahunLama++;
        }

        if ($isBerjalan) {
            $pkbBerjalan   += $pkbTahun;
            $opsenBerjalan += $opsenTahun;
            $dendaPkbBerjalan   += $dendaPkbTahun;
            $dendaOpsenBerjalan += $dendaOpsenTahun;
        } else {
            $pkbTunggakan   += $pkbTahun;
            $opsenTunggakan += $opsenTahun;
            $dendaPkbTunggakan   += $dendaPkbTahun;
            $dendaOpsenTunggakan += $dendaOpsenTahun;
        }
    }

    // --- APPLY AMNESTY ---
    $diskonBerjalan = 0;
    $diskonTunggakan = 0;
    $dendaDibebaskan = 0;

    if ($amnestiAktif) {
        // 1. Pembebasan Sanksi
        $dendaDibebaskan = $dendaPkbBerjalan + $dendaPkbTunggakan + $dendaOpsenBerjalan + $dendaOpsenTunggakan;
        $dendaPkbBerjalan = 0;
        $dendaPkbTunggakan = 0;
        $dendaOpsenBerjalan = 0;
        $dendaOpsenTunggakan = 0;

        if ($hasTunggakan) {
            $alamat = strtoupper($kendaraan['alamat'] ?? '');
            $is75 = preg_match('/\b(SIKKA|ENDE|NAGEKEO|NGADA|MANGGARAI|MATIM|MANGGARAI TIMUR|MABAR|MANGGARAI BARAT)\b/', $alamat);
            
            $persenDiskonTunggakan = $is75 ? 0.75 : 0.50;
            $diskonTunggakan = round($pkbTunggakan * $persenDiskonTunggakan);
        } else {
            $isMutasi = !empty($kendaraan['is_mutasi']);
            $k_jr = strtoupper($kendaraan['k_jr'] ?? '');
            
            $isRoda23 = preg_match('/MOTOR|R2|R3/i', $kendaraan['jenis'] ?? '') || preg_match('/MOTOR/i', $k_jr);
            
            $daysBefore = abs($hariKeterlambatan);
            $persenDiskonBerjalan = 0;
            
            if ($isMutasi) {
                $persenDiskonBerjalan = 0.50;
            } elseif ($isRoda23) {
                if ($daysBefore >= 61 && $daysBefore <= 90) {
                    $persenDiskonBerjalan = 0.20;
                } elseif ($daysBefore >= 31 && $daysBefore <= 60) {
                    $persenDiskonBerjalan = 0.15;
                } elseif ($daysBefore >= 0 && $daysBefore <= 30) {
                    $persenDiskonBerjalan = 0.10;
                }
            } else {
                if ($daysBefore >= 0 && $daysBefore <= 90) {
                    $persenDiskonBerjalan = 0.10;
                }
            }
            
            $diskonBerjalan = round($pkbBerjalan * $persenDiskonBerjalan);
        }
        
        $pkbBerjalan -= $diskonBerjalan;
        $pkbTunggakan -= $diskonTunggakan;
    }

    $pkbTotal   = $pkbBerjalan + $pkbTunggakan;
    $opsenTotal = $opsenBerjalan + $opsenTunggakan;
    $dendaPkbTotal = $dendaPkbBerjalan + $dendaPkbTunggakan;
    $dendaOpsenTotal = $dendaOpsenBerjalan + $dendaOpsenTunggakan;

    return [
        'pkb_berjalan'  => round($pkbBerjalan),
        'pkb_tunggakan' => round($pkbTunggakan),
        'pkb_total'     => round($pkbTotal),
        'opsen_pkb_berjalan'  => round($opsenBerjalan),
        'opsen_pkb_tunggakan' => round($opsenTunggakan),
        'opsen_pkb'           => round($opsenTotal),
        'denda_pkb_berjalan'  => round($dendaPkbBerjalan),
        'denda_pkb_tunggakan' => round($dendaPkbTunggakan),
        'denda_pkb_total'     => round($dendaPkbTotal),
        'denda_opsen_berjalan'  => round($dendaOpsenBerjalan),
        'denda_opsen_tunggakan' => round($dendaOpsenTunggakan),
        'denda_opsen'           => round($dendaOpsenTotal),
        'tahun_berjalan'  => 1,
        'tahun_tunggakan' => max(count($periods) - 1, 0),
        'tahun_total'     => count($periods),
        'pkb_lama'       => round($pkbLama),
        'pkb_baru'       => round($pkbBaru),
        'denda_pkb_lama' => round($dendaLama),
        'denda_pkb_baru' => round($dendaBaru),
        'tahun_lama' => $tahunLama,
        'tahun_baru' => $tahunBaru,
        'peringatan' => '',
        'amnesti_aktif' => $amnestiAktif,
        'amnesti_diskon_pkb_berjalan' => $diskonBerjalan,
        'amnesti_diskon_pkb_tunggakan' => $diskonTunggakan,
        'amnesti_denda_dibebaskan' => $dendaDibebaskan,
        'amnesti_total_potongan' => $diskonBerjalan + $diskonTunggakan + $dendaDibebaskan
    ];
}


/* ============================================================================
 * BAGIAN 4 - SWDKLLJ
 * ========================================================================== */

/**
 * Menentukan golongan dan tarif SWDKLLJ.
 *
 * Data tarif dibaca dari $dbTarifSwdkllj (tabel tarif_swdkllj).
 * Keyword klasifikasi dibaca dari $dbKlasifikasiKendaraan (tabel klasifikasi_kendaraan).
 *
 * Logika kondisional yang bergantung pada parameter runtime ($kGuna, $isiCylinder)
 * tetap dipertahankan di sini karena tidak bisa direpresentasikan sebagai data flat.
 */
function getTarifSwdkllj(
    string $jenisKend,
    string $kJr,
    int $isiCylinder,
    string $kGuna
): array {

    global $dbTarifSwdkllj, $dbKlasifikasiKendaraan;

    $jenisUpper = strtoupper($jenisKend);
    $kJrUpper   = strtoupper($kJr);
    $kGunaUpper = strtoupper(trim($kGuna));


    /*
     * Helper: ambil baris tarif untuk satu golongan.
     */
    $getTarif = static function (string $golongan) use ($dbTarifSwdkllj): array {

        $t = $dbTarifSwdkllj[$golongan] ?? null;

        // Fallback jika golongan tidak ditemukan di DB
        if (!$t) {
            $fallback = [
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
            $t = $fallback[$golongan] ?? ['tarif' => 0, 'admin' => 3000];
        }

        return [
            'golongan' => $golongan,
            'tarif'    => (int)$t['tarif'],
            'admin'    => (int)$t['admin'],
        ];
    };


    /*
     * Sepeda motor (deteksi via K_JR atau JenisKendaraan).
     */
    $isMotor =
        strpos($kJrUpper, 'SEPEDA MOTOR') !== false ||
        strpos($jenisUpper, 'SEPEDA MOTOR') !== false;

    if ($isMotor) {
        if ($isiCylinder < 50)   return $getTarif('A');
        if ($isiCylinder <= 250) return $getTarif('C1');
        return $getTarif('C2');
    }


    /*
     * Deteksi jenis kendaraan lain menggunakan data keyword dari DB.
     * Keyword diurutkan terpanjang → terpendek (sudah dilakukan di config_loader).
     */
    $detectedJenis = null;

    foreach ($dbKlasifikasiKendaraan as $klas) {
        if (strpos($jenisUpper, $klas['keyword']) !== false) {
            $detectedJenis = $klas['jenis'];
            break;
        }
    }


    /*
     * Terapkan logika kondisional berdasarkan jenis yang terdeteksi.
     */

    if ($detectedJenis === 'ALAT_BERAT') {
        return $getTarif('B');
    }

    if ($detectedJenis === 'BUS') {
        /*
         * Bus umum vs bus pribadi ditentukan oleh K_GUNA.
         */
        return $getTarif($kGunaUpper === 'UMUM' ? 'EU' : 'EP');
    }

    if ($detectedJenis === 'TRUCK') {
        return $getTarif('F');
    }

    if ($detectedJenis === 'PICKUP') {
        /*
         * Pick-up dengan silinder > 2400cc → masuk golongan F (truck berat).
         */
        return $getTarif($isiCylinder > 2400 ? 'F' : 'DP');
    }


    /*
     * Mobil penumpang umum dengan kapasitas silinder <= 1600cc.
     */
    if ($kGunaUpper === 'UMUM' && $isiCylinder <= 1600) {
        return $getTarif('DU');
    }


    /*
     * Default: sedan / jeep / minibus pribadi.
     */
    return $getTarif('DP');
}


/**
 * Menghitung SWDKLLJ.
 *
 * $jumlahTahun = jumlah periode yang harus dibayar.
 *
 * Contoh:
 * 1 tahun x Rp35.000 = Rp35.000
 * 2 tahun x Rp35.000 = Rp70.000
 *
 * Untuk menjaga agar perhitungan tidak berubah secara agresif,
 * denda SWDKLLJ default = 0.
 */
function hitungSwdkllj(
    string $jenisKend,
    string $kJr,
    int $isiCylinder,
    string $kGuna,
    int $jumlahTahun = 1
): array {

    $tarifData = getTarifSwdkllj(
        $jenisKend,
        $kJr,
        $isiCylinder,
        $kGuna
    );

    $jumlahTahun  = max(1, $jumlahTahun);
    $tarifTahunan = $tarifData['tarif'];
    $admin        = $tarifData['admin'];

    /*
     * Admin SWDKLLJ dikenakan setiap periode.
     */
    $swdklljPerTahun = $tarifTahunan + $admin;
    $total           = $swdklljPerTahun * $jumlahTahun;

    /*
     * Denda SWDKLLJ.
     *
     * Saat ini 0 karena belum ada parameter/database khusus
     * untuk menentukan denda SWDKLLJ per periode.
     */
    $denda = 0;

    return [
        'golongan' => $tarifData['golongan'],
        'tarif'    => $tarifTahunan,
        'admin'    => $admin,

        'jumlah_tahun' => $jumlahTahun,

        'total'              => $total,
        'denda'              => $denda,
        'total_dengan_denda' => $total + $denda,
    ];
}


/* ============================================================================
 * BAGIAN 5 - STNK & TNKB
 * ========================================================================== */

/**
 * Menghitung biaya STNK & TNKB jika:
 * - sudah jatuh tempo, atau
 * - akan jatuh tempo dalam 3 bulan.
 *
 * Tarif dibaca dari $dbTarifStnkTnkb (tabel tarif_stnk_tnkb).
 */
function hitungBiayaStnk(
    string $sdStnk,
    string $jenisKend,
    string $kJr
): array {

    global $dbTarifStnkTnkb;

    $kosong = [
        'wajib'      => false,
        'biaya_tnkb' => 0,
        'biaya_stnk' => 0,
        'total'      => 0,
        'info'       => ''
    ];

    if (!tanggalValid($sdStnk)) {
        return $kosong;
    }

    try {

        $jatuhTempo = new DateTime($sdStnk);
        $sekarang   = new DateTime('today');

        $batasAwal =
            (clone $jatuhTempo)->modify('-3 months');

        /*
         * Masih lebih dari 3 bulan sebelum jatuh tempo.
         */
        if ($sekarang < $batasAwal) {
            return $kosong;
        }


        $jenisUpper = strtoupper($jenisKend);
        $kJrUpper   = strtoupper($kJr);

        $isMotor =
            strpos($kJrUpper, 'SEPEDA MOTOR') !== false ||
            strpos($jenisUpper, 'SEPEDA MOTOR') !== false ||
            strpos($kJrUpper, 'RODA TIGA') !== false;


        /*
         * Tarif dari DB dengan fallback hardcoded.
         */
        if ($isMotor) {
            $t          = $dbTarifStnkTnkb['MOTOR'] ?? null;
            $biayaTnkb  = $t ? (int)$t['biaya_tnkb'] : 60000;
            $biayaStnk  = $t ? (int)$t['biaya_stnk'] : 100000;
        } else {
            $t          = $dbTarifStnkTnkb['MOBIL'] ?? null;
            $biayaTnkb  = $t ? (int)$t['biaya_tnkb'] : 100000;
            $biayaStnk  = $t ? (int)$t['biaya_stnk'] : 200000;
        }


        if ($sekarang > $jatuhTempo) {

            $hari = (int)$sekarang->diff($jatuhTempo)->days;
            $info = 'STNK sudah berakhir (' . $hari . ' hari lalu)';

        } else {

            $hari = (int)$sekarang->diff($jatuhTempo)->days;
            $info = 'STNK berakhir dalam ' . $hari . ' hari';
        }


        return [
            'wajib'      => true,
            'biaya_tnkb' => $biayaTnkb,
            'biaya_stnk' => $biayaStnk,
            'total'      => $biayaTnkb + $biayaStnk,
            'info'       => $info
        ];

    } catch (Exception $e) {

        return $kosong;
    }
}