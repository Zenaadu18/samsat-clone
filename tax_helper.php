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
function getTarifPkb(string $kGuna, bool $tarifBaru, bool $isKhusus05 = false): float
{
    global $dbTarifPkb;

    $kGuna = strtoupper(trim($kGuna));

    // Kendaraan ambulance, sosial, sosial keagamaan (termasuk yang bertnkb hitam / K_GUNA pribadi) dikenakan tarif khusus 0,5%
    if (
        $isKhusus05 ||
        strpos($kGuna, 'AMBULANCE') !== false ||
        strpos($kGuna, 'SOSIAL') !== false ||
        strpos($kGuna, 'KEAGAMAAN') !== false ||
        strpos($kGuna, 'AGAMA') !== false ||
        strpos($kGuna, 'KEBERSIHAN') !== false ||
        strpos($kGuna, 'PEMADAM') !== false
    ) {
        return 0.005;
    }

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

    $diff = $jatuhTempo->diff($sekarang);
    $bulan = ($diff->y * 12) + $diff->m;
    if ($diff->d > 0) {
        $bulan += 1;
    }

    return min($bulan, $maksbulan);
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
    $rasioOpsen = (float)($dbKonfigurasi['rasio_opsen_pkb'] ?? 0.66);
    $pctDenda   = (float)($dbKonfigurasi['persen_denda_pkb_per_bulan'] ?? 0.01);
    
    // 1. CEK STATUS & TANGGAL AMNESTI
    $amnestiAktif = !empty($dbKonfigurasi['amnesti_aktif']) && (int)$dbKonfigurasi['amnesti_aktif'] === 1;
    $amnestiMulai = $dbKonfigurasi['amnesti_mulai'] ?? '2026-09-01';
    $amnestiSelesai = $dbKonfigurasi['amnesti_selesai'] ?? '2026-11-30';
    
    if ($amnestiAktif) {
        $startAmnesti = buatTanggal($amnestiMulai);
        $endAmnesti = buatTanggal($amnestiSelesai);
        if (!$startAmnesti || !$endAmnesti || $today < $startAmnesti || $today > $endAmnesti) {
            $amnestiAktif = false;
        }
    }

    // 2. IDENTIFIKASI PERIODE TERLEBIH DAHULU (Menentukan ada tunggakan atau tidak)
    $periodStart = buatTanggal($sdNotice);
    $periods = [];
    $isValidDate = true;

    if (!$periodStart) {
        $isValidDate = false;
        $periodEnd = clone $today;
        $periodEnd->modify('+1 year');
        $periods[] = ['start' => clone $today, 'end' => clone $periodEnd];
    } else {
        $current = clone $periodStart;
        $guard   = 0;
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
    }

    // Batasi maksimal 5 tahun (ambil 5 data terbaru dari belakang)
    if (count($periods) > 5) {
        $periods = array_slice($periods, -5);
    }

    // Ekstrak penanda khusus kendaraan
    $isMutasi       = !empty($kendaraan['is_mutasi']);
    $isDisabilitas  = !empty($kendaraan['is_disabilitas']);
    $isKarwas       = !empty($kendaraan['is_karwas']);
    $isSosial       = !empty($kendaraan['is_sosial'])
        || strpos(strtoupper($kGuna), 'SOSIAL') !== false
        || strpos(strtoupper($kGuna), 'KEAGAMAAN') !== false
        || strpos(strtoupper($kGuna), 'AGAMA') !== false;

    $isAmbulance    = !empty($kendaraan['is_ambulance'])
        || strpos(strtoupper($kGuna), 'AMBULANCE') !== false
        || strpos(strtoupper($kendaraan['jenis'] ?? ''), 'AMBULANCE') !== false
        || strpos(strtoupper($kendaraan['type'] ?? ''), 'AMBULANCE') !== false
        || strpos(strtoupper($kendaraan['rubah_bentuk'] ?? ''), 'AMBULANCE') !== false;

    // Kendaraan tarif khusus 0,5% (Ambulance, Sosial, Sosial Keagamaan, Kebersihan, dll)
    $isKhusus05     = $isAmbulance || $isSosial;

    // 3. TERAPKAN DISKON DP PKB (PENGURANGAN NJKB)
    // Sesuai Pergub: Diskon standar NJKB 17.5% untuk periode berjalan tanpa tunggakan.
    // Jika Disabilitas: Pengurangan tambahan 50% (menjadi 67.5% jika 1 periode, atau 50% jika ada tunggakan).
    if ($amnestiAktif) {
        $diskonDpPkb = (float)($dbKonfigurasi['amnesti_diskon_dp_pkb'] ?? 0.175);
        if (count($periods) === 1) {
            $persenDiskonDp = $isDisabilitas ? min(0.90, $diskonDpPkb + 0.50) : $diskonDpPkb;
            if ($persenDiskonDp > 0) {
                $dpPkb = $dpPkb - ($dpPkb * $persenDiskonDp);
            }
        } else {
            if ($isDisabilitas) {
                $dpPkb = $dpPkb * 0.50;
            }
        }
    } elseif ($isDisabilitas) {
        $dpPkb = $dpPkb * 0.50;
    }

    // 4. PERSIAPAN VARIABEL SELANJUTNYA
    $pkbBerjalan = 0; $pkbTunggakan = 0;
    $pkbBerjalanKotor = 0; $pkbTunggakanKotor = 0;
    $opsenBerjalan = 0; $opsenTunggakan = 0;
    $opsenBerjalanKotor = 0; $opsenTunggakanKotor = 0;
    $diskonOpsenBerjalan = 0; $diskonOpsenTunggakan = 0;
    $dendaPkbBerjalan = 0; $dendaPkbTunggakan = 0;
    $dendaPkbBerjalanKotor = 0; $dendaPkbTunggakanKotor = 0;
    $dendaOpsenBerjalan = 0; $dendaOpsenTunggakan = 0;
    $dendaOpsenBerjalanKotor = 0; $dendaOpsenTunggakanKotor = 0;
    
    $amnestiDiskonPkbBerjalan = 0;
    $amnestiDiskonPkbTunggakan = 0;
    $diskonDendaPkbBerjalan = 0;
    $diskonDendaPkbTunggakan = 0;
    $diskonDendaOpsenBerjalan = 0;
    $diskonDendaOpsenTunggakan = 0;
    $amnestiDendaDibebaskan = 0;

    $indexBerjalan = count($periods) - 1;

    $bebasDendaPkbBerjalan    = (int)($dbKonfigurasi['amnesti_bebas_denda_pkb_berjalan'] ?? 1);
    $bebasDendaPkbTunggakan   = (int)($dbKonfigurasi['amnesti_bebas_denda_pkb_tunggakan'] ?? 1);
    $bebasDendaOpsenBerjalan  = (int)($dbKonfigurasi['amnesti_bebas_denda_opsen_berjalan'] ?? 1);
    $bebasDendaOpsenTunggakan = (int)($dbKonfigurasi['amnesti_bebas_denda_opsen_tunggakan'] ?? 1);
    
    $k_jr = strtoupper($kendaraan['k_jr'] ?? '');
    $isRoda23 = preg_match('/MOTOR|R2|R3/i', $kendaraan['jenis'] ?? '') || preg_match('/MOTOR/i', $k_jr);
    
    $uptAsal = strtoupper($kendaraan['upt_asal'] ?? '');
    $is75 = preg_match('/\b(SIKKA|ENDE|NAGEKEO|NGADA|MANGGARAI|MATIM|MANGGARAI TIMUR|MABAR|MANGGARAI BARAT)\b/', $uptAsal);
    $persenKhusus = (float)($dbKonfigurasi['amnesti_diskon_tunggakan_khusus'] ?? 1.00);
    $persenUmum   = (float)($dbKonfigurasi['amnesti_diskon_tunggakan_umum'] ?? 1.00);

    // 5. LOOPING KALKULASI PER TAHUN
    foreach ($periods as $index => $period) {
        $start = $period['start'];
        $end   = $period['end'];
        $isBerjalan = ($index === $indexBerjalan);
        $isNew      = menggunakanTarifBaru($start);
        
        $tarif = getTarifPkb($kGuna, $isNew, $isKhusus05);
        $pkbTahun = round($dpPkb * $tarif); // PKB Kotor Tahun Ini
        
        // Tentukan Diskon PKB Tahun Ini
        $persenDiskonTahun = 0;
        if ($amnestiAktif) {
            if ($isBerjalan) {
                if ($isMutasi) {
                    $persenDiskonTahun = 0.50; 
                } elseif ($isValidDate && $today <= $start) {
                    $daysBefore = clone $today;
                    $selisihHari = $daysBefore->diff($start)->days;
                    
                    // Diskon Taat Pajak (pembayaran sebelum jatuh tempo) berlaku berjenjang untuk semua kendaraan (R2/R3/R4)
                    if ($selisihHari >= 61 && $selisihHari <= 90) { 
                        $persenDiskonTahun = (float)($dbKonfigurasi['amnesti_diskon_pkb_awal_r2_90hr'] ?? 0.20); 
                    } elseif ($selisihHari >= 31 && $selisihHari <= 60) { 
                        $persenDiskonTahun = (float)($dbKonfigurasi['amnesti_diskon_pkb_awal_r2_60hr'] ?? 0.15); 
                    } elseif ($selisihHari >= 0 && $selisihHari <= 30) { 
                        $persenDiskonTahun = (float)($dbKonfigurasi['amnesti_diskon_pkb_awal_r2_30hr'] ?? 0.10); 
                    }
                }
            } else {
                // PENERAPAN DISKON TUNGGAKAN (Sesuai Pergub Tahap III: Bebas Tunggakan 100%)
                $persenDiskonTahun = $is75 ? $persenKhusus : $persenUmum;
            }
        }

        $diskonPkbTahun = round($pkbTahun * $persenDiskonTahun);
        $pkbNetTahun = $pkbTahun - $diskonPkbTahun;
        
        // Terapkan Insentif Angkutan Umum Berizin Trayek / Kartu Pengawasan (Karwas): Diskon 40% (bayar 60%)
        if ($isBerjalan && $isKarwas && $pkbNetTahun > 0) {
            $diskonKarwas = round($pkbNetTahun * 0.40);
            $diskonPkbTahun += $diskonKarwas;
            $pkbNetTahun -= $diskonKarwas;
        }
        
        // Hitung Opsen Kotor (tanpa diskon) & Opsen Net (setelah diskon)
        $opsenTahunKotor = $isNew ? round($pkbTahun * $rasioOpsen) : 0;
        $opsenTahunNet   = $isNew ? round($pkbNetTahun * $rasioOpsen) : 0;
        $diskonOpsenTahun = $opsenTahunKotor - $opsenTahunNet;

        // Hitung Denda (Kotor)
        $bulanLambat = 0;
        if ($today > $start) {
            $bulanLambat = hitungBulanTerlambatDariTanggal($start);
        }

        // Aturan Denda PKB: jika tarif baru 1% per bulan, jika tarif lama 2% per bulan
        $pctDendaPkbTahun = $isNew ? 0.01 : 0.02;
        $dendaPkbTahun = round($bulanLambat * $pctDendaPkbTahun * $pkbTahun);

        // Denda Opsen HANYA dikenakan pada perhitungan PKB tarif baru (1% per bulan dari Opsen PKB Kotor Normal)
        $dendaOpsenTahun = $isNew ? round($bulanLambat * 0.01 * $opsenTahunKotor) : 0;

        // Catat Kotor
        if ($isBerjalan) {
            $pkbBerjalanKotor += $pkbTahun;
            $opsenBerjalanKotor += $opsenTahunKotor;
            $diskonOpsenBerjalan += $diskonOpsenTahun;
            $dendaPkbBerjalanKotor += $dendaPkbTahun;
            $dendaOpsenBerjalanKotor += $dendaOpsenTahun;
        } else {
            $pkbTunggakanKotor += $pkbTahun;
            $opsenTunggakanKotor += $opsenTahunKotor;
            $diskonOpsenTunggakan += $diskonOpsenTahun;
            $dendaPkbTunggakanKotor += $dendaPkbTahun;
            $dendaOpsenTunggakanKotor += $dendaOpsenTahun;
        }

        // Terapkan Keringanan Amnesti Denda
        $netDendaPkbTahun = $dendaPkbTahun;
        $netDendaOpsenTahun = $dendaOpsenTahun;
        $dendaBebasTahun = 0;

        if ($amnestiAktif) {
            if ($isBerjalan) {
                if ($bebasDendaPkbBerjalan === 1) { 
                    $diskonDendaPkbBerjalan += $dendaPkbTahun;
                    $dendaBebasTahun += $dendaPkbTahun; 
                    $netDendaPkbTahun = 0; 
                }
                if ($bebasDendaOpsenBerjalan === 1) { 
                    $diskonDendaOpsenBerjalan += $dendaOpsenTahun;
                    $dendaBebasTahun += $dendaOpsenTahun; 
                    $netDendaOpsenTahun = 0; 
                }
                $amnestiDiskonPkbBerjalan += $diskonPkbTahun;
            } else {
                if ($bebasDendaPkbTunggakan === 1) { 
                    $diskonDendaPkbTunggakan += $dendaPkbTahun;
                    $dendaBebasTahun += $dendaPkbTahun; 
                    $netDendaPkbTahun = 0; 
                }
                if ($bebasDendaOpsenTunggakan === 1) { 
                    $diskonDendaOpsenTunggakan += $dendaOpsenTahun;
                    $dendaBebasTahun += $dendaOpsenTahun; 
                    $netDendaOpsenTahun = 0; 
                }
                $amnestiDiskonPkbTunggakan += $diskonPkbTahun;
            }
            $amnestiDendaDibebaskan += $dendaBebasTahun;
        }

        // Akumulasi Nilai Bersih ke Variabel Utama
        if ($isBerjalan) {
            $pkbBerjalan         += $pkbNetTahun;
            $opsenBerjalan       += $opsenTahunNet;
            $dendaPkbBerjalan    += $netDendaPkbTahun;
            $dendaOpsenBerjalan  += $netDendaOpsenTahun;
        } else {
            $pkbTunggakan        += $pkbNetTahun;
            $opsenTunggakan      += $opsenTahunNet;
            $dendaPkbTunggakan   += $netDendaPkbTahun;
            $dendaOpsenTunggakan += $netDendaOpsenTahun;
        }
    }

    $pkbTotal        = $pkbBerjalan + $pkbTunggakan;
    $opsenTotal      = $opsenBerjalan + $opsenTunggakan;
    $dendaPkbTotal   = $dendaPkbBerjalan + $dendaPkbTunggakan;
    $dendaOpsenTotal = $dendaOpsenBerjalan + $dendaOpsenTunggakan;

    return [
        'pkb_berjalan'                 => round($pkbBerjalan),
        'pkb_berjalan_kotor'           => round($pkbBerjalanKotor),
        'diskon_pkb_berjalan'          => round($amnestiDiskonPkbBerjalan),
        'pkb_tunggakan'                => round($pkbTunggakan),
        'pkb_tunggakan_kotor'          => round($pkbTunggakanKotor),
        'diskon_pkb_tunggakan'         => round($amnestiDiskonPkbTunggakan),
        'pkb_total'                    => round($pkbTotal),
        'opsen_pkb_berjalan'           => round($opsenBerjalan),
        'opsen_pkb_berjalan_kotor'     => round($opsenBerjalanKotor),
        'diskon_opsen_pkb_berjalan'    => round($diskonOpsenBerjalan),
        'opsen_pkb_tunggakan'          => round($opsenTunggakan),
        'opsen_pkb_tunggakan_kotor'    => round($opsenTunggakanKotor),
        'diskon_opsen_pkb_tunggakan'   => round($diskonOpsenTunggakan),
        'opsen_pkb'                    => round($opsenTotal),
        'diskon_opsen_total'           => round($diskonOpsenBerjalan + $diskonOpsenTunggakan),
        'denda_pkb_berjalan'           => round($dendaPkbBerjalan),
        'denda_pkb_berjalan_kotor'     => round($dendaPkbBerjalanKotor),
        'diskon_denda_pkb_berjalan'    => round($diskonDendaPkbBerjalan),
        'denda_pkb_tunggakan'          => round($dendaPkbTunggakan),
        'denda_pkb_tunggakan_kotor'    => round($dendaPkbTunggakanKotor),
        'diskon_denda_pkb_tunggakan'   => round($diskonDendaPkbTunggakan),
        'denda_pkb_total'              => round($dendaPkbTotal),
        'denda_opsen_berjalan'         => round($dendaOpsenBerjalan),
        'denda_opsen_berjalan_kotor'   => round($dendaOpsenBerjalanKotor),
        'diskon_denda_opsen_berjalan'  => round($diskonDendaOpsenBerjalan),
        'denda_opsen_tunggakan'        => round($dendaOpsenTunggakan),
        'denda_opsen_tunggakan_kotor'  => round($dendaOpsenTunggakanKotor),
        'diskon_denda_opsen_tunggakan' => round($diskonDendaOpsenTunggakan),
        'denda_opsen'                  => round($dendaOpsenTotal),
        'tahun_berjalan'               => 1,
        'tahun_tunggakan'              => max(count($periods) - 1, 0),
        'tahun_total'                  => count($periods),
        'peringatan'                   => !$isValidDate ? 'Data SD Notice tidak valid. Perhitungan hanya untuk 1 tahun dengan tarif terkini.' : '',
        'amnesti_aktif'                => $amnestiAktif,
        'amnesti_diskon_pkb_berjalan'  => $amnestiDiskonPkbBerjalan,
        'amnesti_diskon_pkb_tunggakan' => $amnestiDiskonPkbTunggakan,
        'amnesti_denda_dibebaskan'     => $amnestiDendaDibebaskan,
        'amnesti_total_potongan'       => $amnestiDiskonPkbBerjalan + $amnestiDiskonPkbTunggakan + $amnestiDendaDibebaskan + $diskonOpsenBerjalan + $diskonOpsenTunggakan
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

    // Fallback cerdas jika kapasitas mesin / isi cylinder tidak tercatat (0 / kosong)
    if ($isiCylinder <= 0) {
        $isiCylinder = $isMotor ? 125 : 1500;
    }

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
 * Menghitung SWDKLLJ dan Denda secara proporsional per periode.
 */
function hitungSwdkllj(
    string $jenisKend,
    string $kJr,
    int $isiCylinder,
    string $kGuna,
    string $sdNotice
): array {
    global $dbKonfigurasi, $dbTarifSwdkllj;

    $tarifData = getTarifSwdkllj(
        $jenisKend,
        $kJr,
        $isiCylinder,
        $kGuna
    );

    $tarifTahunan = $tarifData['tarif'];
    $admin        = $tarifData['admin'];
    $gol          = $tarifData['golongan'];

    // Ambil rentang denda dari database (Hanya sebagai referensi, tidak dipakai)
    $t = $dbTarifSwdkllj[$gol] ?? [];
    
    // ABAIKAN denda dari database. Selalu hitung murni menggunakan matematika baku Jasa Raharja.
    // Aturan Jasa Raharja: 25%, 50%, 75%, 100% dari tarif tahunan pokok.
    // Maksimal denda dibatasi Rp 100.000 untuk semua golongan (PMK No.16/2017).
    $maxDenda = 100000;
    $dendaT1 = (int)min(round($tarifTahunan * 0.25), $maxDenda); // 1 - 90 hari
    $dendaT2 = (int)min(round($tarifTahunan * 0.50), $maxDenda); // 91 - 180 hari
    $dendaT3 = (int)min(round($tarifTahunan * 0.75), $maxDenda); // 181 - 270 hari
    $dendaT4 = (int)min(round($tarifTahunan * 1.00), $maxDenda); // > 270 hari

    // Bentuk Periode (Sama seperti PKB)

   
    // Bentuk Periode (Sama seperti PKB)
    $today = new DateTime('today');
    $periodStart = buatTanggal($sdNotice);
    $periods = [];
    $isValidDate = true;

    if (!$periodStart) {
        $isValidDate = false;
        $periodEnd = clone $today;
        $periodEnd->modify('+1 year');
        $periods[] = ['start' => clone $today, 'end' => clone $periodEnd];
    } else {
        $current = clone $periodStart;
        $guard   = 0;
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
    }

    // === TAMBAHKAN LOGIKA INI ===
    // Batasi maksimal 5 tahun (ambil 5 data terbaru dari belakang)
    if (count($periods) > 5) {
        $periods = array_slice($periods, -5);
    }
    // ============================

    $jumlahTahun = count($periods);
    $tahunTotal = min(5, max(1, $jumlahTahun));
    $tahunTunggakan = min(4, max(0, $tahunTotal - 1));
    $tahunBerjalan = 1;

    $swdklljPerTahun = $tarifTahunan + $admin;
    $pokokBerjalan = $swdklljPerTahun;
    $pokokTunggakan = $tahunTunggakan * $swdklljPerTahun;
    $total = $pokokBerjalan + $pokokTunggakan;

    // Cek Konfigurasi Amnesti Denda SWDKLLJ
    $amnestiAktif = !empty($dbKonfigurasi['amnesti_aktif']) && (int)$dbKonfigurasi['amnesti_aktif'] === 1;
    if ($amnestiAktif) {
        $startAmnesti = buatTanggal($dbKonfigurasi['amnesti_mulai'] ?? '2026-09-01');
        $endAmnesti   = buatTanggal($dbKonfigurasi['amnesti_selesai'] ?? '2026-11-30');
        if (!$startAmnesti || !$endAmnesti || $today < $startAmnesti || $today > $endAmnesti) {
            $amnestiAktif = false;
        }
    }

    $bebasDendaTunggakan = (int)($dbKonfigurasi['amnesti_bebas_denda_swdkllj_tunggakan'] ?? 1);
    $bebasDendaBerjalan  = (int)($dbKonfigurasi['amnesti_bebas_denda_swdkllj_berjalan'] ?? 0);

    // 1. Denda Tahun Berjalan (1 tahun berjalan)
    $indexBerjalan = $jumlahTahun - 1;
    $periodBerjalan = $periods[$indexBerjalan] ?? null;
    $dendaBerjalan = 0;

    if ($periodBerjalan && $isValidDate && $today > $periodBerjalan['start']) {
        $selisihHari = (int)$periodBerjalan['start']->diff($today)->days;
        if ($selisihHari > 0) {
            if ($selisihHari <= 90) {
                $dendaBerjalan = $dendaT1;
            } elseif ($selisihHari <= 180) {
                $dendaBerjalan = $dendaT2;
            } elseif ($selisihHari <= 270) {
                $dendaBerjalan = $dendaT3;
            } else {
                $dendaBerjalan = $dendaT4;
            }
        }
    }

    // 2. Denda Masa Tunggakan (Maksimal 4 tahun tunggakan saja, masing-masing Denda T4)
    $dendaTunggakan = $tahunTunggakan * $dendaT4;

    $dendaBerjalanKotor = $dendaBerjalan;
    $dendaTunggakanKotor = $dendaTunggakan;
    $diskonDendaSwdklljBerjalan = 0;
    $diskonDendaSwdklljTunggakan = 0;

    // 3. Terapkan Amnesti jika aktif
    if ($amnestiAktif) {
        if ($bebasDendaTunggakan === 1) {
            $diskonDendaSwdklljTunggakan = $dendaTunggakan;
            $dendaTunggakan = 0;
        }
        if ($bebasDendaBerjalan === 1) {
            $diskonDendaSwdklljBerjalan = $dendaBerjalan;
            $dendaBerjalan = 0;
        }
    }

    $totalDenda = $dendaTunggakan + $dendaBerjalan;

    return [
        'golongan'               => $gol,
        'tarif'                  => $tarifTahunan,
        'admin'                  => $admin,
        'per_tahun'              => $swdklljPerTahun,
        'tahun_berjalan'         => $tahunBerjalan,
        'tahun_tunggakan'        => $tahunTunggakan,
        'jumlah_tahun'           => $tahunTotal,
        'pokok_berjalan'         => $pokokBerjalan,
        'pokok_tunggakan'        => $pokokTunggakan,
        'total'                  => $total,
        'denda_berjalan'         => $dendaBerjalan,
        'denda_berjalan_kotor'   => $dendaBerjalanKotor,
        'diskon_denda_berjalan'  => $diskonDendaSwdklljBerjalan,
        'denda_tunggakan'        => $dendaTunggakan,
        'denda_tunggakan_kotor'  => $dendaTunggakanKotor,
        'diskon_denda_tunggakan' => $diskonDendaSwdklljTunggakan,
        'denda'                  => $totalDenda,
        'total_dengan_denda'     => $total + $totalDenda,
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