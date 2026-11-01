<?php
// simulasi_helper.php
// Engine kalkulasi perbandingan penetapan pajak kendaraan bermotor NTT:
// 1. Penetapan Normal (Tanpa Amnesty)
// 2. Penetapan Pergub 54 (Sept - Okt 2026)
// 3. Penetapan Ranpergub (Nov - Des 2026)

require_once __DIR__ . '/tax_helper.php';

/**
 * Menghitung bulan keterlambatan dari tanggal jatuh tempo ke tanggal simulasi tertentu
 */
function hitungBulanTerlambatSimulasi(DateTime $jatuhTempo, DateTime $tanggalSimulasi): int {
    if ($tanggalSimulasi <= $jatuhTempo) {
        return 0;
    }
    $diff = $jatuhTempo->diff($tanggalSimulasi);
    $bulan = ($diff->y * 12) + $diff->m;
    if ($diff->d > 0) {
        $bulan += 1;
    }
    return min(24, max(0, $bulan));
}

/**
 * Helper pembentuk periode dari SD Notice sampai Tanggal Simulasi
 */
function buatPeriodeSimulasi(?DateTime $periodStart, DateTime $simDate): array {
    $periods = [];
    if (!$periodStart) {
        $end = clone $simDate;
        $end->modify('+1 year');
        $periods[] = ['start' => clone $simDate, 'end' => $end];
        return $periods;
    }

    $current = clone $periodStart;
    $guard = 0;
    if ($periodStart >= $simDate) {
        $periodEnd = clone $current;
        $periodEnd->modify('+1 year');
        $periods[] = ['start' => clone $current, 'end' => $periodEnd];
    } else {
        while ($current < $simDate && $guard < 25) {
            $periodEnd = clone $current;
            $periodEnd->modify('+1 year');
            $periods[] = ['start' => clone $current, 'end' => $periodEnd];
            $current = $periodEnd;
            $guard++;
        }
    }

    // Batasi maksimal 5 tahun sesuai regulasi
    if (count($periods) > 5) {
        $periods = array_slice($periods, -5);
    }

    return $periods;
}

/**
 * Kalkulasi SWDKLLJ & Denda untuk simulasi
 */
