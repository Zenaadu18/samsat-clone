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

$isMutasi       = !empty($data['is_mutasi']);
$isDisabilitas  = !empty($data['is_disabilitas']);
$isKarwas       = !empty($data['is_karwas']);
$isSosial       = !empty($data['is_sosial']) || !empty($data['is_sosial_agama']);
$isAmbulance    = !empty($data['is_ambulance']);
$isKhusus05     = $isAmbulance || $isSosial;


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

$uptAsal = trim((string)($apiData['UPT_ASAL'] ?? ''));

$rubahBentuk = strtoupper(trim((string)($apiData['RubahBentuk'] ?? '')));


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

/* ============================================================================
 * 5. DASAR PENGENAAN PKB
 * ========================================================================== */

$njub = 0;

if ($rubahBentuk !== '' && $rubahBentuk !== '-') {
    
    // Sesuaikan batas tahun bawah karena data master excel hanya sampai 2006
    $tahunCari = $tahun < 2006 ? 2006 : $tahun;
    
    $stmtNjub = $conn->prepare('SELECT nilai FROM njmkb_data WHERE rubah_bentuk = ? AND jenis_dasar = ? AND tahun = ? LIMIT 1');
    
    if ($stmtNjub) {
        $stmtNjub->bind_param('ssi', $rubahBentuk, $jenisKend, $tahunCari);
        $stmtNjub->execute();
        $rowNjub = $stmtNjub->get_result()->fetch_assoc();
        $stmtNjub->close();
        
        if ($rowNjub) {
            $njub = (float)$rowNjub['nilai'];
        }
    }
}

// Rumus baru dengan mempertimbangkan Nilai Jual Ubah Bentuk (NJUB)
$dpPkb = ($njkb + $njub) * $bobot;


/* ============================================================================
 * 6. HITUNG PKB
 * ========================================================================== */

$pkbData = hitungPkbLengkap(
    $dpPkb,
    $kGuna,
    $sdNotice,
    [
        'alamat'        => $alamat,
        'nopol'         => $nopolClean,
        'is_mutasi'     => $isMutasi,
        'is_disabilitas'=> $isDisabilitas,
        'is_karwas'     => $isKarwas,
        'is_sosial'     => $isSosial,
        'is_ambulance'  => $isAmbulance,
        'jenis'         => $jenisKend,
        'type'          => $tipe,
        'rubah_bentuk'  => $rubahBentuk,
        'k_jr'          => $kJr,
        'upt_asal'      => $uptAsal
    ]
);


/* ============================================================================
 * 7. HITUNG SWDKLLJ
 * ========================================================================== */

$jumlahTahunPajak =
    max(
        1,
        (int)($pkbData['tahun_total'] ?? 1)
    );

