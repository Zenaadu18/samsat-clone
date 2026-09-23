<?php
// tax_helper.php
// Helper kalkulasi pajak kendaraan bermotor NTT.

/**
 * Tarif baru PKB + Opsen mulai berlaku.
 */
const TARIF_BARU_BERLAKU = '2025-01-05';

/**
 * Maksimal bulan denda yang dihitung.
 */
const MAKS_BULAN_DENDA = 24;


/* ============================================================================
 * BAGIAN 1 - TARIF PKB
 * ========================================================================== */

/**
 * Mengambil tarif PKB berdasarkan penggunaan kendaraan.
 *
 * Tarif lama:
 * - PRIBADI -> 1.5%
 * - UMUM    -> 1.0%
 * - lainnya -> 0.5%
 *
 * Tarif baru:
 * - PRIBADI -> 1.2%
 * - selain PRIBADI -> 0.5%
 */
function getTarifPkb(string $kGuna, bool $tarifBaru): float
{
    $kGuna = strtoupper(trim($kGuna));

    if ($tarifBaru) {
        return ($kGuna === 'PRIBADI') ? 0.012 : 0.005;
    }

    if ($kGuna === 'PRIBADI') {
        return 0.015;
    }

    if (strpos($kGuna, 'UMUM') !== false) {
        return 0.010;
    }

    return 0.005;
}


/**
 * Mengecek apakah tanggal menggunakan tarif baru.
 */
