
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

@extends('layouts.app')
@section('title', 'Cek Pajak Massal')

@push('styles')
<style>

        :root {

            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-soft: #eff6ff;

            --background: #f6f8fc;

            --card: rgba(255,255,255,.95);

            --text: #172033;
            --text-soft: #64748b;
            --text-muted: #94a3b8;

            --border: #e2e8f0;

            --success: #059669;
            --success-soft: #ecfdf5;

            --warning: #d97706;
            --warning-soft: #fffbeb;

            --danger: #dc2626;

            --radius: 22px;

            --shadow:
                0 18px 50px
                rgba(15,23,42,.08);
        }


        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        html {
            scroll-behavior: smooth;
        }


        body {

            min-height: 100vh;

            font-family:
                'Outfit',
                sans-serif;

            color: var(--text);

            background:

                radial-gradient(
                    circle at 8% 0%,
                    rgba(37,99,235,.11),
                    transparent 30%
                ),

                radial-gradient(
                    circle at 95% 10%,
                    rgba(96,165,250,.08),
                    transparent 27%
                ),

                linear-gradient(
                    180deg,
                    #f8fafc 0%,
                    #f1f5f9 100%
                );

            padding:
                28px 20px 50px;
        }


        /* =========================================
           CONTAINER
        ========================================= */

        .container {

            width: 100%;

            max-width: 760px;

            margin: 0 auto;
        }


        /* =========================================
           NAVIGATION
        ========================================= */

        .topbar {

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 28px;
        }


        .back-link {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            color: var(--text-soft);

            text-decoration: none;

            font-size: 14px;

            font-weight: 600;

            transition: .2s ease;
        }


        .back-link:hover {

            color: var(--primary);

            transform: translateX(-2px);
        }


        .back-icon {

            width: 34px;
            height: 34px;

            display: flex;

            align-items: center;
            justify-content: center;

            border:
                1px solid var(--border);

            border-radius: 10px;

            background: white;

            color: var(--primary);
        }


        .back-icon svg {

            width: 17px;
            height: 17px;
        }


        .page-badge {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            padding:
                7px 12px;

            border:
                1px solid var(--border);

            border-radius: 999px;

            background:
                rgba(255,255,255,.75);

            color: var(--text-soft);

            font-size: 12px;

            font-weight: 600;
        }


        .badge-dot {

            width: 7px;
            height: 7px;

            border-radius: 50%;

            background: var(--primary);

            box-shadow:
                0 0 0 4px
                rgba(37,99,235,.1);
        }


        /* =========================================
           HERO
        ========================================= */

        .hero {

            margin-bottom: 25px;
        }


        .hero-icon {

            width: 60px;
            height: 60px;

            display: flex;

            align-items: center;
            justify-content: center;

            margin-bottom: 18px;

            border-radius: 18px;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #60a5fa
                );

            color: white;

            box-shadow:
                0 15px 30px
                rgba(37,99,235,.22);
        }


        .hero-icon svg {

            width: 30px;
            height: 30px;
        }


        h1 {

            font-size:
                clamp(28px, 5vw, 37px);

            line-height: 1.12;

            letter-spacing: -.8px;

            font-weight: 800;

            margin-bottom: 9px;
        }


        .subtitle {

            max-width: 620px;

            color: var(--text-soft);

            font-size: 15px;

            line-height: 1.6;
        }


        /* =========================================
           MAIN CARD
        ========================================= */

        .card {

            background: var(--card);

            border:
                1px solid rgba(226,232,240,.9);

            border-radius: var(--radius);

            box-shadow: var(--shadow);

            overflow: hidden;
        }


        .card-body {

            padding: 28px;
        }


        /* =========================================
           PROCESS STEPS
        ========================================= */

        .steps {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 10px;

            margin-bottom: 25px;
        }


        .step {

            display: flex;

            align-items: center;

            gap: 9px;

            padding: 10px;

            border-radius: 12px;

            background: #f8fafc;

            border:
                1px solid #edf2f7;
        }


        .step-number {

            width: 27px;
            height: 27px;

            flex: 0 0 auto;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 8px;

            background: var(--primary-soft);

            color: var(--primary);

            font-size: 11px;

            font-weight: 800;
        }


        .step-text {

            color: #475569;

            font-size: 11px;

            font-weight: 700;
        }


        /* =========================================
           UPLOAD AREA
        ========================================= */

        .upload-box {

            position: relative;

            min-height: 260px;

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            padding: 30px 20px;

            border:
                2px dashed #cbd5e1;

            border-radius: 18px;

            background:
                linear-gradient(
                    180deg,
                    #fafcff,
                    #f8fafc
                );

            cursor: pointer;

            transition:
                border-color .25s ease,
                background .25s ease,
                transform .25s ease;
        }


        .upload-box:hover,
        .upload-box.dragging {

            border-color: var(--primary);

            background:
                linear-gradient(
                    180deg,
                    #eff6ff,
                    #f8fbff
                );

            transform: translateY(-2px);
        }


        .upload-box.has-file {

            border-color: var(--success);

            background:
                linear-gradient(
                    180deg,
                    #ecfdf5,
                    #f8fffc
                );
        }


        input[type="file"] {

            position: absolute;

            inset: 0;

            width: 100%;
            height: 100%;

            opacity: 0;

            cursor: pointer;
        }


        .upload-icon {

            width: 70px;
            height: 70px;

            display: flex;

            align-items: center;
            justify-content: center;

            margin-bottom: 17px;

            border-radius: 20px;

            background: var(--primary-soft);

            color: var(--primary);

            box-shadow:
                0 8px 20px
                rgba(37,99,235,.08);

            transition: .25s ease;
        }


        .upload-box:hover .upload-icon {

            transform:
                translateY(-3px)
                scale(1.04);
        }


        .upload-icon svg {

            width: 32px;
            height: 32px;
        }


        .upload-title {

            color: var(--text);

            font-size: 16px;

            font-weight: 800;

            margin-bottom: 6px;

            text-align: center;
        }


        .upload-description {

            color: var(--text-soft);

            font-size: 13px;

            text-align: center;

            line-height: 1.5;
        }


        .upload-hint {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            margin-top: 13px;

            padding:
                6px 10px;

            border-radius: 8px;

            background: white;

            border:
                1px solid var(--border);

            color: var(--text-muted);

            font-size: 11px;

            font-weight: 600;
        }


        .upload-hint svg {

            width: 13px;
            height: 13px;
        }


        /* =========================================
           FILE INFORMATION
        ========================================= */

        .file-info {

            display: none;

            align-items: center;

            gap: 12px;

            margin-top: 15px;

            padding: 13px 15px;

            border:
                1px solid #bbf7d0;

            border-radius: 13px;

            background: var(--success-soft);
        }


        .file-info.visible {

            display: flex;
        }


        .file-icon {

            width: 38px;
            height: 38px;

            flex: 0 0 auto;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 10px;

            background: white;

            color: var(--success);
        }


        .file-icon svg {

            width: 20px;
            height: 20px;
        }


        .file-details {

            min-width: 0;

            flex: 1;
        }


        .file-name {

            overflow: hidden;

            color: #166534;

            font-size: 13px;

            font-weight: 800;

            text-overflow: ellipsis;

            white-space: nowrap;
        }


        .file-meta {

            margin-top: 2px;

            color: #15803d;

            font-size: 11px;
        }


        .remove-file {

            width: 30px;
            height: 30px;

            display: flex;

            align-items: center;
            justify-content: center;

            border: none;

            border-radius: 8px;

            background: white;

            color: #64748b;

            cursor: pointer;
        }


        .remove-file:hover {

            color: var(--danger);

            background: #fef2f2;
        }


        /* =========================================
           ACTION
        ========================================= */

        .action-area {

            margin-top: 18px;
        }


        .process-button {

            width: 100%;

            height: 54px;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 9px;

            border: none;

            border-radius: 14px;

            background:
                linear-gradient(
                    135deg,
                    var(--primary),
                    var(--primary-dark)
                );

            color: white;

            font-family: inherit;

            font-size: 14px;

            font-weight: 800;

            cursor: pointer;

            box-shadow:
                0 10px 22px
                rgba(37,99,235,.2);

            transition:
                transform .2s ease,
                box-shadow .2s ease,
                opacity .2s ease;
        }


        .process-button svg {

            width: 19px;
            height: 19px;
        }


        .process-button:hover:not(:disabled) {

            transform: translateY(-2px);

            box-shadow:
                0 14px 28px
                rgba(37,99,235,.27);
        }


        .process-button:disabled {

            background: #cbd5e1;

            box-shadow: none;

            cursor: not-allowed;
        }


        .process-button.processing {

            background: var(--success);

            cursor: wait;
        }


        /* =========================================
           WARNING
        ========================================= */

        .notice {

            display: flex;

            align-items: flex-start;

            gap: 10px;

            margin-top: 17px;

            padding: 13px 15px;

            border:
                1px solid #fde68a;

            border-radius: 12px;

            background: var(--warning-soft);

            color: #92400e;

            font-size: 11px;

            line-height: 1.55;
        }


        .notice svg {

            width: 17px;
            height: 17px;

            flex: 0 0 auto;

            margin-top: 1px;
        }


        /* =========================================
           FOOTER
        ========================================= */

        .footer {

            padding:
                25px 10px 0;

            text-align: center;

            color: var(--text-muted);

            font-size: 12px;

            line-height: 1.6;
        }


        .footer strong {

            color: var(--text-soft);

            font-weight: 700;
        }


        /* =========================================
           RESPONSIVE
        ========================================= */

        @media (max-width: 650px) {

            body {

                padding:
                    20px 15px 35px;
            }


            .topbar {

                margin-bottom: 22px;
            }


            .page-badge {

                display: none;
            }


            .hero {

                margin-bottom: 20px;
            }


            .hero-icon {

                width: 53px;
                height: 53px;

                margin-bottom: 15px;
            }


            h1 {

                font-size: 29px;
            }


            .subtitle {

                font-size: 14px;
            }


            .card-body {

                padding: 20px;
            }


            .steps {

                gap: 7px;
            }


            .step {

                flex-direction: column;

                align-items: center;

                text-align: center;

                padding: 9px 5px;

                gap: 6px;
            }


            .step-text {

                font-size: 9px;
            }


            .upload-box {

                min-height: 225px;

                padding:
                    25px 15px;
            }


            .upload-icon {

                width: 62px;
                height: 62px;
            }


            .upload-title {

                font-size: 15px;
            }


            .upload-description {

                font-size: 12px;
            }

        }


        @media (max-width: 400px) {

            .card-body {

                padding: 17px;
            }


            .steps {

                grid-template-columns: 1fr;
            }


            .step {

                flex-direction: row;

                justify-content: flex-start;

                text-align: left;

                padding: 9px 10px;
            }


            .step-text {

                font-size: 11px;
            }

        }


        /* =========================================
           ACCESSIBILITY
        ========================================= */

        a:focus-visible,
        button:focus-visible,
        input:focus-visible {

            outline:
                3px solid
                rgba(37,99,235,.25);

            outline-offset: 3px;
        }


        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {

                transition: none !important;
            }
        }

    </style>