// KEMBALIKAN parameter $sdNotice agar sistem bisa menghitung hari keterlambatan
$swdklljData = hitungSwdkllj(
    $jenisKend,
    $kJr,
    $isiCylinder,
    $kGuna,
    $sdNotice 
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
    getTarifPkb($kGuna, false, $isKhusus05) * 100;

$tarifBaru =
    getTarifPkb($kGuna, true, $isKhusus05) * 100;


/* ============================================================================
 * 10. TOTAL
 * ========================================================================== */

$pkbBerjalan = (int)$pkbData['pkb_berjalan'];
$pkbTunggakan = (int)$pkbData['pkb_tunggakan'];
$pkbTotal = (int)$pkbData['pkb_total'];

$dendaPkb = (int)$pkbData['denda_pkb_total'];
$opsenPkb = (int)$pkbData['opsen_pkb'];
$dendaOpsenPkb = (int)$pkbData['denda_opsen'];

$biayaStnk = (int)$stnkData['biaya_stnk'];
$biayaTnkb = (int)$stnkData['biaya_tnkb'];

$amnestiPotongan = (int)($pkbData['amnesti_total_potongan'] ?? 0);

/*
 * Total akhir dikurangi potongan amnesti
 */
$totalPajak =
      $pkbTotal
    + $dendaPkb
    + $opsenPkb
    + $dendaOpsenPkb
    + $swdkllj
    + $dendaSwdkllj
    + $stnkData['total'];


$totalPenghematanLengkap = 
      (int)($pkbData['diskon_pkb_berjalan'] ?? 0)
    + (int)($pkbData['diskon_pkb_tunggakan'] ?? 0)
    + (int)($pkbData['diskon_opsen_pkb_berjalan'] ?? 0)
    + (int)($pkbData['diskon_opsen_pkb_tunggakan'] ?? 0)
    + (int)($pkbData['diskon_denda_pkb_berjalan'] ?? 0)
    + (int)($pkbData['diskon_denda_pkb_tunggakan'] ?? 0)
    + (int)($pkbData['diskon_denda_opsen_berjalan'] ?? 0)
    + (int)($pkbData['diskon_denda_opsen_tunggakan'] ?? 0)
    + (int)($swdklljData['diskon_denda_berjalan'] ?? 0)
    + (int)($swdklljData['diskon_denda_tunggakan'] ?? 0);

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

        'pkb_berjalan_kotor' =>
            (int)$pkbData['pkb_berjalan_kotor'],

        'diskon_pkb_berjalan' =>
            (int)$pkbData['diskon_pkb_berjalan'],

        'pkb_tunggakan' =>
            $pkbTunggakan,

        'pkb_tunggakan_kotor' =>
            (int)$pkbData['pkb_tunggakan_kotor'],

        'diskon_pkb_tunggakan' =>
            (int)$pkbData['diskon_pkb_tunggakan'],

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

        'opsen_pkb_berjalan_kotor' =>
            (int)$pkbData['opsen_pkb_berjalan_kotor'],

        'diskon_opsen_pkb_berjalan' =>
            (int)$pkbData['diskon_opsen_pkb_berjalan'],

        'opsen_pkb_tunggakan' =>
            (int)$pkbData['opsen_pkb_tunggakan'],

        'opsen_pkb_tunggakan_kotor' =>
            (int)$pkbData['opsen_pkb_tunggakan_kotor'],

        'diskon_opsen_pkb_tunggakan' =>
            (int)$pkbData['diskon_opsen_pkb_tunggakan'],


        /* ---------------------------------------------------------------
         * DENDA PKB & DENDA OPSEN
         * ------------------------------------------------------------- */

        'denda_pkb' =>
            $dendaPkb,

        'denda_pkb_total' =>
            $dendaPkb,

        'denda_pkb_berjalan' =>
            (int)$pkbData['denda_pkb_berjalan'],

        'denda_pkb_berjalan_kotor' =>
            (int)$pkbData['denda_pkb_berjalan_kotor'],

        'diskon_denda_pkb_berjalan' =>
            (int)$pkbData['diskon_denda_pkb_berjalan'],

        'denda_pkb_tunggakan' =>
            (int)$pkbData['denda_pkb_tunggakan'],

        'denda_pkb_tunggakan_kotor' =>
            (int)$pkbData['denda_pkb_tunggakan_kotor'],

        'diskon_denda_pkb_tunggakan' =>
            (int)$pkbData['diskon_denda_pkb_tunggakan'],

        'denda_opsen_pkb' =>
            $dendaOpsenPkb,

        'denda_opsen' =>
            $dendaOpsenPkb,

        'denda_opsen_berjalan' =>
            (int)$pkbData['denda_opsen_berjalan'],

        'denda_opsen_berjalan_kotor' =>
            (int)$pkbData['denda_opsen_berjalan_kotor'],

        'diskon_denda_opsen_berjalan' =>
            (int)$pkbData['diskon_denda_opsen_berjalan'],

        'denda_opsen_tunggakan' =>
            (int)$pkbData['denda_opsen_tunggakan'],

        'denda_opsen_tunggakan_kotor' =>
            (int)$pkbData['denda_opsen_tunggakan_kotor'],

        'diskon_denda_opsen_tunggakan' =>
            (int)$pkbData['diskon_denda_opsen_tunggakan'],


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

        'swdkllj_berjalan' =>
            (int)$swdklljData['pokok_berjalan'],

        'swdkllj_tunggakan' =>
            (int)$swdklljData['pokok_tunggakan'],

        'denda_swdkllj' =>
            $dendaSwdkllj,

        'denda_swdkllj_berjalan' =>
            (int)$swdklljData['denda_berjalan'],

        'denda_swdkllj_berjalan_kotor' =>
            (int)$swdklljData['denda_berjalan_kotor'],

        'diskon_denda_swdkllj_berjalan' =>
            (int)$swdklljData['diskon_denda_berjalan'],

        'denda_swdkllj_tunggakan' =>
            (int)$swdklljData['denda_tunggakan'],

        'denda_swdkllj_tunggakan_kotor' =>
            (int)$swdklljData['denda_tunggakan_kotor'],

        'diskon_denda_swdkllj_tunggakan' =>
            (int)$swdklljData['diskon_denda_tunggakan'],


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
        'amnesti_total_potongan' => (int)$totalPenghematanLengkap,

        /* ---------------------------------------------------------------
         * TOTAL
         * ------------------------------------------------------------- */

        'total_pajak' =>
            (int)$totalPajak
    ]
]);