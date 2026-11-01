<?php
// api_tax.php
// Endpoint API khusus untuk pengecekan pajak kendaraan bermotor NTT

header('Content-Type: application/json; charset=utf-8');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");

require 'db.php';
require 'config_loader.php';
require 'tax_helper.php';

function responseJson(array $data, int $httpCode = 200): void
{
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

$nopolInput = trim((string)($data['nopol'] ?? $_GET['nopol'] ?? ''));

if ($nopolInput === '') {
    responseJson([
        'status'  => 'error',
        'message' => 'Nomor polisi wajib diisi.'
    ], 400);
}

$nopolClean = strtoupper(preg_replace('/\s+/', '', $nopolInput));
$isMutasi = !empty($data['is_mutasi']) || !empty($_GET['is_mutasi']);
$isDisabilitas = !empty($data['is_disabilitas']) || !empty($_GET['is_disabilitas']);
$isKarwas = !empty($data['is_karwas']) || !empty($_GET['is_karwas']);
$isSosial = !empty($data['is_sosial']) || !empty($_GET['is_sosial']) || !empty($data['is_sosial_agama']);
$isAmbulance = !empty($data['is_ambulance']) || !empty($_GET['is_ambulance']);

$getNopolUrl = $dbKonfigurasi['api_url_samsat'] ?? 'https://dash.bpad.nttprov.go.id/pajak/webdtd/pendataan/core/php/getnopol.php';

$ch = curl_init($getNopolUrl);
$postData = json_encode(['nopol' => $nopolClean]);

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
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $httpCode < 200 || $httpCode >= 300) {
    responseJson([
        'status'  => 'error',
        'message' => 'Gagal terhubung ke server Samsat pusat.'
    ], 502);
}

$apiData = json_decode($response, true);
if (!is_array($apiData) || empty($apiData['Merk'])) {
    responseJson([
        'status'  => 'error',
        'message' => 'Data kendaraan dengan nopol tersebut tidak ditemukan.'
    ], 404);
}

// Ekstrak Variabel Kendaraan dari API Samsat
$merk = trim((string)($apiData['Merk'] ?? ''));
$tipe = trim((string)($apiData['Type'] ?? ''));
$tahun = (int)($apiData['TahunPembuatan'] ?? 0);
$kGuna = trim((string)($apiData['K_GUNA'] ?? ''));
$kJr = trim((string)($apiData['K_JR'] ?? ''));
$jenisKend = strtoupper(trim((string)($apiData['JenisKendaraan'] ?? '')));
$isiCylinder = (int)($apiData['IsiCylinder'] ?? 0);
$sdNotice = trim((string)($apiData['SD_NOTICE'] ?? ''));
$sdStnk = trim((string)($apiData['SD_STNK'] ?? ''));
$nama = trim((string)($apiData['NamaPemilik'] ?? '-'));
$alamat = trim((string)($apiData['Alamat'] ?? '-'));
$rt = trim((string)($apiData['Rt'] ?? '-'));
$rw = trim((string)($apiData['Rw'] ?? '-'));
$kodeKel = trim((string)($apiData['KodeKel'] ?? ''));
$kelurahan = trim((string)($apiData['Kelurahan'] ?? ''));
$kodeKec = trim((string)($apiData['KodeKec'] ?? ''));
$kecamatan = trim((string)($apiData['Kecamatan'] ?? ''));
$uptAsal = trim((string)($apiData['UPT_ASAL'] ?? ''));

$rubahBentuk = strtoupper(trim((string)($apiData['RubahBentuk'] ?? ''))); // <-- Tambahkan baris ini

// Cari NJKB & Bobot (Multi-Tier Fallback)
$njkb = 0.0; $bobot = 0.0;
$stmt = $conn->prepare('SELECT njkb, bobot, tipe FROM njkb_data WHERE merk = ? AND tipe = ? AND tahun_buat = ? LIMIT 1');
if ($stmt) {
    $stmt->bind_param('ssi', $merk, $tipe, $tahun);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) {
        $njkb = (float)$row['njkb'];
        $bobot = (float)$row['bobot'];
    }
}

if ($njkb <= 0.0) {
    $stmt2 = $conn->prepare('SELECT njkb, bobot, tipe FROM njkb_data WHERE merk LIKE ? AND tipe LIKE ? ORDER BY ABS(tahun_buat - ?) ASC LIMIT 1');
    if ($stmt2) {
        $mLike = '%' . $merk . '%'; $tLike = '%' . $tipe . '%';
        $stmt2->bind_param('ssi', $mLike, $tLike, $tahun);
        $stmt2->execute();
        $row2 = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();
        if ($row2) {
            $njkb = (float)$row2['njkb'];
            $bobot = (float)$row2['bobot'];
        }
    }
}

// Ekstraksi kata model jika masih 0
if ($njkb <= 0.0) {
    $keywords = [];
    if (preg_match('/\((.*?)\)/', $tipe, $matches)) {
        $inside = trim($matches[1]);
        $subParts = preg_split('/[\/\,\-\s]+/', $inside);
        foreach ($subParts as $sp) {
            $sp = trim($sp);
            if (strlen($sp) >= 3 && !in_array($sp, $keywords, true)) $keywords[] = $sp;
        }
    }
    $words = preg_split('/[\s\/\(\)\,\-]+/', $tipe);
    foreach ($words as $w) {
        $w = trim($w);
        if (strlen($w) >= 3 && !in_array($w, $keywords, true)) $keywords[] = $w;
    }
    foreach ($keywords as $kw) {
        $stmtKw = $conn->prepare('SELECT njkb, bobot, tipe FROM njkb_data WHERE merk LIKE ? AND tipe LIKE ? ORDER BY ABS(tahun_buat - ?) ASC LIMIT 1');
        if ($stmtKw) {
            $mLike = '%' . $merk . '%'; $kwLike = '%' . $kw . '%';
            $stmtKw->bind_param('ssi', $mLike, $kwLike, $tahun);
            $stmtKw->execute();
            $rowKw = $stmtKw->get_result()->fetch_assoc();
            $stmtKw->close();
            if ($rowKw) {
                $njkb = (float)$rowKw['njkb'];
                $bobot = (float)$rowKw['bobot'];
                break;
            }
        }
    }
}

