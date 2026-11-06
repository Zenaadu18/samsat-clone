<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ApiController — Legacy compatibility endpoints.
 *
 * Menangani route lama /api_tax.php dan /calculate_tax.php
 * yang masih mungkin dipanggil dari sistem eksternal.
 * Logika sudah di-refactor ke pure Laravel (DB facade + helper functions).
 * Tidak ada lagi require ke folder legacy/.
 */
class ApiController extends Controller
{
    /**
     * GET|POST /api_tax.php  — endpoint legacy (format output array of objects)
     */
    public function apiTax(Request $request): JsonResponse
    {
        $nopolInput = strtoupper(preg_replace('/\s+/', '', trim(
            (string) ($request->input('nopol') ?? $request->query('nopol', ''))
        )));

        if ($nopolInput === '') {
            return response()->json(['status' => 'error', 'message' => 'Nomor polisi wajib diisi.'], 400);
        }

        $isMutasi = (bool) ($request->input('is_mutasi') ?? $request->query('is_mutasi', false));

        // Muat konfigurasi
        $this->loadConfig();

        // Ambil URL API dari config global
        $apiUrl = $GLOBALS['dbKonfigurasi']['api_url_samsat']
            ?? 'https://dash.bpad.nttprov.go.id/pajak/webdtd/pendataan/core/php/getnopol.php';

        try {
            $apiData = $this->fetchFromSamsat($apiUrl, $nopolInput);
        } catch (\Exception $e) {
            Log::warning('Samsat API error [apiTax]', ['nopol' => $nopolInput, 'error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode() ?: 502);
        }

        // Ekstrak data kendaraan
        $merk        = trim((string) ($apiData['Merk'] ?? ''));
        $tipe        = trim((string) ($apiData['Type'] ?? ''));
        $tahun       = (int) ($apiData['TahunPembuatan'] ?? 0);
        $kGuna       = trim((string) ($apiData['K_GUNA'] ?? ''));
        $kJr         = trim((string) ($apiData['K_JR'] ?? ''));
        $jenisKend   = strtoupper(trim((string) ($apiData['JenisKendaraan'] ?? '')));
        $isiCylinder = function_exists('normalisasiCc') ? normalisasiCc($apiData['IsiCylinder'] ?? 0) : (int) ($apiData['IsiCylinder'] ?? 0);
        $sdNotice    = trim((string) ($apiData['SD_NOTICE'] ?? ''));
        $sdStnk      = trim((string) ($apiData['SD_STNK'] ?? ''));
        $nama        = trim((string) ($apiData['NamaPemilik'] ?? '-'));
        $alamat      = trim((string) ($apiData['Alamat'] ?? '-'));
        $rt          = trim((string) ($apiData['Rt'] ?? '-'));
        $rw          = trim((string) ($apiData['Rw'] ?? '-'));
        $kodeKel     = trim((string) ($apiData['KodeKel'] ?? ''));
        $kelurahan   = trim((string) ($apiData['Kelurahan'] ?? ''));
        $kodeKec     = trim((string) ($apiData['KodeKec'] ?? ''));
        $kecamatan   = trim((string) ($apiData['Kecamatan'] ?? ''));
        $uptAsal     = trim((string) ($apiData['UPT_ASAL'] ?? ''));
        $rubahBentuk = strtoupper(trim((string) ($apiData['RubahBentuk'] ?? '')));

        // Cari NJKB & bobot
        [$njkb, $bobot] = $this->cariNjkb($merk, $tipe, $tahun);
        $njub  = $this->cariNjub($rubahBentuk, $jenisKend, $tahun);
        $dpPkb = ($njkb + $njub) * $bobot;

        // Kalkulasi pajak
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

        $pkbBerjalan  = (int) $pkbData['pkb_berjalan'];
        $pkbTunggakan = (int) $pkbData['pkb_tunggakan'];
        $dendaPkb     = (int) $pkbData['denda_pkb_total'];
        $opsenPkb     = (int) $pkbData['opsen_pkb'];
        $dendaOpsen   = (int) $pkbData['denda_opsen'];
        $swdkllj      = (int) $swdklljData['total'];
        $dendaSwdkllj = (int) $swdklljData['denda'];
        $biayaStnk    = (int) $stnkData['biaya_stnk'];
        $biayaTnkb    = (int) $stnkData['biaya_tnkb'];
        $totalPajak   = $pkbBerjalan + $pkbTunggakan + $dendaPkb + $opsenPkb + $dendaOpsen
            + $swdkllj + $dendaSwdkllj + $stnkData['total'];

        // Format output sesuai format legacy (array berisi objek)
        return response()->json([[
            'NOPOL'          => $nopolInput,
            'NoKTP'          => '000000000000',
            'NamaPemilik'    => $nama,
            'Alamat'         => $alamat,
            'Rt'             => $rt,
            'Rw'             => $rw,
            'KodeKel'        => $kodeKel,
            'Kelurahan'      => $kelurahan,
            'KodeKec'        => $kodeKec,
            'Kecamatan'      => $kecamatan,
            'STNK'           => $sdStnk,
            'NOTICE'         => $sdNotice,
            'PKB_BERJALAN'   => $pkbBerjalan,
            'PKB_TUNGGAKAN'  => $pkbTunggakan,
            'DENDA_PKB'      => $dendaPkb,
            'OPSEN_PKB'      => $opsenPkb,
            'DENDA_OPSEN'    => $dendaOpsen,
            'SWDKLLJ'        => $swdkllj,
            'DENDA_SWDKLLJ'  => $dendaSwdkllj,
            'BIAYA_STNK'     => $biayaStnk,
            'BIAYA_TNKB'     => $biayaTnkb,
            'TOTAL_TAGIHAN'  => $totalPajak,
        ]]);
    }

    /**
     * GET|POST /calculate_tax.php — endpoint legacy (format output detail)
     */
    public function calculateTax(Request $request): JsonResponse
    {
        $rawInput = $request->all();
        $nopolInput = strtoupper(preg_replace('/\s+/', '', trim(
            (string) ($rawInput['nopol'] ?? '')
        )));

        if ($nopolInput === '') {
            return response()->json(['status' => 'error', 'message' => 'Nomor polisi wajib diisi.'], 400);
        }

        $isMutasi = !empty($rawInput['is_mutasi']);

        $this->loadConfig();

        $apiUrl = $GLOBALS['dbKonfigurasi']['api_url_samsat']
            ?? 'https://dash.bpad.nttprov.go.id/pajak/webdtd/pendataan/core/php/getnopol.php';

        try {
            $apiData = $this->fetchFromSamsat($apiUrl, $nopolInput);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'curl_errno' => 0, 'http_code' => $e->getCode() ?: 502], 502);
        }

        $merk        = trim((string) ($apiData['Merk'] ?? ''));
        $tipe        = trim((string) ($apiData['Type'] ?? ''));
        $tahun       = (int) ($apiData['TahunPembuatan'] ?? 0);
        $kGuna       = trim((string) ($apiData['K_GUNA'] ?? ''));
        $kJr         = trim((string) ($apiData['K_JR'] ?? ''));
        $jenisKend   = strtoupper(trim((string) ($apiData['JenisKendaraan'] ?? '')));
        $isiCylinder = function_exists('normalisasiCc') ? normalisasiCc($apiData['IsiCylinder'] ?? 0) : (int) ($apiData['IsiCylinder'] ?? 0);
        $sdNotice    = trim((string) ($apiData['SD_NOTICE'] ?? ''));
        $sdStnk      = trim((string) ($apiData['SD_STNK'] ?? ''));
        $nama        = trim((string) ($apiData['NamaPemilik'] ?? '-'));
        $alamat      = trim((string) ($apiData['Alamat'] ?? '-'));
        $uptAsal     = trim((string) ($apiData['UPT_ASAL'] ?? ''));
        $rubahBentuk = strtoupper(trim((string) ($apiData['RubahBentuk'] ?? '')));

        [$njkb, $bobot] = $this->cariNjkb($merk, $tipe, $tahun);
        if ($njkb === 0.0) {
            return response()->json(['status' => 'error', 'message' => "Data NJKB tidak ditemukan untuk {$merk} - {$tipe} ({$tahun})."], 404);
        }

        $njub  = $this->cariNjub($rubahBentuk, $jenisKend, $tahun);
        $dpPkb = ($njkb + $njub) * $bobot;

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

        $pkbBerjalan   = (int) $pkbData['pkb_berjalan'];
        $pkbTunggakan  = (int) $pkbData['pkb_tunggakan'];
        $pkbTotal      = (int) $pkbData['pkb_total'];
        $dendaPkb      = (int) $pkbData['denda_pkb_total'];
        $opsenPkb      = (int) $pkbData['opsen_pkb'];
        $dendaOpsenPkb = (int) $pkbData['denda_opsen'];
        $swdkllj       = (int) $swdklljData['total'];
        $dendaSwdkllj  = (int) $swdklljData['denda'];
        $biayaStnk     = (int) $stnkData['biaya_stnk'];
        $biayaTnkb     = (int) $stnkData['biaya_tnkb'];
        $totalPajak    = $pkbTotal + $dendaPkb + $opsenPkb + $dendaOpsenPkb
            + $swdkllj + $dendaSwdkllj + $stnkData['total'];

        $tarifLama = getTarifPkb($kGuna, false) * 100;
        $tarifBaru = getTarifPkb($kGuna, true) * 100;

        return response()->json([
            'status' => 'success',
            'data' => [
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
                'njkb'            => round($njkb),
                'bobot'           => $bobot,
                'dp_pkb'          => round($dpPkb),
                'pkb_berjalan'    => $pkbBerjalan,
                'pkb_tunggakan'   => $pkbTunggakan,
                'pkb_total'       => $pkbTotal,
                'tahun_berjalan'  => (int) ($pkbData['tahun_berjalan'] ?? 1),
                'tahun_tunggakan' => (int) ($pkbData['tahun_tunggakan'] ?? 0),
                'tahun_total'     => (int) ($pkbData['tahun_total'] ?? 1),
                'tunggakan_ada'   => (int) ($pkbData['tahun_tunggakan'] ?? 0) > 0,
                'opsen_pkb'            => $opsenPkb,
                'opsen_pkb_berjalan'   => (int) $pkbData['opsen_pkb_berjalan'],
                'opsen_pkb_tunggakan'  => (int) $pkbData['opsen_pkb_tunggakan'],
                'denda_pkb'       => $dendaPkb,
                'denda_pkb_total' => $dendaPkb,
                'denda_opsen_pkb' => $dendaOpsenPkb,
                'denda_opsen'     => $dendaOpsenPkb,
                'swdkllj_golongan'    => $swdklljData['golongan'],
                'swdkllj_tarif'       => $swdklljData['tarif'],
                'swdkllj_admin'       => $swdklljData['admin'],
                'swdkllj_jumlah_tahun'=> $swdklljData['jumlah_tahun'],
                'swdkllj'         => $swdkllj,
                'denda_swdkllj'   => $dendaSwdkllj,
                'stnk_wajib'      => (bool) $stnkData['wajib'],
                'biaya_stnk'      => $biayaStnk,
                'biaya_tnkb'      => $biayaTnkb,
                'biaya_stnk_total'=> (int) $stnkData['total'],
                'stnk_info'       => $stnkData['info'],
                'tarif_lama'      => $tarifLama . '%',
                'tarif_baru'      => $tarifBaru . '%',
                'tarif_label'     => $tarifBaru . '%',
                'peringatan'      => $pkbData['peringatan'],
                'amnesti_aktif'                => (bool) ($pkbData['amnesti_aktif'] ?? false),
                'amnesti_diskon_pkb_berjalan'  => (int) ($pkbData['amnesti_diskon_pkb_berjalan'] ?? 0),
                'amnesti_diskon_pkb_tunggakan' => (int) ($pkbData['amnesti_diskon_pkb_tunggakan'] ?? 0),
                'amnesti_denda_dibebaskan'     => (int) ($pkbData['amnesti_denda_dibebaskan'] ?? 0),
                'amnesti_total_potongan'       => (int) ($pkbData['amnesti_total_potongan'] ?? 0),
                'total_pajak' => (int) $totalPajak,
            ],
        ]);
    }

