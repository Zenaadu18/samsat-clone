<?php

namespace App\Services;

use DateTime;
use Exception;

class SimulationService
{
    protected TaxCalculationService $taxService;

    public function __construct(TaxCalculationService $taxService)
    {
        $this->taxService = $taxService;
    }

    /**
     * Hitung keterlambatan bulan dari jatuh tempo ke tanggal tertentu.
     */
    protected function hitungBulanTerlambat(DateTime $jatuhTempo, DateTime $tanggalSimulasi): int
    {
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
     * Bentuk periode simulasi dari SD Notice sampai Tanggal Simulasi.
     */
    protected function buatPeriodeSimulasi(?DateTime $periodStart, DateTime $simDate): array
    {
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

            if ($current >= $simDate && $guard < 25) {
                $daysToJt = (int)$simDate->diff($current)->days;
                if ($daysToJt <= 90) {
                    $periodEnd = clone $current;
                    $periodEnd->modify('+1 year');
                    $periods[] = ['start' => clone $current, 'end' => $periodEnd];
                }
            }
        }

        if (count($periods) > 5) {
            $periods = array_slice($periods, -5);
        }

        return $periods;
    }

    /**
     * Hitung SWDKLLJ untuk skenario simulasi ('normal' atau 'ranpergub').
     */
    protected function hitungSwdklljSimulasi(
        string $jenisKend,
        string $kJr,
        int $isiCylinder,
        string $kGuna,
        ?DateTime $periodStart,
        DateTime $simDate,
        string $skenario
    ): array {
        $tarifData = \getTarifSwdkllj($jenisKend, $kJr, $isiCylinder, $kGuna);
        $tarifTahunan = (int)$tarifData['tarif'];
        $admin = (int)$tarifData['admin'];
        $swdklljPerTahun = $tarifTahunan + $admin;

        $periods = $this->buatPeriodeSimulasi($periodStart, $simDate);
        $jumlahTahun = count($periods);

        $tahunTotal = min(5, max(1, $jumlahTahun));
        $tahunTunggakan = min(4, max(0, $tahunTotal - 1));
        $tahunBerjalan = 1;

        $maxDenda = 100000;
        $dendaT1 = (int)min(round($tarifTahunan * 0.25), $maxDenda);
        $dendaT2 = (int)min(round($tarifTahunan * 0.50), $maxDenda);
        $dendaT3 = (int)min(round($tarifTahunan * 0.75), $maxDenda);
        $dendaT4 = (int)min(round($tarifTahunan * 1.00), $maxDenda);

        $indexBerjalan = $jumlahTahun - 1;
        $periodBerjalan = $periods[$indexBerjalan] ?? null;
        $dendaBerjalan = 0;

        if ($periodBerjalan && $periodStart && $simDate > $periodBerjalan['start']) {
            $selisihHari = (int)$periodBerjalan['start']->diff($simDate)->days;
            if ($selisihHari > 0) {
                if ($selisihHari <= 90) $dendaBerjalan = $dendaT1;
                elseif ($selisihHari <= 180) $dendaBerjalan = $dendaT2;
                elseif ($selisihHari <= 270) $dendaBerjalan = $dendaT3;
                else $dendaBerjalan = $dendaT4;
            }
        }

        $dendaTunggakan = $tahunTunggakan * $dendaT4;
        $pokokBerjalan = $swdklljPerTahun;
        $pokokTunggakan = $tahunTunggakan * $swdklljPerTahun;
        $totalPokok = $pokokBerjalan + $pokokTunggakan;

        if ($skenario === 'ranpergub') {
            $dendaTunggakan = 0; // Denda masa tunggakan dibebaskan
            $totalDenda = $dendaBerjalan; // Denda berjalan tetap dibayar jika terlambat
        } else {
            $totalDenda = $dendaTunggakan + $dendaBerjalan;
        }

        return [
            'golongan'        => $tarifData['golongan'],
            'tarif_pokok'     => $tarifTahunan,
            'admin'           => $admin,
            'per_tahun'       => $swdklljPerTahun,
            'tahun_berjalan'  => $tahunBerjalan,
            'tahun_tunggakan' => $tahunTunggakan,
            'jumlah_tahun'    => $tahunTotal,
            'pokok_berjalan'  => $pokokBerjalan,
            'pokok_tunggakan' => $pokokTunggakan,
            'total_pokok'     => $totalPokok,
            'denda_berjalan'  => $dendaBerjalan,
            'denda_tunggakan' => $dendaTunggakan,
            'total_denda'     => $totalDenda,
            'total'           => $totalPokok + $totalDenda,
        ];
    }