@endpush

@section('content')
<div class="w-full">

<div class="container">


    <!-- =========================================
         TOPBAR
    ========================================= -->

    <div class="topbar">

        <a
            href="/"
            class="back-link"
        >

            <span class="back-icon">

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M19 12H5"/>
                    <path d="M12 19l-7-7 7-7"/>
                </svg>

            </span>

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

        <div class="hero-icon">

            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
            >

                <path d="M4 4h16v16H4z"/>

                <path d="M8 8h8"/>
                <path d="M8 12h8"/>
                <path d="M8 16h5"/>

            </svg>

        </div>


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

                    <div class="upload-icon">

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >

                            <path
                                d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"
                            />

                            <polyline
                                points="17 8 12 3 7 8"
                            />

                            <line
                                x1="12"
                                y1="3"
                                x2="12"
                                y2="15"
                            />

                        </svg>

                    </div>


                    <div class="upload-title">
                        Pilih atau tarik file ke sini
                    </div>


                    <div class="upload-description">
                        Upload file teks berisi daftar
                        nomor polisi kendaraan.
                    </div>


                    <div class="upload-hint">

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
                            <path d="M7 12h7"/>
                            <path d="M7 16h4"/>

                        </svg>

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

                    <div class="file-icon">

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >

                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>

                            <polyline points="14 2 14 8 20 8"/>

                            <path d="m9 15 2 2 4-4"/>

                        </svg>

                    </div>


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


