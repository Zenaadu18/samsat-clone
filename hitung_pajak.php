<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Cek Pajak Kendaraan Bermotor </title>


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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>


    <link rel="stylesheet" href="assets/css/hitung_pajak.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>


<body>


<div class="container">

    <!-- ================================================================
         TOPBAR
    ================================================================= -->

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

            Cek Pajak Kendaraan

        </div>

    </div>

    <!-- ================================================================
         HEADER
    ================================================================= -->

    <div class="header">

        <div class="logo">
            <i class="fa-solid fa-car-side" style="font-size: 32px;"></i>
        </div>

        <h1>Informasi Pajak Kendaraan Bermotor</h1>

        <p class="subtitle">
            Provinsi Nusa Tenggara Timur
        </p>

    </div>


    <!-- ================================================================
         MAIN CARD
    ================================================================= -->

    <div class="card">


        <!-- FORM -->

        <form id="taxForm">

            <label
                class="form-label"
                for="nopol"
            >
                Nomor Polisi
            </label>


            <div class="input-wrapper">

                <div class="input-icon">
                    <i class="fa-solid fa-car"></i>
                </div>

                <input
                    type="text"
                    id="nopol"
                    value="<?= htmlspecialchars($_GET['nopol'] ?? '') ?>"
                    placeholder="Contoh: DH3156PF"
                    autocomplete="off"
                    required
                >

            </div>




            <button
                type="submit"
                id="submitBtn"
            >

                <span id="btnText">
                    Hitung Pajak
                </span>

                <div
                    class="loader"
                    id="btnLoader"
                ></div>

            </button>

        </form>


        <!-- ERROR -->

        <div
            class="error-message"
            id="errorMsg"
        ></div>


        <!-- ============================================================
             RESULT
        ============================================================= -->

        <div
            class="result-container"
            id="resultContainer"
        >


            <!-- ========================================================
                 IDENTITAS KENDARAAN
            ========================================================= -->

            <div class="section">

                <div class="section-header">

                    <div class="section-icon">
                        <i class="fa-solid fa-id-card"></i>
                    </div>

                    <div class="section-title">
                        Identitas Kendaraan
                    </div>

                </div>


                <div class="identity-box">


                    <div class="identity-row">

                        <div class="identity-label">
                            Nopol
                        </div>

                        <div class="identity-value">
                            <span
                                class="nopol"
                                id="resNopol"
                            >
                                -
                            </span>
                        </div>

                    </div>


                    <div class="identity-row">

                        <div class="identity-label">
                            Pemilik
                        </div>

                        <div
                            class="identity-value owner"
                            id="resPemilik"
                        >
                            -
                        </div>

                    </div>


                    <div class="identity-row">

                        <div class="identity-label">
                            Unit
                        </div>

                        <div
                            class="identity-value"
                            id="resUnit"
                        >
                            -
                        </div>

                    </div>


                    <div class="identity-row">
                        <div class="identity-label">Tahun</div>
                        <div class="identity-value" id="resTahun">-</div>
                    </div>

                    <!-- >>> TAMBAHKAN BAGIAN INI <<< -->
                    <div class="identity-row">
                        <div class="identity-label">Masa Pajak</div>
                        <div class="identity-value" id="resSdNotice" style="color: #ea580c;">-</div>
                    </div>

                    <div class="identity-row">
                        <div class="identity-label">Masa STNK</div>
                        <div class="identity-value" id="resSdStnk" style="color: #2563eb;">-</div>
                    </div>


                </div>

            </div>


            <!-- ========================================================
                 RINCIAN TAGIHAN
            ========================================================= -->

            <div class="section">

                <div class="section-header">

                    <div class="section-icon">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>

                    <div class="section-title">
                        Rincian Biaya Tagihan
                    </div>

                </div>


                <div class="bill-box">

                    <!-- 1. PKB (BERJALAN + TUNGGAKAN) -->
                    <div class="bill-row current" id="rowPkbWrap" style="flex-direction: column; align-items: stretch; transition: background 0.2s; cursor: pointer;">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <div class="bill-name" style="user-select: none; display: flex; align-items: center; gap: 6px;">
                                <span>PKB</span>
                                <span class="badge badge-blue" id="badgePkbTahun">Pokok</span>
                                <span id="pkbToggleIcon" style="font-size: 11px; color: var(--primary); padding: 2px 6px; border-radius: 4px; background: #e0f2fe;">▼</span>
                            </div>
                            <div class="bill-value" id="resPkbTotal">
                                Rp 0
                            </div>
                        </div>

                        <!-- RINCIAN PKB -->
                        <div id="pkbDetail" style="display: none; margin-top: 10px; padding: 10px 14px; background: #f0fdf4; border-radius: 8px; border: 1px dashed #86efac; font-size: 12.5px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: #64748b;">• PKB Berjalan</span>
                                <span id="resPkbBerjalanVal" style="color: #334155; font-weight: 600;">Rp 0</span>
                            </div>
                            <div id="subPkbTunggakanWrap" style="display: none; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: #64748b;">• Tunggakan Pokok PKB</span>
                                <span id="resPkbTunggakanVal" style="color: #334155; font-weight: 600;">Rp 0</span>
                            </div>
                            <div id="subPkbDiskonWrap" style="display: none; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: #16a34a; font-weight: 600;">• Keringanan / Diskon Diberikan</span>
                                <span id="resPkbDiskonVal" style="color: #16a34a; font-weight: 700;">- Rp 0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-top: 1px solid #bbf7d0; padding-top: 5px; margin-top: 3px;">
                                <span style="color: #15803d; font-weight: 600;">Total PKB Bersih</span>
                                <span id="resPkbNetVal" style="color: #15803d; font-weight: 700;">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- 2. DENDA PKB (BERJALAN + TUNGGAKAN) -->
                    <div class="bill-row penalty" id="rowDendaPkbWrap" style="flex-direction: column; align-items: stretch; transition: background 0.2s; cursor: pointer;">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <div class="bill-name" style="user-select: none; display: flex; align-items: center; gap: 6px;">
                                <span>Denda PKB</span>
                                <span id="dendaPkbToggleIcon" style="font-size: 11px; color: #b45309; padding: 2px 6px; border-radius: 4px; background: #fef3c7;">▼</span>
                            </div>
                            <div class="bill-value" id="resDendaPkbTotal">
                                Rp 0
                            </div>
                        </div>

                        <!-- RINCIAN DENDA PKB -->
                        <div id="dendaPkbDetail" style="display: none; margin-top: 10px; padding: 10px 14px; background: #f0fdf4; border-radius: 8px; border: 1px dashed #86efac; font-size: 12.5px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: #64748b;">• Denda PKB Berjalan Normal</span>
                                <span id="resDendaPkbBerjalanVal" style="color: #334155; font-weight: 600;">Rp 0</span>
                            </div>
                            <div id="subDendaPkbTunggakanWrap" style="display: none; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: #64748b;">• Tunggakan Denda PKB Normal</span>
                                <span id="resDendaPkbTunggakanVal" style="color: #334155; font-weight: 600;">Rp 0</span>
                            </div>
                            <div id="subDendaPkbDiskonWrap" style="display: none; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: #16a34a; font-weight: 600;">• Pembebasan Amnesti Denda (100%)</span>
                                <span id="resDendaPkbDiskonVal" style="color: #16a34a; font-weight: 700;">- Rp 0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-top: 1px solid #bbf7d0; padding-top: 5px; margin-top: 3px;">
                                <span style="color: #15803d; font-weight: 600;">Total Denda PKB yang Dibayar</span>
                                <span id="resDendaPkbNetVal" style="color: #15803d; font-weight: 700;">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- 3. SWDKLLJ (BERJALAN + TUNGGAKAN) -->
                    <div class="bill-row" id="rowSwdklljWrap" style="flex-direction: column; align-items: stretch; transition: background 0.2s; cursor: pointer;">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <div class="bill-name" style="user-select: none; display: flex; align-items: center; gap: 6px;">
                                <span>SWDKLLJ</span>
                                <span id="swdklljToggleIcon" style="font-size: 11px; color: var(--primary); padding: 2px 6px; border-radius: 4px; background: #e0f2fe;">▼</span>
                            </div>
                            <div class="bill-value" id="resSwdklljTotal">
                                Rp 0
                            </div>
                        </div>

                        <!-- RINCIAN SWDKLLJ -->
                        <div id="swdklljDetail" style="display: none; margin-top: 10px; padding: 10px 14px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1; font-size: 12.5px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: #64748b;">• SWDKLLJ Berjalan</span>
                                <span id="resSwdklljBerjalanVal" style="color: #334155; font-weight: 600;">Rp 0</span>
                            </div>
                            <div id="subSwdklljTunggakanWrap" style="display: none; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: #64748b;">• Tunggakan Pokok SWDKLLJ</span>
                                <span id="resSwdklljTunggakanVal" style="color: #334155; font-weight: 600;">Rp 0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-top: 1px solid #e2e8f0; padding-top: 5px; margin-top: 3px;">
                                <span style="color: #1e293b; font-weight: 600;">Total SWDKLLJ yang Dibayar</span>
                                <span id="resSwdklljNetVal" style="color: #1e293b; font-weight: 700;">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- 4. DENDA SWDKLLJ (BERJALAN + TUNGGAKAN) -->
                    <div class="bill-row penalty" id="rowDendaSwdklljWrap" style="flex-direction: column; align-items: stretch; transition: background 0.2s; cursor: pointer;">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <div class="bill-name" style="user-select: none; display: flex; align-items: center; gap: 6px;">
                                <span>Denda SWDKLLJ</span>
                                <span id="dendaSwdklljToggleIcon" style="font-size: 11px; color: #b45309; padding: 2px 6px; border-radius: 4px; background: #fef3c7;">▼</span>
                            </div>
                            <div class="bill-value" id="resDendaSwdklljTotal">
                                Rp 0
                            </div>
                        </div>

                        <!-- RINCIAN DENDA SWDKLLJ -->
                        <div id="dendaSwdklljDetail" style="display: none; margin-top: 10px; padding: 10px 14px; background: #f0fdf4; border-radius: 8px; border: 1px dashed #86efac; font-size: 12.5px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: #64748b;">• Denda SWDKLLJ Berjalan Normal</span>
                                <span id="resDendaSwdklljBerjalanVal" style="color: #334155; font-weight: 600;">Rp 0</span>
                            </div>
                            <div id="subDendaSwdklljTunggakanWrap" style="display: none; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: #64748b;">• Tunggakan Denda SWDKLLJ Normal</span>
                                <span id="resDendaSwdklljTunggakanVal" style="color: #334155; font-weight: 600;">Rp 0</span>
                            </div>
                            <div id="subDendaSwdklljDiskonWrap" style="display: none; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: #16a34a; font-weight: 600;">• Pembebasan Amnesti Denda</span>
                                <span id="resDendaSwdklljDiskonVal" style="color: #16a34a; font-weight: 700;">- Rp 0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-top: 1px solid #bbf7d0; padding-top: 5px; margin-top: 3px;">
                                <span style="color: #15803d; font-weight: 600;">Total Denda SWDKLLJ yang Dibayar</span>
                                <span id="resDendaSwdklljNetVal" style="color: #15803d; font-weight: 700;">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- 5. OPSEN PKB (BERJALAN + TUNGGAKAN) -->
                    <div class="bill-row" id="rowOpsenWrap" style="flex-direction: column; align-items: stretch; transition: background 0.2s; cursor: pointer;">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <div class="bill-name" style="user-select: none; display: flex; align-items: center; gap: 6px;">
                                <span>Opsen PKB (66%)</span>
                                <span id="opsenToggleIcon" style="font-size: 11px; color: var(--primary); padding: 2px 6px; border-radius: 4px; background: #e0f2fe;">▼</span>
                            </div>
                            <div class="bill-value" id="resOpsenTotal">
                                Rp 0
                            </div>
                        </div>

                        <!-- RINCIAN OPSEN PKB -->
                        <div id="opsenDetail" style="display: none; margin-top: 10px; padding: 10px 14px; background: #f0fdf4; border-radius: 8px; border: 1px dashed #86efac; font-size: 12.5px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: #64748b;">• Opsen PKB Berjalan</span>
                                <span id="resOpsenBerjalanVal" style="color: #334155; font-weight: 600;">Rp 0</span>
                            </div>
                            <div id="subOpsenTunggakanWrap" style="display: none; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: #64748b;">• Tunggakan Opsen PKB Normal</span>
                                <span id="resOpsenTunggakanVal" style="color: #334155; font-weight: 600;">Rp 0</span>
                            </div>
                            <div id="subOpsenDiskonWrap" style="display: none; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: #16a34a; font-weight: 600;">• Pembebasan Opsen Tunggakan (Amnesti)</span>
                                <span id="resOpsenDiskonVal" style="color: #16a34a; font-weight: 700;">- Rp 0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-top: 1px solid #bbf7d0; padding-top: 5px; margin-top: 3px;">
                                <span style="color: #15803d; font-weight: 600;">Total Opsen PKB yang Dibayar</span>
                                <span id="resOpsenNetVal" style="color: #15803d; font-weight: 700;">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- 6. DENDA OPSEN PKB (BERJALAN + TUNGGAKAN) -->
                    <div class="bill-row penalty" id="rowDendaOpsenWrap" style="flex-direction: column; align-items: stretch; transition: background 0.2s; cursor: pointer;">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <div class="bill-name" style="user-select: none; display: flex; align-items: center; gap: 6px;">
                                <span>Denda Opsen PKB</span>
                                <span id="dendaOpsenToggleIcon" style="font-size: 11px; color: #b45309; padding: 2px 6px; border-radius: 4px; background: #fef3c7;">▼</span>
                            </div>
                            <div class="bill-value" id="resDendaOpsenTotal">
                                Rp 0
                            </div>
                        </div>

                        <!-- RINCIAN DENDA OPSEN -->
                        <div id="dendaOpsenDetail" style="display: none; margin-top: 10px; padding: 10px 14px; background: #f0fdf4; border-radius: 8px; border: 1px dashed #86efac; font-size: 12.5px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: #64748b;">• Denda Opsen Berjalan Normal</span>
                                <span id="resDendaOpsenBerjalanVal" style="color: #334155; font-weight: 600;">Rp 0</span>
                            </div>
                            <div id="subDendaOpsenTunggakanWrap" style="display: none; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: #64748b;">• Tunggakan Denda Opsen Normal</span>
                                <span id="resDendaOpsenTunggakanVal" style="color: #334155; font-weight: 600;">Rp 0</span>
                            </div>
                            <div id="subDendaOpsenDiskonWrap" style="display: none; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: #16a34a; font-weight: 600;">• Pembebasan Amnesti Denda Opsen</span>
                                <span id="resDendaOpsenDiskonVal" style="color: #16a34a; font-weight: 700;">- Rp 0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-top: 1px solid #bbf7d0; padding-top: 5px; margin-top: 3px;">
                                <span style="color: #15803d; font-weight: 600;">Total Denda Opsen yang Dibayar</span>
                                <span id="resDendaOpsenNetVal" style="color: #15803d; font-weight: 700;">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- 7. BIAYA STNK -->
                    <div class="bill-row" id="rowBiayaStnk" style="display: none;">
                        <div class="bill-name">
                            Biaya STNK
                        </div>
                        <div class="bill-value" id="resBiayaStnk">
                            Rp 0
                        </div>
                    </div>

                    <!-- 8. BIAYA TNKB -->
                    <div class="bill-row" id="rowBiayaTnkb" style="display: none;">
                        <div class="bill-name">
                            Biaya TNKB
                        </div>
                        <div class="bill-value" id="resBiayaTnkb">
                            Rp 0
                        </div>
                    </div>

                </div>


                <!-- TUNGGAKAN INFO -->

                <div
                    class="arrears-info"
                    id="arrearsInfo"
                >
                    <i class="fa-solid fa-triangle-exclamation"></i> Kendaraan memiliki
                    <strong id="arrearsYearText">0</strong>
                    tahun tunggakan.
                    Tahun terbaru dihitung sebagai
                    <strong>PKB berjalan</strong>, sedangkan
                    tahun sebelumnya dihitung sebagai
                    <strong>tunggakan PKB</strong>.
                </div>


            </div>


            <!-- ========================================================
                 AMNESTI
            ========================================================= -->

            <div
                class="bill-row"
                id="rowAmnesti"
                style="display:none; background-color: #dcfce7; border: 1px solid #bbf7d0;"
            >

                <div class="bill-name" style="color: #166534; font-weight: bold;">
                    Diskon Amnesti (PKB & Sanksi)
                </div>

                <div
                    class="bill-value"
                    id="resAmnesti"
                    style="color: #16a34a; font-weight: bold;"
                >
                    - Rp 0
                </div>

            </div>


            <!-- ========================================================
                 TOTAL
            ========================================================= -->

            <div class="total-box">

                <div class="total-label">
                    TOTAL BAYAR
                </div>

                <div
                    class="total-value"
                    id="resTotal"
                >
                    Rp 0
                </div>

            </div>


            <!-- WARNING -->

            <div
                class="warning"
                id="resPeringatan"
            >

                <i class="fa-solid fa-triangle-exclamation"></i>
                <span id="resPeringatanText"></span>

            </div>

            <!-- ========================================================
                 TOMBOL DOWNLOAD GAMBAR
            ========================================================= -->
            <div style="margin-top: 25px;">
                <button type="button" id="btnDownloadImg" style="background: #10b981; margin-bottom: 15px; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.2);">
                    <i class="fa-solid fa-camera"></i> Unduh Hasil (Gambar)
                </button>
            </div>

            <!-- WARNING -->


            <div class="footer">

                Estimasi perhitungan pajak berdasarkan
                data kendaraan yang tersedia.

            </div>


        </div>

    </div>

</div>


<script src="assets/js/hitung_pajak.js?v=<?= filemtime(__DIR__ . '/assets/js/hitung_pajak.js') ?>"></script>


</body>

</html>