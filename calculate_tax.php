<?php
// calculate_tax.php

header('Content-Type: application/json; charset=utf-8');

require 'db.php';
require 'config_loader.php';
require 'tax_helper.php';


/* ============================================================================
 * UTILITAS RESPONSE
 * ========================================================================== */

function responseJson(array $data, int $httpCode = 200): void
{
    http_response_code($httpCode);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}


/* ============================================================================
 * 1. AMBIL INPUT
 * ========================================================================== */

$rawInput = file_get_contents('php://input');

$data = json_decode($rawInput, true);

if (!is_array($data)) {

    responseJson([
        'status'  => 'error',
        'message' => 'Format request tidak valid.'
    ], 400);
}


$nopolInput = trim((string)($data['nopol'] ?? ''));

if ($nopolInput === '') {

    responseJson([
        'status'  => 'error',
        'message' => 'Nomor polisi wajib diisi.'
    ], 400);
}


/*
 * Bersihkan spasi dan ubah menjadi uppercase.
 */
$nopolClean = strtoupper(
    preg_replace('/\s+/', '', $nopolInput)
);

$isMutasi = !empty($data['is_mutasi']);


/* ============================================================================
 * 2. AMBIL DATA KENDARAAN DARI API GETNOPOL
 * ========================================================================== */

$getNopolUrl =
    $dbKonfigurasi['api_url_samsat']
    ?? 'https://dash.bpad.nttprov.go.id/pajak/webdtd/pendataan/core/php/getnopol.php';

$ch = curl_init($getNopolUrl);

$postData = json_encode([
    'nopol' => $nopolClean
]);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_POST => true,

    CURLOPT_POSTFIELDS => $postData,

    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'Content-Length: ' . strlen($postData)
    ],

    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT => 30,

    CURLOPT_FOLLOWLOCATION => true,

    CURLOPT_USERAGENT =>
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36',

    /*
     * Server Samsat kemungkinan menggunakan sertifikat
     * yang tidak dikenali oleh environment hosting.
     */
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0
]);


$response = curl_exec($ch);

$curlError = curl_error($ch);

$curlErrno = curl_errno($ch);

$httpCode = curl_getinfo(
    $ch,
    CURLINFO_HTTP_CODE
);

curl_close($ch);


/*
 * Jika cURL gagal.
 */
if ($response === false) {

    responseJson([
        'status' => 'error',

        'message' =>
            'Gagal terhubung ke server Samsat.',

        'detail' =>
            $curlError,

        'curl_errno' =>
            $curlErrno,

        'http_code' =>
            $httpCode
    ], 502);
}


/*
 * HTTP error.
 */
if ($httpCode < 200 || $httpCode >= 300) {

    responseJson([
        'status' => 'error',

        'message' =>
            'Server Samsat mengembalikan HTTP ' . $httpCode . '.',

        'detail' =>
            substr(trim($response), 0, 500),

        'http_code' =>
            $httpCode
    ], 502);
}


/*
 * Pastikan response JSON.
 */
$apiData = json_decode($response, true);

if (!is_array($apiData)) {

    responseJson([
        'status' => 'error',

        'message' =>
            'Response server Samsat bukan JSON yang valid.',

        'detail' =>
            substr(trim($response), 0, 500),

        'http_code' =>
            $httpCode
    ], 502);
}





/* ============================================================================
 * 3. EKSTRAK DATA KENDARAAN
 * ========================================================================== */

$merk =
    trim((string)($apiData['Merk'] ?? ''));

$tipe =
    trim((string)($apiData['Type'] ?? ''));

$tahun =
    (int)($apiData['TahunPembuatan'] ?? 0);

$kGuna =
    trim((string)($apiData['K_GUNA'] ?? ''));

$kJr =
    trim((string)($apiData['K_JR'] ?? ''));

$jenisKend =
    strtoupper(
        trim((string)($apiData['JenisKendaraan'] ?? ''))
    );

$isiCylinder =
    (int)($apiData['IsiCylinder'] ?? 0);

$sdNotice =
    trim((string)($apiData['SD_NOTICE'] ?? ''));

$sdStnk =
    trim((string)($apiData['SD_STNK'] ?? ''));

$nama =
    trim((string)($apiData['NamaPemilik'] ?? '-'));

$alamat =
    trim((string)($apiData['Alamat'] ?? '-'));


/* ============================================================================
 * 4. CARI NJKB & BOBOT
 * ========================================================================== */

$njkb = 0;
$bobot = 0;


/*
 * Exact match.
 */
$stmt = $conn->prepare(
    'SELECT njkb, bobot
     FROM njkb_data
     WHERE merk = ?
       AND tipe = ?
       AND tahun_buat = ?
     LIMIT 1'
);

if (!$stmt) {

    responseJson([
        'status'  => 'error',
        'message' => 'Gagal menyiapkan query NJKB.'
    ], 500);
}


$stmt->bind_param(
    'ssi',
    $merk,
    $tipe,
    $tahun
);