function hitungSwdklljSimulasi(
    string $jenisKend,
    string $kJr,
    int $isiCylinder,
    string $kGuna,
    ?DateTime $periodStart,
    DateTime $simDate,
    string $skenario // 'normal', 'pergub54', 'ranpergub'
): array {
    $tarifData = getTarifSwdkllj($jenisKend, $kJr, $isiCylinder, $kGuna);
    $tarifTahunan = $tarifData['tarif'];
    $admin = $tarifData['admin'];
    $swdklljPerTahun = $tarifTahunan + $admin;

    $periods = buatPeriodeSimulasi($periodStart, $simDate);
    $jumlahTahun = count($periods);

    // Batas maksimum tunggakan SWDKLLJ: 4 tahun tunggakan saja, dan 1 tahun berjalan (Total max 5 tahun)
    $tahunTotal = min(5, max(1, $jumlahTahun));
    $tahunTunggakan = min(4, max(0, $tahunTotal - 1));
    $tahunBerjalan = 1;

    $maxDenda = 100000;
    $dendaT1 = (int)min(round($tarifTahunan * 0.25), $maxDenda);
    $dendaT2 = (int)min(round($tarifTahunan * 0.50), $maxDenda);
    $dendaT3 = (int)min(round($tarifTahunan * 0.75), $maxDenda);
    $dendaT4 = (int)min(round($tarifTahunan * 1.00), $maxDenda);

    // 1. Hitung Denda Tahun Berjalan (1 tahun berjalan)
    $indexBerjalan = $jumlahTahun - 1;
    $periodBerjalan = $periods[$indexBerjalan] ?? null;
    $dendaBerjalan = 0;

    if ($periodBerjalan && $periodStart && $simDate > $periodBerjalan['start']) {
        $selisihHari = (int)$periodBerjalan['start']->diff($simDate)->days;
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

    // 2. Hitung Denda Tunggakan (Maksimal 4 tahun tunggakan saja, masing-masing kena Denda T4)
    $dendaTunggakan = $tahunTunggakan * $dendaT4;

    // 3. Pokok SWDKLLJ
    $pokokBerjalan = $swdklljPerTahun;
    $pokokTunggakan = $tahunTunggakan * $swdklljPerTahun;
    $totalPokok = $pokokBerjalan + $pokokTunggakan;

    // 4. Amnesti Denda (Pergub 54 & Ranpergub menghapus denda tunggakan 100%)
    if ($skenario === 'pergub54' || $skenario === 'ranpergub') {
        $dendaTunggakan = 0; // Denda masa tunggakan dibebaskan
        $totalDenda = $dendaBerjalan; // Denda berjalan tetap dibayar jika terlambat
    } else {
        // Skenario Normal: Denda tunggakan (max 4 tahun) + Denda berjalan (1 tahun)
        $totalDenda = $dendaTunggakan + $dendaBerjalan;
    }

    return [
        'golongan' => $tarifData['golongan'],
        'tarif_pokok' => $tarifTahunan,
        'admin' => $admin,
        'per_tahun' => $swdklljPerTahun,
        'tahun_berjalan' => $tahunBerjalan,
        'tahun_tunggakan' => $tahunTunggakan,
        'jumlah_tahun' => $tahunTotal,
        'pokok_berjalan' => $pokokBerjalan,
        'pokok_tunggakan' => $pokokTunggakan,
        'total_pokok' => $totalPokok,
        'denda_berjalan' => $dendaBerjalan,
        'denda_tunggakan' => $dendaTunggakan,
        'total_denda' => $totalDenda,
        'total' => $totalPokok + $totalDenda
    ];
}

/**
 * Fungsi Utama: Menghitung Komparasi 3 Skenario Penetapan
 */
function hitungKomparasiPenetapan(array $params): array {
    global $dbKonfigurasi;

    // 1. Ekstrak Parameter
    $njkb = (float)($params['njkb'] ?? 0);
    $bobot = (float)($params['bobot'] ?? 1);
    $njub = (float)($params['njub'] ?? 0);
    $kGuna = strtoupper(trim((string)($params['k_guna'] ?? 'PRIBADI')));
    $jenisKend = strtoupper(trim((string)($params['jenis_kendaraan'] ?? '')));
    $kJr = strtoupper(trim((string)($params['k_jr'] ?? '')));
    $isiCylinder = (int)($params['isi_cylinder'] ?? 0);
    $sdNoticeStr = trim((string)($params['sd_notice'] ?? ''));
    $sdStnkStr = trim((string)($params['sd_stnk'] ?? ''));
    $uptAsal = strtoupper(trim((string)($params['upt_asal'] ?? '')));
    $isMutasi = !empty($params['is_mutasi']);
    $kanalBayar = strtolower(trim((string)($params['kanal_bayar'] ?? 'loket'))); // 'loket' or 'online'
    $tanggalSimulasiStr = trim((string)($params['tanggal_simulasi'] ?? ''));

    // Tanggal Acuan Simulasi
    $simDate = buatTanggal($tanggalSimulasiStr) ?? new DateTime('today');
    $periodStart = buatTanggal($sdNoticeStr);

    // Deteksi Roda 2 / 3 vs Roda 4+
    $isRoda23 = false;
    if (isset($params['is_roda23'])) {
        $isRoda23 = (bool)$params['is_roda23'];
    } else {
        $isRoda23 = preg_match('/MOTOR|R2|R3|SEPEDA/i', $jenisKend) || preg_match('/MOTOR|RODA TIGA/i', $kJr);
    }

    // Hitung Dasar Pengenaan PKB Normal
    $dpNormal = round(($njkb + $njub) * $bobot);

    // Bentuk Periode
    $periods = buatPeriodeSimulasi($periodStart, $simDate);
    $jumlahTahun = count($periods);
    $indexBerjalan = $jumlahTahun - 1;
    $tahunTunggakan = max(0, $jumlahTahun - 1);
    $isMenunggak = ($tahunTunggakan > 0);

    // Status Pembayaran & Selisih Hari
    $statusPembayaran = 'Normal';
    $selisihHari = 0;
    if ($periodStart) {
        if ($periodStart >= $simDate) {
            $selisihHari = (int)$simDate->diff($periodStart)->days;
            $statusPembayaran = 'Taat Pajak (' . $selisihHari . ' hari sebelum JT)';
        } elseif ($isMenunggak) {
            $selisihHari = (int)$periodStart->diff($simDate)->days;
            $statusPembayaran = 'Menunggak (' . $tahunTunggakan . ' tahun tunggakan)';
        } else {
            $selisihHari = (int)$periodStart->diff($simDate)->days;
            $statusPembayaran = 'Terlambat Bayar (' . $selisihHari . ' hari)';
        }
    }
    if ($isMutasi) {
        $statusPembayaran .= ' [Mutasi Masuk Luar Daerah]';
    }

    // Biaya STNK & TNKB
    $stnkData = hitungBiayaStnk($sdStnkStr, $jenisKend, $kJr);
    $biayaStnk = (int)$stnkData['biaya_stnk'];
    $biayaTnkb = (int)$stnkData['biaya_tnkb'];
    $biayaStnkTotal = (int)$stnkData['total'];

    // SWDKLLJ
    $swdklljNormal = hitungSwdklljSimulasi($jenisKend, $kJr, $isiCylinder, $kGuna, $periodStart, $simDate, 'normal');
    $swdklljPergub54 = hitungSwdklljSimulasi($jenisKend, $kJr, $isiCylinder, $kGuna, $periodStart, $simDate, 'pergub54');
    $swdklljRanpergub = hitungSwdklljSimulasi($jenisKend, $kJr, $isiCylinder, $kGuna, $periodStart, $simDate, 'ranpergub');

    /* ========================================================================
     * 1. SKENARIO NORMAL (TANPA AMNESTY)
     * ======================================================================== */
    $normPkBerjalan = 0;
    $normPkTunggakan = 0;
    $normOpsenBerjalan = 0;
    $normOpsenTunggakan = 0;
    $normDendaPkb = 0;
    $normDendaOpsen = 0;

    foreach ($periods as $idx => $per) {
        $isBerjalan = ($idx === $indexBerjalan);
        $isNew = menggunakanTarifBaru($per['start']);
        $tarif = getTarifPkb($kGuna, $isNew);
        $pkbThn = round($dpNormal * $tarif);
        $opsenThn = $isNew ? round($pkbThn * 0.66) : 0;

        $bln = 0;
        if ($simDate > $per['start']) {
            $bln = hitungBulanTerlambatSimulasi($per['start'], $simDate);
        }

        // Pengaturan Denda PKB:
        // Tarif Lama (1.5%): denda 2% per bulan (0.02)
        // Tarif Baru: denda 1% per bulan (0.01)
        $pctDendaPkb = $isNew ? 0.01 : 0.02;
        $dendaPkbThn = round($bln * $pctDendaPkb * $pkbThn);

        // Denda Opsen HANYA dikenakan pada perhitungan PKB tarif baru (1% per bulan)
        $dendaOpsenThn = $isNew ? round($bln * 0.01 * $opsenThn) : 0;

        if ($isBerjalan) {
            $normPkBerjalan += $pkbThn;
            $normOpsenBerjalan += $opsenThn;
        } else {
            $normPkTunggakan += $pkbThn;
            $normOpsenTunggakan += $opsenThn;
        }
        $normDendaPkb += $dendaPkbThn;
        $normDendaOpsen += $dendaOpsenThn;
    }

    $normTotalPajak = $normPkBerjalan + $normPkTunggakan + $normOpsenBerjalan + $normOpsenTunggakan
                    + $normDendaPkb + $normDendaOpsen + $swdklljNormal['total'] + $biayaStnkTotal;

    $resNormal = [
        'skenario' => 'Penetapan Normal',
        'badge' => 'Tanpa Keringanan',
        'dp_pkb' => $dpNormal,
        'pengurangan_dp_pct' => 0,
        'pengurangan_dp_rp' => 0,
        'pkb_berjalan' => $normPkBerjalan,
        'diskon_pkb_berjalan_pct' => 0,
        'diskon_pkb_berjalan_rp' => 0,
        'pkb_tunggakan' => $normPkTunggakan,
        'diskon_pkb_tunggakan_pct' => 0,
        'diskon_pkb_tunggakan_rp' => 0,
        'pkb_total' => $normPkBerjalan + $normPkTunggakan,
        'opsen_pkb_berjalan' => $normOpsenBerjalan,
        'opsen_pkb_tunggakan' => $normOpsenTunggakan,
        'opsen_pkb_total' => $normOpsenBerjalan + $normOpsenTunggakan,
        'denda_pkb' => $normDendaPkb,
        'denda_opsen' => $normDendaOpsen,
        'denda_dibebaskan' => 0,
        'swdkllj_pokok' => $swdklljNormal['total_pokok'],
        'swdkllj_denda' => $swdklljNormal['total_denda'],
        'swdkllj_total' => $swdklljNormal['total'],
        'biaya_stnk_tnkb' => $biayaStnkTotal,
        'total_bayar' => $normTotalPajak,
        'total_hemat' => 0,
        'keterangan' => 'Perhitungan standar tanpa potongan dasar pengenaan, tanpa diskon pokok, dan denda keterlambatan berlaku penuh.'
    ];


    /* ========================================================================
     * 2. SKENARIO PERGUB 54 (SEPT - OKT 2026)
     * ======================================================================== */
    // Di Pergub 54:
    // Jika hanya 1 periode (tidak menunggak): DP dipotong 17.5%
    // Jika menunggak (> 1 periode): DP normal, tunggakan didiskon 50% atau 75%
    $p54DpPkb = (!$isMenunggak) ? round($dpNormal * (1 - 0.175)) : $dpNormal;
    $p54PenguranganDpPct = (!$isMenunggak) ? 17.5 : 0;
    $p54PenguranganDpRp = $dpNormal - $p54DpPkb;

    $p54PkBerjalanKotor = 0;
    $p54PkBerjalanNet = 0;
    $p54DiskonBerjalanRp = 0;
    $p54DiskonBerjalanPct = 0;

    $p54PkTunggakanKotor = 0;
    $p54PkTunggakanNet = 0;
    $p54DiskonTunggakanRp = 0;
    $p54DiskonTunggakanPct = 0;

    $p54OpsenBerjalan = 0;
    $p54OpsenTunggakan = 0;

    // Diskon Tunggakan Pergub 54
    $is75 = preg_match('/\b(SIKKA|ENDE|NAGEKEO|NGADA|MANGGARAI|MATIM|MANGGARAI TIMUR|MABAR|MANGGARAI BARAT)\b/', $uptAsal);
    $persenDiskonTunggakanP54 = $is75 ? 0.75 : 0.50;

    foreach ($periods as $idx => $per) {
        $isBerjalan = ($idx === $indexBerjalan);
        $isNew = menggunakanTarifBaru($per['start']);
        $tarif = getTarifPkb($kGuna, $isNew);

        if ($isBerjalan) {
            $p54PkBerjalanKotor = round($p54DpPkb * $tarif);
            $diskonPct = 0;
            if ($isMutasi) {
                $diskonPct = 0.50;
            } elseif ($periodStart && $simDate <= $per['start']) {
                $sh = (int)$simDate->diff($per['start'])->days;
                if ($isRoda23) {
                    if ($sh >= 61 && $sh <= 90) $diskonPct = 0.20;
                    elseif ($sh >= 31 && $sh <= 60) $diskonPct = 0.15;
                    elseif ($sh >= 0 && $sh <= 30) $diskonPct = 0.10;
                } else {
                    if ($sh >= 0 && $sh <= 90) $diskonPct = 0.10;
                }
            }
            $p54DiskonBerjalanPct = $diskonPct * 100;
            $p54DiskonBerjalanRp = round($p54PkBerjalanKotor * $diskonPct);
            $p54PkBerjalanNet = $p54PkBerjalanKotor - $p54DiskonBerjalanRp;
            $p54OpsenBerjalan = $isNew ? round($p54PkBerjalanNet * 0.66) : 0;
        } else {
            // Tunggakan
            $pkbThnKotor = round($dpNormal * $tarif);
            $diskonThnRp = round($pkbThnKotor * $persenDiskonTunggakanP54);
            $pkbThnNet = $pkbThnKotor - $diskonThnRp;

            $p54PkTunggakanKotor += $pkbThnKotor;
            $p54DiskonTunggakanRp += $diskonThnRp;
            $p54PkTunggakanNet += $pkbThnNet;
            $p54OpsenTunggakan += $isNew ? round($pkbThnNet * 0.66) : 0;
        }
    }
    if ($p54PkTunggakanKotor > 0) {
        $p54DiskonTunggakanPct = round(($p54DiskonTunggakanRp / $p54PkTunggakanKotor) * 100);
    }

    // Denda dibebaskan 100% di Pergub 54
    $p54DendaDibebaskan = $normDendaPkb + $normDendaOpsen + ($swdklljNormal['total_denda'] - $swdklljPergub54['total_denda']);
    $p54TotalBayar = $p54PkBerjalanNet + $p54PkTunggakanNet + $p54OpsenBerjalan + $p54OpsenTunggakan
                    + $swdklljPergub54['total'] + $biayaStnkTotal;
    $p54TotalHemat = $normTotalPajak - $p54TotalBayar;

    $resPergub54 = [
        'skenario' => 'Pergub 54 (Sept - Okt 2026)',
        'badge' => 'Amnesti Periode 1',
        'dp_pkb' => $p54DpPkb,
        'pengurangan_dp_pct' => $p54PenguranganDpPct,
        'pengurangan_dp_rp' => $p54PenguranganDpRp,
        'pkb_berjalan' => $p54PkBerjalanNet,
        'diskon_pkb_berjalan_pct' => $p54DiskonBerjalanPct,
        'diskon_pkb_berjalan_rp' => $p54DiskonBerjalanRp,
        'pkb_tunggakan' => $p54PkTunggakanNet,
        'diskon_pkb_tunggakan_pct' => $p54DiskonTunggakanPct,
        'diskon_pkb_tunggakan_rp' => $p54DiskonTunggakanRp,
        'pkb_total' => $p54PkBerjalanNet + $p54PkTunggakanNet,
        'opsen_pkb_berjalan' => $p54OpsenBerjalan,
        'opsen_pkb_tunggakan' => $p54OpsenTunggakan,
        'opsen_pkb_total' => $p54OpsenBerjalan + $p54OpsenTunggakan,
        'denda_pkb' => 0,
        'denda_opsen' => 0,
        'denda_dibebaskan' => $p54DendaDibebaskan,
        'swdkllj_pokok' => $swdklljPergub54['total_pokok'],
        'swdkllj_denda' => $swdklljPergub54['total_denda'],
        'swdkllj_total' => $swdklljPergub54['total'],
        'biaya_stnk_tnkb' => $biayaStnkTotal,
        'total_bayar' => $p54TotalBayar,
        'total_hemat' => max(0, $p54TotalHemat),
        'keterangan' => 'Diskon DP 17,5% untuk tidak menunggak; diskon tunggakan ' . ($persenDiskonTunggakanP54 * 100) . '%; denda PKB/Opsen dibebaskan 100%.'
    ];


    /* ========================================================================
     * 3. SKENARIO RANPERGUB (NOV - DES 2026)
     * Sesuai Ketentuan 10 Butir Ranpergub:
     * - Berlaku 1 Nov - 31 Des 2026
     * - Kanal: Loket vs Payment Online
     * ======================================================================== */
    $isOnline = ($kanalBayar === 'online');

    // Menentukan Status Kasus Ranpergub
    $isTaatPajak = (!$isMenunggak && $periodStart && $simDate <= $periodStart && $selisihHari <= 90);
    $isTerlambat = (!$isMenunggak && $periodStart && $simDate > $periodStart);
    // $isMenunggak = ($tahunTunggakan > 0)
    // $isMutasi = true/false

    $ranPenguranganDpPct = 0;
    $ranDiskonPokokBerjalanPct = 0;
    $ranKeteranganKasus = '';

    if ($isMutasi) {
        // Point 4: PKB Mutasi Masuk Luar Daerah
        // a. Pengurangan Dasar Pengenaan PKB 17,5%
        // b. Ditambah pengurangan pokok PKB tahun berjalan 50%
        $ranPenguranganDpPct = 17.5;
        $ranDiskonPokokBerjalanPct = 50.0;
        $ranKeteranganKasus = 'Mutasi Masuk: Pengurangan DP 17,5% + Diskon Pokok 50%';
    } elseif ($isTaatPajak) {
        // Point 3 & Point 5: PKB Taat Pajak
        // Dasar Pengenaan berkurang 17,5%
        $ranPenguranganDpPct = 17.5;

        if ($isRoda23) {
            if ($selisihHari >= 61 && $selisihHari <= 90) {
                // Loket: 20%, Online: 20% + 5% = 25%
                $ranDiskonPokokBerjalanPct = $isOnline ? (20.0 + 5.0) : 20.0;
                $ranKeteranganKasus = 'Taat Pajak R2/R3 (61-90 hari): Pengurangan DP 17,5% + Diskon Pokok ' . ($isOnline ? '20% + 5% (Online) = 25%' : '20% (Loket)');
            } elseif ($selisihHari >= 31 && $selisihHari <= 60) {
                // Loket: 15%, Online: 15% + 5% = 20%
                $ranDiskonPokokBerjalanPct = $isOnline ? (15.0 + 5.0) : 15.0;
                $ranKeteranganKasus = 'Taat Pajak R2/R3 (31-60 hari): Pengurangan DP 17,5% + Diskon Pokok ' . ($isOnline ? '15% + 5% (Online) = 20%' : '15% (Loket)');
            } else {
                // 0 - 30 hari: Loket: 10%, Online: 10% + 5% = 15%
                $ranDiskonPokokBerjalanPct = $isOnline ? (10.0 + 5.0) : 10.0;
                $ranKeteranganKasus = 'Taat Pajak R2/R3 (0-30 hari): Pengurangan DP 17,5% + Diskon Pokok ' . ($isOnline ? '10% + 5% (Online) = 15%' : '10% (Loket)');
            }
        } else {
            // Roda 4+: 0 - 90 hari: Loket: 10%, Online: 10% + 2% = 12%
            $ranDiskonPokokBerjalanPct = $isOnline ? (10.0 + 2.0) : 10.0;
            $ranKeteranganKasus = 'Taat Pajak R4+ (0-90 hari): Pengurangan DP 17,5% + Diskon Pokok ' . ($isOnline ? '10% + 2% (Online) = 12%' : '10% (Loket)');
        }
    } elseif ($isTerlambat) {
        // Point 6 & Point 7: Terlambat membayar pajak
        // a. Pengurangan Dasar Pengenaan PKB 17,5%
        $ranPenguranganDpPct = 17.5;
        if ($isOnline) {
            // Ditambah pengurangan pokok PKB Tahun Berjalan 5% (R2/R3) atau 2% (R4+)
            $ranDiskonPokokBerjalanPct = $isRoda23 ? 5.0 : 2.0;
            $ranKeteranganKasus = 'Terlambat Bayar (Online): Pengurangan DP 17,5% + Diskon Pokok ' . $ranDiskonPokokBerjalanPct . '%';
        } else {
            // Loket: Hanya pengurangan DP 17,5%
            $ranDiskonPokokBerjalanPct = 0.0;
            $ranKeteranganKasus = 'Terlambat Bayar (Loket): Pengurangan DP 17,5% (Pokok berjalan normal)';
        }
    } elseif ($isMenunggak) {
        // Point 8 & Point 9: PKB Menunggak Pajak
        // a. Dasar Pengenaan PKB TIDAK ADA pengurangan
        $ranPenguranganDpPct = 0.0;
        // b. Ditambah PKB masa Tunggakan DIHAPUS 100%!
        if ($isOnline) {
            // Ditambah pengurangan pokok PKB Tahun Berjalan 5% (R2/R3) atau 2% (R4+)
            $ranDiskonPokokBerjalanPct = $isRoda23 ? 5.0 : 2.0;
            $ranKeteranganKasus = 'Menunggak (Online): DP Normal, Tunggakan DIHAPUS 100%, Diskon Pokok Berjalan ' . $ranDiskonPokokBerjalanPct . '%';
        } else {
            // Loket: Dasar pengenaan tidak ada pengurangan, tunggakan dihapus 100%
            $ranDiskonPokokBerjalanPct = 0.0;
            $ranKeteranganKasus = 'Menunggak (Loket): DP Normal, Tunggakan DIHAPUS 100%, Pokok Berjalan Normal';
        }
    } else {
        // Default fallback (1 tahun berjalan normal tanpa tunggakan)
        $ranPenguranganDpPct = 17.5;
        $ranDiskonPokokBerjalanPct = $isOnline ? ($isRoda23 ? 5.0 : 2.0) : 0.0;
        $ranKeteranganKasus = 'Pengurangan DP 17,5%' . ($ranDiskonPokokBerjalanPct > 0 ? ' + Diskon Online ' . $ranDiskonPokokBerjalanPct . '%' : '');
    }

    // Hitung DP Efektif Ranpergub
    $ranDpPkb = round($dpNormal * (1 - ($ranPenguranganDpPct / 100)));
    $ranPenguranganDpRp = $dpNormal - $ranDpPkb;

    // Hitung Pokok Berjalan
    $tarifBerjalan = getTarifPkb($kGuna, menggunakanTarifBaru($periods[$indexBerjalan]['start']));
    $ranPkBerjalanKotor = round($ranDpPkb * $tarifBerjalan);
    $ranDiskonBerjalanRp = round($ranPkBerjalanKotor * ($ranDiskonPokokBerjalanPct / 100));
    $ranPkBerjalanNet = $ranPkBerjalanKotor - $ranDiskonBerjalanRp;
    $ranOpsenBerjalan = round($ranPkBerjalanNet * 0.66);

    // Hitung Pokok Tunggakan Ranpergub
    // Point 8b & 9b: "Ditambah PKB masa Tunggakan dihapus 100%"
    $ranPkTunggakanKotor = $normPkTunggakan;
    $ranPkTunggakanNet = 0; // DIHAPUS 100%!
    $ranDiskonTunggakanRp = $normPkTunggakan;
    $ranDiskonTunggakanPct = ($normPkTunggakan > 0) ? 100 : 0;
    $ranOpsenTunggakan = 0; // Tunggakan dihapus, opsen juga 0

    // Point 10: "Denda PKB dihapus 100% untuk seluruh jenis pendaftaran"
    $ranDendaPkb = 0;
    $ranDendaOpsen = 0;
    $ranDendaDibebaskan = $normDendaPkb + $normDendaOpsen + ($swdklljNormal['total_denda'] - $swdklljRanpergub['total_denda']);

    $ranTotalBayar = $ranPkBerjalanNet + $ranPkTunggakanNet + $ranOpsenBerjalan + $ranOpsenTunggakan
                   + $swdklljRanpergub['total'] + $biayaStnkTotal;
    $ranTotalHemat = $normTotalPajak - $ranTotalBayar;

    $resRanpergub = [
        'skenario' => 'Ranpergub (Nov - Des 2026)',
        'badge' => 'Amnesti Periode 2 (Terbaru)',
        'kanal_bayar' => $kanalBayar,
        'is_online' => $isOnline,
        'dp_pkb' => $ranDpPkb,
        'pengurangan_dp_pct' => $ranPenguranganDpPct,
        'pengurangan_dp_rp' => $ranPenguranganDpRp,
        'pkb_berjalan' => $ranPkBerjalanNet,
        'diskon_pkb_berjalan_pct' => $ranDiskonPokokBerjalanPct,
        'diskon_pkb_berjalan_rp' => $ranDiskonBerjalanRp,
        'pkb_tunggakan' => $ranPkTunggakanNet,
        'diskon_pkb_tunggakan_pct' => $ranDiskonTunggakanPct,
        'diskon_pkb_tunggakan_rp' => $ranDiskonTunggakanRp,
        'pkb_total' => $ranPkBerjalanNet + $ranPkTunggakanNet,
        'opsen_pkb_berjalan' => $ranOpsenBerjalan,
        'opsen_pkb_tunggakan' => $ranOpsenTunggakan,
        'opsen_pkb_total' => $ranOpsenBerjalan + $ranOpsenTunggakan,
        'denda_pkb' => 0,
        'denda_opsen' => 0,
        'denda_dibebaskan' => $ranDendaDibebaskan,
        'swdkllj_pokok' => $swdklljRanpergub['total_pokok'],
        'swdkllj_denda' => $swdklljRanpergub['total_denda'],
        'swdkllj_total' => $swdklljRanpergub['total'],
        'biaya_stnk_tnkb' => $biayaStnkTotal,
        'total_bayar' => $ranTotalBayar,
        'total_hemat' => max(0, $ranTotalHemat),
        'keterangan' => $ranKeteranganKasus . '; Denda PKB/Opsen Dihapus 100%.'
    ];

    /* ========================================================================
     * 4. SIMULASI BBNKB (POINT 2 RANPERGUB)
     * ======================================================================== */
    // Normal: DP BBNKB = DP Normal. Pokok = DP * 10% (BBNKB I) atau tarif BBNKB
    // Ranpergub: Pengurangan Dasar Pengenaan: R2/R3 sebesar 15%, R4+ sebesar 20%
    $tarifBbnkb = 0.10; // Tarif BBNKB 1 (10%)
    $dpBbnkbNormal = $dpNormal;
    $pokokBbnkbNormal = round($dpBbnkbNormal * $tarifBbnkb);
    $opsenBbnkbNormal = round($pokokBbnkbNormal * 0.66);
    $totalBbnkbNormal = $pokokBbnkbNormal + $opsenBbnkbNormal;

    $diskonDpBbnkbPct = $isRoda23 ? 15.0 : 20.0;
    $dpBbnkbRanpergub = round($dpNormal * (1 - ($diskonDpBbnkbPct / 100)));
    $pokokBbnkbRanpergub = round($dpBbnkbRanpergub * $tarifBbnkb);
    $opsenBbnkbRanpergub = round($pokokBbnkbRanpergub * 0.66);
    $totalBbnkbRanpergub = $pokokBbnkbRanpergub + $opsenBbnkbRanpergub;
    $hematBbnkb = $totalBbnkbNormal - $totalBbnkbRanpergub;

    $resBbnkb = [
        'is_roda23' => $isRoda23,
        'tarif_bbnkb' => $tarifBbnkb * 100 . '%',
        'normal' => [
            'dp_bbnkb' => $dpBbnkbNormal,
            'pokok' => $pokokBbnkbNormal,
            'opsen' => $opsenBbnkbNormal,
            'total' => $totalBbnkbNormal
        ],
        'pergub54' => [
            'dp_bbnkb' => $dpBbnkbNormal, // Di Pergub 54 tidak ada insentif pengurangan DP BBNKB
            'pokok' => $pokokBbnkbNormal,
            'opsen' => $opsenBbnkbNormal,
            'total' => $totalBbnkbNormal
        ],
        'ranpergub' => [
            'pengurangan_dp_pct' => $diskonDpBbnkbPct,
            'dp_bbnkb' => $dpBbnkbRanpergub,
            'pokok' => $pokokBbnkbRanpergub,
            'opsen' => $opsenBbnkbRanpergub,
            'total' => $totalBbnkbRanpergub,
            'total_hemat' => $hematBbnkb
        ]
    ];

    return [
        'metadata' => [
            'tanggal_simulasi' => $simDate->format('Y-m-d'),
            'tanggal_simulasi_formatted' => $simDate->format('d M Y'),
            'sd_notice' => $sdNoticeStr,
            'sd_stnk' => $sdStnkStr,
            'status_pembayaran' => $statusPembayaran,
            'selisih_hari' => $selisihHari,
            'jumlah_tahun' => $jumlahTahun,
            'tahun_tunggakan' => $tahunTunggakan,
            'is_roda23' => $isRoda23,
            'kategori_roda' => $isRoda23 ? 'Roda 2 & 3' : 'Roda 4 dan seterusnya',
            'penggunaan' => $kGuna,
            'is_mutasi' => $isMutasi,
            'kanal_bayar' => $kanalBayar,
            'dp_normal' => $dpNormal
        ],
        'komparasi' => [
            'normal' => $resNormal,
            'pergub54' => $resPergub54,
            'ranpergub' => $resRanpergub
        ],
        'bbnkb' => $resBbnkb,
        'selisih_ranpergub_vs_pergub54' => $p54TotalBayar - $ranTotalBayar
    ];
}
