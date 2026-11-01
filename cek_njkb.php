<?php
// cek_njkb.php
require 'db.php';

$hasilPencarian = [];
$pencarianDilakukan = false;

$mode = trim($_GET['mode'] ?? '');
$nopolInput = trim($_GET['nopol'] ?? '');
$jenisInput = trim($_GET['jenis'] ?? '');
$merekInput = trim($_GET['merek'] ?? '');
$tipeInput  = trim($_GET['tipe'] ?? '');

// Tentukan mode pencarian aktif
if (!empty($nopolInput)) {
    $mode = 'plat';
} elseif (!empty($merekInput) || !empty($tipeInput)) {
    $mode = 'merek';
} else {
    $mode = ($mode === 'plat') ? 'plat' : 'merek';
}

$hasilPlat = null;
$errorPlat = '';
$pencarianPlatDilakukan = false;

// 1. PENCARIAN BERDASARKAN PLAT NOMOR
if (!empty($nopolInput)) {
    $pencarianPlatDilakukan = true;
    $nopolClean = strtoupper(preg_replace('/\s+/', '', $nopolInput));

    // Ambil URL API Samsat dari database konfigurasi
    $getNopolUrl = 'https://dash.bpad.nttprov.go.id/pajak/webdtd/pendataan/core/php/getnopol.php';
    $resCfg = $conn->query("SELECT nilai FROM konfigurasi WHERE kunci = 'api_url_samsat' LIMIT 1");
    if ($resCfg && $rowCfg = $resCfg->fetch_assoc()) {
        if (!empty($rowCfg['nilai'])) {
            $getNopolUrl = $rowCfg['nilai'];
        }
    }

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
        CURLOPT_TIMEOUT => 25,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        $errorPlat = 'Gagal terhubung ke server Samsat pusat' . ($curlErr ? ": $curlErr" : '.');
    } else {
        $apiData = json_decode($response, true);
        if (!is_array($apiData) || empty($apiData['Merk'])) {
            $errorPlat = 'Data kendaraan dengan nomor polisi "' . htmlspecialchars($nopolInput) . '" tidak ditemukan di database Samsat.';
        } else {
            $merk = trim((string)($apiData['Merk'] ?? ''));
            $tipe = trim((string)($apiData['Type'] ?? ''));
            $tahun = (int)($apiData['TahunPembuatan'] ?? 0);
            $jenisKend = strtoupper(trim((string)($apiData['JenisKendaraan'] ?? '')));
            $isiCylinder = (int)($apiData['IsiCylinder'] ?? 0);
            $kGuna = trim((string)($apiData['K_GUNA'] ?? 'PRIBADI'));
            $rubahBentuk = strtoupper(trim((string)($apiData['RubahBentuk'] ?? '')));
            $warnaTnkb = trim((string)($apiData['WarnaTNKB'] ?? $apiData['WARNA_TNKB'] ?? 'HITAM'));
            $sdNotice = trim((string)($apiData['SD_NOTICE'] ?? ''));
            $sdStnk = trim((string)($apiData['SD_STNK'] ?? ''));

            $njkb = 0.0;
            $bobot = 1.0;
            $namaModelNjkb = '';

            // Cari NJKB Eksak
            $stmt = $conn->prepare('SELECT tahun_buat, merk, tipe, njkb, bobot FROM njkb_data WHERE merk = ? AND tipe = ? AND tahun_buat = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('ssi', $merk, $tipe, $tahun);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res && $row = $res->fetch_assoc()) {
                    $njkb = (float)$row['njkb'];
                    $bobot = (float)$row['bobot'];
                    $namaModelNjkb = $row['merk'] . ' ' . $row['tipe'];
                }
                $stmt->close();
            }

            // Fallback LIKE jika belum ketemu
            if ($njkb === 0.0) {
                $stmt2 = $conn->prepare('SELECT tahun_buat, merk, tipe, njkb, bobot FROM njkb_data WHERE merk LIKE ? AND tipe LIKE ? ORDER BY ABS(tahun_buat - ?) ASC LIMIT 1');
                if ($stmt2) {
                    $mLike = '%' . $merk . '%';
                    $tLike = '%' . $tipe . '%';
                    $stmt2->bind_param('ssi', $mLike, $tLike, $tahun);
                    $stmt2->execute();
                    $res2 = $stmt2->get_result();
                    if ($res2 && $row2 = $res2->fetch_assoc()) {
                        $njkb = (float)$row2['njkb'];
                        $bobot = (float)$row2['bobot'];
                        $namaModelNjkb = $row2['merk'] . ' ' . $row2['tipe'];
                    }
                    $stmt2->close();
                }
            }

            // Fallback Kata Pertama Tipe
            if ($njkb === 0.0 && !empty($merk)) {
                $tipeWords = explode(' ', $tipe);
                $firstWord = $tipeWords[0] ?? '';
                if (strlen($firstWord) >= 3) {
                    $stmt3 = $conn->prepare('SELECT tahun_buat, merk, tipe, njkb, bobot FROM njkb_data WHERE merk LIKE ? AND tipe LIKE ? ORDER BY ABS(tahun_buat - ?) ASC LIMIT 1');
                    if ($stmt3) {
                        $mLike = '%' . $merk . '%';
                        $tLike = '%' . $firstWord . '%';
                        $stmt3->bind_param('ssi', $mLike, $tLike, $tahun);
                        $stmt3->execute();
                        $res3 = $stmt3->get_result();
                        if ($res3 && $row3 = $res3->fetch_assoc()) {
                            $njkb = (float)$row3['njkb'];
                            $bobot = (float)$row3['bobot'];
                            $namaModelNjkb = $row3['merk'] . ' ' . $row3['tipe'];
                        }
                        $stmt3->close();
                    }
                }
            }

            // Cek NJUB jika ada ubah bentuk
            $njub = 0;
            if ($rubahBentuk !== '' && $rubahBentuk !== '-') {
                $tahunCari = $tahun < 2006 ? 2006 : $tahun;
                try {
                    $stmtNjub = $conn->prepare('SELECT nilai FROM njmkb_data WHERE rubah_bentuk = ? AND jenis_dasar = ? AND tahun = ? LIMIT 1');
                    if ($stmtNjub) {
                        $stmtNjub->bind_param('ssi', $rubahBentuk, $jenisKend, $tahunCari);
                        $stmtNjub->execute();
                        $resNjub = $stmtNjub->get_result();
                        if ($resNjub && $rowNjub = $resNjub->fetch_assoc()) {
                            $njub = (int)$rowNjub['nilai'];
                        }
                        $stmtNjub->close();
                    }
                } catch (Exception $e) {
                    // Abaikan jika tabel tidak ada
                }
            }

            $dasarPkb = ($njkb * ($bobot > 0 ? $bobot : 1.0)) + $njub;

            $hasilPlat = [
                'nopol' => $nopolClean,
                'merk' => $merk,
                'tipe' => $tipe,
                'tahun' => $tahun,
                'jenisKend' => $jenisKend,
                'isiCylinder' => $isiCylinder,
                'kGuna' => $kGuna,
                'rubahBentuk' => $rubahBentuk,
                'warnaTnkb' => $warnaTnkb,
                'sdNotice' => $sdNotice,
                'sdStnk' => $sdStnk,
                'njkb' => $njkb,
                'bobot' => $bobot,
                'njub' => $njub,
                'dasarPkb' => $dasarPkb,
                'namaModelNjkb' => $namaModelNjkb
            ];
        }
    }
}