<script>

    const fileInput =
        document.getElementById('fileNopol');

    const dropArea =
        document.getElementById('dropArea');

    const fileInfo =
        document.getElementById('fileInfo');

    const fileName =
        document.getElementById('fileName');

    const fileMeta =
        document.getElementById('fileMeta');

    const removeFile =
        document.getElementById('removeFile');

    const btnProses =
        document.getElementById('btnProses');

    const form =
        document.getElementById('massForm');


    /* =========================================
       FORMAT FILE SIZE
    ========================================= */

    function formatFileSize(bytes) {

        if (bytes === 0) {
            return '0 Bytes';
        }

        const units = [
            'Bytes',
            'KB',
            'MB',
            'GB'
        ];

        const i =
            Math.floor(
                Math.log(bytes) /
                Math.log(1024)
            );

        return (
            parseFloat(
                (
                    bytes /
                    Math.pow(1024, i)
                ).toFixed(2)
            ) +
            ' ' +
            units[i]
        );
    }


    /* =========================================
       UPDATE FILE
    ========================================= */

    function updateFile(file) {

        if (!file) {

            resetFile();

            return;
        }


        const extension =
            file.name
                .split('.')
                .pop()
                .toLowerCase();


        if (extension !== 'txt') {

            alert(
                'Format file tidak valid. Silakan pilih file .TXT.'
            );

            resetFile();

            return;
        }


        fileName.textContent =
            file.name;


        /*
         * Hitung jumlah NOPOL
         */

        const reader =
            new FileReader();


        reader.onload =
            function(event) {

                const content =
                    event.target.result || '';

                const lines =
                    content
                        .split(/\r?\n/)
                        .map(line => line.trim())
                        .filter(Boolean);

                fileMeta.textContent =
                    formatFileSize(file.size) +
                    ' · ' +
                    lines.length +
                    ' nomor polisi terdeteksi';
            };


        reader.readAsText(file);


        fileInfo.classList.add('visible');

        dropArea.classList.add('has-file');

        btnProses.disabled = false;
    }


    /* =========================================
       RESET
    ========================================= */

    function resetFile() {

        fileInput.value = '';

        fileInfo.classList.remove('visible');

        dropArea.classList.remove('has-file');

        fileName.textContent = '-';

        fileMeta.textContent = '-';

        btnProses.disabled = true;
    }


    /* =========================================
       FILE INPUT
    ========================================= */

    fileInput.addEventListener(
        'change',
        function() {

            if (
                this.files &&
                this.files.length > 0
            ) {

                updateFile(
                    this.files[0]
                );

            } else {

                resetFile();

            }

        }
    );


    /* =========================================
       REMOVE FILE
    ========================================= */

    removeFile.addEventListener(
        'click',
        function(event) {

            event.preventDefault();

            event.stopPropagation();

            resetFile();

        }
    );


    /* =========================================
       DRAG & DROP
    ========================================= */

    [
        'dragenter',
        'dragover'
    ].forEach(eventName => {

        dropArea.addEventListener(
            eventName,
            function(event) {

                event.preventDefault();

                dropArea.classList.add(
                    'dragging'
                );

            }
        );

    });


    [
        'dragleave',
        'drop'
    ].forEach(eventName => {

        dropArea.addEventListener(
            eventName,
            function(event) {

                event.preventDefault();

                dropArea.classList.remove(
                    'dragging'
                );

            }
        );

    });


    dropArea.addEventListener(
        'drop',
        function(event) {

            const files =
                event.dataTransfer.files;

            if (
                files &&
                files.length > 0
            ) {

                const file =
                    files[0];

                try {

                    const dataTransfer =
                        new DataTransfer();

                    dataTransfer.items.add(file);

                    fileInput.files =
                        dataTransfer.files;

                } catch (error) {

                    /*
                     * Browser lama mungkin tidak
                     * mendukung DataTransfer.
                     * UI tetap akan menampilkan file.
                     */

                }

                updateFile(file);
            }

        }
    );


    /* =========================================
       SUBMIT
    ========================================= */

    form.addEventListener(
        'submit',
        function() {

            btnProses.disabled = true;

            btnProses.classList.add(
                'processing'
            );


            btnProses.innerHTML = `

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >

                    <path
                        d="M12 2v4"
                    />

                    <path
                        d="M12 18v4"
                    />

                    <path
                        d="m4.93 4.93 2.83 2.83"
                    />

                    <path
                        d="m16.24 16.24 2.83 2.83"
                    />

                    <path
                        d="M2 12h4"
                    />

                    <path
                        d="M18 12h4"
                    />

                    <path
                        d="m4.93 19.07 2.83-2.83"
                    />

                    <path
                        d="m16.24 7.76 2.83-2.83"
                    />

                </svg>

                Sedang Memproses Data...

            `;

        }
    );

</script>

</div>
@endsection


