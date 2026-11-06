<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TaxCalculationService;
use App\Services\SimulationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PwaApiController extends Controller
{
    protected TaxCalculationService $taxService;
    protected SimulationService $simulationService;

    public function __construct(TaxCalculationService $taxService, SimulationService $simulationService)
    {
        $this->taxService = $taxService;
        $this->simulationService = $simulationService;
    }

    /**
     * POST /api/pwa/check
     * Cek Pajak Kendaraan berdasarkan Nopol
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'nopol'     => 'required|string|min:4|max:15|regex:/^[a-zA-Z0-9\s]+$/',
            'is_mutasi' => 'sometimes|boolean',
        ]);

        $nopolInput = preg_replace('/[^A-Z0-9]/', '', strtoupper((string)$request->input('nopol')));
        $isMutasi = (bool)$request->input('is_mutasi', false);

        try {
            $data = $this->taxService->calculateNativeTax($nopolInput, $isMutasi, $request->all());

            $totalBiaya = (float)($data['total_bayar'] ?? $data['total_pajak'] ?? 0);
            $this->logActivityIfUser($request, 'Cek Pajak', 'Cek Nopol ' . $nopolInput . ' (' . ($data['pemilik'] ?? '-') . ')', $totalBiaya);

            return response()->json([
                'status'  => 'success',
                'message' => 'Data pajak berhasil dihitung.',
                'data'    => $data,
            ]);
        } catch (Exception $e) {
            $statusCode = in_array($e->getCode(), [400, 404, 422, 502, 503]) ? $e->getCode() : 500;
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $statusCode);
        } catch (\Throwable $e) {
            Log::error('PWA Tax Check Exception: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kendala teknis pada sistem. Silakan coba beberapa saat lagi.',
            ], 500);
        }
    }

    /**
     * POST /api/pwa/simulate
     * Simulasi Komparasi Penetapan Pajak: Normal vs Pergub 64
     */
    public function simulate(Request $request): JsonResponse
    {
        $mode = $request->input('mode', 'nopol');

        if ($mode === 'manual') {
            $request->validate([
                'njkb'             => 'required|numeric|min:1',
                'bobot'            => 'nullable|numeric|min:0.1',
                'njub'             => 'nullable|numeric|min:0',
                'k_guna'           => 'nullable|string',
                'jenis_kendaraan'  => 'nullable|string',
                'sd_notice'        => 'nullable|date',
                'sd_stnk'          => 'nullable|date',
                'kanal_bayar'      => 'nullable|string',
                'tanggal_simulasi' => 'nullable|date',
            ]);

            $params = [
                'njkb'             => (float)$request->input('njkb'),
                'bobot'            => (float)$request->input('bobot', 1.0),
                'njub'             => (float)$request->input('njub', 0),
                'k_guna'           => $request->input('k_guna', 'PRIBADI'),
                'is_roda23'        => (bool)$request->input('is_roda23', false),
                'jenis_kendaraan'  => $request->input('jenis_kendaraan', 'MINIBUS'),
                'isi_cylinder'     => (int)$request->input('isi_cylinder', 1500),
                'sd_notice'        => (string)$request->input('sd_notice', ''),
                'sd_stnk'          => (string)$request->input('sd_stnk', ''),
                'is_mutasi'        => (bool)$request->input('is_mutasi', false),
                'kanal_bayar'      => (string)$request->input('kanal_bayar', 'loket'),
                'tanggal_simulasi' => (string)$request->input('tanggal_simulasi', date('Y-m-d')),
            ];

            try {
                $simResult = $this->simulationService->simulate($params);

                $totalBiaya = (float)($simResult['pergub64']['total_bayar'] ?? $simResult['total_bayar'] ?? 0);
                $this->logActivityIfUser($request, 'Mulai Simulasi', 'Simulasi Manual: ' . ($params['jenis_kendaraan'] ?? 'Kendaraan') . ' - NJKB Rp ' . number_format($params['njkb'], 0, ',', '.'), $totalBiaya);

                return response()->json([
                    'status' => 'success',
                    'mode'   => 'manual',
                    'data'   => [
                        'kendaraan' => [
                            'nopol'           => 'SIMULASI MANUAL',
                            'pemilik'         => 'Simulasi Tanpa Nopol',
                            'alamat'          => 'Nusa Tenggara Timur',
                            'merk'            => $request->input('merk', 'UMUM'),
                            'tipe'            => $request->input('tipe', 'STANDAR'),
                            'tahun'           => (int)$request->input('tahun', date('Y')),
                            'unit'            => trim($request->input('merk', 'KENDARAAN') . ' ' . $request->input('tipe', '')),
                            'jenis_kendaraan' => $params['jenis_kendaraan'],
                            'penggunaan'      => $params['k_guna'],
                            'sd_notice'       => $params['sd_notice'],
                            'sd_stnk'         => $params['sd_stnk'],
                            'njkb'            => $params['njkb'],
                            'bobot'           => $params['bobot'],
                            'dp_pkb'          => round(($params['njkb'] + $params['njub']) * $params['bobot']),
                        ],
                        'simulasi' => $simResult,
                    ],
                ]);
            } catch (\Throwable $e) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal melakukan simulasi: ' . $e->getMessage(),
                ], 500);
            }
        }

        // Mode Nopol
        $request->validate([
            'nopol'            => 'required|string|min:4|max:15|regex:/^[a-zA-Z0-9\s]+$/',
            'kanal_bayar'      => 'nullable|string',
            'tanggal_simulasi' => 'nullable|date',
            'is_mutasi'        => 'sometimes|boolean',
        ]);

        $nopolInput = preg_replace('/[^A-Z0-9]/', '', strtoupper((string)$request->input('nopol')));
        $kanalBayar = (string)$request->input('kanal_bayar', 'loket');
        $tglSimulasi = (string)$request->input('tanggal_simulasi', date('Y-m-d'));
        $isMutasi = (bool)$request->input('is_mutasi', false);

        try {
            $apiData = $this->taxService->fetchSamsatData($nopolInput);

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
            $noRangka    = trim((string)($apiData['NoRangka'] ?? ''));

            $njkbInfo = $this->taxService->findNjkb($merk, $tipe, $tahun, $nopolInput, $noRangka, $jenisKend, $kJr, $isiCylinder);
            $njkb = $njkbInfo['njkb'];
            $bobot = $njkbInfo['bobot'];

            $njub = $this->taxService->findNjub($rubahBentuk, $jenisKend, $tahun);
            $dpPkb = ($njkb + $njub) * $bobot;

            $params = [
                'njkb'             => $njkb,
                'bobot'            => $bobot,
                'njub'             => $njub,
                'k_guna'           => $kGuna,
                'jenis_kendaraan'  => $jenisKend,
                'k_jr'             => $kJr,
                'isi_cylinder'     => $isiCylinder,
                'sd_notice'        => $sdNotice,
                'sd_stnk'          => $sdStnk,
                'upt_asal'         => $uptAsal,
                'is_mutasi'        => $isMutasi,
                'kanal_bayar'      => $kanalBayar,
                'tanggal_simulasi' => $tglSimulasi,
            ];

            $simResult = $this->simulationService->simulate($params);

            $totalBiaya = (float)($simResult['pergub64']['total_bayar'] ?? $simResult['total_bayar'] ?? 0);
            $this->logActivityIfUser($request, 'Mulai Simulasi', 'Simulasi Nopol ' . $nopolInput . ' (' . ($nama !== '-' ? $nama : trim($merk . ' ' . $tipe)) . ')', $totalBiaya);

            return response()->json([
                'status' => 'success',
                'mode'   => 'nopol',
                'data'   => [
                    'kendaraan' => [
                        'nopol'           => $nopolInput,
                        'pemilik'         => $nama,
                        'alamat'          => $alamat,
                        'merk'            => $merk,
                        'tipe'            => $tipe,
                        'tahun'           => $tahun,
                        'unit'            => trim($merk . ' ' . $tipe),
                        'jenis_kendaraan' => $jenisKend,
                        'penggunaan'      => $kGuna,
                        'sd_notice'       => $sdNotice,
                        'sd_stnk'         => $sdStnk,
                        'upt_asal'        => $uptAsal,
                        'njkb'            => round($njkb),
                        'bobot'           => $bobot,
                        'dp_pkb'          => round($dpPkb),
                    ],
                    'simulasi'  => $simResult,
                ],
            ]);
        } catch (Exception $e) {
            $code = in_array($e->getCode(), [400, 404, 422, 502]) ? $e->getCode() : 500;
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $code);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/pwa/njkb/nopol
     * Cek NJKB & Data Kendaraan berdasarkan Plat Nomor (seperti cek_njkb.php)
     */
    public function checkNjkbByNopol(Request $request): JsonResponse
    {
        $request->validate([
            'nopol' => 'required|string|min:4|max:15|regex:/^[a-zA-Z0-9\s]+$/',
        ]);

        $nopolClean = preg_replace('/[^A-Z0-9]/', '', strtoupper((string)$request->input('nopol')));

        try {
            $apiData = $this->taxService->fetchSamsatData($nopolClean);

            $merk        = trim((string)($apiData['Merk'] ?? ''));
            $tipe        = trim((string)($apiData['Type'] ?? ''));
            $tahun       = (int)($apiData['TahunPembuatan'] ?? 0);
            $jenisKend   = strtoupper(trim((string)($apiData['JenisKendaraan'] ?? '')));
            $isiCylinder = function_exists('normalisasiCc') ? normalisasiCc($apiData['IsiCylinder'] ?? 0) : (int)($apiData['IsiCylinder'] ?? 0);
            $kGuna       = trim((string)($apiData['K_GUNA'] ?? 'PRIBADI'));
            $rubahBentuk = strtoupper(trim((string)($apiData['RubahBentuk'] ?? '')));
            $warnaTnkb   = trim((string)($apiData['WarnaTNKB'] ?? $apiData['WARNA_TNKB'] ?? 'HITAM'));
            $sdNotice    = trim((string)($apiData['SD_NOTICE'] ?? ''));
            $sdStnk      = trim((string)($apiData['SD_STNK'] ?? ''));
            $noRangka    = trim((string)($apiData['NoRangka'] ?? ''));

            $njkbInfo = $this->taxService->findNjkb($merk, $tipe, $tahun, $nopolClean, $noRangka);
            $njkb = (float)$njkbInfo['njkb'];
            $bobot = (float)($njkbInfo['bobot'] > 0 ? $njkbInfo['bobot'] : 1.0);
            $sumber = $njkbInfo['sumber'] ?? '';

            $njub = (float)$this->taxService->findNjub($rubahBentuk, $jenisKend, $tahun);
            $dasarPkb = round(($njkb + $njub) * $bobot);

            $this->logActivityIfUser($request, 'Cek Nilai Jual', 'Cek NJKB Nopol ' . $nopolClean . ' (' . trim($merk . ' ' . $tipe) . ')', (float)$njkb);

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'nopol'         => $nopolClean,
                    'merk'          => $merk,
                    'tipe'          => $tipe,
                    'tahun'         => $tahun,
                    'jenis_kend'    => $jenisKend,
                    'isi_cylinder'  => $isiCylinder,
                    'k_guna'        => $kGuna,
                    'rubah_bentuk'  => $rubahBentuk,
                    'warna_tnkb'    => $warnaTnkb,
                    'sd_notice'     => $sdNotice,
                    'sd_stnk'       => $sdStnk,
                    'njkb'          => round($njkb),
                    'bobot'         => $bobot,
                    'njub'          => round($njub),
                    'dasar_pkb'     => $dasarPkb,
                    'sumber'        => $sumber,
                ],
            ]);
        } catch (Exception $e) {
            $code = in_array($e->getCode(), [400, 404, 422, 502, 503]) ? $e->getCode() : 500;
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $code);
        } catch (\Throwable $e) {
            Log::error('PWA NJKB Check Exception: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kendala teknis pada sistem. Silakan coba beberapa saat lagi.',
            ], 500);
        }
    }

    /**
     * GET /api/pwa/njkb
     * Cari NJKB berdasarkan merk/tipe/tahun
     */
    public function searchNjkb(Request $request): JsonResponse
    {
        $rawQ = trim((string)$request->input('q', ''));
        $q = substr(preg_replace('/[^a-zA-Z0-9\s\-\/\.]/', '', $rawQ), 0, 40);

        $rawMerk = trim((string)$request->input('merk', ''));
        $merk = substr(preg_replace('/[^a-zA-Z0-9\s\-]/', '', $rawMerk), 0, 30);

        $tahun = (int)$request->input('tahun', 0);

        $query = DB::table('njkb_data');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('merk', 'like', "%{$q}%")
                    ->orWhere('tipe', 'like', "%{$q}%");
            });
        }

        if ($merk !== '') {
            $query->where('merk', 'like', "%{$merk}%");
        }

        if ($tahun >= 1990 && $tahun <= (int)date('Y') + 1) {
            $query->where('tahun_buat', $tahun);
        }

        $results = $query->orderBy('merk')
            ->orderBy('tipe')
            ->orderByDesc('tahun_buat')
            ->limit(50)
            ->get(['merk', 'tipe', 'tahun_buat as tahun', 'njkb as nilai_jual', 'bobot']);

        if ($q !== '' || $merk !== '') {
            $firstNjkb = $results->first() ? (float)($results->first()->nilai_jual ?? 0) : null;
            $this->logActivityIfUser($request, 'Cek Nilai Jual', 'Katalog NJKB: ' . trim($merk . ' ' . $q . ($tahun ? ' ' . $tahun : '')), $firstNjkb);
        }

        return response()->json([
            'status' => 'success',
            'count'  => $results->count(),
            'data'   => $results,
        ]);
    }

    /**
     * GET /api/pwa/config
     */
    public function config(): JsonResponse
    {
        $config = $this->taxService->getConfig();
        return response()->json([
            'status' => 'success',
            'data'   => [
                'amnesti_aktif'   => (bool)($config['konfigurasi']['amnesti_aktif'] ?? true),
                'amnesti_mulai'   => $config['konfigurasi']['amnesti_mulai'] ?? '2026-09-01',
                'amnesti_selesai' => $config['konfigurasi']['amnesti_selesai'] ?? '2026-12-31',
                'app_name'        => 'Pajak.In',
                'version'         => '2.0.0',
            ],
        ]);
    }

    /**
     * POST /api/pwa/simulate-bbn2
     * Simulasi Perhitungan Bea Balik Nama ke-2 (BBN 2)
     */
    public function simulateBbn2(Request $request): JsonResponse
    {
        $mode = $request->input('mode', 'nopol');
        $includeTembak = filter_var($request->input('include_tembak', $request->input('use_tembak', false)), FILTER_VALIDATE_BOOLEAN);
        $statusPajak = $request->input('status_pajak', $request->input('pajak_status', 'otomatis')); // 'otomatis' | 'lunas' | 'belum_bayar'

        global $dbKonfigurasi, $dbTarifStnkTnkb;
        if (empty($dbKonfigurasi) || empty($GLOBALS['dbKonfigurasi'])) {
            require_once app_path('Helpers/config_loader.php');
            if (function_exists('reload_samsat_config')) {
                reload_samsat_config();
            }
            $dbKonfigurasi = $GLOBALS['dbKonfigurasi'];
            $dbTarifStnkTnkb = $GLOBALS['dbTarifStnkTnkb'] ?? [];
        }

        if ($mode === 'manual') {
            $isRoda23 = (bool)$request->input('is_roda23', false);
            $jenisKendaraan = (string)$request->input('jenis_kendaraan', $isRoda23 ? 'SEPEDA MOTOR R2' : 'MINIBUS');
            $tagihanPajak = (float)$request->input('tagihan_pajak', 0);
            if ($statusPajak === 'lunas') {
                $tagihanPajak = 0;
            }

            $kendaraanData = [
                'nopol'           => 'SIMULASI MANUAL',
                'pemilik'         => 'Simulasi BBN 2 Manual',
                'alamat'          => 'Nusa Tenggara Timur',
                'merk'            => $request->input('merk', 'UMUM'),
                'tipe'            => $request->input('tipe', 'STANDAR'),
                'tahun'           => (int)$request->input('tahun', date('Y')),
                'unit'            => trim($request->input('merk', 'KENDARAAN') . ' ' . $request->input('tipe', '')),
                'jenis_kendaraan' => $jenisKendaraan,
                'penggunaan'      => $request->input('k_guna', 'PRIBADI'),
                'is_roda23'       => $isRoda23,
            ];

            return $this->buildBbn2Response($kendaraanData, $isRoda23, $tagihanPajak, $includeTembak, $statusPajak, $request, 'Estimasi BBN 2 Manual: ' . ($kendaraanData['jenis_kendaraan'] ?? 'Kendaraan'));
        }

        // Mode Nopol
        $request->validate([
            'nopol' => 'required|string|min:4|max:15|regex:/^[a-zA-Z0-9\s]+$/',
        ]);

        $nopolInput = preg_replace('/[^A-Z0-9]/', '', strtoupper((string)$request->input('nopol')));

        try {
            $taxCalc = $this->taxService->calculateNativeTax($nopolInput, false, $request->all());

            $isRoda23 = preg_match('/MOTOR|R2|R3|SEPEDA/i', $taxCalc['jenis_kendaraan'] ?? '') || preg_match('/MOTOR/i', $taxCalc['k_jr'] ?? '');
            
            // Hitung tagihan pajak berdasarkan status_pajak
            $tagihanPajak = 0;
            if ($statusPajak === 'lunas') {
                $tagihanPajak = 0;
            } elseif ($statusPajak === 'belum_bayar') {
                $tagihanPajak = (float)($taxCalc['total_pajak'] ?? 0);
            } else {
                // Otomatis: jika pajak sudah aktif/lunas -> 0, jika menunggak/terlambat -> total_pajak
                $isAktif = !empty($taxCalc['is_pajak_aktif']);
                $tagihanPajak = $isAktif ? 0 : (float)($taxCalc['total_pajak'] ?? 0);
            }

            $kendaraanData = [
                'nopol'           => $taxCalc['nopol'],
                'pemilik'         => $taxCalc['pemilik'],
                'alamat'          => $taxCalc['alamat'],
                'merk'            => $taxCalc['merk'],
                'tipe'            => $taxCalc['tipe'],
                'tahun'           => $taxCalc['tahun'],
                'unit'            => $taxCalc['unit'],
                'jenis_kendaraan' => $taxCalc['jenis_kendaraan'],
                'penggunaan'      => $taxCalc['penggunaan'],
                'sd_notice'       => $taxCalc['sd_notice'],
                'sd_stnk'         => $taxCalc['sd_stnk'],
                'is_pajak_aktif'  => $taxCalc['is_pajak_aktif'],
                'is_roda23'       => $isRoda23,
                'detail_pajak'    => [
                    'total_pajak'    => $taxCalc['total_pajak'],
                    'pkb_berjalan'   => $taxCalc['pkb_berjalan'],
                    'pkb_tunggakan'  => $taxCalc['pkb_tunggakan'],
                    'opsen_pkb'      => $taxCalc['opsen_pkb'],
                    'swdkllj'        => $taxCalc['swdkllj'] + $taxCalc['denda_swdkllj'],
                    'amnesti_potongan' => $taxCalc['amnesti_total_potongan'] ?? 0,
                ]
            ];

            return $this->buildBbn2Response($kendaraanData, $isRoda23, $tagihanPajak, $includeTembak, $statusPajak, $request, 'Estimasi BBN 2 Nopol ' . $nopolInput . ' (' . ($taxCalc['pemilik'] ?? '-') . ')');
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghitung BBN 2: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper pembangun respon BBN 2
     */
    protected function buildBbn2Response(array $kendaraan, bool $isRoda23, float $tagihanPajak, bool $includeTembak, string $statusPajak, ?Request $request = null, ?string $logDetail = null): JsonResponse
    {
        global $dbKonfigurasi, $dbTarifStnkTnkb;

        // 1. Biaya BPKB (R2/R3: 225.000, R4+: 375.000)
        $cfgBpkbR2 = (int)($dbKonfigurasi['tarif_bpkb_r2'] ?? 225000);
        $cfgBpkbR4 = (int)($dbKonfigurasi['tarif_bpkb_r4'] ?? 375000);
        $biayaBpkb = $isRoda23 ? $cfgBpkbR2 : $cfgBpkbR4;

        // 2 & 3. Biaya STNK dan TNKB (dari tabel tarif_stnk_tnkb)
        $jenisTarif = $isRoda23 ? 'MOTOR' : 'MOBIL';
        $tarifStnkRow = $dbTarifStnkTnkb[$jenisTarif] ?? [];
        $biayaStnk = (int)($tarifStnkRow['biaya_stnk'] ?? ($isRoda23 ? 100000 : 200000));
        $biayaTnkb = (int)($tarifStnkRow['biaya_tnkb'] ?? ($isRoda23 ? 60000 : 100000));
        $biayaStnkTnkbTotal = $biayaStnk + $biayaTnkb;

        // 4. Biaya Tembak jika tanpa gesek fisik (R2/R3: 210.000, R4+: 260.000)
        $cfgTembakR2 = (int)($dbKonfigurasi['tarif_tembak_r2'] ?? 210000);
        $cfgTembakR4 = (int)($dbKonfigurasi['tarif_tembak_r4'] ?? 260000);
        $tarifTembakSatuan = $isRoda23 ? $cfgTembakR2 : $cfgTembakR4;
        $biayaTembak = $includeTembak ? $tarifTembakSatuan : 0;

        // 5. Total Estimasi Bea Balik Nama ke-2
        $totalBbn2 = $tagihanPajak + $biayaBpkb + $biayaStnk + $biayaTnkb + $biayaTembak;

        if ($request && $logDetail) {
            $this->logActivityIfUser($request, 'Cek Estimasi BBNKB', $logDetail, (float)$totalBbn2);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'kendaraan' => $kendaraan,
                'rincian_biaya' => [
                    'tagihan_pajak'      => $tagihanPajak,
                    'status_pajak'       => $statusPajak,
                    'biaya_bpkb'         => $biayaBpkb,
                    'biaya_stnk'         => $biayaStnk,
                    'biaya_tnkb'         => $biayaTnkb,
                    'biaya_stnk_tnkb'    => $biayaStnkTnkbTotal,
                    'include_tembak'     => $includeTembak,
                    'tarif_tembak'       => $tarifTembakSatuan,
                    'biaya_tembak'       => $biayaTembak,
                    'total_biaya'        => $totalBbn2,
                ],
                'keterangan' => [
                    'kategori_roda' => $isRoda23 ? 'Roda 2 / Roda 3' : 'Roda 4 atau Lebih',
                    'pajak_status_label' => $tagihanPajak > 0 ? 'Pajak Menunggak/Harus Dibayar' : 'Pajak Sudah Lunas (Rp 0)',
                    'bpkb_label' => 'Penerbitan BPKB Baru (' . ($isRoda23 ? 'R2/R3' : 'R4+') . ')',
                    'stnk_label' => 'Penerbitan STNK Baru 5 Tahunan',
                    'tnkb_label' => 'Cetak TNKB Plat Nomor Baru',
                    'tembak_label' => $includeTembak ? 'Cek Fisik Bantuan / Tembak (Tanpa Gesek Fisik)' : 'Cek Fisik Standar (Langsung di Samsat)',
                ]
            ]
        ]);
    }

    /**
     * Helper mencatat riwayat aktivitas user
     */
    protected function logActivityIfUser(Request $request, string $activity, string $detail, ?float $totalBiaya = null): void
    {
        try {
            $token = null;
            $header = $request->header('Authorization', '');
            if (preg_match('/Bearer\s+(\S+)/i', $header, $matches)) {
                $token = $matches[1];
            } else {
                $token = $request->input('api_token');
            }

            if ($token) {
                $user = \Illuminate\Support\Facades\DB::table('users')->where('api_token', $token)->first();
                if ($user) {
                    \Illuminate\Support\Facades\DB::table('activity_logs')->insert([
                        'user_id'     => $user->id,
                        'username'    => $user->username,
                        'user_name'   => $user->name,
                        'activity'    => $activity,
                        'detail'      => $detail,
                        'total_biaya' => $totalBiaya !== null && $totalBiaya > 0 ? $totalBiaya : null,
                        'ip_address'  => $request->ip(),
                        'user_agent'  => substr((string)$request->userAgent(), 0, 250),
                        'created_at'  => now(),
                    ]);
                }
            }
        } catch (\Throwable $e) {}
    }
}

