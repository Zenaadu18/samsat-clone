
<?php
// pajak_massal.php

// ============================================================================
// LOGIKA PEMROSESAN FILE & GENERATE CSV
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_nopol'])) {

    set_time_limit(0);

    $fileTmp = $_FILES['file_nopol']['tmp_name'];

    if (!file_exists($fileTmp)) {
        die("File tidak ditemukan.");
    }

    $nopolList = file(
        $fileTmp,
        FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
    );

    // Header download CSV
    header('Content-Type: text/csv; charset=utf-8');
    header(
        'Content-Disposition: attachment; filename="Rekap_Pajak_Massal_' .
        date('Ymd_His') .
        '.csv"'
    );

    $fp = fopen('php://output', 'w');

    // BOM UTF-8 untuk Excel
    fwrite($fp, "\xEF\xBB\xBF");

    // Header CSV
    fputcsv($fp, [
        'NOPOL',
        'Nama Pemilik',
        'Alamat',
        'Kelurahan',
        'Kecamatan',
        'Masa STNK',
        'Masa Notice',
        'PKB Berjalan',
        'PKB Tunggakan',
        'Denda PKB',
        'Opsen PKB',
        'Denda Opsen',
        'SWDKLLJ',
        'Denda SWDKLLJ',
        'Biaya STNK',
        'Biaya TNKB',
        'Total Tagihan',
        'Status Sistem'
    ], ';');

    // URL API dinamis
    $protocol =
        isset($_SERVER['HTTPS']) &&
        $_SERVER['HTTPS'] === 'on'
            ? 'https'
            : 'http';

    $host = $_SERVER['HTTP_HOST'];

    $dir = dirname($_SERVER['REQUEST_URI']);

    $dir =
        ($dir === '/' || $dir === '\\')
            ? ''
            : $dir;

    $apiUrlBase =
        $protocol .
        '://' .
        $host .
        $dir .
        '/api_tax.php?nopol=';


    foreach ($nopolList as $nopol) {

        $nopolClean = trim($nopol);

        if ($nopolClean === '') {
            continue;
        }

        $apiUrl =
            $apiUrlBase .
            urlencode($nopolClean);

        $ch = curl_init($apiUrl);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0
        ]);

        $response = curl_exec($ch);

        $httpCode =
            curl_getinfo(
                $ch,
                CURLINFO_HTTP_CODE
            );

        curl_close($ch);


        if ($httpCode === 200 && $response) {

            $result =
                json_decode(
                    $response,
                    true
                );

            if (isset($result[0])) {

                $d = $result[0];

                fputcsv($fp, [

                    $d['NOPOL'] ?? '',
                    $d['NamaPemilik'] ?? '',
                    $d['Alamat'] ?? '',
                    $d['Kelurahan'] ?? '',
                    $d['Kecamatan'] ?? '',
                    $d['STNK'] ?? '',
                    $d['NOTICE'] ?? '',

                    $d['PKB_BERJALAN'] ?? 0,
                    $d['PKB_TUNGGAKAN'] ?? 0,
                    $d['DENDA_PKB'] ?? 0,

                    $d['OPSEN_PKB'] ?? 0,
                    $d['DENDA_OPSEN'] ?? 0,

                    $d['SWDKLLJ'] ?? 0,
                    $d['DENDA_SWDKLLJ'] ?? 0,

                    $d['BIAYA_STNK'] ?? 0,
                    $d['BIAYA_TNKB'] ?? 0,

                    $d['TOTAL_TAGIHAN'] ?? 0,

                    'SUKSES'

                ], ';');

            } else {

                fputcsv($fp, [
                    $nopolClean,
                    '-',
                    '-',
                    '-',
                    '-',
                    '-',
                    '-',
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    'GAGAL (Format JSON Salah)'
                ], ';');
            }

        } else {

            fputcsv($fp, [
                $nopolClean,
                '-',
                '-',
                '-',
                '-',
                '-',
                '-',
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                'GAGAL (API Error/Kendaraan Tidak Ditemukan)'
            ], ';');
        }

        // Jeda request
        usleep(300000);
    }

    fclose($fp);

    exit;
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

    <title>
        Pajak Massal | BAPENDA NTT
    </title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <link rel="stylesheet" href="assets/css/pajak_massal.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>


<body>