    /* =========================================================================
     * PRIVATE HELPERS (sama dengan TaxController)
     * ======================================================================= */

    private function loadConfig(): void
    {
        global $dbKonfigurasi, $dbTarifPkb, $dbTarifSwdkllj, $dbTarifStnkTnkb, $dbKlasifikasiKendaraan;

        $cached = Cache::remember('samsat_config', 300, function () {
            global $dbKonfigurasi, $dbTarifPkb, $dbTarifSwdkllj, $dbTarifStnkTnkb, $dbKlasifikasiKendaraan;
            return compact('dbKonfigurasi', 'dbTarifPkb', 'dbTarifSwdkllj', 'dbTarifStnkTnkb', 'dbKlasifikasiKendaraan');
        });

        $dbKonfigurasi          = $cached['dbKonfigurasi'];
        $dbTarifPkb             = $cached['dbTarifPkb'];
        $dbTarifSwdkllj         = $cached['dbTarifSwdkllj'];
        $dbTarifStnkTnkb        = $cached['dbTarifStnkTnkb'];
        $dbKlasifikasiKendaraan = $cached['dbKlasifikasiKendaraan'];
    }

    private function fetchFromSamsat(string $apiUrl, string $nopol): array
    {
        $ttl      = (int) env('SAMSAT_NOPOL_CACHE_TTL', 300);
        $cacheKey = 'samsat_nopol_' . md5($nopol);

        return Cache::remember($cacheKey, $ttl, function () use ($apiUrl, $nopol) {
            $postData  = json_encode(['nopol' => $nopol]);
            $sslVerify = filter_var(env('SAMSAT_SSL_VERIFY', 'true'), FILTER_VALIDATE_BOOLEAN);

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
                CURLOPT_SSL_VERIFYPEER => $sslVerify,
                CURLOPT_SSL_VERIFYHOST => $sslVerify ? 2 : 0,
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
            if (!is_array($apiData)) {
                throw new \Exception('Data kendaraan dengan nopol tersebut tidak ditemukan.', 404);
            }

            return $apiData;
        });
    }

