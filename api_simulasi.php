<?php
// api_simulasi.php
// Endpoint API untuk Simulasi Komparasi Penetapan Pajak Kendaraan NTT:
// Normal vs Pergub 54 vs Ranpergub

header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config_loader.php';
require_once __DIR__ . '/simulasi_helper.php';

function responseJson(array $data, int $httpCode = 200): void {
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true) ?? [];
if (!is_array($input) && !empty($_POST)) {
    $input = $_POST;
}

$mode = $input['mode'] ?? 'nopol'; // 'nopol' atau 'manual'
$kanalBayar = strtolower(trim((string)($input['kanal_bayar'] ?? 'loket')));
$tanggalSimulasi = trim((string)($input['tanggal_simulasi'] ?? date('Y-m-d')));
$isMutasiInput = !empty($input['is_mutasi']);

if ($mode === 'manual') {
    // Mode Simulasi Manual
    $njkb = (float)($input['njkb'] ?? 0);
    $bobot = (float)($input['bobot'] ?? 1.0);
    $njub = (float)($input['njub'] ?? 0);
    $kGuna = strtoupper(trim((string)($input['k_guna'] ?? 'PRIBADI')));
    $isRoda23 = !empty($input['is_roda23']);
    $inputJenis = strtoupper(trim((string)($input['jenis_kendaraan'] ?? '')));
    if ($inputJenis !== '') {
        $jenisKend = $inputJenis;
    } else {
        $jenisKend = $isRoda23 ? 'SEPEDA MOTOR' : 'MINIBUS';
    }
    $kJr = $isRoda23 ? 'SEPEDA MOTOR' : $jenisKend;
    $isiCylinder = (int)($input['isi_cylinder'] ?? ($isRoda23 ? 125 : (strpos($jenisKend, 'TRUCK') !== false ? 3500 : 1500)));
    $sdNotice = trim((string)($input['sd_notice'] ?? ''));
    $sdStnk = trim((string)($input['sd_stnk'] ?? ''));
    $uptAsal = trim((string)($input['upt_asal'] ?? 'UPT KUPANG'));

    if ($njkb <= 0) {
        responseJson([
            'status' => 'error',
            'message' => 'Nilai NJKB harus lebih besar dari 0.'
        ], 400);
    }

    $params = [
        'njkb' => $njkb,
        'bobot' => $bobot,
        'njub' => $njub,
        'k_guna' => $kGuna,
        'is_roda23' => $isRoda23,
        'jenis_kendaraan' => $jenisKend,
        'k_jr' => $kJr,
        'isi_cylinder' => $isiCylinder,
        'sd_notice' => $sdNotice,
        'sd_stnk' => $sdStnk,
        'upt_asal' => $uptAsal,
        'is_mutasi' => $isMutasiInput,
        'kanal_bayar' => $kanalBayar,
        'tanggal_simulasi' => $tanggalSimulasi
    ];

    $simulasiResult = hitungKomparasiPenetapan($params);

    responseJson([
        'status' => 'success',
        'mode' => 'manual',
        'data' => [
            'kendaraan' => [
                'nopol' => 'SIMULASI MANUAL',
                'pemilik' => 'Simulasi Tanpa Nopol',
                'alamat' => '-',
                'merk' => trim((string)($input['merk'] ?? ($isRoda23 ? 'HONDA' : 'TOYOTA'))),
                'tipe' => trim((string)($input['tipe'] ?? ($isRoda23 ? 'BEAT' : 'AVANZA'))),
                'tahun' => (int)($input['tahun'] ?? date('Y')),
                'unit' => trim((string)($input['merk'] ?? ($isRoda23 ? 'HONDA' : 'TOYOTA'))) . ' / ' . trim((string)($input['tipe'] ?? ($isRoda23 ? 'BEAT' : 'AVANZA'))) . ' (' . $jenisKend . ')',
                'jenis_kendaraan' => $jenisKend,
                'penggunaan' => $kGuna,
                'sd_notice' => $sdNotice,
                'sd_stnk' => $sdStnk,
                'njkb' => $njkb,
                'bobot' => $bobot,
                'dp_pkb' => round(($njkb + $njub) * $bobot)
            ],
            'simulasi' => $simulasiResult
        ]
    ]);
}

