<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="theme-color" content="#2563eb">
    <meta name="description" content="Portal Layanan BAPENDA NTT - Pusat Layanan Informasi Pajak dan Aset Daerah Provinsi NTT">

    <title>Portal Layanan BAPENDA NTT</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<main class="container">

    <!-- SERVICE MENU -->
    <section aria-labelledby="service-title">

        <div class="menu-grid" aria-label="Daftar layanan BAPENDA">

            <!-- PAJAK KENDARAAN -->
            <a href="hitung_pajak.php"
               class="menu-card"
               aria-label="Buka layanan Cek Pajak Kendaraan">

                <div class="menu-icon icon-blue" aria-hidden="true"><i class="fa-solid fa-calculator" style="font-size: 26px;"></i></div>

                <h3 class="menu-title">Cek Pajak Kendaraan</h3>

                <p class="menu-desc">
                    Hitung estimasi tagihan Pajak Kendaraan Bermotor (PKB),
                    denda, dan SWDKLLJ dengan mudah.
                </p>

                <div class="menu-footer">
                    <span class="menu-action">Buka layanan</span>
                    <span class="menu-arrow" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>

            <!-- SIMULASI PENETAPAN -->
            <a href="simulasi_penetapan.php"
               class="menu-card"
               aria-label="Buka layanan Simulasi Penetapan">

                <div class="menu-icon icon-emerald" aria-hidden="true"><i class="fa-solid fa-scale-balanced" style="font-size: 26px;"></i></div>

                <h3 class="menu-title">Simulasi Penetapan</h3>

                <p class="menu-desc">
                    Komparasi penetapan Normal (Tanpa Amnesti), Pergub 54 (Sept - Okt), dan Pergub 64 (Nov - Des).
                </p>

                <div class="menu-footer">
                    <span class="menu-action">Buka simulasi</span>
                    <span class="menu-arrow" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>

            <!-- NJKB -->
            <a href="cek_njkb.php"
               class="menu-card"
               aria-label="Buka layanan Data NJKB">

                <div class="menu-icon icon-orange" aria-hidden="true"><i class="fa-solid fa-file-invoice-dollar" style="font-size: 26px;"></i></div>

                <h3 class="menu-title">Data NJKB</h3>

                <p class="menu-desc">
                    Cari Nilai Jual Kendaraan Bermotor berdasarkan
                    tipe kendaraan dan tahun pembuatan.
                </p>

                <div class="menu-footer">
                    <span class="menu-action">Lihat data</span>
                    <span class="menu-arrow" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>

            <!-- PAJAK MASSAL -->
            <a href="pajak_massal.php"
               class="menu-card"
               aria-label="Buka layanan Cek Pajak Massal">

                <div class="menu-icon icon-purple" aria-hidden="true"><i class="fa-solid fa-layer-group" style="font-size: 26px;"></i></div>

                <h3 class="menu-title">Cek Pajak Massal</h3>

                <p class="menu-desc">
                    Unggah file TXT untuk memproses banyak nomor polisi
                    sekaligus dan mendapatkan rekap tagihan.
                </p>

                <div class="menu-footer">
                    <span class="menu-action">Proses data</span>
                    <span class="menu-arrow" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>

           

        </div>
    </section>

</main>

</body>
</html>