function menggunakanTarifBaru(DateTime $tanggal): bool
{
    $cutoff = new DateTime(TARIF_BARU_BERLAKU);

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
 * Maksimal 24 bulan.
 */
function hitungBulanTerlambatDariTanggal(DateTime $jatuhTempo): int
{
    $sekarang = new DateTime('today');

    if ($sekarang <= $jatuhTempo) {
        return 0;
    }

    $hari = (int)$sekarang->diff($jatuhTempo)->days;

    return min(
        (int)ceil($hari / 30),
        MAKS_BULAN_DENDA
    );
}


/**
 * Kompatibilitas dengan fungsi lama.
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
 */
function hitungPkbLengkap(
    float $dpPkb,
    string $kGuna,
    string $sdNotice
): array {

    $today = new DateTime('today');

    /*
     * Jika SD_NOTICE kosong, tidak bisa mengetahui jumlah periode.
     * Maka hitung 1 tahun sebagai PKB berjalan.
     */
    if (!tanggalValid($sdNotice)) {

        $tarif = getTarifPkb($kGuna, true);

        $pkbBerjalan = round($dpPkb * $tarif);
        $opsenBerjalan = round($pkbBerjalan * 0.66);

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

            'denda_opsen_berjalan'  => 0,
            'denda_opsen_tunggakan' => 0,
            'denda_opsen'           => 0,

            'tahun_berjalan'  => 1,
            'tahun_tunggakan' => 0,
            'tahun_total'     => 1,

            // Data internal jika masih dibutuhkan.
            'pkb_lama'       => 0,
            'pkb_baru'       => $pkbBerjalan,
            'denda_pkb_lama' => 0,
            'denda_pkb_baru' => 0,
            'tahun_lama'     => 0,
            'tahun_baru'     => 1,

            'peringatan' =>
                'Data SD Notice (masa pajak) tidak tersedia. Perhitungan hanya untuk 1 tahun dengan tarif terkini.'
        ];
    }


    $periodStart = buatTanggal($sdNotice);

    if (!$periodStart) {

        $tarif = getTarifPkb($kGuna, true);

        $pkbBerjalan = round($dpPkb * $tarif);
        $opsenBerjalan = round($pkbBerjalan * 0.66);

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

            'denda_opsen_berjalan'  => 0,
            'denda_opsen_tunggakan' => 0,
            'denda_opsen'           => 0,

            'tahun_berjalan'  => 1,
            'tahun_tunggakan' => 0,
            'tahun_total'     => 1,

            'pkb_lama'       => 0,
            'pkb_baru'       => $pkbBerjalan,
            'denda_pkb_lama' => 0,
            'denda_pkb_baru' => 0,
            'tahun_lama'     => 0,
            'tahun_baru'     => 1,

            'peringatan' =>
                'Data SD Notice tidak valid. Perhitungan hanya untuk 1 tahun dengan tarif terkini.'
        ];
    }


    /*
     * Jika SD_NOTICE masih di masa depan:
     * kendaraan dianggap hanya memiliki PKB berjalan.
     */
    if ($periodStart >= $today) {

        $isNew = menggunakanTarifBaru($periodStart);
        $tarif = getTarifPkb($kGuna, $isNew);

        $pkb = round($dpPkb * $tarif);
        $opsen = $isNew ? round($pkb * 0.66) : 0;

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

            'denda_opsen_berjalan'  => 0,
            'denda_opsen_tunggakan' => 0,
            'denda_opsen'           => 0,

            'tahun_berjalan'  => 1,
            'tahun_tunggakan' => 0,
            'tahun_total'     => 1,

            'pkb_lama'       => $isNew ? 0 : $pkb,
            'pkb_baru'       => $isNew ? $pkb : 0,
            'denda_pkb_lama' => 0,
            'denda_pkb_baru' => 0,
            'tahun_lama'     => $isNew ? 0 : 1,
            'tahun_baru'     => $isNew ? 1 : 0,

            'peringatan' => ''
        ];
    }


    /*
     * Kita buat seluruh periode terlebih dahulu.
     *
     * Contoh:
     *
     * SD_NOTICE = 2023-06-01
     *
     * periode:
     * 2023-2024
     * 2024-2025
     * 2025-2026
     *
     * Periode terakhir = PKB berjalan.
     * Semua sebelumnya = tunggakan.
     */
    $periods = [];

    $current = clone $periodStart;
    $guard = 0;

    while ($current < $today && $guard < 25) {

        $periodEnd = clone $current;
        $periodEnd->modify('+1 year');

        $periods[] = [
            'start' => clone $current,
            'end'   => clone $periodEnd
        ];

        $current = $periodEnd;
        $guard++;
    }


    /*
     * Jika tidak ada periode, fallback 1 tahun.
     */
    if (empty($periods)) {

        $tarif = getTarifPkb($kGuna, true);

        $pkb = round($dpPkb * $tarif);
        $opsen = round($pkb * 0.66);

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

            'denda_opsen_berjalan'  => 0,
            'denda_opsen_tunggakan' => 0,
            'denda_opsen'           => 0,

            'tahun_berjalan'  => 1,
            'tahun_tunggakan' => 0,
            'tahun_total'     => 1,

            'pkb_lama'       => 0,
            'pkb_baru'       => $pkb,
            'denda_pkb_lama' => 0,
            'denda_pkb_baru' => 0,
            'tahun_lama'     => 0,
            'tahun_baru'     => 1,

            'peringatan' => ''
        ];
    }


    /*
     * Inisialisasi.
     */
    $pkbBerjalan = 0;
    $pkbTunggakan = 0;

    $opsenBerjalan = 0;
    $opsenTunggakan = 0;

    $dendaPkbBerjalan = 0;
    $dendaPkbTunggakan = 0;

    $dendaOpsenBerjalan = 0;
    $dendaOpsenTunggakan = 0;

    $pkbLama = 0;
    $pkbBaru = 0;

    $dendaLama = 0;
    $dendaBaru = 0;

    $tahunLama = 0;
    $tahunBaru = 0;


    /*
     * Index terakhir = PKB berjalan.
     */
    $indexBerjalan = count($periods) - 1;


    foreach ($periods as $index => $period) {

        $start = $period['start'];
        $end   = $period['end'];

        $isBerjalan = ($index === $indexBerjalan);
        $isNew      = menggunakanTarifBaru($start);

        $tarif = getTarifPkb($kGuna, $isNew);

        $pkbTahun = round($dpPkb * $tarif);

        /*
         * Opsen hanya untuk periode tarif baru.
         */
        $opsenTahun = $isNew
            ? round($pkbTahun * 0.66)
            : 0;


        /*
         * Denda hanya dihitung jika periode sudah jatuh tempo.
         *
         * PKB berjalan tetap bisa memiliki denda jika jatuh temponya
         * sudah lewat, tetapi tetap masuk kategori PKB berjalan.
         */
        $bulanLambat = 0;

        if ($today > $end) {
            $bulanLambat = hitungBulanTerlambatDariTanggal($end);
        }

        $dendaPkbTahun = round(
            $bulanLambat * 0.01 * $pkbTahun
        );

        $dendaOpsenTahun = $isNew
            ? round($bulanLambat * 0.01 * $opsenTahun)
            : 0;


        /*
         * Simpan statistik tarif internal.
         */
        if ($isNew) {
            $pkbBaru += $pkbTahun;
            $dendaBaru += $dendaPkbTahun;
            $tahunBaru++;
        } else {
            $pkbLama += $pkbTahun;
            $dendaLama += $dendaPkbTahun;
            $tahunLama++;
        }


        /*
         * Kelompokkan menjadi BERJALAN atau TUNGGAKAN.
         */
        if ($isBerjalan) {

            $pkbBerjalan += $pkbTahun;
            $opsenBerjalan += $opsenTahun;

            $dendaPkbBerjalan += $dendaPkbTahun;
            $dendaOpsenBerjalan += $dendaOpsenTahun;

        } else {

            $pkbTunggakan += $pkbTahun;
            $opsenTunggakan += $opsenTahun;

            $dendaPkbTunggakan += $dendaPkbTahun;
            $dendaOpsenTunggakan += $dendaOpsenTahun;
        }
    }


    /*
     * Total.
     */
    $pkbTotal = $pkbBerjalan + $pkbTunggakan;
    $opsenTotal = $opsenBerjalan + $opsenTunggakan;

    $dendaPkbTotal =
        $dendaPkbBerjalan +
        $dendaPkbTunggakan;

    $dendaOpsenTotal =
        $dendaOpsenBerjalan +
        $dendaOpsenTunggakan;


    return [

        /*
         * Tampilan utama.
         */
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

        /*
         * Data kompatibilitas / internal.
         */
        'pkb_lama'       => round($pkbLama),
        'pkb_baru'       => round($pkbBaru),

        'denda_pkb_lama' => round($dendaLama),
        'denda_pkb_baru' => round($dendaBaru),

        'tahun_lama' => $tahunLama,
        'tahun_baru' => $tahunBaru,

        'tahun_total' => count($periods),

        'peringatan' => ''
    ];
}