// Mode Nopol (default)
$nopolInput = trim((string)($input['nopol'] ?? $_GET['nopol'] ?? ''));
if ($nopolInput === '') {
    responseJson([
        'status' => 'error',
        'message' => 'Nomor polisi wajib diisi.'
    ], 400);
}

$nopolClean = strtoupper(preg_replace('/\s+/', '', $nopolInput));

// 1. Tarik Data Samsat Pusat
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
        'status' => 'error',
        'message' => 'Gagal terhubung ke server Samsat pusat.'
    ], 502);
}

$apiData = json_decode($response, true);
if (!is_array($apiData) || empty($apiData['Merk'])) {
    responseJson([
        'status' => 'error',
        'message' => 'Data kendaraan dengan nopol tersebut tidak ditemukan di database Samsat.'
    ], 404);
}

// Ekstrak data kendaraan
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
$uptAsal = trim((string)($apiData['UPT_ASAL'] ?? ''));
$rubahBentuk = strtoupper(trim((string)($apiData['RubahBentuk'] ?? '')));

// 2. Cari NJKB & Bobot di Database
$njkb = 0;
$bobot = 0;

$stmt = $conn->prepare('SELECT njkb, bobot FROM njkb_data WHERE merk = ? AND tipe = ? AND tahun_buat = ? LIMIT 1');
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

if ($njkb === 0.0) {
    // Fallback LIKE + tahun
    $merkLike = '%' . $merk . '%';
    $tipeLike = '%' . $tipe . '%';
    $stmt2 = $conn->prepare('SELECT njkb, bobot FROM njkb_data WHERE merk LIKE ? AND tipe LIKE ? AND tahun_buat = ? LIMIT 1');
    if ($stmt2) {
        $stmt2->bind_param('ssi', $merkLike, $tipeLike, $tahun);
        $stmt2->execute();
        $row2 = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();
        if ($row2) {
            $njkb = (float)$row2['njkb'];
            $bobot = (float)$row2['bobot'];
        }
    }
}

if ($njkb === 0.0) {
    // Fallback LIKE tanpa tahun
    $stmt3 = $conn->prepare('SELECT njkb, bobot FROM njkb_data WHERE merk LIKE ? AND tipe LIKE ? ORDER BY tahun_buat DESC LIMIT 1');
    if ($stmt3) {
        $stmt3->bind_param('ss', $merkLike, $tipeLike);
        $stmt3->execute();
        $row3 = $stmt3->get_result()->fetch_assoc();
        $stmt3->close();
        if ($row3) {
            $njkb = (float)$row3['njkb'];
            $bobot = (float)$row3['bobot'];
        }
    }
}

// 3. NJUB jika ada rubah bentuk
$njub = 0;
if ($rubahBentuk !== '' && $rubahBentuk !== '-') {
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

// Jalankan komparasi simulasi penetapan
$params = [
    'njkb' => $njkb,
    'bobot' => $bobot,
    'njub' => $njub,
    'k_guna' => $kGuna,
    'jenis_kendaraan' => $jenisKend,
    'k_jr' => $kJr,
    'isi_cylinder' => $isiCylinder,
    'sd_notice' => $sdNotice,
    'sd_stnk' => $sdStnk,
    'upt_asal' => $uptAsal,
    'is_mutasi' => $isMutasiInput,
    'kanal_bayar' => $kanalBayar,
    'tanggal_simulasi' => $tanggalSimulasi
];

$simulasiResult = hitungKomparasiPenetapan($params);

responseJson([
    'status' => 'success',
    'mode' => 'nopol',
    'data' => [
        'kendaraan' => [
            'nopol' => $nopolClean,
            'pemilik' => $nama,
            'alamat' => $alamat,
            'merk' => $merk,
            'tipe' => $tipe,
            'tahun' => $tahun,
            'unit' => trim($merk . ' / ' . $tipe),
            'jenis_kendaraan' => $jenisKend,
            'penggunaan' => $kGuna,
            'sd_notice' => $sdNotice,
            'sd_stnk' => $sdStnk,
            'upt_asal' => $uptAsal,
            'njkb' => round($njkb),
            'bobot' => $bobot,
            'dp_pkb' => round($dpPkb)
        ],
        'simulasi' => $simulasiResult
    ]
]);