$stmt->execute();

$row = $stmt
    ->get_result()
    ->fetch_assoc();

$stmt->close();


/*
 * Jika exact match ditemukan.
 */
if ($row) {

    $njkb =
        (float)$row['njkb'];

    $bobot =
        (float)$row['bobot'];

} else {

    /*
     * Fallback LIKE + tahun.
     */
    $merkLike = '%' . $merk . '%';
    $tipeLike = '%' . $tipe . '%';


    $stmt2 = $conn->prepare(
        'SELECT njkb, bobot
         FROM njkb_data
         WHERE merk LIKE ?
           AND tipe LIKE ?
           AND tahun_buat = ?
         LIMIT 1'
    );


    if (!$stmt2) {

        responseJson([
            'status'  => 'error',
            'message' => 'Gagal menyiapkan query fallback NJKB.'
        ], 500);
    }


    $stmt2->bind_param(
        'ssi',
        $merkLike,
        $tipeLike,
        $tahun
    );

    $stmt2->execute();

    $row2 = $stmt2
        ->get_result()
        ->fetch_assoc();

    $stmt2->close();


    if ($row2) {

        $njkb =
            (float)$row2['njkb'];

        $bobot =
            (float)$row2['bobot'];

    } else {

        /*
         * Fallback terakhir:
         * ambil tahun paling baru.
         */
        $stmt3 = $conn->prepare(
            'SELECT njkb, bobot
             FROM njkb_data
             WHERE merk LIKE ?
               AND tipe LIKE ?
             ORDER BY tahun_buat DESC
             LIMIT 1'
        );


        if (!$stmt3) {

            responseJson([
                'status'  => 'error',
                'message' => 'Gagal menyiapkan query NJKB terakhir.'
            ], 500);
        }


        $stmt3->bind_param(
            'ss',
            $merkLike,
            $tipeLike
        );

        $stmt3->execute();

        $row3 = $stmt3
            ->get_result()
            ->fetch_assoc();

        $stmt3->close();


        if ($row3) {

            $njkb =
                (float)$row3['njkb'];

            $bobot =
                (float)$row3['bobot'];

        } else {

            responseJson([
                'status'  => 'error',
                'message' =>
                    "Data NJKB tidak ditemukan untuk {$merk} - {$tipe} ({$tahun})."
            ], 404);
        }
    }
}


/* ============================================================================
 * 5. DASAR PENGENAAN PKB
 * ========================================================================== */

$dpPkb =
    $njkb * $bobot;


/* ============================================================================
 * 6. HITUNG PKB
 * ========================================================================== */

$pkbData = hitungPkbLengkap(
    $dpPkb,
    $kGuna,
    $sdNotice,
    [
        'alamat' => $alamat,
        'nopol' => $nopolClean,
        'is_mutasi' => $isMutasi,
        'jenis' => $jenisKend,
        'k_jr' => $kJr
    ]
);


/* ============================================================================
 * 7. HITUNG SWDKLLJ
 *
 * Jumlah tahun = jumlah periode yang harus dibayar.
 * ========================================================================== */

$jumlahTahunPajak =
    max(
        1,
        (int)($pkbData['tahun_total'] ?? 1)
    );


$swdklljData = hitungSwdkllj(
    $jenisKend,
    $kJr,
    $isiCylinder,
    $kGuna,
    $jumlahTahunPajak
);


$swdkllj =
    (int)$swdklljData['total'];


/*
 * Denda SWDKLLJ.
 */
$dendaSwdkllj =
    (int)$swdklljData['denda'];


/* ============================================================================
 * 8. HITUNG STNK & TNKB
 * ========================================================================== */

$stnkData = hitungBiayaStnk(
    $sdStnk,
    $jenisKend,
    $kJr
);


/* ============================================================================
 * 9. LABEL TUNGGAKAN
 * ========================================================================== */

$tahunTunggakan =
    (int)($pkbData['tahun_tunggakan'] ?? 0);

$tahunBerjalan =
    (int)($pkbData['tahun_berjalan'] ?? 1);


$tunggakanAda =
    $tahunTunggakan > 0;


/*
 * Label tarif hanya untuk informasi internal/tampilan kecil.
 * Tidak lagi menampilkan tarif lama vs baru sebagai rincian.
 */
$tarifLama =
    getTarifPkb($kGuna, false) * 100;

$tarifBaru =
    getTarifPkb($kGuna, true) * 100;


/* ============================================================================
 * 10. TOTAL
 * ========================================================================== */

$pkbBerjalan =
    (int)$pkbData['pkb_berjalan'];

$pkbTunggakan =
    (int)$pkbData['pkb_tunggakan'];

$pkbTotal =
    (int)$pkbData['pkb_total'];


$dendaPkb =
    (int)$pkbData['denda_pkb_total'];


$opsenPkb =
    (int)$pkbData['opsen_pkb'];


$dendaOpsenPkb =
    (int)$pkbData['denda_opsen'];