// 2. PENCARIAN BERDASARKAN MEREK & TIPE
// Ambil daftar Merek yang unik
$listMerk = [];
$resMerk = $conn->query("SELECT DISTINCT merk FROM njkb_data ORDER BY merk ASC");
if ($resMerk) {
    while ($row = $resMerk->fetch_assoc()) {
        $listMerk[] = $row['merk'];
    }
}

// Jika form merek disubmit
if ($mode === 'merek' && (!empty($merekInput) || !empty($tipeInput))) {
    $pencarianDilakukan = true;
    $query = "
        SELECT tahun_buat, merk, tipe, njkb, bobot
        FROM njkb_data
        WHERE 1=1
    ";

    $params = [];
    $types = "";

    if (!empty($merekInput)) {
        $query .= " AND merk LIKE ?";
        $params[] = "%" . $merekInput . "%";
        $types .= "s";
    }

    if (!empty($tipeInput)) {
        $query .= " AND tipe LIKE ?";
        $params[] = "%" . $tipeInput . "%";
        $types .= "s";
    }

    $query .= " ORDER BY tahun_buat ASC";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $hasilPencarian[] = $row;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="theme-color"
        content="#2563eb"
    >

    <title>Data NJKB Kendaraan | BAPENDA NTT</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/cek_njkb.css?v=<?= filemtime(__DIR__ . '/assets/css/cek_njkb.css') ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>


<body>

<div class="container">


    <!-- TOP BAR -->

    <div class="topbar">

        <a
            href="index.php"
            class="back-link"
        >

            <span class="back-icon"><i class="fa-solid fa-arrow-left"></i></span>

            Kembali ke Portal

        </a>


        <div class="page-label">

            <span class="status-dot"></span>

            Data NJKB

        </div>

    </div>


    <!-- HERO -->

    <header class="hero">

        <div class="hero-icon"><i class="fa-solid fa-file-invoice-dollar" style="font-size: 28px;"></i></div>


        <h1>
            Data NJKB Kendaraan
        </h1>


        <p class="subtitle">
            Cari Nilai Jual Kendaraan Bermotor berdasarkan Plat Nomor atau berdasarkan merek, tipe, dan tahun pembuatan kendaraan.
        </p>

    </header>


    <!-- SEARCH -->

    <section class="search-card">

        <div class="search-header">

            <div class="search-header-icon"><i class="fa-solid fa-magnifying-glass"></i></div>

            <div>

                <div class="search-title">
                    Cari Data NJKB Kendaraan
                </div>

                <div class="search-description">
                    Pilih metode pencarian: melalui Plat Nomor (data langsung dari Samsat) atau cari manual berdasarkan Merek &amp; Tipe.
                </div>

            </div>

        </div>

        <!-- TABS PENCARIAN -->
        <div class="njkb-tabs">
            <button
                type="button"
                class="njkb-tab-btn <?= $mode === 'plat' ? 'active' : '' ?>"
                id="btn-tab-plat"
                onclick="switchNjkbTab('plat')"
            >
                <i class="fa-solid fa-id-card"></i> Cek Berdasarkan Plat Nomor
            </button>

            <button
                type="button"
                class="njkb-tab-btn <?= $mode !== 'plat' ? 'active' : '' ?>"
                id="btn-tab-merek"
                onclick="switchNjkbTab('merek')"
            >
                <i class="fa-solid fa-car"></i> Cari Berdasarkan Merek &amp; Tipe
            </button>
        </div>


        <!-- TAB PANE 1: PLAT NOMOR -->
        <div class="njkb-tab-pane <?= $mode === 'plat' ? 'active' : '' ?>" id="pane-plat">
            <form
                method="GET"
                action=""
                autocomplete="off"
            >
                <input type="hidden" name="mode" value="plat">

                <div class="plat-form-grid">
                    <div class="form-group">
                        <label for="nopol">
                            Nomor Polisi (Plat Kendaraan)
                        </label>

                        <div class="input-wrapper">
                            <input
                                type="text"
                                id="nopol"
                                name="nopol"
                                value="<?= htmlspecialchars($nopolInput) ?>"
                                placeholder="Contoh: DH 1234 AB atau EB 5678 CD"
                                required
                                autocomplete="off"
                                style="text-transform: uppercase; font-weight: 700; letter-spacing: 1px;"
                            >

                            <span class="input-icon">
                                <i class="fa-solid fa-id-card"></i>
                            </span>
                        </div>

                        <p class="input-hint">
                            <i class="fa-solid fa-circle-info" style="margin-right: 4px;"></i> Masukkan plat nomor wilayah NTT (DH, EB, ED). Sistem akan otomatis menarik spesifikasi kendaraan dan mencocokkan Nilai Jual (NJKB).
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="search-button full-btn"
                    >
                        <i class="fa-solid fa-magnifying-glass"></i> Cek Nilai Jual Plat
                    </button>
                </div>
            </form>
        </div>


        <!-- TAB PANE 2: MEREK & TIPE -->
        <div class="njkb-tab-pane <?= $mode !== 'plat' ? 'active' : '' ?>" id="pane-merek">
            <form
                method="GET"
                action=""
                autocomplete="off"
            >
                <input type="hidden" name="mode" value="merek">

                <div class="form-grid">

                    <!-- MEREK -->
                    <div class="form-group">

                        <label for="merek">
                            Merek Kendaraan
                        </label>

                        <div class="input-wrapper">

                            <input
                                type="text"
                                id="merek"
                                name="merek"
                                value="<?= htmlspecialchars($merekInput) ?>"
                                placeholder="Contoh: Toyota, Honda"
                                required
                                autocomplete="off"
                            >

                            <span class="input-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M5 17h14"/>
                                    <path d="M6 17l1-6h10l1 6"/>
                                    <path d="M8 11l1.2-3h5.6l1.2 3"/>
                                    <circle cx="8" cy="17" r="1.5"/>
                                    <circle cx="16" cy="17" r="1.5"/>
                                </svg>
                            </span>

                        </div>

                    </div>


                    <!-- TIPE -->
                    <div class="form-group">

                        <label for="tipe">
                            Tipe Kendaraan
                            <span class="optional">Opsional</span>
                        </label>

                        <div class="input-wrapper">

                            <input
                                type="text"
                                id="tipe"
                                name="tipe"
                                value="<?= htmlspecialchars($tipeInput) ?>"
                                placeholder="Contoh: Avanza, Beat"
                                autocomplete="off"
                            >

                            <span class="input-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <rect
                                        x="3"
                                        y="4"
                                        width="18"
                                        height="16"
                                        rx="2"
                                    />
                                    <path d="M7 8h10"/>
                                    <path d="M7 12h6"/>
                                    <path d="M7 16h3"/>
                                </svg>
                            </span>

                        </div>

                    </div>


                    <!-- BUTTON -->
                    <button
                        type="submit"
                        class="search-button"
                    >
                        <i class="fa-solid fa-magnifying-glass"></i> Cari Data NJKB
                    </button>

                </div>

            </form>
        </div>

    </section>


    <!-- =========================================
       RESULT: HASIL PENCARIAN PLAT NOMOR
    ========================================= -->
    <?php if ($pencarianPlatDilakukan): ?>

        <?php if ($hasilPlat): ?>
            <section class="plat-result-card">
                <!-- Header Kartu Plat -->
                <div class="plat-card-header">
                    <div class="plat-badge-container">
                        <div class="indonesia-plat">
                            <span><?= htmlspecialchars($hasilPlat['nopol']) ?></span>
                        </div>

                        <div class="plat-vehicle-summary">
                            <h2 class="plat-vehicle-title">
                                <?= htmlspecialchars($hasilPlat['merk']) ?> <?= htmlspecialchars($hasilPlat['tipe']) ?>
                            </h2>

                            <div class="plat-tags">
                                <span class="tag-item"><i class="fa-solid fa-calendar"></i> Tahun <?= htmlspecialchars($hasilPlat['tahun']) ?></span>
                                <span class="tag-item"><i class="fa-solid fa-car-side"></i> <?= htmlspecialchars($hasilPlat['jenisKend']) ?></span>
                                <?php if ($hasilPlat['isiCylinder'] > 0): ?>
                                    <span class="tag-item"><i class="fa-solid fa-gauge-high"></i> <?= htmlspecialchars($hasilPlat['isiCylinder']) ?> CC</span>
                                <?php endif; ?>
                                <span class="tag-item tag-guna"><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($hasilPlat['kGuna']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Highlight Nilai Jual & Bobot -->
                <div class="njkb-highlight-grid">
                    <div class="njkb-box primary-box">
                        <div class="njkb-box-label">
                            <i class="fa-solid fa-sack-dollar"></i> Nilai Jual Kendaraan (NJKB)
                        </div>
                        <div class="njkb-box-value">
                            Rp <?= number_format($hasilPlat['njkb'], 0, ',', '.') ?>
                        </div>
                        <div class="njkb-box-desc">
                            <?php if (!empty($hasilPlat['namaModelNjkb'])): ?>
                                Acuan: <?= htmlspecialchars($hasilPlat['namaModelNjkb']) ?>
                            <?php else: ?>
                                Nilai jual acuan penetapan PKB
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="njkb-box">
                        <div class="njkb-box-label">
                            <i class="fa-solid fa-weight-hanging"></i> Bobot Kendaraan
                        </div>
                        <div class="njkb-box-value">
                            <?= htmlspecialchars(number_format($hasilPlat['bobot'], 2)) ?>
                        </div>
                        <div class="njkb-box-desc">
                            Koefisien bobot jalan &amp; dampak
                        </div>
                    </div>

                    <div class="njkb-box success-box">
                        <div class="njkb-box-label">
                            <i class="fa-solid fa-calculator"></i> Dasar Pengenaan PKB
                        </div>
                        <div class="njkb-box-value">
                            Rp <?= number_format($hasilPlat['dasarPkb'], 0, ',', '.') ?>
                        </div>
                        <div class="njkb-box-desc">
                            (NJKB × Bobot)<?= $hasilPlat['njub'] > 0 ? ' + NJUB' : '' ?>
                        </div>
                    </div>
                </div>

                <?php if ($hasilPlat['njub'] > 0): ?>
                    <div class="njub-notice">
                        <i class="fa-solid fa-circle-info" style="font-size: 18px;"></i>
                        <div>
                            Kendaraan memiliki <strong>Ubah Bentuk (<?= htmlspecialchars($hasilPlat['rubahBentuk']) ?>)</strong> dengan Nilai Jual Ubah Bentuk (NJUB) sebesar <strong>Rp <?= number_format($hasilPlat['njub'], 0, ',', '.') ?></strong>.
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($hasilPlat['njkb'] == 0): ?>
                    <div class="njkb-warning">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size: 18px;"></i>
                        <div>
                            Data kendaraan terdaftar di Samsat pusat, namun nilai tabel NJKB untuk tipe <strong><?= htmlspecialchars($hasilPlat['tipe']) ?> (Tahun <?= htmlspecialchars($hasilPlat['tahun']) ?>)</strong> belum ditemukan pada basis data NJKB lokal.
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Rincian Spesifikasi Kendaraan -->
                <div class="plat-details-table">
                    <div class="detail-row">
                        <div class="detail-label"><i class="fa-solid fa-id-badge"></i> Nomor Polisi</div>
                        <div class="detail-val"><strong><?= htmlspecialchars($hasilPlat['nopol']) ?></strong></div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label"><i class="fa-solid fa-industry"></i> Merek Kendaraan</div>
                        <div class="detail-val"><?= htmlspecialchars($hasilPlat['merk']) ?></div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label"><i class="fa-solid fa-car"></i> Tipe / Model</div>
                        <div class="detail-val"><?= htmlspecialchars($hasilPlat['tipe']) ?></div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label"><i class="fa-solid fa-calendar-check"></i> Tahun Pembuatan</div>
                        <div class="detail-val"><?= htmlspecialchars($hasilPlat['tahun']) ?></div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label"><i class="fa-solid fa-shapes"></i> Jenis Kendaraan</div>
                        <div class="detail-val"><?= htmlspecialchars($hasilPlat['jenisKend']) ?></div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label"><i class="fa-solid fa-gauge"></i> Isi Silinder</div>
                        <div class="detail-val"><?= htmlspecialchars($hasilPlat['isiCylinder']) ?> CC</div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label"><i class="fa-solid fa-users"></i> Penggunaan / Fungsi</div>
                        <div class="detail-val"><?= htmlspecialchars($hasilPlat['kGuna']) ?></div>
                    </div>

                    <?php if (!empty($hasilPlat['sdNotice'])): ?>
                        <div class="detail-row">
                            <div class="detail-label"><i class="fa-solid fa-clock"></i> Jatuh Tempo Pajak (Notice)</div>
                            <div class="detail-val"><?= htmlspecialchars($hasilPlat['sdNotice']) ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($hasilPlat['sdStnk'])): ?>
                        <div class="detail-row">
                            <div class="detail-label"><i class="fa-solid fa-calendar-days"></i> Akhir Masa Berlaku STNK</div>
                            <div class="detail-val"><?= htmlspecialchars($hasilPlat['sdStnk']) ?></div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tombol Tindakan Cepat -->
                <div class="plat-actions">
                    <a href="hitung_pajak.php?nopol=<?= urlencode($hasilPlat['nopol']) ?>" class="plat-btn-primary">
                        <i class="fa-solid fa-calculator"></i> Hitung Estimasi Pajak Kendaraan Ini
                    </a>

                    <a href="simulasi_penetapan.php?nopol=<?= urlencode($hasilPlat['nopol']) ?>" class="plat-btn-secondary">
                        <i class="fa-solid fa-file-invoice"></i> Buka Simulasi Penetapan
                    </a>
                </div>
            </section>

        <?php else: ?>

            <section class="result-card">
                <div class="empty-state">
                    <div class="empty-icon" style="color: #ef4444; background: #fee2e2;">
                        <i class="fa-solid fa-triangle-exclamation" style="font-size: 28px;"></i>
                    </div>

                    <div class="empty-title">
                        Data Kendaraan Tidak Ditemukan
                    </div>

                    <p class="empty-text">
                        <?= htmlspecialchars($errorPlat) ?>
                    </p>
                </div>
            </section>

        <?php endif; ?>

    <?php endif; ?>


    <!-- =========================================
       RESULT: HASIL PENCARIAN MEREK & TIPE
    ========================================= -->
    <?php if ($pencarianDilakukan): ?>

        <section class="result-card">

            <!-- RESULT HEADER -->
            <div class="result-header">

                <div class="result-heading">

                    <div class="result-icon"><i class="fa-solid fa-check-circle" style="font-size: 20px;"></i></div>

                    <div>

                        <div class="result-title">
                            Hasil Pencarian
                        </div>

                        <div class="result-query">

                            <?php if (!empty($merekInput)): ?>
                                <?= htmlspecialchars($merekInput) ?>
                            <?php endif; ?>

                            <?php if (!empty($tipeInput)): ?>
                                · <?= htmlspecialchars($tipeInput) ?>
                            <?php endif; ?>

                        </div>

                    </div>

                </div>

                <?php if (!empty($hasilPencarian)): ?>
                    <div class="result-count">
                        <?= count($hasilPencarian) ?> data ditemukan
                    </div>
                <?php endif; ?>

            </div>


            <?php if (!empty($hasilPencarian)): ?>

                <!-- TABLE -->
                <div class="table-wrapper">

                    <table>

                        <thead>
                            <tr>
                                <th>Tahun</th>
                                <th>Merek</th>
                                <th>Tipe Kendaraan</th>
                                <th>NJKB</th>
                                <th>Bobot</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($hasilPencarian as $row): ?>
                            <tr>
                                <td data-label="Tahun">
                                    <span class="year">
                                        <?= htmlspecialchars($row['tahun_buat']) ?>
                                    </span>
                                </td>

                                <td data-label="Merek">
                                    <span class="brand-name">
                                        <?= htmlspecialchars($row['merk']) ?>
                                    </span>
                                </td>

                                <td data-label="Tipe">
                                    <span class="vehicle-type">
                                        <?= htmlspecialchars($row['tipe']) ?>
                                    </span>
                                </td>

                                <td data-label="NJKB">
                                    <span class="price">
                                        Rp <?= number_format($row['njkb'], 0, ',', '.') ?>
                                    </span>
                                </td>

                                <td data-label="Bobot">
                                    <span class="weight-badge">
                                        <?= htmlspecialchars($row['bobot']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>

                    </table>

                </div>

                <div class="result-footer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 11v5"/>
                        <path d="M12 8h.01"/>
                    </svg>
                    Data ditampilkan berdasarkan tahun pembuatan dari yang terlama hingga terbaru.
                </div>

            <?php else: ?>

                <!-- EMPTY STATE -->
                <div class="empty-state">

                    <div class="empty-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7"/>
                            <path d="m20 20-4-4"/>
                            <path d="M8 8l6 6"/>
                            <path d="m14 8-6 6"/>
                        </svg>
                    </div>

                    <div class="empty-title">
                        Data tidak ditemukan
                    </div>

                    <p class="empty-text">
                        Tidak ada data NJKB yang sesuai dengan merek atau tipe kendaraan yang Anda cari. Coba gunakan kata kunci yang lebih umum.
                    </p>

                </div>

            <?php endif; ?>

        </section>

    <?php endif; ?>


    <!-- FOOTER -->

    <footer class="page-footer">

        &copy; <?= date('Y') ?>

        <strong>
            Badan Pendapatan dan Aset Daerah NTT
        </strong>

        <br>

        Portal Layanan Informasi Pajak &amp; Aset Daerah

    </footer>


</div>

<script>
function switchNjkbTab(tab) {
    const btnPlat = document.getElementById('btn-tab-plat');
    const btnMerek = document.getElementById('btn-tab-merek');
    const panePlat = document.getElementById('pane-plat');
    const paneMerek = document.getElementById('pane-merek');

    if (tab === 'plat') {
        btnPlat.classList.add('active');
        btnMerek.classList.remove('active');
        panePlat.classList.add('active');
        paneMerek.classList.remove('active');
        const nopolInput = document.getElementById('nopol');
        if (nopolInput) {
            nopolInput.focus();
        }
    } else {
        btnMerek.classList.add('active');
        btnPlat.classList.remove('active');
        paneMerek.classList.add('active');
        panePlat.classList.remove('active');
        const merekInput = document.getElementById('merek');
        if (merekInput) {
            merekInput.focus();
        }
    }
}
</script>

</body>

</html>