/* ============================================================================
 * BAGIAN 4 - SWDKLLJ
 * ========================================================================== */

/**
 * Menentukan golongan dan tarif SWDKLLJ.
 */
function getTarifSwdkllj(
    string $jenisKend,
    string $kJr,
    int $isiCylinder,
    string $kGuna
): array {

    $jenisKend = strtoupper($jenisKend);
    $kJrUpper = strtoupper($kJr);
    $kGunaUpper = strtoupper(trim($kGuna));

    $isMotor =
        strpos($kJrUpper, 'SEPEDA MOTOR') !== false ||
        strpos($jenisKend, 'SEPEDA MOTOR') !== false;


    /*
     * Sepeda motor.
     */
    if ($isMotor) {

        if ($isiCylinder < 50) {
            return [
                'golongan' => 'A',
                'tarif'    => 0,
                'admin'    => 3000
            ];
        }

        if ($isiCylinder <= 250) {
            return [
                'golongan' => 'C1',
                'tarif'    => 32000,
                'admin'    => 3000
            ];
        }

        return [
            'golongan' => 'C2',
            'tarif'    => 80000,
            'admin'    => 3000
        ];
    }


    /*
     * Kendaraan khusus / alat berat.
     */
    foreach ([
        'TRAKTOR',
        'BULDOZER',
        'FORKLIT',
        'FORKLIFT',
        'MOBIL DEREK',
        'DEREK',
        'EXCAVATOR',
        'CRANE'
    ] as $keyword) {

        if (strpos($jenisKend, $keyword) !== false) {

            return [
                'golongan' => 'B',
                'tarif'    => 20000,
                'admin'    => 3000
            ];
        }
    }


    /*
     * Bus / mikrobus.
     */
    if (
        strpos($jenisKend, 'BUS') !== false ||
        strpos($jenisKend, 'MICROBUS') !== false ||
        strpos($jenisKend, 'MIKRO') !== false
    ) {

        if ($kGunaUpper === 'UMUM') {
            return [
                'golongan' => 'EU',
                'tarif'    => 87000,
                'admin'    => 3000
            ];
        }

        return [
            'golongan' => 'EP',
            'tarif'    => 150000,
            'admin'    => 3000
        ];
    }


    /*
     * Truck / tangki / gandeng / container.
     */
    foreach ([
        'TRUCK',
        'TRUK',
        'TANGKI',
        'GANDENG',
        'CONTAINER',
        'KONTAINER',
        'TRACTOR HEAD'
    ] as $keyword) {

        if (strpos($jenisKend, $keyword) !== false) {

            return [
                'golongan' => 'F',
                'tarif'    => 160000,
                'admin'    => 3000
            ];
        }
    }


    /*
     * Pick up / mobil barang.
     */
    foreach ([
        'PICK UP',
        'PICKUP',
        'MOBIL BARANG',
        'BLIND VAN',
        'LIGHT TRUCK'
    ] as $keyword) {

        if (strpos($jenisKend, $keyword) !== false) {

            if ($isiCylinder > 2400) {

                return [
                    'golongan' => 'F',
                    'tarif'    => 160000,
                    'admin'    => 3000
                ];
            }

            return [
                'golongan' => 'DP',
                'tarif'    => 140000,
                'admin'    => 3000
            ];
        }
    }


    /*
     * Mobil penumpang umum <= 1600 cc.
     */
    if (
        $kGunaUpper === 'UMUM' &&
        $isiCylinder <= 1600
    ) {

        return [
            'golongan' => 'DU',
            'tarif'    => 70000,
            'admin'    => 3000
        ];
    }


    /*
     * Default sedan / jeep / minibus pribadi.
     */
    return [
        'golongan' => 'DP',
        'tarif'    => 140000,
        'admin'    => 3000
    ];
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

    $jumlahTahun = max(1, $jumlahTahun);

    $tarifTahunan = $tarifData['tarif'];
    $admin = $tarifData['admin'];

    /*
     * Admin SWDKLLJ dikenakan setiap periode.
     */
    $swdklljPerTahun =
        $tarifTahunan +
        $admin;

    $total = $swdklljPerTahun * $jumlahTahun;

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

        'total'       => $total,
        'denda'       => $denda,

        'total_dengan_denda' => $total + $denda
    ];
}