    private function cariNjkb(string $merk, string $tipe, int $tahun): array
    {
        $row = \DB::selectOne(
            'SELECT njkb, bobot FROM njkb_data WHERE merk = ? AND tipe = ? AND tahun_buat = ? LIMIT 1',
            [$merk, $tipe, $tahun]
        );
        if ($row) return [(float) $row->njkb, (float) $row->bobot];

        $row = \DB::selectOne(
            'SELECT njkb, bobot FROM njkb_data WHERE merk LIKE ? AND tipe LIKE ? AND tahun_buat = ? LIMIT 1',
            ["%{$merk}%", "%{$tipe}%", $tahun]
        );
        if ($row) return [(float) $row->njkb, (float) $row->bobot];

        $row = \DB::selectOne(
            'SELECT njkb, bobot FROM njkb_data WHERE merk LIKE ? AND tipe LIKE ? ORDER BY tahun_buat DESC LIMIT 1',
            ["%{$merk}%", "%{$tipe}%"]
        );
        if ($row) return [(float) $row->njkb, (float) $row->bobot];

        return [0.0, 0.0];
    }

    private function cariNjub(string $rubahBentuk, string $jenisKend, int $tahun): float
    {
        if ($rubahBentuk === '' || $rubahBentuk === '-') return 0.0;
        $tahunCari = max($tahun, 2006);
        $row = \DB::selectOne(
            'SELECT nilai FROM njmkb_data WHERE rubah_bentuk = ? AND jenis_dasar = ? AND tahun = ? LIMIT 1',
            [$rubahBentuk, $jenisKend, $tahunCari]
        );
        return $row ? (float) $row->nilai : 0.0;
    }
}