    /**
     * Hitung komparasi simulasi: Normal vs Pergub 64 (Ranpergub).
     */
    public function simulate(array $params): array
    {
        $njkb            = (float)($params['njkb'] ?? 0);
        $bobot           = (float)($params['bobot'] ?? 1.0);
        $njub            = (float)($params['njub'] ?? 0);
        $kGuna           = strtoupper(trim((string)($params['k_guna'] ?? 'PRIBADI')));
        $jenisKend       = strtoupper(trim((string)($params['jenis_kendaraan'] ?? 'MINIBUS')));
        $kJr             = strtoupper(trim((string)($params['k_jr'] ?? '')));
        $isiCylinder     = (int)($params['isi_cylinder'] ?? 1500);
        $sdNoticeStr     = trim((string)($params['sd_notice'] ?? ''));
        $sdStnkStr       = trim((string)($params['sd_stnk'] ?? ''));
        $isMutasi        = (bool)($params['is_mutasi'] ?? false);
        $kanalBayar      = strtolower(trim((string)($params['kanal_bayar'] ?? 'loket')));
        $tglSimulasiStr  = trim((string)($params['tanggal_simulasi'] ?? date('Y-m-d')));

        $simDate = !empty($tglSimulasiStr) ? new DateTime($tglSimulasiStr) : new DateTime();
        $noticeDate = !empty($sdNoticeStr) ? new DateTime($sdNoticeStr) : null;
        $stnkDate = !empty($sdStnkStr) ? new DateTime($sdStnkStr) : null;

        // Deteksi Roda 2 / 3 vs Roda 4+
        $isRoda23 = false;
        if (isset($params['is_roda23'])) {
            $isRoda23 = (bool)$params['is_roda23'];
        } else {
            $isRoda23 = preg_match('/MOTOR|R2|R3|SEPEDA/i', $jenisKend) || preg_match('/MOTOR|RODA TIGA/i', $kJr);
        }

        $dpNormal = round(($njkb + $njub) * $bobot);

        // Bentuk Periode
        $periods = $this->buatPeriodeSimulasi($noticeDate, $simDate);
        $jumlahTahun = count($periods);
        $indexBerjalan = $jumlahTahun - 1;
        $tahunTunggakan = max(0, $jumlahTahun - 1);
        $isMenunggak = ($tahunTunggakan > 0);

        // Status Pembayaran & Selisih Hari
        $statusPembayaran = 'Normal';
        $selisihHari = 0;
        if ($noticeDate) {
            if ($noticeDate >= $simDate) {
                $selisihHari = (int)$simDate->diff($noticeDate)->days;
                $statusPembayaran = 'Taat Pajak (' . $selisihHari . ' hari sebelum JT)';
            } elseif ($isMenunggak) {
                $selisihHari = (int)$noticeDate->diff($simDate)->days;
                $statusPembayaran = 'Menunggak (' . $tahunTunggakan . ' tahun tunggakan)';
            } else {
                $selisihHari = (int)$noticeDate->diff($simDate)->days;
                $statusPembayaran = 'Terlambat Bayar (' . $selisihHari . ' hari)';
            }
        }
        if ($isMutasi) {
            $statusPembayaran .= ' [Mutasi Masuk Luar Daerah]';
        }

        // Biaya STNK & TNKB
        $stnkData = \hitungBiayaStnk($sdStnkStr, $jenisKend, $kJr);
        $biayaStnk = (int)$stnkData['biaya_stnk'];
        $biayaTnkb = (int)$stnkData['biaya_tnkb'];
        $biayaStnkTotal = (int)$stnkData['total'];

        // SWDKLLJ
        $swdklljNormal = $this->hitungSwdklljSimulasi($jenisKend, $kJr, $isiCylinder, $kGuna, $noticeDate, $simDate, 'normal');
        $swdklljRanpergub = $this->hitungSwdklljSimulasi($jenisKend, $kJr, $isiCylinder, $kGuna, $noticeDate, $simDate, 'ranpergub');

        /* ====================================================================
         * 1. PENETAPAN NORMAL
         * ==================================================================== */
        $normRincianTahun = [];
        $normPkBerjalan = 0;
        $normPkTunggakan = 0;
        $normDendaPkb = 0;
        $normDendaOpsen = 0;
        $normOpsenBerjalan = 0;
        $normOpsenTunggakan = 0;

        foreach ($periods as $idx => $per) {
            $isBerjalan = ($idx === $indexBerjalan);
            $isNew = \menggunakanTarifBaru($per['start']);
            $tarif = \getTarifPkb($kGuna, $isNew);
            $pkbThn = round($dpNormal * $tarif);
            $opsenThn = $isNew ? (int)floor($pkbThn * 0.66) : 0;

            $bln = 0;
            if ($simDate > $per['start']) {
                if ($isBerjalan) {
                    $bln = $this->hitungBulanTerlambat($per['start'], $simDate);
                } else {
                    $dendaAkhir = ($per['end'] <= $simDate) ? clone $per['end'] : clone $simDate;
                    $bln = $this->hitungBulanTerlambat($per['start'], $dendaAkhir);
                }
            }

            $pctDendaPkb = $isNew ? 0.01 : 0.02;
            $dendaPkbThn = round($bln * $pctDendaPkb * $pkbThn);
            $dendaOpsenThn = $isNew ? (int)floor($bln * 0.01 * $opsenThn) : 0;

            if ($isBerjalan) {
                $normPkBerjalan += $pkbThn;
                $normOpsenBerjalan += $opsenThn;
            } else {
                $normPkTunggakan += $pkbThn;
                $normOpsenTunggakan += $opsenThn;
            }
            $normDendaPkb += $dendaPkbThn;
            $normDendaOpsen += $dendaOpsenThn;

            $normRincianTahun[] = [
                'tahun_ke'        => $idx + 1,
                'jatuh_tempo'     => $per['start']->format('Y-m-d'),
                'is_berjalan'     => $isBerjalan,
                'tarif_pkb'       => ($tarif * 100) . '%',
                'pkb_pokok'       => $pkbThn,
                'opsen_pokok'     => $opsenThn,
                'bulan_terlambat' => $bln,
                'denda_pkb'       => $dendaPkbThn,
                'denda_opsen'     => $dendaOpsenThn,
                'subtotal'        => $pkbThn + $opsenThn + $dendaPkbThn + $dendaOpsenThn,
            ];
        }

        $normTotalPajak = $normPkBerjalan + $normPkTunggakan + $normOpsenBerjalan + $normOpsenTunggakan
                        + $normDendaPkb + $normDendaOpsen + $swdklljNormal['total'] + $biayaStnkTotal;

        $resNormal = [
            'skenario'                 => 'Penetapan Normal',
            'badge'                    => 'Tanpa Keringanan',
            'dp_pkb'                   => $dpNormal,
            'pengurangan_dp_pct'       => 0,
            'pengurangan_dp_rp'        => 0,
            'pkb_berjalan'             => $normPkBerjalan,
            'diskon_pkb_berjalan_pct'  => 0,
            'diskon_pkb_berjalan_rp'   => 0,
            'pkb_tunggakan'            => $normPkTunggakan,
            'diskon_pkb_tunggakan_pct' => 0,
            'diskon_pkb_tunggakan_rp'  => 0,
            'pkb_total'                => $normPkBerjalan + $normPkTunggakan,
            'opsen_pkb_berjalan'       => $normOpsenBerjalan,
            'opsen_pkb_tunggakan'      => $normOpsenTunggakan,
            'opsen_pkb_total'          => $normOpsenBerjalan + $normOpsenTunggakan,
            'denda_pkb'                => $normDendaPkb,
            'denda_opsen'              => $normDendaOpsen,
            'denda_total'              => $normDendaPkb + $normDendaOpsen,
            'denda_dibebaskan'         => 0,
            'swdkllj_pokok'            => $swdklljNormal['total_pokok'],
            'swdkllj_denda'            => $swdklljNormal['total_denda'],
            'swdkllj_total'            => $swdklljNormal['total'],
            'biaya_stnk_tnkb'          => $biayaStnkTotal,
            'total_bayar'              => $normTotalPajak,
            'rincian_periode'          => $normRincianTahun,
            'keterangan'               => 'Perhitungan standar tanpa potongan dasar pengenaan, tanpa diskon pokok, dan denda keterlambatan berlaku penuh.'
        ];

        /* ====================================================================
         * 2. PENETAPAN PERGUB 64 (RANPERGUB NOV - DES 2026)
         * ==================================================================== */
        $isOnline = in_array($kanalBayar, ['online', 'signal', 'mobile_banking', 'atm', 'bank_ntt', 'qris']) || empty($kanalBayar);

        $isTaatPajak = (!$isMenunggak && $noticeDate && $simDate <= $noticeDate && $selisihHari <= 90);
        $isTerlambat = (!$isMenunggak && $noticeDate && $simDate > $noticeDate);

        $ranPenguranganDpPct = 0.0;
        $ranDiskonPokokBerjalanPct = 0.0;
        $ranKeteranganKasus = '';

        if ($isMutasi) {
            $ranPenguranganDpPct = 17.5;
            $ranDiskonPokokBerjalanPct = 50.0;
            $ranKeteranganKasus = 'Mutasi Masuk: Pengurangan DP 17,5% + Diskon Pokok 50%';
        } elseif ($isTaatPajak) {
            $ranPenguranganDpPct = 17.5;
            if ($isRoda23) {
                if ($selisihHari >= 61 && $selisihHari <= 90) {
                    $ranDiskonPokokBerjalanPct = $isOnline ? 25.0 : 20.0;
                    $ranKeteranganKasus = 'Taat Pajak R2/R3 (61-90 hari): Pengurangan DP 17,5% + Diskon Pokok ' . ($isOnline ? '20% + 5% (Online) = 25%' : '20% (Loket)');
                } elseif ($selisihHari >= 31 && $selisihHari <= 60) {
                    $ranDiskonPokokBerjalanPct = $isOnline ? 20.0 : 15.0;
                    $ranKeteranganKasus = 'Taat Pajak R2/R3 (31-60 hari): Pengurangan DP 17,5% + Diskon Pokok ' . ($isOnline ? '15% + 5% (Online) = 20%' : '15% (Loket)');
                } else {
                    $ranDiskonPokokBerjalanPct = $isOnline ? 15.0 : 10.0;
                    $ranKeteranganKasus = 'Taat Pajak R2/R3 (0-30 hari): Pengurangan DP 17,5% + Diskon Pokok ' . ($isOnline ? '10% + 5% (Online) = 15%' : '10% (Loket)');
                }
            } else {
                $ranDiskonPokokBerjalanPct = $isOnline ? 12.0 : 10.0;
                $ranKeteranganKasus = 'Taat Pajak R4+ (0-90 hari): Pengurangan DP 17,5% + Diskon Pokok ' . ($isOnline ? '10% + 2% (Online) = 12%' : '10% (Loket)');
            }
        } elseif ($isTerlambat) {
            $ranPenguranganDpPct = 17.5;
            if ($isOnline) {
                $ranDiskonPokokBerjalanPct = $isRoda23 ? 5.0 : 2.0;
                $ranKeteranganKasus = 'Terlambat Bayar (Online): Pengurangan DP 17,5% + Diskon Pokok ' . $ranDiskonPokokBerjalanPct . '%';
            } else {
                $ranDiskonPokokBerjalanPct = 0.0;
                $ranKeteranganKasus = 'Terlambat Bayar (Loket): Pengurangan DP 17,5% (Pokok berjalan normal)';
            }
        } elseif ($isMenunggak) {
            // Point 8 & Point 9: PKB Menunggak Pajak
            // a. Dasar Pengenaan PKB TIDAK ADA pengurangan
            $ranPenguranganDpPct = 0.0;
            // b. Ditambah PKB masa Tunggakan DIHAPUS 100%!
            if ($isOnline) {
                $ranDiskonPokokBerjalanPct = $isRoda23 ? 5.0 : 2.0;
                $ranKeteranganKasus = 'Menunggak (Online): DP Normal, Tunggakan DIHAPUS 100%, Diskon Pokok Berjalan ' . $ranDiskonPokokBerjalanPct . '%';
            } else {
                $ranDiskonPokokBerjalanPct = 0.0;
                $ranKeteranganKasus = 'Menunggak (Loket): DP Normal, Tunggakan DIHAPUS 100%, Pokok Berjalan Normal';
            }
        } else {
            $ranPenguranganDpPct = 17.5;
            $ranDiskonPokokBerjalanPct = $isOnline ? ($isRoda23 ? 5.0 : 2.0) : 0.0;
            $ranKeteranganKasus = 'Pengurangan DP 17,5%' . ($ranDiskonPokokBerjalanPct > 0 ? ' + Diskon Online ' . $ranDiskonPokokBerjalanPct . '%' : '');
        }

        // Hitung DP Efektif Ranpergub
        $ranDpPkb = round($dpNormal * (1 - ($ranPenguranganDpPct / 100)));
        $ranPenguranganDpRp = $dpNormal - $ranDpPkb;

        // Hitung Pokok Berjalan
        $periodBerjalan = end($periods);
        $jtBerjalan = $periodBerjalan ? $periodBerjalan['start'] : $simDate;
        $isBerjalanTarifBaru = \menggunakanTarifBaru($jtBerjalan);
        $tarifBerjalan = \getTarifPkb($kGuna, $isBerjalanTarifBaru);

        $ranPkBerjalanKotor = round($ranDpPkb * $tarifBerjalan);
        $ranDiskonBerjalanRp = (int)floor($ranPkBerjalanKotor * ($ranDiskonPokokBerjalanPct / 100));
        $ranPkBerjalanNet = $ranPkBerjalanKotor - $ranDiskonBerjalanRp;
        $ranOpsenKotor = $isBerjalanTarifBaru ? (int)floor($ranPkBerjalanKotor * 0.66) : 0;
        $ranDiskonOpsenRp = (int)round($ranOpsenKotor * ($ranDiskonPokokBerjalanPct / 100));
        $ranOpsenBerjalan = $ranOpsenKotor - $ranDiskonOpsenRp;

        // Point 8b & 9b: PKB masa Tunggakan dihapus 100%
        $ranPkTunggakanNet = 0;
        $ranDiskonTunggakanRp = $normPkTunggakan;
        $ranDiskonTunggakanPct = ($normPkTunggakan > 0) ? 100 : 0;
        $ranOpsenTunggakan = 0;

        // Point 10: Denda PKB dihapus 100%
        $ranDendaDibebaskan = $normDendaPkb + $normDendaOpsen + ($swdklljNormal['total_denda'] - $swdklljRanpergub['total_denda']);

        $ranTotalBayar = $ranPkBerjalanNet + $ranPkTunggakanNet + $ranOpsenBerjalan + $ranOpsenTunggakan
                       + $swdklljRanpergub['total'] + $biayaStnkTotal;
        $ranTotalHemat = max(0, $normTotalPajak - $ranTotalBayar);

        $resRanpergub = [
            'skenario'                 => 'Pergub No. 64 / Ranpergub',
            'badge'                    => 'Amnesti Pajak NTT (Diskon Maksimal)',
            'kanal_bayar'              => $kanalBayar,
            'is_online'                => $isOnline,
            'dp_pkb'                   => $ranDpPkb,
            'pengurangan_dp_pct'       => $ranPenguranganDpPct,
            'pengurangan_dp_rp'        => $ranPenguranganDpRp,
            'pkb_berjalan'             => $ranPkBerjalanNet,
            'diskon_pkb_berjalan_pct'  => $ranDiskonPokokBerjalanPct,
            'diskon_pkb_berjalan_rp'   => $ranDiskonBerjalanRp,
            'pkb_tunggakan'            => $ranPkTunggakanNet,
            'diskon_pkb_tunggakan_pct' => $ranDiskonTunggakanPct,
            'diskon_pkb_tunggakan_rp'  => $ranDiskonTunggakanRp,
            'pkb_total'                => $ranPkBerjalanNet + $ranPkTunggakanNet,
            'opsen_pkb_berjalan'       => $ranOpsenBerjalan,
            'opsen_pkb_tunggakan'      => $ranOpsenTunggakan,
            'opsen_pkb_total'          => $ranOpsenBerjalan + $ranOpsenTunggakan,
            'denda_pkb'                => 0,
            'denda_opsen'              => 0,
            'denda_total'              => 0,
            'denda_dibebaskan'         => $ranDendaDibebaskan,
            'swdkllj_pokok'            => $swdklljRanpergub['total_pokok'],
            'swdkllj_denda'            => $swdklljRanpergub['total_denda'],
            'swdkllj_total'            => $swdklljRanpergub['total'],
            'biaya_stnk_tnkb'          => $biayaStnkTotal,
            'total_bayar'              => $ranTotalBayar,
            'total_hemat'              => $ranTotalHemat,
            'keterangan'               => $ranKeteranganKasus . '; Denda PKB/Opsen Dihapus 100%.'
        ];

        /* ====================================================================
         * 3. SIMULASI BBNKB
         * ==================================================================== */
        $tarifBbnkb = 0.10;
        $dpBbnkbNormal = $dpNormal;
        $pokokBbnkbNormal = round($dpBbnkbNormal * $tarifBbnkb);
        $opsenBbnkbNormal = (int)floor($pokokBbnkbNormal * 0.66);
        $totalBbnkbNormal = $pokokBbnkbNormal + $opsenBbnkbNormal;

        $diskonDpBbnkbPct = $isRoda23 ? 15.0 : 20.0;
        $dpBbnkbRanpergub = round($dpNormal * (1 - ($diskonDpBbnkbPct / 100)));
        $pokokBbnkbRanpergub = round($dpBbnkbRanpergub * $tarifBbnkb);
        $opsenBbnkbRanpergub = (int)floor($pokokBbnkbRanpergub * 0.66);
        $totalBbnkbRanpergub = $pokokBbnkbRanpergub + $opsenBbnkbRanpergub;
        $hematBbnkb = max(0, $totalBbnkbNormal - $totalBbnkbRanpergub);

        $resBbnkb = [
            'is_roda23'   => $isRoda23,
            'tarif_bbnkb' => ($tarifBbnkb * 100) . '%',
            'normal'      => [
                'dp_bbnkb' => $dpBbnkbNormal,
                'pokok'    => $pokokBbnkbNormal,
                'opsen'    => $opsenBbnkbNormal,
                'total'    => $totalBbnkbNormal,
            ],
            'ranpergub'   => [
                'pengurangan_dp_pct' => $diskonDpBbnkbPct,
                'dp_bbnkb'           => $dpBbnkbRanpergub,
                'pokok'              => $pokokBbnkbRanpergub,
                'opsen'              => $opsenBbnkbRanpergub,
                'total'              => $totalBbnkbRanpergub,
                'total_hemat'        => $hematBbnkb,
            ],
        ];

        return [
            'metadata' => [
                'tanggal_simulasi'           => $simDate->format('Y-m-d'),
                'tanggal_simulasi_formatted' => $simDate->format('d M Y'),
                'sd_notice'                  => $sdNoticeStr,
                'sd_stnk'                    => $sdStnkStr,
                'status_pembayaran'          => $statusPembayaran,
                'selisih_hari'               => $selisihHari,
                'jumlah_tahun'               => $jumlahTahun,
                'tahun_tunggakan'            => $tahunTunggakan,
                'is_roda23'                  => $isRoda23,
                'kategori_roda'              => $isRoda23 ? 'Roda 2 & 3' : 'Roda 4 dan seterusnya',
                'penggunaan'                 => $kGuna,
                'is_mutasi'                  => $isMutasi,
                'kanal_bayar'                => $kanalBayar,
                'dp_normal'                  => $dpNormal,
            ],
            'komparasi' => [
                'normal'    => $resNormal,
                'ranpergub' => $resRanpergub,
            ],
            'bbnkb'       => $resBbnkb,
            'total_hemat' => $resRanpergub['total_hemat'],
        ];
    }
}

