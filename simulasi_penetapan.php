<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulasi Penetapan Pajak Kendaraan Bermotor - BAPENDA NTT</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/simulasi_penetapan.css">
</head>
<body>

<div class="simulasi-container">

    <!-- TOPBAR -->
    <div class="topbar">
        <a href="index.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Portal
        </a>
        <div class="page-badge">
            <span class="badge-dot"></span>
            Simulasi Penetapan Pajak
        </div>
    </div>

    <!-- HEADER -->
    <header class="header-simulasi">
        <div class="header-icon">
            <i class="fa-solid fa-scale-balanced"></i>
        </div>
        <h1>Simulasi Komparasi Penetapan Pajak</h1>
        <p class="subtitle">
            Bandingkan secara komprehensif hasil penetapan <strong>Normal (Tanpa Amnesty)</strong>, 
            <strong>Pergub 54 (Sept - Okt 2026)</strong>, dan <strong>Pergub 64 (Nov - Des 2026)</strong>.
        </p>
    </header>

    <!-- ACCORDION: KETENTUAN Pergub 64 -->
    <div class="policy-card">
        <div class="policy-header" id="policyHeader">
            <span><i class="fa-solid fa-circle-info" style="color: #2563eb; margin-right: 8px;"></i>Ketentuan Pemberian Keringanan Pergub 64 (Nov - Des 2026)</span>
            <i class="fa-solid fa-chevron-down" id="policyIcon"></i>
        </div>
        <div class="policy-content" id="policyContent">
            <ol>
                <li><strong>Masa Berlaku:</strong> 1 November 2026 s.d. 31 Desember 2026.</li>
                <li><strong>BBNKB:</strong> Pengurangan Dasar Pengenaan BBNKB Roda 2 & 3 sebesar <strong>15%</strong>, dan Roda 4+ sebesar <strong>20%</strong>.</li>
                <li><strong>PKB Taat Pajak (Loket):</strong> Bayar 0-90 hari sebelum jatuh tempo diberikan pengurangan Dasar Pengenaan <strong>17,5%</strong> + pengurangan pokok PKB berjalan (R2/R3: 10%-20%, R4+: 10%).</li>
                <li><strong>PKB Mutasi Masuk Luar Daerah:</strong> Pengurangan Dasar Pengenaan <strong>17,5%</strong> + pengurangan pokok PKB berjalan <strong>50%</strong>.</li>
                <li><strong>PKB Taat Pajak (Payment Online):</strong> Pengurangan Dasar Pengenaan <strong>17,5%</strong> + diskon pokok berjalan ditambah <strong>+5% (R2/R3)</strong> atau <strong>+2% (R4+)</strong>.</li>
                <li><strong>PKB Terlambat (Loket):</strong> Pengurangan Dasar Pengenaan PKB <strong>17,5%</strong>.</li>
                <li><strong>PKB Terlambat (Payment Online):</strong> Pengurangan Dasar Pengenaan PKB <strong>17,5%</strong> + pengurangan pokok PKB berjalan <strong>5% (R2/R3)</strong> atau <strong>2% (R4+)</strong>.</li>
                <li><strong>PKB Menunggak (Loket):</strong> Dasar Pengenaan normal (tanpa pengurangan) + <strong>PKB Masa Tunggakan DIHAPUS 100%</strong>.</li>
                <li><strong>PKB Menunggak (Payment Online):</strong> Dasar Pengenaan normal + <strong>PKB Masa Tunggakan DIHAPUS 100%</strong> + pengurangan pokok berjalan <strong>5% (R2/R3)</strong> atau <strong>2% (R4+)</strong>.</li>
                <li><strong>Pembebasan Denda:</strong> Denda PKB & Denda Opsen <strong>DIHAPUS 100%</strong> untuk seluruh jenis pendaftaran.</li>
            </ol>
        </div>
    </div>

    <!-- MAIN CONTROLS CARD -->
    <div class="card-control">
        <!-- MODE TABS -->
        <div class="mode-tabs">
            <button type="button" class="mode-tab-btn active" id="tabNopolBtn">
                <i class="fa-solid fa-car-rear"></i> Berdasarkan Nomor Polisi
            </button>
            <button type="button" class="mode-tab-btn" id="tabManualBtn">
                <i class="fa-solid fa-sliders"></i> Simulasi Manual / Bebas
            </button>
        </div>

        <!-- FORM 1: NOPOL -->
        <div id="formNopol">
            <form id="formNopolEl">
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label class="form-label" for="nopolInput">
                            <i class="fa-solid fa-magnifying-glass"></i> Nomor Polisi
                        </label>
                        <input type="text" id="nopolInput" class="input-control" value="<?= htmlspecialchars($_GET['nopol'] ?? '') ?>" placeholder="Contoh: DH3156PF" autocomplete="off" required>
                    </div>
                </div>
            </form>
        </div>

        <!-- FORM 2: MANUAL -->
        <div id="formManual" style="display: none;">
            <form id="formManualEl">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="manualNjkb">
                            <i class="fa-solid fa-money-bill-wave"></i> Nilai Jual (NJKB) Rp
                        </label>
                        <input type="number" id="manualNjkb" class="input-control" placeholder="Contoh: 18000000" value="18000000">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="manualJenisKendaraan">
                            <i class="fa-solid fa-car"></i> Jenis Kendaraan
                        </label>
                        <select id="manualJenisKendaraan" class="select-control">
                            <option value="SEPEDA MOTOR R2" data-bobot="1.000" data-kategori="R2" selected>Spm / Sepeda Motor Roda 2 (Bobot 1.000)</option>
                            <option value="SEPEDA MOTOR R3" data-bobot="1.000" data-kategori="R2">Spm / Sepeda Motor Roda 3 (Bobot 1.000)</option>
                            <option value="SEDAN" data-bobot="1.025" data-kategori="R4">Sedan (Bobot 1.025)</option>
                            <option value="JEEP" data-bobot="1.050" data-kategori="R4">Jeep (Bobot 1.050)</option>
                            <option value="MINIBUS" data-bobot="1.050" data-kategori="R4">Minibus (Bobot 1.050)</option>
                            <option value="STATION WAGON" data-bobot="1.075" data-kategori="R4">Station Wagon (Bobot 1.075)</option>
                            <option value="PICK UP" data-bobot="1.085" data-kategori="R4">Pick Up (Bobot 1.085)</option>
                            <option value="MICROBUS" data-bobot="1.085" data-kategori="R4">Microbus (Bobot 1.085)</option>
                            <option value="BLIND VAN" data-bobot="1.085" data-kategori="R4">Blind Van (Bobot 1.085)</option>
                            <option value="BUS" data-bobot="1.100" data-kategori="R4">Bus (Bobot 1.100)</option>
                            <option value="LIGHT TRUCK" data-bobot="1.300" data-kategori="R4">Light Truck (Bobot 1.300)</option>
                            <option value="TRUCK" data-bobot="1.400" data-kategori="R4">Truck / Truk (Bobot 1.400)</option>
                            <option value="ALAT BERAT" data-bobot="1.000" data-kategori="R4">Alat Berat / Khusus (Bobot 1.000)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="manualBobot">
                            <i class="fa-solid fa-weight-scale"></i> Bobot Kendaraan
                        </label>
                        <input type="number" id="manualBobot" class="input-control" step="0.001" value="1.000">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="manualKategoriRoda">
                            <i class="fa-solid fa-shapes"></i> Kategori Kendaraan
                        </label>
                        <select id="manualKategoriRoda" class="select-control">
                            <option value="R2" selected>Roda 2 / Roda 3 (Sepeda Motor)</option>
                            <option value="R4">Roda 4 dan seterusnya (Mobil/Truck/Bus)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="manualKGuna">
                            <i class="fa-solid fa-user-tag"></i> Penggunaan
                        </label>
                        <select id="manualKGuna" class="select-control">
                            <option value="PRIBADI" selected>PRIBADI</option>
                            <option value="UMUM">UMUM</option>
                            <option value="DINAS">PEMERINTAH / DINAS</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="manualSdNotice">Masa Pajak Berakhir (SD Notice)</label>
                        <input type="date" id="manualSdNotice" class="input-control" value="2026-11-20">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="manualSdStnk">Masa Berlaku STNK (5 Tahunan)</label>
                        <input type="date" id="manualSdStnk" class="input-control" value="2028-11-20">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="manualUptAsal">Wilayah UPT Samsat</label>
                        <select id="manualUptAsal" class="select-control">
                            <option value="UPT KUPANG" selected>UPT KOTA KUPANG / KABUPATEN</option>
                            <option value="SIKKA">UPT WILAYAH FLORES / LEMBATA (Diskon Tunggakan 75%)</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- SHARED PARAMETERS: KANAL BAYAR & TANGGAL SIMULASI -->
        <div class="kanal-bayar-box">
            <div class="kanal-title">
                <strong><i class="fa-solid fa-building-columns"></i> Kanal Pembayaran Penetapan:</strong>
                <span>Pilihan kanal memengaruhi tambahan diskon pokok pada Pergub 64 (+5% R2/R3, +2% R4+).</span>
            </div>
            <div class="kanal-options">
                <button type="button" class="kanal-btn active" id="kanalLoketBtn">
                    <i class="fa-solid fa-store"></i> Loket Samsat
                </button>
                <button type="button" class="kanal-btn" id="kanalOnlineBtn">
                    <i class="fa-solid fa-mobile-screen-button"></i> Payment Online
                </button>
            </div>
        </div>

        <div class="form-row" style="align-items: center; margin-top: 14px;">
            <div class="form-group">
                <label class="form-label" for="tanggalSimulasi">
                    <i class="fa-regular fa-calendar"></i> Tanggal Acuan Simulasi Pembayaran
                </label>
                <input type="date" id="tanggalSimulasi" class="input-control">
            </div>

            <div class="form-group" style="padding-top: 24px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; font-weight: 600;">
                    <input type="checkbox" id="isMutasi" style="width: 18px; height: 18px; cursor: pointer;">
                    Kendaraan Mutasi Masuk ke Wilayah NTT (Diskon Pokok 50%)
                </label>
            </div>

            <div class="form-group" style="padding-top: 20px; align-items: flex-end;">
                <button type="button" class="btn-primary-simpan" id="btnSubmitNopol" onclick="jalankanSimulasi()">
                    <span id="btnTextNopol"><i class="fa-solid fa-calculator"></i> Jalankan Simulasi</span>
                    <div class="loader-spinner" id="spinnerNopol"></div>
                </button>
            </div>
        </div>

        <!-- ERROR ALERT -->
        <div id="simulasiError" class="alert-box error" style="display: none; margin-top: 16px; padding: 14px; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 12px; font-weight: 600;"></div>
    </div>

    <!-- VEHICLE INFORMATION STRIP -->
    <div class="vehicle-infobar" id="vehicleInfoBar">
        <div class="v-item">
            <span>Nomor Polisi</span>
            <strong id="infoNopol">-</strong>
        </div>
        <div class="v-item">
            <span>Merk & Tipe Kendaraan</span>
            <strong id="infoUnit">-</strong>
        </div>
        <div class="v-item">
            <span>Klasifikasi</span>
            <strong id="infoRoda">-</strong>
        </div>
        <div class="v-item">
            <span>Jatuh Tempo (SD Notice)</span>
            <strong id="infoNotice">-</strong>
        </div>
        <div class="v-item">
            <span>Status Pembayaran</span>
            <div><span class="status-badge" id="infoStatus">-</span></div>
        </div>
    </div>

    <!-- SIMULATION RESULTS -->
    <div id="simulasiResult" style="display: none;">

        <!-- 3 COMPARISON CARDS -->
        <div class="comparison-grid">

            <!-- 1. NORMAL -->
            <div class="comparison-card card-normal">
                <span class="card-header-badge badge-normal"><i class="fa-solid fa-circle"></i> Tanpa Keringanan</span>
                <h3 class="card-title">Penetapan Normal</h3>
                <p class="card-subtitle">Regulasi tarif standar tanpa program relaksasi</p>

                <div class="card-price-box">
                    <div class="price-label">Total Penetapan</div>
                    <div class="price-val" id="normTotal">Rp 0</div>
                    <div class="saving-tag" style="background: transparent; color: var(--text-muted);">Tidak ada potongan</div>
                </div>

                <ul class="item-breakdown">
                    <li>
                        <span class="label">PKB Tahun Berjalan</span>
                        <span class="val" id="normPkbBerjalan">Rp 0</span>
                    </li>
                    <li>
                        <span class="label">PKB Masa Tunggakan</span>
                        <span class="val" id="normPkbTunggakan">Rp 0</span>
                    </li>
                    <li>
                        <span class="label">Opsen PKB (66%)</span>
                        <span class="val" id="normOpsen">Rp 0</span>
                    </li>
                    <li>
                        <span class="label">Denda PKB & Opsen</span>
                        <span class="val" id="normDendaPkb">Rp 0</span>
                    </li>
                    <li>
                        <span class="label">SWDKLLJ & Denda</span>
                        <span class="val" id="normSwdkllj">Rp 0</span>
                    </li>
                    <li>
                        <span class="label">Biaya STNK & TNKB</span>
                        <span class="val" id="normStnk">Rp 0</span>
                    </li>
                </ul>
            </div>

            <!-- 2. PERGUB 54 -->
            <div class="comparison-card card-pergub54">
                <span class="card-header-badge badge-p54"><i class="fa-solid fa-clock-rotate-left"></i> Sept - Okt 2026</span>
                <h3 class="card-title">Pergub 54</h3>
                <p class="card-subtitle">Amnesti Pajak Periode September - Oktober</p>

                <div class="card-price-box">
                    <div class="price-label">Total Penetapan</div>
                    <div class="price-val" id="p54Total">Rp 0</div>
                    <div class="saving-tag saving-p54" id="p54Hemat">Hemat Rp 0</div>
                </div>

                <ul class="item-breakdown">
                    <li>
                        <span class="label">PKB Tahun Berjalan</span>
                        <span class="val" id="p54PkbBerjalan">Rp 0</span>
                    </li>
                    <li>
                        <span class="label">PKB Masa Tunggakan</span>
                        <span class="val" id="p54PkbTunggakan">Rp 0</span>
                    </li>
                    <li>
                        <span class="label">Opsen PKB (66%)</span>
                        <span class="val" id="p54Opsen">Rp 0</span>
                    </li>
                    <li>
                        <span class="label">Denda PKB & Opsen</span>
                        <span class="val free-tag" id="p54DendaPkb">Bebas 100%</span>
                    </li>
                    <li>
                        <span class="label">SWDKLLJ & Denda</span>
                        <span class="val" id="p54Swdkllj">Rp 0</span>
                    </li>
                    <li>
                        <span class="label">Biaya STNK & TNKB</span>
                        <span class="val" id="p54Stnk">Rp 0</span>
                    </li>
                </ul>

                <p style="font-size: 12px; color: var(--text-soft); margin-top: auto; padding-top: 10px; border-top: 1px solid var(--border);" id="p54Ket"></p>
            </div>

            <!-- 3. Pergub 64 -->
            <div class="comparison-card card-ranpergub">
                <span class="card-header-badge badge-ran"><i class="fa-solid fa-bolt"></i> Nov - Des 2026</span>
                <h3 class="card-title">Pergub 64</h3>
                <p class="card-subtitle">Pemberian Keringanan Tambahan & Hapus Tunggakan</p>

                <div class="card-price-box">
                    <div class="price-label">Total Penetapan</div>
                    <div class="price-val" id="ranTotal">Rp 0</div>
                    <div class="saving-tag saving-ran" id="ranHemat">Hemat Rp 0</div>
                </div>

                <ul class="item-breakdown">
                    <li>
                        <span class="label">PKB Tahun Berjalan <small id="ranBerjalanDiskon" style="color: #047857;"></small></span>
                        <span class="val discounted" id="ranPkbBerjalan">Rp 0</span>
                    </li>
                    <li>
                        <span class="label">PKB Masa Tunggakan</span>
                        <span class="val free-tag" id="ranPkbTunggakan">Rp 0</span>
                    </li>
                    <li>
                        <span class="label">Opsen PKB (66%)</span>
                        <span class="val" id="ranOpsen">Rp 0</span>
                    </li>
                    <li>
                        <span class="label">Denda PKB & Opsen</span>
                        <span class="val free-tag" id="ranDendaPkb">Dihapus 100%</span>
                    </li>
                    <li>
                        <span class="label">SWDKLLJ & Denda</span>
                        <span class="val" id="ranSwdkllj">Rp 0</span>
                    </li>
                    <li>
                        <span class="label">Biaya STNK & TNKB</span>
                        <span class="val" id="ranStnk">Rp 0</span>
                    </li>
                </ul>

                <p style="font-size: 12px; color: #047857; font-weight: 600; margin-top: auto; padding-top: 10px; border-top: 1px solid #bbf7d0;" id="ranKet"></p>
            </div>

        </div>

        <!-- SELISIH HIGHLIGHT BANNER -->
        <div style="background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%); border: 1.5px solid #a7f3d0; border-radius: 16px; padding: 16px 22px; margin-bottom: 28px; text-align: center; font-size: 15px;" id="selisihRanVsP54">
        </div>

        <!-- DETAILED COMPARATIVE TABLE -->
        <div class="card-table-wrapper">
            <div class="section-title">
                <span><i class="fa-solid fa-table-list" style="margin-right: 8px; color: var(--primary);"></i>Matriks Perbandingan Rinci Komponen Penetapan</span>
                <span style="font-size: 12.5px; font-weight: 600; color: var(--text-soft);">Satuan mata uang: Rupiah (IDR)</span>
            </div>

            <div class="table-responsive">
                <table class="simulasi-table">
                    <thead>
                        <tr>
                            <th>Komponen Penetapan</th>
                            <th>1. Normal</th>
                            <th>2. Pergub 54</th>
                            <th>3. Pergub 64</th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr>
                            <td>Pokok PKB Tahun Berjalan</td>
                            <td id="tdNormBerjalan">Rp 0</td>
                            <td id="tdP54Berjalan">Rp 0</td>
                            <td id="tdRanBerjalan" style="color: #047857; font-weight: 700;">Rp 0</td>
                        </tr>
                        <tr>
                            <td>Pokok PKB Masa Tunggakan</td>
                            <td id="tdNormTunggakan">Rp 0</td>
                            <td id="tdP54Tunggakan">Rp 0</td>
                            <td id="tdRanTunggakan" style="color: #047857; font-weight: 700;">Rp 0</td>
                        </tr>
                        <tr>
                            <td>Opsen PKB (66%)</td>
                            <td id="tdNormOpsen">Rp 0</td>
                            <td id="tdP54Opsen">Rp 0</td>
                            <td id="tdRanOpsen" style="color: #047857; font-weight: 700;">Rp 0</td>
                        </tr>
                        <tr>
                            <td>Denda PKB & Denda Opsen</td>
                            <td id="tdNormDenda">Rp 0</td>
                            <td id="tdP54Denda">Rp 0</td>
                            <td id="tdRanDenda" style="color: #047857; font-weight: 700;">Bebas 100%</td>
                        </tr>
                        <tr>
                            <td>SWDKLLJ Pokok + Denda</td>
                            <td id="tdNormSwdkllj">Rp 0</td>
                            <td id="tdP54Swdkllj">Rp 0</td>
                            <td id="tdRanSwdkllj">Rp 0</td>
                        </tr>
                        <tr>
                            <td>PNBP STNK & TNKB (5 Tahunan)</td>
                            <td id="tdNormStnk">Rp 0</td>
                            <td id="tdP54Stnk">Rp 0</td>
                            <td id="tdRanStnk">Rp 0</td>
                        </tr>
                        <tr class="highlight-total">
                            <td>TOTAL PENETAPAN PAJAK</td>
                            <td id="tdNormTotal" style="color: #475569;">Rp 0</td>
                            <td id="tdP54Total" style="color: #b45309;">Rp 0</td>
                            <td id="tdRanTotal" style="color: #047857; font-size: 16px;">Rp 0</td>
                        </tr>
                        <tr class="highlight-saving">
                            <td>TOTAL PENGHEMATAN (DISKON)</td>
                            <td id="tdNormHemat">Rp 0</td>
                            <td id="tdP54Hemat">Rp 0</td>
                            <td id="tdRanHemat">Rp 0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SIMULASI BBNKB -->
        <div class="bbnkb-box">
            <div class="section-title">
                <span><i class="fa-solid fa-file-invoice" style="margin-right: 8px; color: #059669;"></i>Simulasi BBNKB (Bea Balik Nama Kendaraan Bermotor)</span>
                <span id="bbnkbKategori" style="font-size: 13px; font-weight: 700; color: #047857; background: #ecfdf5; padding: 4px 12px; border-radius: 999px;">-</span>
            </div>
            <p style="font-size: 13px; color: var(--text-soft); margin-bottom: 16px;">
                Sesuai butir 2 Pergub 64: Pengurangan Dasar Pengenaan BBNKB Roda 2 & 3 sebesar <strong>15%</strong>, dan Roda 4+ sebesar <strong>20%</strong>.
            </p>

            <div class="table-responsive">
                <table class="simulasi-table">
                    <thead>
                        <tr>
                            <th>Komponen BBNKB</th>
                            <th>Normal</th>
                            <th>Pergub 54</th>
                            <th>Pergub 64</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Dasar Pengenaan (DP) BBNKB</td>
                            <td id="bbnkbNormDp">Rp 0</td>
                            <td id="bbnkbP54Dp">Rp 0</td>
                            <td id="bbnkbRanDp" style="color: #047857; font-weight: 700;">Rp 0</td>
                        </tr>
                        <tr>
                            <td>Pokok BBNKB (Tarif 10%)</td>
                            <td id="bbnkbNormPokok">Rp 0</td>
                            <td id="bbnkbP54Pokok">Rp 0</td>
                            <td id="bbnkbRanPokok" style="color: #047857; font-weight: 700;">Rp 0</td>
                        </tr>
                        <tr>
                            <td>Opsen BBNKB (66%)</td>
                            <td id="bbnkbNormOpsen">Rp 0</td>
                            <td id="bbnkbP54Opsen">Rp 0</td>
                            <td id="bbnkbRanOpsen" style="color: #047857; font-weight: 700;">Rp 0</td>
                        </tr>
                        <tr class="highlight-total">
                            <td>TOTAL BBNKB</td>
                            <td id="bbnkbNormTotal">Rp 0</td>
                            <td id="bbnkbP54Total">Rp 0</td>
                            <td id="bbnkbRanTotal" style="color: #047857;">Rp 0</td>
                        </tr>
                        <tr class="highlight-saving">
                            <td>PENGHEMATAN BBNKB</td>
                            <td>Rp 0</td>
                            <td>Rp 0</td>
                            <td id="bbnkbRanHemat">Rp 0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ACTION TOOLS (PRINT & COPY) -->
        <div class="action-bar">
            <button type="button" class="btn-action-outline" id="btnSalin">
                <i class="fa-regular fa-copy"></i> Salin Ringkasan
            </button>
            <button type="button" class="btn-action-outline" id="btnPrint">
                <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
            </button>
        </div>

    </div>

</div>

<script src="assets/js/simulasi_penetapan.js?v=<?= filemtime(__DIR__ . '/assets/js/simulasi_penetapan.js') ?>"></script>
</body>
</html>
