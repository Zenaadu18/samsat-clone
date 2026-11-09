<?php

namespace App\Services;

use DateTime;
use Exception;
use mysqli;

class TaxCalculationService
{
    /**
     * Memeriksa apakah saat ini berada di luar jam operasional API Utama Samsat NTT:
     * - API Utama aktif: 00:01 WITA s/d 21:00 WITA (mengembalikan false).
     * - API Backup aktif: 21:01 WITA s/d 00:00 WITA (mengembalikan true).
     */
    public function isSamsatNightTime(?array $dbKonfigurasi = null): bool
    {
        $wita = new \DateTime('now', new \DateTimeZone('Asia/Makassar'));
        $currentMinute = ((int)$wita->format('H') * 60) + (int)$wita->format('i');

        $mulaiStr = $dbKonfigurasi['jam_operasional_nopol_mulai'] ?? '00:01';
        $selesaiStr = $dbKonfigurasi['jam_operasional_nopol_selesai'] ?? '21:00';

        [$jamMulai, $menitMulai] = array_pad(array_map('intval', explode(':', $mulaiStr)), 2, 0);
        [$jamSelesai, $menitSelesai] = array_pad(array_map('intval', explode(':', $selesaiStr)), 2, 0);

        $mulaiMinute = ($jamMulai * 60) + $menitMulai;
        $selesaiMinute = ($jamSelesai * 60) + $menitSelesai;

        // Jika pembatasan_jam_nopol_aktif = 0, jam operasional tidak dibatasi secara kaku, namun tetap bisa fallback
        $pembatasanAktif = (int)($dbKonfigurasi['pembatasan_jam_nopol_aktif'] ?? 1);
        if ($pembatasanAktif === 0) {
            return false;
        }

        // Di luar jam operasional (21:01 s/d 00:00 WITA), gunakan API Backup
        if ($mulaiMinute <= $selesaiMinute) {
            if ($currentMinute > $selesaiMinute || $currentMinute < $mulaiMinute) {
                return true;
            }
        } else {
            if ($currentMinute > $selesaiMinute && $currentMinute < $mulaiMinute) {
                return true;
            }
        }

        return false;
    }