<div class="container">


    <!-- =========================================
         TOPBAR
    ========================================= -->

    <div class="topbar">

        <a
            href="index.php"
            class="back-link"
        >

            <span class="back-icon"><i class="fa-solid fa-arrow-left"></i></span>

            Kembali ke Portal

        </a>


        <div class="page-badge">

            <span class="badge-dot"></span>

            Layanan Pajak Massal

        </div>

    </div>


    <!-- =========================================
         HERO
    ========================================= -->

    <header class="hero">

        <div class="hero-icon"><i class="fa-solid fa-layer-group" style="font-size: 28px;"></i></div>


        <h1>
            Pengecekan Pajak Massal
        </h1>


        <p class="subtitle">
            Proses banyak nomor polisi sekaligus dan
            dapatkan rekap data pajak kendaraan dalam
            format CSV yang siap dibuka menggunakan Excel.
        </p>

    </header>


    <!-- =========================================
         MAIN CARD
    ========================================= -->

    <main class="card">

        <div class="card-body">


            <!-- STEPS -->

            <div class="steps">

                <div class="step">

                    <div class="step-number">
                        1
                    </div>

                    <div class="step-text">
                        Pilih File
                    </div>

                </div>


                <div class="step">

                    <div class="step-number">
                        2
                    </div>

                    <div class="step-text">
                        Proses Data
                    </div>

                </div>


                <div class="step">

                    <div class="step-number">
                        3
                    </div>

                    <div class="step-text">
                        Unduh Rekap
                    </div>

                </div>

            </div>


            <!-- FORM -->

            <form
                method="POST"
                action=""
                enctype="multipart/form-data"
                id="massForm"
            >


                <!-- UPLOAD -->

                <div
                    class="upload-box"
                    id="dropArea"
                >

                    <div class="upload-icon"><i class="fa-solid fa-cloud-arrow-up" style="font-size: 32px;"></i></div>


                    <div class="upload-title">
                        Pilih atau tarik file ke sini
                    </div>


                    <div class="upload-description">
                        Upload file teks berisi daftar
                        nomor polisi kendaraan.
                    </div>


                    <div class="upload-hint">
                        <i class="fa-solid fa-file-lines"></i>

                        .TXT · 1 NOPOL per baris

                    </div>


                    <input
                        type="file"
                        name="file_nopol"
                        id="fileNopol"
                        accept=".txt,text/plain"
                        required
                    >

                </div>


                <!-- FILE INFO -->

                <div
                    class="file-info"
                    id="fileInfo"
                >

                    <div class="file-icon"><i class="fa-solid fa-file-check" style="font-size: 20px;"></i></div>


                    <div class="file-details">

                        <div
                            class="file-name"
                            id="fileName"
                        >
                            -
                        </div>

                        <div
                            class="file-meta"
                            id="fileMeta"
                        >
                            -
                        </div>

                    </div>


                    <button
                        type="button"
                        class="remove-file"
                        id="removeFile"
                        title="Hapus file"
                        aria-label="Hapus file"
                    >

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >

                            <line
                                x1="18"
                                y1="6"
                                x2="6"
                                y2="18"
                            />

                            <line
                                x1="6"
                                y1="6"
                                x2="18"
                                y2="18"
                            />

                        </svg>

                    </button>

                </div>


                <!-- ACTION -->

                <div class="action-area">

                    <button
                        type="submit"
                        id="btnProses"
                        class="process-button"
                        disabled
                    >

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >

                            <path d="M12 3v12"/>
                            <path d="m7 10 5 5 5-5"/>
                            <path d="M5 21h14"/>

                        </svg>

                        Proses &amp; Unduh Laporan

                    </button>

                </div>


                <!-- NOTICE -->

                <div class="notice">

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >

                        <circle
                            cx="12"
                            cy="12"
                            r="9"
                        />

                        <line
                            x1="12"
                            y1="8"
                            x2="12"
                            y2="12"
                        />

                        <line
                            x1="12"
                            y1="16"
                            x2="12.01"
                            y2="16"
                        />

                    </svg>


                    <div>

                        Proses dapat membutuhkan waktu
                        beberapa saat tergantung jumlah
                        nomor polisi. Jangan menutup halaman
                        selama proses berlangsung.

                    </div>

                </div>


            </form>

        </div>

    </main>


    <!-- FOOTER -->

    <footer class="footer">

        &copy; <?= date('Y') ?>

        <strong>
            Badan Pendapatan dan Aset Daerah NTT
        </strong>

        <br>

        Portal Layanan Informasi Pajak &amp; Aset Daerah

    </footer>


</div>


<script src="assets/js/pajak_massal.js"></script>

</body>

</html>