/* ============================================================================
 * BAGIAN 5 - STNK & TNKB
 * ========================================================================== */

/**
 * Menghitung biaya STNK & TNKB jika:
 * - sudah jatuh tempo, atau
 * - akan jatuh tempo dalam 3 bulan.
 */
function hitungBiayaStnk(
    string $sdStnk,
    string $jenisKend,
    string $kJr
): array {

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
        $sekarang = new DateTime('today');

        $batasAwal =
            (clone $jatuhTempo)->modify('-3 months');

        /*
         * Masih lebih dari 3 bulan sebelum jatuh tempo.
         */
        if ($sekarang < $batasAwal) {
            return $kosong;
        }


        $jenisUpper = strtoupper($jenisKend);
        $kJrUpper = strtoupper($kJr);

        $isMotor =
            strpos($kJrUpper, 'SEPEDA MOTOR') !== false ||
            strpos($jenisUpper, 'SEPEDA MOTOR') !== false ||
            strpos($kJrUpper, 'RODA TIGA') !== false;


        /*
         * Tarif sesuai logika existing.
         */
        if ($isMotor) {

            $biayaTnkb = 60000;
            $biayaStnk = 100000;

        } else {

            $biayaTnkb = 100000;
            $biayaStnk = 200000;
        }


        if ($sekarang > $jatuhTempo) {

            $hari = (int)$sekarang->diff($jatuhTempo)->days;

            $info =
                'STNK sudah berakhir (' .
                $hari .
                ' hari lalu)';

        } else {

            $hari = (int)$sekarang->diff($jatuhTempo)->days;

            $info =
                'STNK berakhir dalam ' .
                $hari .
                ' hari';
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