$biayaStnk =
    (int)$stnkData['biaya_stnk'];


$biayaTnkb =
    (int)$stnkData['biaya_tnkb'];


/*
 * Total akhir.
 */
$totalPajak =
      $pkbTotal
    + $dendaPkb
    + $opsenPkb
    + $dendaOpsenPkb
    + $swdkllj
    + $dendaSwdkllj
    + $stnkData['total'];


/* ============================================================================
 * 11. RESPONSE
 * ========================================================================== */

responseJson([

    'status' => 'success',

    'data' => [

        /* ---------------------------------------------------------------
         * IDENTITAS
         * ------------------------------------------------------------- */

        'nopol' =>
            $nopolClean,

        'pemilik' =>
            $nama,

        'alamat' =>
            $alamat,

        'merk' =>
            $merk,

        'tipe' =>
            $tipe,

        'tahun' =>
            $tahun,

        'unit' =>
            trim($merk . ' / ' . $tipe),

        'jenis_kendaraan' =>
            $jenisKend,

        'penggunaan' =>
            $kGuna,

        'sd_notice' =>
            $sdNotice,

        'sd_stnk' =>
            $sdStnk,


        /* ---------------------------------------------------------------
         * NJKB
         * ------------------------------------------------------------- */

        'njkb' =>
            round($njkb),

        'bobot' =>
            $bobot,

        'dp_pkb' =>
            round($dpPkb),


        /* ---------------------------------------------------------------
         * PKB
         * ------------------------------------------------------------- */

        'pkb_berjalan' =>
            $pkbBerjalan,

        'pkb_tunggakan' =>
            $pkbTunggakan,

        'pkb_total' =>
            $pkbTotal,

        'tahun_berjalan' =>
            $tahunBerjalan,

        'tahun_tunggakan' =>
            $tahunTunggakan,

        'tahun_total' =>
            $jumlahTahunPajak,

        'tunggakan_ada' =>
            $tunggakanAda,


        /* ---------------------------------------------------------------
         * OPSEN
         * ------------------------------------------------------------- */

        'opsen_pkb' =>
            $opsenPkb,

        'opsen_pkb_berjalan' =>
            (int)$pkbData['opsen_pkb_berjalan'],

        'opsen_pkb_tunggakan' =>
            (int)$pkbData['opsen_pkb_tunggakan'],


        /* ---------------------------------------------------------------
         * DENDA
         * ------------------------------------------------------------- */

        'denda_pkb' =>
            $dendaPkb,

        'denda_pkb_total' =>
            $dendaPkb,

        'denda_opsen_pkb' =>
            $dendaOpsenPkb,

        'denda_opsen' =>
            $dendaOpsenPkb,


        /* ---------------------------------------------------------------
         * SWDKLLJ
         * ------------------------------------------------------------- */

        'swdkllj_golongan' =>
            $swdklljData['golongan'],

        'swdkllj_tarif' =>
            $swdklljData['tarif'],

        'swdkllj_admin' =>
            $swdklljData['admin'],

        'swdkllj_jumlah_tahun' =>
            $swdklljData['jumlah_tahun'],

        'swdkllj' =>
            $swdkllj,

        'denda_swdkllj' =>
            $dendaSwdkllj,


        /* ---------------------------------------------------------------
         * STNK / TNKB
         * ------------------------------------------------------------- */

        'stnk_wajib' =>
            (bool)$stnkData['wajib'],

        'biaya_stnk' =>
            $biayaStnk,

        'biaya_tnkb' =>
            $biayaTnkb,

        'biaya_stnk_total' =>
            (int)$stnkData['total'],

        'stnk_info' =>
            $stnkData['info'],


        /* ---------------------------------------------------------------
         * TARIF / PERINGATAN
         * ------------------------------------------------------------- */

        'tarif_lama' =>
            $tarifLama . '%',

        'tarif_baru' =>
            $tarifBaru . '%',

        'tarif_label' =>
            $tarifBaru . '%',

        'peringatan' =>
            $pkbData['peringatan'],
            
        /* ---------------------------------------------------------------
         * AMNESTI
         * ------------------------------------------------------------- */
        'amnesti_aktif' => (bool)($pkbData['amnesti_aktif'] ?? false),
        'amnesti_diskon_pkb_berjalan' => (int)($pkbData['amnesti_diskon_pkb_berjalan'] ?? 0),
        'amnesti_diskon_pkb_tunggakan' => (int)($pkbData['amnesti_diskon_pkb_tunggakan'] ?? 0),
        'amnesti_denda_dibebaskan' => (int)($pkbData['amnesti_denda_dibebaskan'] ?? 0),
        'amnesti_total_potongan' => (int)($pkbData['amnesti_total_potongan'] ?? 0),

        /* ---------------------------------------------------------------
         * TOTAL
         * ------------------------------------------------------------- */

        'total_pajak' =>
            (int)$totalPajak
    ]
]);