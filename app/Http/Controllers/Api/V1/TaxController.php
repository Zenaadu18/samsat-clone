<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TaxController extends Controller
{
    /**
     * POST /api/v1/hitung-pajak
     *
     * Menghitung rincian pajak kendaraan bermotor secara lengkap.
     * Response mencakup PKB, opsen, denda, SWDKLLJ, dan biaya STNK/TNKB.
     */
    public function hitungPajak(Request $request): JsonResponse
    {
        // 1. Validasi Input
        $request->validate([
            'nopol'     => 'required|string|min:4|max:12',
            'is_mutasi' => 'sometimes|boolean',
        ]);

        $nopolInput = strtoupper(preg_replace('/\s+/', '', $request->input('nopol')));
        $isMutasi   = (bool) $request->input('is_mutasi', false);

        // 2. Muat Konfigurasi (dari cache agar tidak query DB setiap request)
        $this->loadConfig();

        // 3. Ambil Data Kendaraan dari API Samsat
        $apiUrl = $GLOBALS['dbKonfigurasi']['api_url_samsat']
            ?? 'https://dash.bpad.nttprov.go.id/pajak/webdtd/pendataan/core/php/getnopol.php';

        try {
            $apiData = $this->fetchFromSamsat($apiUrl, $nopolInput);
        } catch (\Exception $e) {
            Log::warning('Samsat API error', ['nopol' => $nopolInput, 'error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 502);
        }

        // 4. Ekstrak Data Kendaraan
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
        $alamat      = trim((string)($apiData['Alamat'] ?? '-'));
        $uptAsal     = trim((string)($apiData['UPT_ASAL'] ?? ''));
        $rubahBentuk = strtoupper(trim((string)($apiData['RubahBentuk'] ?? '')));

        // 5. Cari NJKB & Bobot
        [$njkb, $bobot] = $this->cariNjkb($merk, $tipe, $tahun);

        if ($njkb === 0.0) {
            return $this->errorResponse(
                "Data NJKB tidak ditemukan untuk {$merk} - {$tipe} ({$tahun}).",
                404
            );
        }

        // 6. Hitung NJUB (jika ada rubah bentuk)
        $njub = $this->cariNjub($rubahBentuk, $jenisKend, $tahun);
        $dpPkb = ($njkb + $njub) * $bobot;

        // 7. Kalkulasi Pajak menggunakan Helper
        $pkbData     = hitungPkbLengkap($dpPkb, $kGuna, $sdNotice, [
            'alamat'    => $alamat,
            'nopol'     => $nopolInput,
            'is_mutasi' => $isMutasi,
            'jenis'     => $jenisKend,
            'k_jr'      => $kJr,
            'upt_asal'  => $uptAsal,
        ]);
        $swdklljData = hitungSwdkllj($jenisKend, $kJr, $isiCylinder, $kGuna, $sdNotice);
        $stnkData    = hitungBiayaStnk($sdStnk, $jenisKend, $kJr);

        // 8. Susun Nilai Final
        $pkbBerjalan    = (int)$pkbData['pkb_berjalan'];
        $pkbTunggakan   = (int)$pkbData['pkb_tunggakan'];
        $pkbTotal       = (int)$pkbData['pkb_total'];
        $dendaPkb       = (int)$pkbData['denda_pkb_total'];
        $opsenPkb       = (int)$pkbData['opsen_pkb'];
        $dendaOpsenPkb  = (int)$pkbData['denda_opsen'];
        $swdkllj        = (int)$swdklljData['total'];
        $dendaSwdkllj   = (int)$swdklljData['denda'];
        $biayaStnk      = (int)$stnkData['biaya_stnk'];
        $biayaTnkb      = (int)$stnkData['biaya_tnkb'];

        $totalPajak = $pkbTotal + $dendaPkb + $opsenPkb + $dendaOpsenPkb
            + $swdkllj + $dendaSwdkllj + $stnkData['total'];

        return $this->successResponse([
            // Identitas
            'nopol'           => $nopolInput,
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

            // NJKB
            'njkb'   => round($njkb),
            'njub'   => round($njub),
            'bobot'  => $bobot,
            'dp_pkb' => round($dpPkb),

            // PKB
            'pkb_berjalan'   => $pkbBerjalan,
            'pkb_tunggakan'  => $pkbTunggakan,
            'pkb_total'      => $pkbTotal,
            'tahun_berjalan' => (int)($pkbData['tahun_berjalan'] ?? 1),
            'tahun_tunggakan'=> (int)($pkbData['tahun_tunggakan'] ?? 0),
            'tahun_total'    => (int)($pkbData['tahun_total'] ?? 1),
            'tunggakan_ada'  => (int)($pkbData['tahun_tunggakan'] ?? 0) > 0,

            // Opsen
            'opsen_pkb'          => $opsenPkb,
            'opsen_pkb_berjalan' => (int)$pkbData['opsen_pkb_berjalan'],
            'opsen_pkb_tunggakan'=> (int)$pkbData['opsen_pkb_tunggakan'],

            // Denda
            'denda_pkb'      => $dendaPkb,
            'denda_opsen'    => $dendaOpsenPkb,

            // SWDKLLJ
            'swdkllj_golongan'    => $swdklljData['golongan'],
            'swdkllj_tarif'       => $swdklljData['tarif'],
            'swdkllj_admin'       => $swdklljData['admin'],
            'swdkllj_jumlah_tahun'=> $swdklljData['jumlah_tahun'],
            'swdkllj'             => $swdkllj,
            'denda_swdkllj'       => $dendaSwdkllj,

            // STNK / TNKB
            'stnk_wajib'       => (bool)$stnkData['wajib'],
            'biaya_stnk'       => $biayaStnk,
            'biaya_tnkb'       => $biayaTnkb,
            'biaya_stnk_total' => (int)$stnkData['total'],
            'stnk_info'        => $stnkData['info'],

            // Tarif Info
            'tarif_pkb'  => (getTarifPkb($kGuna, true) * 100) . '%',
            'peringatan' => $pkbData['peringatan'],

            // Amnesti
            'amnesti_aktif'                => (bool)($pkbData['amnesti_aktif'] ?? false),
            'amnesti_diskon_pkb_berjalan'  => (int)($pkbData['amnesti_diskon_pkb_berjalan'] ?? 0),
            'amnesti_diskon_pkb_tunggakan' => (int)($pkbData['amnesti_diskon_pkb_tunggakan'] ?? 0),
            'amnesti_denda_dibebaskan'     => (int)($pkbData['amnesti_denda_dibebaskan'] ?? 0),
            'amnesti_total_potongan'       => (int)($pkbData['amnesti_total_potongan'] ?? 0),

            // Total
            'total_pajak' => (int)$totalPajak,
        ]);
    }

    /**
     * GET /api/v1/cek-njkb
     *
     * Cek Nilai Jual Kendaraan Bermotor berdasarkan merk, tipe, dan tahun.
     */
    public function cekNjkb(Request $request): JsonResponse
    {
        $request->validate([
            'merk'  => 'required|string|max:100',
            'tipe'  => 'required|string|max:100',
            'tahun' => 'required|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        $merk  = trim($request->input('merk'));
        $tipe  = trim($request->input('tipe'));
        $tahun = (int)$request->input('tahun');

        [$njkb, $bobot] = $this->cariNjkb($merk, $tipe, $tahun);

        if ($njkb === 0.0) {
            return $this->errorResponse("Data NJKB tidak ditemukan untuk {$merk} - {$tipe} ({$tahun}).", 404);
        }

        return $this->successResponse([
            'merk'   => $merk,
            'tipe'   => $tipe,
            'tahun'  => $tahun,
            'njkb'   => round($njkb),
            'bobot'  => $bobot,
            'dp_pkb' => round($njkb * $bobot),
        ]);
    }

    /**
     * GET /api/v1/status
     *
     * Endpoint status API untuk pengecekan koneksi / health check.
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'status'  => 'ok',
            'service' => 'BAPENDA NTT Tax API',
            'version' => 'v1',
            'time'    => now()->toIso8601String(),
        ]);
    }

    /* =========================================================================
     * PRIVATE HELPERS
     * ======================================================================= */

    /**
     * Muat semua konfigurasi ke $GLOBALS dengan caching 5 menit.
     * Mencegah 5 query DB dijalankan di setiap request.
     */
    private function loadConfig(): void
    {
        global $dbKonfigurasi, $dbTarifPkb, $dbTarifSwdkllj, $dbTarifStnkTnkb, $dbKlasifikasiKendaraan;

        $cached = Cache::remember('samsat_config', 300, function () {
            // Load config dari Helpers (sudah di-autoload via composer.json)
            // Variabel global sudah terisi oleh app/Helpers/config_loader.php
            global $dbKonfigurasi, $dbTarifPkb, $dbTarifSwdkllj, $dbTarifStnkTnkb, $dbKlasifikasiKendaraan;
            return [
                'dbKonfigurasi'          => $dbKonfigurasi,
                'dbTarifPkb'             => $dbTarifPkb,
                'dbTarifSwdkllj'         => $dbTarifSwdkllj,
                'dbTarifStnkTnkb'        => $dbTarifStnkTnkb,
                'dbKlasifikasiKendaraan' => $dbKlasifikasiKendaraan,
            ];
        });

        $dbKonfigurasi          = $cached['dbKonfigurasi'];
        $dbTarifPkb             = $cached['dbTarifPkb'];
        $dbTarifSwdkllj         = $cached['dbTarifSwdkllj'];
        $dbTarifStnkTnkb        = $cached['dbTarifStnkTnkb'];
        $dbKlasifikasiKendaraan = $cached['dbKlasifikasiKendaraan'];
    }

    /**
     * Ambil data kendaraan dari API Samsat pusat.
     * Hasil di-cache 5 menit per nopol untuk mengurangi roundtrip.
     *
     * @throws \Exception
     */
    private function fetchFromSamsat(string $apiUrl, string $nopol): array
    {
        $cacheKey = 'samsat_nopol_' . md5($nopol);

        return Cache::remember($cacheKey, 300, function () use ($apiUrl, $nopol) {
            $postData = json_encode(['nopol' => $nopol]);

            $ch = curl_init($apiUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $postData,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Content-Length: ' . strlen($postData),
                ],
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT      => 'BAPENDA-NTT-Portal/1.0',
                // SSL verification diaktifkan — gunakan bundle CA sistem
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);

            $response  = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false) {
                throw new \Exception('Gagal terhubung ke server Samsat: ' . $curlError, 502);
            }

            if ($httpCode < 200 || $httpCode >= 300) {
                throw new \Exception('Server Samsat mengembalikan HTTP ' . $httpCode, 502);
            }

            $apiData = json_decode($response, true);

            if (!is_array($apiData) || empty($apiData['Merk'])) {
                throw new \Exception('Data kendaraan dengan nopol tersebut tidak ditemukan.', 404);
            }

            return $apiData;
        });
    }

    /**
     * Cari NJKB & bobot dari DB dengan 3 level fallback.
     * Mengembalikan [$njkb, $bobot].
     */
    private function cariNjkb(string $merk, string $tipe, int $tahun): array
    {
        // Level 1: Exact match
        $row = \DB::selectOne(
            'SELECT njkb, bobot FROM njkb_data WHERE merk = ? AND tipe = ? AND tahun_buat = ? LIMIT 1',
            [$merk, $tipe, $tahun]
        );
        if ($row) return [(float)$row->njkb, (float)$row->bobot];

        // Level 2: LIKE + tahun sama
        $row = \DB::selectOne(
            'SELECT njkb, bobot FROM njkb_data WHERE merk LIKE ? AND tipe LIKE ? AND tahun_buat = ? LIMIT 1',
            ["%{$merk}%", "%{$tipe}%", $tahun]
        );
        if ($row) return [(float)$row->njkb, (float)$row->bobot];

        // Level 3: LIKE tahun terbaru
        $row = \DB::selectOne(
            'SELECT njkb, bobot FROM njkb_data WHERE merk LIKE ? AND tipe LIKE ? ORDER BY tahun_buat DESC LIMIT 1',
            ["%{$merk}%", "%{$tipe}%"]
        );
        if ($row) return [(float)$row->njkb, (float)$row->bobot];

        return [0.0, 0.0];
    }

    /**
     * Cari Nilai Jual Ubah Bentuk (NJUB) dari tabel njmkb_data.
     */
    private function cariNjub(string $rubahBentuk, string $jenisKend, int $tahun): float
    {
        if ($rubahBentuk === '' || $rubahBentuk === '-') return 0.0;

        $tahunCari = max($tahun, 2006);
        $row = \DB::selectOne(
            'SELECT nilai FROM njmkb_data WHERE rubah_bentuk = ? AND jenis_dasar = ? AND tahun = ? LIMIT 1',
            [$rubahBentuk, $jenisKend, $tahunCari]
        );

        return $row ? (float)$row->nilai : 0.0;
    }

    /**
     * Format response sukses yang seragam.
     */
    private function successResponse(array $data, int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ], $code);
    }

    /**
     * Format response error yang seragam.
     */
    private function errorResponse(string $message, int $code = 400): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
        ], $code);
    }
}