    /**
     * Memanggil API Utama Samsat GetNopol.
     */
    protected function fetchFromPrimarySamsat(string $nopolClean, ?array $dbKonfigurasi = null): ?array
    {
        $getNopolUrl = $dbKonfigurasi['api_url_samsat'] ?? 'https://dash.bpad.nttprov.go.id/pajak/webdtd/pendataan/core/php/getnopol.php';

        try {
            $ch = curl_init($getNopolUrl);
            $postData = json_encode(['nopol' => $nopolClean]);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $postData,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Content-Length: ' . strlen($postData)
                ],
                CURLOPT_CONNECTTIMEOUT => 6,
                CURLOPT_TIMEOUT        => 12,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36',
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
                $decoded = json_decode($response, true);
                if (is_array($decoded) && !empty($decoded['Merk']) && ($decoded['status'] ?? '') !== 'timeup') {
                    return $decoded;
                }
            }
        } catch (\Throwable $e) {}

        return null;
    }

    /**
     * Memanggil API Gateway Cadangan (Senacode API) ketika API Utama offline / di luar jam operasional (setelah jam 9 malam WITA).
     */
    protected function fetchFromBackupApi(string $nopolClean, ?array $dbKonfigurasi = null): ?array
    {
        $backupUrl = $dbKonfigurasi['api_url_backup'] ?? 'http://api.senacode.my.id/kendaraan/';
        $backupKey = $dbKonfigurasi['api_key_backup'] ?? 'senacode-samsat-key-2026';

        // Siapkan kandidat URL (dukung fallback HTTP jika HTTPS Cloudflare timeout/handshake 525)
        $cleanBackupUrl = rtrim($backupUrl, '/');
        $candidateUrls = [$cleanBackupUrl];
        if (stripos($cleanBackupUrl, 'https://') === 0) {
            $candidateUrls[] = preg_replace('/^https:\/\//i', 'http://', $cleanBackupUrl);
        }
        $candidateUrls = array_values(array_unique($candidateUrls));

        $response = false;
        $httpCode = 0;

        foreach ($candidateUrls as $candidateBase) {
            $url = $candidateBase . '/?nopol=' . urlencode($nopolClean);
            try {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER     => [
                        'Accept: application/json',
                        'X-API-Key: ' . $backupKey,
                        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                    ],
                    CURLOPT_CONNECTTIMEOUT => 5,
                    CURLOPT_TIMEOUT        => 8,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
                    break;
                }
            } catch (\Throwable $e) {}
        }

        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            return null;
        }

        try {
            $json = json_decode($response, true);
            if (!is_array($json) || ($json['status'] ?? '') !== 'success' || empty($json['data'])) {
                return null;
            }

            $d = $json['data'];
            if (isset($d[0]) && is_array($d[0])) {
                $d = $d[0];
            }

            // Normalisasi format tanggal STNK & Notice ke YYYY-MM-DD
            $sdNotice = trim((string)($d['jatuh_tempo'] ?? ''));
            if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $sdNotice, $m)) {
                $sdNotice = "{$m[3]}-{$m[2]}-{$m[1]}";
            }

            $sdStnk = trim((string)($d['jatuh_tempo_stnk'] ?? ''));
            if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $sdStnk, $m)) {
                $sdStnk = "{$m[3]}-{$m[2]}-{$m[1]}";
            }

            // Normalisasi jenis kendaraan
            $jenisKend = strtoupper(trim((string)($d['jenis'] ?? '')));
            if ($jenisKend === 'SEPEDA MOTOR') {
                $jenisKend = 'SEPEDA MOTOR R2';
            }

            $njkbRaw = isset($d['njkb_raw']) ? (float)$d['njkb_raw'] : 0.0;
            if ($njkbRaw <= 0 && !empty($d['njkb'])) {
                $njkbRaw = (float)preg_replace('/[^0-9]/', '', (string)$d['njkb']);
            }

            $bobotVal = isset($d['njkb_detail']['bobot']) ? (float)$d['njkb_detail']['bobot'] : 0.0;

            // Normalisasi kapasitas mesin / Isi Cylinder (cc)
            $rawCc = trim((string)($d['cc'] ?? $d['isi_silinder'] ?? $d['cylinder'] ?? '0'));
            $isiCylinder = function_exists('normalisasiCc') ? normalisasiCc($rawCc) : (int)preg_replace('/[^0-9]/', '', $rawCc);

            return [
                'kode'            => '1',
                'status'          => 'success',
                'NOPOL'           => strtoupper(trim((string)($d['no_polisi'] ?? $nopolClean))),
                'Merk'            => trim((string)($d['merk'] ?? '')),
                'Type'            => trim((string)($d['tipe'] ?? '')),
                'TahunPembuatan'  => (int)($d['tahun_buat'] ?? 0),
                'JenisKendaraan'  => $jenisKend,
                'IsiCylinder'     => $isiCylinder,
                'K_GUNA'          => strtoupper(trim((string)($d['penggunaan'] ?? 'PRIBADI'))),
                'K_JR'            => (strpos($jenisKend, 'MOTOR') !== false ? 'SEPEDA MOTOR' : ''),
                'RubahBentuk'     => '-',
                'WarnaTNKB'       => strtoupper(trim((string)($d['warna_plat'] ?? $d['warna_kendaraan'] ?? 'HITAM'))),
                'SD_NOTICE'       => $sdNotice,
                'SD_STNK'         => $sdStnk,
                'NamaPemilik'     => trim((string)($d['nama'] ?? '-')),
                'Alamat'          => trim((string)($d['alamat'] ?? '-')),
                'UPT_ASAL'        => 'KUPANG',
                'NoRangka'        => trim((string)($d['no_rangka'] ?? '')),
                'NoMesin'         => trim((string)($d['no_mesin'] ?? '')),
                'is_backup_api'   => true,
                'njkb_api'        => $njkbRaw,
                'bobot_api'       => $bobotVal,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Mengambil data kendaraan live dari API Samsat GetNopol atau API Cadangan Senacode.
     * Otomatis memprioritaskan API Utama pada 00:01 - 21:00 WITA, dan API Cadangan pada 21:01 - 00:00 WITA.
     */
    public function fetchSamsatData(string $nopol): array
    {
        global $conn, $dbKonfigurasi;

        if (!$conn) {
            require_once app_path('Helpers/db.php');
        }
        if (empty($dbKonfigurasi) || empty($GLOBALS['dbKonfigurasi'])) {
            require_once app_path('Helpers/config_loader.php');
            if (function_exists('reload_samsat_config')) {
                reload_samsat_config();
            }
            $dbKonfigurasi = $GLOBALS['dbKonfigurasi'];
        }

        $nopolClean = strtoupper(preg_replace('/\s+/', '', $nopol));
        $cacheKey = 'samsat_nopol_' . md5($nopolClean);

        // Cek cache lokal terlebih dahulu
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if (is_array($cached) && !empty($cached['Merk'])) {
            return $cached;
        }

        $apiData = null;
        $isNight = $this->isSamsatNightTime($dbKonfigurasi);

        if ($isNight) {
            // Periode non-aktif API Utama (21:01 - 00:00 WITA):
            // Utamakan API Cadangan Senacode terlebih dahulu
            $backupData = $this->fetchFromBackupApi($nopolClean, $dbKonfigurasi);
            if ($backupData !== null && !empty($backupData['Merk'])) {
                $apiData = $backupData;
            } else {
                // Fallback ke API utama jika backup tidak berhasil
                $primaryData = $this->fetchFromPrimarySamsat($nopolClean, $dbKonfigurasi);
                if ($primaryData !== null && !empty($primaryData['Merk'])) {
                    $apiData = $primaryData;
                }
            }
        } else {
            // Jam operasional utama (00:01 - 21:00 WITA):
            // Utamakan API Utama Samsat
            $primaryData = $this->fetchFromPrimarySamsat($nopolClean, $dbKonfigurasi);
            if ($primaryData !== null && !empty($primaryData['Merk'])) {
                $apiData = $primaryData;
            } else {
                // Fallback ke API Cadangan jika API utama gagal/offline/timeup
                $backupData = $this->fetchFromBackupApi($nopolClean, $dbKonfigurasi);
                if ($backupData !== null && !empty($backupData['Merk'])) {
                    $apiData = $backupData;
                }
            }
        }

        if (empty($apiData) || empty($apiData['Merk'])) {
            if (is_array($cached) && !empty($cached['Merk'])) {
                return $cached;
            }
            throw new Exception('Maaf, Data Nomor Polisi tidak ditemukan.', 404);
        }

        \Illuminate\Support\Facades\Cache::put($cacheKey, $apiData, 86400 * 7);

        return $apiData;
    }

    /**
     * Menentukan fallback bobot kendaraan berdasarkan konfigurasi database, jenis, k_jr, atau silinder.
     */
    public function resolveBobot(float $bobot, string $jenisKend = '', string $kJr = '', int $isiCylinder = 0): float
    {
        if ($bobot > 0.0) {
            return $bobot;
        }

        global $dbKonfigurasi;
        if (empty($dbKonfigurasi) || empty($GLOBALS['dbKonfigurasi'])) {
            require_once app_path('Helpers/config_loader.php');
            if (function_exists('reload_samsat_config')) {
                reload_samsat_config();
            }
            $dbKonfigurasi = $GLOBALS['dbKonfigurasi'];
        }

        $jenisUpper = strtoupper($jenisKend);
        $kJrUpper   = strtoupper($kJr);

        // Ambil nilai dari konfigurasi database dengan fallback nilai standar
        $cfgMotor   = (float)($dbKonfigurasi['bobot_default_motor'] ?? 1.000);
        $cfgTruck   = (float)($dbKonfigurasi['bobot_default_truck'] ?? 1.300);
        $cfgBus     = (float)($dbKonfigurasi['bobot_default_bus'] ?? 1.100);
        $cfgPick    = (float)($dbKonfigurasi['bobot_default_pickup'] ?? 1.085);
        $cfgVan     = (float)($dbKonfigurasi['bobot_default_blind_van'] ?? 1.085);
        $cfgMicro   = (float)($dbKonfigurasi['bobot_default_microbus'] ?? 1.085);
        $cfgWagon   = (float)($dbKonfigurasi['bobot_default_station_wagon'] ?? 1.075);
        $cfgJeep    = (float)($dbKonfigurasi['bobot_default_jeep'] ?? 1.050);
        $cfgMinibus = (float)($dbKonfigurasi['bobot_default_minibus'] ?? 1.050);
        $cfgSedan   = (float)($dbKonfigurasi['bobot_default_sedan'] ?? 1.025);

        if (strpos($jenisUpper, 'SEPEDA MOTOR') !== false || strpos($kJrUpper, 'SEPEDA MOTOR') !== false || ($isiCylinder > 0 && $isiCylinder <= 250)) {
            return $cfgMotor;
        } elseif (strpos($jenisUpper, 'MICROBUS') !== false) {
            return $cfgMicro;
        } elseif (strpos($jenisUpper, 'MINIBUS') !== false) {
            return $cfgMinibus;
        } elseif (strpos($jenisUpper, 'TRUCK') !== false) {
            return $cfgTruck;
        } elseif (strpos($jenisUpper, 'BUS') !== false) {
            return $cfgBus;
        } elseif (strpos($jenisUpper, 'PICK') !== false) {
            return $cfgPick;
        } elseif (strpos($jenisUpper, 'BLIND VAN') !== false) {
            return $cfgVan;
        } elseif (strpos($jenisUpper, 'STATION WAGON') !== false) {
            return $cfgWagon;
        } elseif (strpos($jenisUpper, 'JEEP') !== false) {
            return $cfgJeep;
        } elseif (strpos($jenisUpper, 'SEDAN') !== false) {
            return $cfgSedan;
        }

        // Default untuk mobil penumpang
        return $cfgJeep;
    }

    /**
     * Mencari NJKB & Bobot via API realtime atau fallback database lokal.
     */
    public function findNjkb(string $merk, string $tipe, int $tahun, string $nopol = '', string $noRangka = '', string $jenisKend = '', string $kJr = '', int $isiCylinder = 0, ?float $presetNjkb = null, ?float $presetBobot = null): array
    {
        if ($presetNjkb !== null && $presetNjkb > 0) {
            $bobot = ($presetBobot !== null && $presetBobot > 0) ? $presetBobot : 0.0;
            $bobot = $this->resolveBobot($bobot, $jenisKend, $kJr, $isiCylinder);
            return [
                'njkb'        => (float)$presetNjkb,
                'bobot'       => (float)$bobot,
                'sumber'      => 'API Gateway Cadangan (Senacode)',
                'is_estimasi' => false,
            ];
        }

        global $conn;
        if (!$conn) {
            require_once app_path('Helpers/db.php');
        }

        $njkbData = \cariNjkbData($conn, $merk, $tipe, $tahun);
        $bobot = (float)$njkbData['bobot'];
        $sumber = $njkbData['sumber'];
        $isEstimasi = $njkbData['is_estimasi'];

        $nopolClean = strtoupper(preg_replace('/\s+/', '', $nopol));
        $apiNjkb = \ambilNjkbDariApi($nopolClean, $noRangka);
        if ($apiNjkb !== null && $apiNjkb > 0) {
            $njkb = (float)$apiNjkb;
            $sumber = 'API Realtime (cek_pajak)';
            $isEstimasi = false;
        } else {
            $njkb = (float)$njkbData['njkb'];
        }

        // Terapkan penentuan bobot cerdas agar tidak pernah menjadi default 1.0 secara sembarangan
        $bobot = $this->resolveBobot($bobot, $jenisKend, $kJr, $isiCylinder);

        return [
            'njkb'        => (float)$njkb,
            'bobot'       => (float)$bobot,
            'sumber'      => $sumber,
            'is_estimasi' => (bool)$isEstimasi,
        ];
    }

    /**
     * Mencari NJUB (Nilai Jual Ubah Bentuk).
     */
    public function findNjub(string $rubahBentuk, string $jenisKend, int $tahun): float
    {
        global $conn;
        if (!$conn) {
            require_once app_path('Helpers/db.php');
        }

        $njub = 0.0;
        if ($rubahBentuk !== '' && $rubahBentuk !== '-') {
            $tahunCari = $tahun < 2006 ? 2006 : $tahun;
            if ($conn && $conn instanceof \mysqli && !$conn->connect_error) {
                $stmtNjub = $conn->prepare('SELECT nilai FROM njmkb_data WHERE rubah_bentuk = ? AND jenis_dasar = ? AND tahun = ? LIMIT 1');
                if ($stmtNjub) {
                    $stmtNjub->bind_param('ssi', $rubahBentuk, $jenisKend, $tahunCari);
                    $stmtNjub->execute();
                    $rowNjub = $stmtNjub->get_result()->fetch_assoc();
                    $stmtNjub->close();
                    if ($rowNjub) {
                        return (float)$rowNjub['nilai'];
                    }
                }
            }

            // Fallback via Laravel Query Builder (DB Facade)
            if (class_exists(\Illuminate\Support\Facades\DB::class)) {
                try {
                    $rowDb = \Illuminate\Support\Facades\DB::table('njmkb_data')
                        ->where('rubah_bentuk', $rubahBentuk)
                        ->where('jenis_dasar', $jenisKend)
                        ->where('tahun', $tahunCari)
                        ->first();
                    if ($rowDb) {
                        return (float)$rowDb->nilai;
                    }
                } catch (\Throwable $e) {}
            }
        }
        return $njub;
    }

    /**
     * Hitung pajak menggunakan engine & rumus ASLI dari file native (calculate_tax.php & tax_helper.php).
     */
    public function calculateNativeTax(string $nopol, bool $isMutasi = false, array $options = []): array
    {
        global $conn, $dbKonfigurasi, $dbTarifPkb, $dbTarifSwdkllj, $dbTarifStnkTnkb;

        if (!$conn) {
            require_once app_path('Helpers/db.php');
        }

        if (empty($dbKonfigurasi) || empty($GLOBALS['dbKonfigurasi'])) {
            require_once app_path('Helpers/config_loader.php');
            if (function_exists('reload_samsat_config')) {
                reload_samsat_config();
            }
            $dbKonfigurasi = $GLOBALS['dbKonfigurasi'];
        }

        $nopolClean = strtoupper(preg_replace('/\s+/', '', $nopol));
        $isDisabilitas = !empty($options['is_disabilitas']);
        $isKarwas      = !empty($options['is_karwas']);
        $isSosial      = !empty($options['is_sosial']) || !empty($options['is_sosial_agama']);
        $isAmbulance   = !empty($options['is_ambulance']);
        $isKhusus05    = $isAmbulance || $isSosial;

        // 1. Tarik Data Kendaraan via fetchSamsatData (otomatis deteksi jam operasional & backup API Senacode)
        $apiData = !empty($options['api_data']) ? $options['api_data'] : $this->fetchSamsatData($nopolClean);

        // 2. Ekstrak Data Kendaraan
        $merk        = trim((string)($apiData['Merk'] ?? ''));
        $tipe        = trim((string)($apiData['Type'] ?? ''));
        $tahun       = (int)($apiData['TahunPembuatan'] ?? 0);
        $kGuna       = trim((string)($apiData['K_GUNA'] ?? ''));
        $kJr         = trim((string)($apiData['K_JR'] ?? ''));
        $jenisKend   = strtoupper(trim((string)($apiData['JenisKendaraan'] ?? '')));
        $isiCylinder = function_exists('normalisasiCc') ? normalisasiCc($apiData['IsiCylinder'] ?? 0) : (int)($apiData['IsiCylinder'] ?? 0);
        $sdNotice    = trim((string)($apiData['SD_NOTICE'] ?? ''));
        $sdStnk      = trim((string)($apiData['SD_STNK'] ?? ''));
        $nama        = trim((string)($apiData['NamaPemilik'] ?? '-'));
        $nmUsaha     = trim((string)($apiData['NM_USAHA'] ?? ''));
        $alamat      = trim((string)($apiData['Alamat'] ?? '-'));
        $uptAsal     = trim((string)($apiData['UPT_ASAL'] ?? ''));
        $rubahBentuk = strtoupper(trim((string)($apiData['RubahBentuk'] ?? '')));
        $noRangka    = trim((string)($apiData['NoRangka'] ?? $apiData['no_rangka'] ?? $apiData['rangka'] ?? ''));

        // Cek apakah kendaraan angkutan umum atas nama perorangan (bukan Badan Usaha / PT)
        // Sesuai UU HKPD, kendaraan angkutan umum non-badan usaha dikenakan tarif Pribadi (1,2%)
        $isAngkutanUmum = (stripos($kGuna, 'UMUM') !== false);
        $isBadanUsaha   = function_exists('isBadanUsaha') ? isBadanUsaha($nama, $nmUsaha) : false;
        $isUmumNonBadan = ($isAngkutanUmum && !$isBadanUsaha);
        $kGunaHitung    = $isUmumNonBadan ? 'PRIBADI' : $kGuna;

        $presetNjkb = !empty($apiData['njkb_api']) ? (float)$apiData['njkb_api'] : null;
        $presetBobot = !empty($apiData['bobot_api']) ? (float)$apiData['bobot_api'] : null;

        // 3. Periksa Data Penetapan Resmi Real-Time via API BPAD NTT Bank NTT (cek_pajakk)
        $apiTaxDetail = (!empty($noRangka) && function_exists('ambilDataCekPajakApi')) ? \ambilDataCekPajakApi($nopolClean, $noRangka) : null;

        if ($apiTaxDetail !== null && is_array($apiTaxDetail) && isset($apiTaxDetail['pkb']) && (int)$apiTaxDetail['pkb'] > 0) {
            $njkb = (float)($apiTaxDetail['njkb'] ?? $presetNjkb ?? 0);
            $bobot = (float)($presetBobot ?: $this->resolveBobot(0.0, $jenisKend, $kJr, $isiCylinder));
            $sumberNjkb = 'API Realtime BPAD NTT (cek_pajak)';
            $isNjkbEstimasi = false;
            $isFromApi = true;
            $dpPkb = $njkb * $bobot;

            // Rincian resmi langsung dari penetapan BPAD NTT Bank NTT
            $pkbBerjalanKotor = (int)($apiTaxDetail['total_byrprov'] ?? $apiTaxDetail['pkb']);
            $pkbBerjalanNet   = (int)$apiTaxDetail['pkb'];
            $diskonPkbBerjalan= max(0, $pkbBerjalanKotor - $pkbBerjalanNet);

            $pkbTunggakan = (int)($apiTaxDetail['tpkb'] ?? 0);
            $pkbBerjalan  = $pkbBerjalanKotor; // Pokok sebelum diskon untuk rincian resmi
            $pkbTotal     = $pkbBerjalan + $pkbTunggakan;

            $opsenBerjalanKotor = (int)($apiTaxDetail['total_byropsen'] ?? $apiTaxDetail['opsen_pkb'] ?? (floor($pkbBerjalanKotor * 0.66)));
            $opsenBerjalanNet   = (int)($apiTaxDetail['opsen_pkb'] ?? (floor($pkbBerjalanNet * 0.66)));
            $diskonOpsenBerjalan= max(0, $opsenBerjalanKotor - $opsenBerjalanNet);
            $opsenPkb           = $opsenBerjalanKotor; // Pokok sebelum diskon untuk rincian resmi

            $dendaPkb     = (int)($apiTaxDetail['dpkb'] ?? 0);
            $dendaOpsenPkb= (int)($apiTaxDetail['opsen_dpkb'] ?? 0);

            $swdklljBerjalan  = (int)($apiTaxDetail['swdkllj'] ?? 143000);
            $swdklljTunggakan = (int)($apiTaxDetail['tswdkllj'] ?? 0);
            $swdkllj          = $swdklljBerjalan + $swdklljTunggakan;
            $dendaSwdkllj     = (int)($apiTaxDetail['dswdkllj'] ?? 0);

            $biayaStnk = (int)($apiTaxDetail['biaya_stnk'] ?? 0);
            $biayaTnkb = (int)($apiTaxDetail['biaya_tnkb'] ?? 0);
            $stnkData = [
                'wajib'      => ($biayaStnk > 0 || $biayaTnkb > 0),
                'biaya_stnk' => $biayaStnk,
                'biaya_tnkb' => $biayaTnkb,
                'total'      => $biayaStnk + $biayaTnkb,
                'info'       => ($biayaStnk > 0) ? 'Masa Berlaku STNK Habis' : 'STNK Masih Berlaku',
                'keterangan' => ($biayaStnk > 0) ? 'Masa Berlaku STNK Habis' : 'STNK Masih Berlaku'
            ];

            // TOTAL TAGIHAN SEBELUM DISKON (Total Tagihan Normal Samsat)
            $totalByrNormal = (int)($apiTaxDetail['total_byr'] ?? ($pkbTotal + $opsenPkb + $swdkllj + $dendaSwdkllj + $stnkData['total']));
            $totalBayarNet  = (int)($apiTaxDetail['total_bayar'] ?? ($pkbBerjalanNet + $pkbTunggakan + $opsenBerjalanNet + $swdkllj + $dendaSwdkllj + $stnkData['total']));
            $diskonDigital  = max(0, $totalByrNormal - $totalBayarNet);
            $totalPajak     = $totalByrNormal;
            $sumPkbNor      = (int)($apiTaxDetail['sum_pkb_nor'] ?? 0);

            if ($isUmumNonBadan && $dpPkb > 0) {
                // Sesuai UU HKPD, kendaraan Angkutan Umum perorangan dikenakan tarif Pribadi (1,2%)
                $pkbBerjalanKotor = (int)round($dpPkb * 0.012);
                $pkbBerjalanNet   = $pkbBerjalanKotor;
                $diskonPkbBerjalan= 0;
                $opsenBerjalanKotor = (int)floor($pkbBerjalanKotor * 0.66);
                $opsenBerjalanNet   = $opsenBerjalanKotor;
                $diskonOpsenBerjalan= 0;
                $pkbTotal     = $pkbBerjalanKotor + $pkbTunggakan;
                $opsenPkb     = $opsenBerjalanKotor;

                $totalByrNormal = (int)($pkbTotal + $opsenPkb + $swdkllj + $dendaSwdkllj + $stnkData['total']);
                $totalBayarNet  = $totalByrNormal;
                $diskonDigital  = 0;
                $totalPajak     = $totalByrNormal;

                $tarifLama = 1.5;
                $tarifBaru = 1.2;
            } else {
                $tarifLama = \getTarifPkb($kGuna, false, $isKhusus05) * 100;
                $tarifBaru = \getTarifPkb($kGuna, true, $isKhusus05) * 100;
            }

            $tahunTunggakan = !empty($sdNotice) && \buatTanggal($sdNotice) < new \DateTime('today') ? max(0, (int)\buatTanggal($sdNotice)->diff(new \DateTime('today'))->y) : 0;
            $tahunBerjalan  = 1;
            $jumlahTahunPajak = $tahunTunggakan + 1;

            $pkbData = [
                'pkb_berjalan'               => $pkbBerjalan,
                'pkb_berjalan_kotor'         => $pkbBerjalanKotor,
                'diskon_pkb_berjalan'        => $diskonPkbBerjalan,
                'pkb_tunggakan'              => $pkbTunggakan,
                'pkb_tunggakan_kotor'        => max(0, $sumPkbNor - $pkbBerjalanKotor),
                'diskon_pkb_tunggakan'       => max(0, $sumPkbNor - $pkbBerjalanKotor),
                'pkb_total'                  => $pkbTotal,
                'opsen_pkb'                  => $opsenPkb,
                'opsen_pkb_berjalan'         => $opsenBerjalanKotor,
                'opsen_pkb_tunggakan'        => 0,
                'diskon_opsen_pkb_berjalan'  => $diskonOpsenBerjalan,
                'denda_pkb'                  => $dendaPkb,
                'denda_pkb_total'            => $dendaPkb,
                'denda_pkb_berjalan'         => 0,
                'denda_pkb_tunggakan'        => 0,
                'diskon_denda_pkb_berjalan'  => 0,
                'diskon_denda_pkb_tunggakan' => 0,
                'denda_opsen'                => $dendaOpsenPkb,
                'denda_opsen_berjalan'       => 0,
                'denda_opsen_tunggakan'      => 0,
                'tahun_berjalan'             => $tahunBerjalan,
                'tahun_tunggakan'            => $tahunTunggakan,
                'tahun_total'                => $jumlahTahunPajak,
            ];

            $swdklljData = [
                'golongan'               => ($isiCylinder <= 1600 && strpos($jenisKend, 'MOTOR') === false) ? 'DP' : 'EP',
                'tarif'                  => ($isiCylinder <= 1600 && strpos($jenisKend, 'MOTOR') === false) ? 140000 : 150000,
                'admin'                  => 3000,
                'jumlah_tahun'           => $jumlahTahunPajak,
                'total'                  => $swdkllj,
                'pokok_berjalan'         => $swdklljBerjalan,
                'pokok_tunggakan'        => $swdklljTunggakan,
                'denda'                  => $dendaSwdkllj,
                'denda_berjalan'         => $dendaSwdkllj,
                'denda_tunggakan'        => 0,
                'diskon_denda_berjalan'  => 0,
                'diskon_denda_tunggakan' => 0,
            ];
        } else {
            // Jalankan engine kalkulasi lokal native jika API cek_pajakk tidak mengembalikan data penetapan
            $presetNjkb = !empty($apiData['njkb_api']) ? (float)$apiData['njkb_api'] : null;
            $presetBobot = !empty($apiData['bobot_api']) ? (float)$apiData['bobot_api'] : null;
            $njkbInfo = $this->findNjkb($merk, $tipe, $tahun, $nopolClean, $noRangka, $jenisKend, $kJr, $isiCylinder, $presetNjkb, $presetBobot);
            $njkb = $njkbInfo['njkb'];
            $bobot = $njkbInfo['bobot'];
            $sumberNjkb = $njkbInfo['sumber'];
            $isNjkbEstimasi = $njkbInfo['is_estimasi'];
            $isFromApi = ($sumberNjkb === 'API Realtime (cek_pajak)');

            // Safety net kategori jika NJKB <= 0
            if ($njkb <= 0.0) {
                $isNjkbEstimasi = true;
                $isMotorCheck = (strpos($jenisKend, 'SEPEDA MOTOR') !== false || strpos($kJr, 'SEPEDA MOTOR') !== false || ($isiCylinder > 0 && $isiCylinder <= 250));
                if ($isMotorCheck) {
                    $njkb = 14000000.0; $bobot = 1.0; $sumberNjkb = 'Estimasi Standar Sepeda Motor';
                } elseif (strpos($jenisKend, 'TRUCK') !== false || strpos($jenisKend, 'BUS') !== false) {
                    $njkb = 250000000.0; $bobot = 1.3; $sumberNjkb = 'Estimasi Standar Truk/Bus';
                } elseif (strpos($jenisKend, 'PICK') !== false) {
                    $njkb = 120000000.0; $bobot = 1.085; $sumberNjkb = 'Estimasi Standar Pick-up';
                } else {
                    $njkb = 160000000.0; $bobot = 1.025; $sumberNjkb = 'Estimasi Standar Minibus/Sedan';
                }
            }

            // NJUB (Nilai Jual Ubah Bentuk) jika NJKB dari database lokal
            $njub = 0.0;
            if (!$isFromApi && $rubahBentuk !== '' && $rubahBentuk !== '-') {
                $njub = $this->findNjub($rubahBentuk, $jenisKend, $tahun);
            }

            $dpPkb = ($njkb + $njub) * $bobot;

            // Hitung PKB Lengkap menggunakan fungsi native hitungPkbLengkap
            $pkbData = \hitungPkbLengkap(
                $dpPkb,
                $kGunaHitung,
                $sdNotice,
                [
                    'alamat'         => $alamat,
                    'nopol'          => $nopolClean,
                    'is_mutasi'      => $isMutasi,
                    'is_disabilitas' => $isDisabilitas,
                    'is_karwas'      => $isKarwas,
                    'is_sosial'      => $isSosial,
                    'is_ambulance'   => $isAmbulance,
                    'jenis'          => $jenisKend,
                    'type'           => $tipe,
                    'rubah_bentuk'   => $rubahBentuk,
                    'k_jr'           => $kJr,
                    'upt_asal'       => $uptAsal,
                    'nama'           => $nama,
                    'nm_usaha'       => $nmUsaha,
                    'is_online'      => false,
                ]
            );

            // Hitung SWDKLLJ menggunakan fungsi native hitungSwdkllj
            $swdklljData = \hitungSwdkllj($jenisKend, $kJr, $isiCylinder, $kGuna, $sdNotice);
            $swdkllj = (int)$swdklljData['total'];
            $dendaSwdkllj = (int)$swdklljData['denda'];

            // Hitung Biaya STNK & TNKB menggunakan fungsi native hitungBiayaStnk
            $stnkData = \hitungBiayaStnk($sdStnk, $jenisKend, $kJr);

            // Agregasi Rincian & Total
            $tahunTunggakan = (int)($pkbData['tahun_tunggakan'] ?? 0);
            $tahunBerjalan  = (int)($pkbData['tahun_berjalan'] ?? 1);
            $jumlahTahunPajak = max(1, (int)($pkbData['tahun_total'] ?? 1));

            $tarifLama = \getTarifPkb($kGunaHitung, false, $isKhusus05) * 100;
            $tarifBaru = \getTarifPkb($kGunaHitung, true, $isKhusus05) * 100;

            // Pokok Kotor & Bersih sesuai Pergub 64 (setelah potongan DP 17,5% dan Diskon Taat Pajak)
            $pkbBerjalanKotor = (int)$pkbData['pkb_berjalan_kotor'];
            $pkbTunggakanKotor= (int)$pkbData['pkb_tunggakan_kotor'];
            $pkbBerjalanNet   = (int)$pkbData['pkb_berjalan'];
            $pkbTunggakanNet  = (int)$pkbData['pkb_tunggakan'];
            $diskonPkbBerjalan= (int)($pkbData['diskon_pkb_berjalan'] ?? 0);

            $opsenBerjalanKotor = (int)($pkbData['opsen_pkb_berjalan_kotor'] ?? floor($pkbBerjalanKotor * 0.66));
            $opsenBerjalanNet   = (int)($pkbData['opsen_pkb_berjalan'] ?? floor($pkbBerjalanNet * 0.66));
            $diskonOpsenBerjalan= (int)($pkbData['diskon_opsen_pkb_berjalan'] ?? 0);

            $dendaPkb = (int)$pkbData['denda_pkb_total'];
            $dendaOpsenPkb = (int)$pkbData['denda_opsen'];

            $biayaStnk = (int)$stnkData['biaya_stnk'];
            $biayaTnkb = (int)$stnkData['biaya_tnkb'];

            // PKB dan Opsen utama menggunakan nilai bersih setelah diskon taat pajak (Pergub 64)
            $pkbBerjalan  = $pkbBerjalanNet;
            $pkbTunggakan = $pkbTunggakanNet;
            $pkbTotal     = $pkbBerjalan + $pkbTunggakan;
            $opsenPkb     = $opsenBerjalanNet + (int)($pkbData['opsen_pkb_tunggakan'] ?? 0);

            // Total tagihan utama penetapan Pergub 64:
            $totalByrNormal = $pkbTotal + $dendaPkb + $opsenPkb + $dendaOpsenPkb + $swdkllj + $dendaSwdkllj + $stnkData['total'];
            $totalPajak     = $totalByrNormal;

            // Insentif Tambahan Pembayaran Online (PRO NTT / SIGNAL):
            // Sesuai Pergub 64: Tambahan diskon 5% untuk R2/R3 atau 2% untuk R4+
            $isRoda23 = (strpos($jenisKend, 'MOTOR') !== false || strpos($kJr, 'MOTOR') !== false || preg_match('/MOTOR|R2|R3/i', $jenisKend));
            $pctDiskonOnline    = $isRoda23 ? 0.05 : 0.02;
            $diskonDigitalPkb   = (int)floor($pkbBerjalanKotor * $pctDiskonOnline);
            $diskonDigitalOpsen = (int)round($diskonDigitalPkb * 0.66);
            $diskonDigital      = $diskonDigitalPkb + $diskonDigitalOpsen;
            $totalBayarNet      = max(0, $totalByrNormal - $diskonDigital);
        }

        // Cek masa aktif pajak berdasarkan SD Notice (lebih dari 90 hari)
        $today = new \DateTime('today');
        $tglNotice = \buatTanggal($sdNotice);
        $isLebih90Hari = false;
        $sisaHariAktif = 0;

        if ($tglNotice && $tglNotice > $today) {
            $sisaHariAktif = (int)$today->diff($tglNotice)->days;
            if ($sisaHariAktif > 90) {
                $isLebih90Hari = true;
            }
        }

        // Jika masa pajak masih aktif lebih dari 90 hari, tagihan saat ini adalah Rp 0
        if ($isLebih90Hari) {
            $totalPajak = 0;
            $totalByrNormal = 0;
            $totalBayarNet = 0;
            $diskonDigital = 0;
            $diskonPkbBerjalan = 0;
            $diskonOpsenBerjalan = 0;
            $pkbBerjalanNet = 0;
            $opsenBerjalanNet = 0;
            $pkbTotal = 0;
            $pkbBerjalan = 0;
            $pkbTunggakan = 0;
            $opsenPkb = 0;
            $dendaPkb = 0;
            $dendaOpsenPkb = 0;
            $swdkllj = 0;
            $dendaSwdkllj = 0;
            $biayaStnk = 0;
            $biayaTnkb = 0;
            $stnkData['total'] = 0;
            $stnkData['biaya_stnk'] = 0;
            $stnkData['biaya_tnkb'] = 0;
        }

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

        return [
            'nopol'           => $nopolClean,
            'pemilik'         => $nama,
            'alamat'          => $alamat,
            'merk'            => $merk,
            'tipe'            => $tipe,
            'tahun'           => $tahun,
            'unit'            => trim($merk . ' / ' . $tipe),
            'jenis_kendaraan' => $jenisKend,
            'penggunaan'      => $kGuna,
            'sd_notice'       => $sdNotice,
            'sd_stnk'         => $sdStnk,

            'is_pajak_aktif'  => $isLebih90Hari,
            'sisa_hari_aktif' => $sisaHariAktif,
            'status_pajak'    => $isLebih90Hari ? 'aktif' : ($tglNotice && $tglNotice < $today ? 'menunggak' : 'jatuh_tempo'),
            'pesan_aktif'     => $isLebih90Hari ? 'Pajak Kendaraan Masih Aktif' : '',

            'njkb'            => round($njkb),
            'bobot'           => $bobot,
            'dp_pkb'          => round($dpPkb),
            'is_njkb_estimasi'=> $isNjkbEstimasi,
            'sumber_njkb'     => $sumberNjkb,

            'pkb_berjalan'    => $pkbBerjalan,
            'pkb_berjalan_kotor' => (int)$pkbData['pkb_berjalan_kotor'],
            'diskon_pkb_berjalan'=> (int)$pkbData['diskon_pkb_berjalan'],
            'pkb_tunggakan'   => $pkbTunggakan,
            'pkb_tunggakan_kotor'=> (int)$pkbData['pkb_tunggakan_kotor'],
            'diskon_pkb_tunggakan'=> (int)$pkbData['diskon_pkb_tunggakan'],
            'pkb_total'       => $pkbTotal,
            'tahun_berjalan'  => $tahunBerjalan,
            'tahun_tunggakan' => $tahunTunggakan,
            'tahun_total'     => $jumlahTahunPajak,
            'tunggakan_ada'   => $tahunTunggakan > 0,

            'opsen_pkb'                 => $opsenPkb,
            'opsen_pkb_berjalan'        => (int)$pkbData['opsen_pkb_berjalan'],
            'opsen_pkb_berjalan_kotor'  => (int)($pkbData['opsen_pkb_berjalan_kotor'] ?? $opsenBerjalanKotor),
            'diskon_opsen_pkb_berjalan' => (int)($pkbData['diskon_opsen_pkb_berjalan'] ?? 0),
            'opsen_pkb_tunggakan'       => (int)$pkbData['opsen_pkb_tunggakan'],

            'denda_pkb'          => $dendaPkb,
            'denda_pkb_total'    => $dendaPkb,
            'denda_pkb_berjalan' => (int)$pkbData['denda_pkb_berjalan'],
            'denda_pkb_tunggakan'=> (int)$pkbData['denda_pkb_tunggakan'],
            'diskon_denda_pkb_berjalan' => (int)$pkbData['diskon_denda_pkb_berjalan'],
            'diskon_denda_pkb_tunggakan'=> (int)$pkbData['diskon_denda_pkb_tunggakan'],

            'denda_opsen_pkb'       => $dendaOpsenPkb,
            'denda_opsen'           => $dendaOpsenPkb,
            'denda_opsen_berjalan'  => (int)$pkbData['denda_opsen_berjalan'],
            'denda_opsen_tunggakan' => (int)$pkbData['denda_opsen_tunggakan'],

            'swdkllj_golongan'      => $swdklljData['golongan'],
            'swdkllj_tarif'         => $swdklljData['tarif'],
            'swdkllj_admin'         => $swdklljData['admin'],
            'swdkllj_jumlah_tahun'  => $swdklljData['jumlah_tahun'],
            'swdkllj'               => $swdkllj,
            'swdkllj_berjalan'      => (int)$swdklljData['pokok_berjalan'],
            'swdkllj_tunggakan'     => (int)$swdklljData['pokok_tunggakan'],
            'denda_swdkllj'         => $dendaSwdkllj,
            'denda_swdkllj_berjalan'=> (int)$swdklljData['denda_berjalan'],
            'denda_swdkllj_tunggakan'=> (int)$swdklljData['denda_tunggakan'],

            'stnk_wajib'       => (bool)$stnkData['wajib'],
            'biaya_stnk'       => $biayaStnk,
            'biaya_tnkb'       => $biayaTnkb,
            'biaya_stnk_total' => (int)$stnkData['total'],
            'stnk_info'        => $stnkData['info'],

            'tarif_lama'       => $tarifLama . '%',
            'tarif_baru'       => $tarifBaru . '%',
            'tarif_label'      => $tarifBaru . '%',
            'peringatan'       => $pkbData['peringatan'] ?? '',
            'is_umum_non_badan'=> $isUmumNonBadan,
            'notifikasi_tarif' => $isUmumNonBadan ? 'Kendaraan dengan penggunaan Angkutan Umum yang kepemilikannya bukan atas nama Badan Usaha atau PT dikenakan tarif PKB Pribadi (1,2%) sesuai ketentuan UU HKPD.' : '',

            'amnesti_aktif'    => (bool)($pkbData['amnesti_aktif'] ?? false),
            'amnesti_diskon_pkb_berjalan' => (int)($pkbData['amnesti_diskon_pkb_berjalan'] ?? 0),
            'amnesti_diskon_pkb_tunggakan'=> (int)($pkbData['amnesti_diskon_pkb_tunggakan'] ?? 0),
            'amnesti_denda_dibebaskan'    => (int)($pkbData['amnesti_denda_dibebaskan'] ?? 0),
            'amnesti_total_potongan'      => (int)$totalPenghematanLengkap,

            'total_pajak'          => (int)$totalPajak,
            'total_tagihan'        => (int)$totalPajak,
            'total_bayar'          => (int)$totalPajak,
            'total_setelah_diskon' => (int)$totalBayarNet,
            'total_bayar_digital'  => (int)$totalBayarNet,
            'diskon_digital'       => (int)$diskonDigital,
            'diskon_digital_pkb'   => (int)$diskonPkbBerjalan,
            'diskon_digital_opsen' => (int)$diskonOpsenBerjalan,
            'pkb_net'              => (int)$pkbBerjalanNet,
            'opsen_net'            => (int)$opsenBerjalanNet,
        ];
    }

    /**
     * Mengambil seluruh konfigurasi native dari database atau config_loader.
     */
    public function getConfig(): array
    {
        global $conn, $dbKonfigurasi, $dbTarifPkb, $dbTarifSwdkllj, $dbTarifStnkTnkb;

        if (!$conn) {
            require_once app_path('Helpers/db.php');
        }

        if (empty($dbKonfigurasi) || empty($GLOBALS['dbKonfigurasi'])) {
            require_once app_path('Helpers/config_loader.php');
            if (function_exists('reload_samsat_config')) {
                reload_samsat_config();
            }
            $dbKonfigurasi = $GLOBALS['dbKonfigurasi'];
        }

        return [
            'konfigurasi'   => $dbKonfigurasi ?? [],
            'tarif_pkb'     => $dbTarifPkb ?? [],
            'tarif_swdkllj' => $dbTarifSwdkllj ?? [],
            'tarif_stnk'    => $dbTarifStnkTnkb ?? [],
        ];
    }
}