// Fallback model sejenis merk tersebut
if ($njkb <= 0.0 && !empty($merk)) {
    $stmtMerk = $conn->prepare('SELECT njkb, bobot, tipe FROM njkb_data WHERE merk LIKE ? ORDER BY ABS(tahun_buat - ?) ASC LIMIT 1');
    if ($stmtMerk) {
        $mLike = '%' . $merk . '%';
        $stmtMerk->bind_param('si', $mLike, $tahun);
        $stmtMerk->execute();
        $rowMerk = $stmtMerk->get_result()->fetch_assoc();
        $stmtMerk->close();
        if ($rowMerk) {
            $njkb = (float)$rowMerk['njkb'];
            $bobot = (float)$rowMerk['bobot'];
        }
    }
}

// Safety net default
if ($njkb <= 0.0) {
    $isMotorCheck = (strpos($jenisKend, 'SEPEDA MOTOR') !== false || strpos($kJr, 'SEPEDA MOTOR') !== false || ($isiCylinder > 0 && $isiCylinder <= 250));
    if ($isMotorCheck) {
        $njkb = 14000000.0; $bobot = 1.0;
    } elseif (strpos($jenisKend, 'TRUCK') !== false || strpos($jenisKend, 'BUS') !== false) {
        $njkb = 250000000.0; $bobot = 1.3;
    } elseif (strpos($jenisKend, 'PICK') !== false) {
        $njkb = 120000000.0; $bobot = 1.085;
    } else {
        $njkb = 160000000.0; $bobot = 1.025;
    }
}

if ($bobot <= 0.0) {
    if (strpos($jenisKend, 'SEPEDA MOTOR') !== false || strpos($kJr, 'SEPEDA MOTOR') !== false || ($isiCylinder > 0 && $isiCylinder <= 250)) {
        $bobot = 1.0;
    } elseif (strpos($jenisKend, 'TRUCK') !== false || strpos($jenisKend, 'BUS') !== false) {
        $bobot = 1.3;
    } elseif (strpos($jenisKend, 'PICK') !== false) {
        $bobot = 1.085;
    } else {
        $bobot = 1.025;
    }
}

// ---------------------------------------------------------
// TAMBAHKAN LOGIKA NJUB/NJMKB DI SINI
// ---------------------------------------------------------
$njub = 0;
if ($rubahBentuk !== '' && $rubahBentuk !== '-') {
    // Batas bawah tahun sesuai format Excel (2006)
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

$dpPkb = ($njkb + $njub) * $bobot;

// Kalkulasi Pajak menggunakan Helper
$pkbData = hitungPkbLengkap($dpPkb, $kGuna, $sdNotice, [
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
]);

$swdklljData = hitungSwdkllj($jenisKend, $kJr, $isiCylinder, $kGuna, $sdNotice);
$stnkData = hitungBiayaStnk($sdStnk, $jenisKend, $kJr);

// Rincian Komponen Tagihan
$pkbBerjalan = (int)$pkbData['pkb_berjalan'];
$pkbTunggakan = (int)$pkbData['pkb_tunggakan'];
$dendaPkb = (int)$pkbData['denda_pkb_total'];
$opsenPkb = (int)$pkbData['opsen_pkb'];
$dendaOpsen = (int)$pkbData['denda_opsen'];

$swdkllj = (int)$swdklljData['total'];
$dendaSwdkllj = (int)$swdklljData['denda'];

$biayaStnk = (int)$stnkData['biaya_stnk'];
$biayaTnkb = (int)$stnkData['biaya_tnkb'];

$totalPajak = $pkbBerjalan + $pkbTunggakan + $dendaPkb + $opsenPkb + $dendaOpsen + $swdkllj + $dendaSwdkllj + $stnkData['total'];

// Menyusun Format Output Sesuai Permintaan (Array Berisi Objek)
$resultArray = [
    [
        "NOPOL" => $nopolClean,
        "NoKTP" => "000000000000",
        "NamaPemilik" => $nama,
        "Alamat" => $alamat,
        "Rt" => $rt,
        "Rw" => $rw,
        "KodeKel" => $kodeKel,
        "Kelurahan" => $kelurahan,
        "KodeKec" => $kodeKec,
        "Kecamatan" => $kecamatan,
        "STNK" => $sdStnk,
        "NOTICE" => $sdNotice,
        "PKB_BERJALAN" => $pkbBerjalan,
        "PKB_TUNGGAKAN" => $pkbTunggakan,
        "DENDA_PKB" => $dendaPkb,
        "OPSEN_PKB" => $opsenPkb,
        "DENDA_OPSEN" => $dendaOpsen,
        "SWDKLLJ" => $swdkllj,
        "DENDA_SWDKLLJ" => $dendaSwdkllj,
        "BIAYA_STNK" => $biayaStnk,
        "BIAYA_TNKB" => $biayaTnkb,
        "TOTAL_TAGIHAN" => $totalPajak
    ]
];

responseJson($resultArray);