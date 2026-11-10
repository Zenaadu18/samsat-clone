<?php

namespace App\Services;

use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProNttPaymentService
{
    protected TaxCalculationService $taxService;

    public function __construct(TaxCalculationService $taxService)
    {
        $this->taxService = $taxService;
    }

    /**
     * Format nopol bersih (mis. DH8349CB) menjadi format tampil (DH 8349 CB).
     */
    public function formatNopolDisplay(string $nopolClean): string
    {
        if (preg_match('/^([A-Z]{1,2})(\d{1,4})([A-Z]{0,3})$/', $nopolClean, $m)) {
            return trim("{$m[1]} {$m[2]} {$m[3]}");
        }
        return $nopolClean;
    }

    /**
     * Helper HTTP POST JSON ke gateway Senacode dengan fallback HTTP jika TLS timeout.
     */
    protected function postGateway(string $endpointPath, array $payload): array
    {
        $baseUrl = rtrim((string)config('services.senacode.base_url', 'https://api.senacode.my.id'), '/');
        $apiKey  = (string)config('services.senacode.api_key', 'senacode-samsat-key-2026');

        $candidates = [$baseUrl];
        if (stripos($baseUrl, 'https://') === 0) {
            $candidates[] = preg_replace('/^https:\/\//i', 'http://', $baseUrl);
        }
        $candidates = array_values(array_unique($candidates));

        $bodyJson = json_encode($payload);
        $lastError = 'Gagal terhubung ke server pembayaran PRO NTT.';

        foreach ($candidates as $host) {
            $url = $host . '/' . ltrim($endpointPath, '/');
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $bodyJson,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'X-API-Key: ' . $apiKey,
                    'Content-Length: ' . strlen((string)$bodyJson),
                ],
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT        => 28,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);

            $resp = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($curlErr || $httpCode === 0 || $httpCode >= 500) {
                $lastError = $curlErr ?: "Server merespon HTTP {$httpCode}";
                continue;
            }

            $decoded = json_decode((string)$resp, true);
            if (!is_array($decoded)) {
                $lastError = "Respon server bukan JSON valid (HTTP {$httpCode})";
                continue;
            }

            return [
                'ok'        => ($httpCode >= 200 && $httpCode < 300),
                'http_code' => $httpCode,
                'json'      => $decoded,
                'url'       => $url,
            ];
        }

        return [
            'ok'        => false,
            'http_code' => 502,
            'json'      => ['status' => 'error', 'message' => $lastError],
            'url'       => $baseUrl,
        ];
    }

    /**
     * Cek apakah waktu batas bayar sudah terlewati pada zona waktu WITA.
     */
    protected function isBatasBayarExpired(?string $batasBayar): bool
    {
        if (empty($batasBayar)) {
            return false;
        }
        try {
            $tz = new DateTimeZone('Asia/Makassar');
            $exp = new DateTime(trim($batasBayar), $tz);
            $now = new DateTime('now', $tz);
            return $now > $exp;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Terbitkan kode billing pembayaran PRO NTT (khusus kendaraan yang STNK belum jatuh tempo).
     */
    public function createBilling(string $nopol, ?string $nomorRangka = null, ?object $user = null): array
    {
        $nopolClean  = strtoupper((string)preg_replace('/[^A-Za-z0-9]/', '', $nopol));
        $rangkaClean = strtoupper((string)preg_replace('/[^A-Za-z0-9]/', '', (string)$nomorRangka));

        if ($nopolClean === '') {
            throw new Exception('Nomor polisi wajib diisi.', 400);
        }

        // Validasi status STNK & masa pajak kendaraan via TaxCalculationService
        try {
            $tax = $this->taxService->calculateNativeTax($nopolClean);
            if (!empty($tax['stnk_wajib']) || (int)($tax['biaya_stnk_total'] ?? 0) > 0) {
                throw new Exception(
                    'Kendaraan ini sudah masuk masa perpanjangan STNK 5 tahunan. Pembayaran online via PRO NTT hanya berlaku untuk pajak tahunan (STNK belum jatuh tempo).',
                    422
                );
            }
            if (!empty($tax['is_pajak_aktif'])) {
                throw new Exception(
                    'Pajak kendaraan ini masih aktif lebih dari 90 hari ke depan dan belum memasuki masa pembayaran.',
                    422
                );
            }
        } catch (Exception $e) {
            // Re-throw business block (422), tapi bila 404/network & user isi nomor rangka manual biarkan endpoint bayar memeriksa
            if ($e->getCode() === 422 || $rangkaClean === '') {
                throw $e;
            }
        }

        $payload = ['nopol' => $nopolClean];
        if ($rangkaClean !== '') {
            $payload['nomor_rangka'] = $rangkaClean;
        }

        $gateway = $this->postGateway('/bayar_pajak.php', $payload);
        $json = $gateway['json'];

        if (!$gateway['ok'] || ($json['status'] ?? '') !== 'success' || empty($json['pembayaran']['kode_bayar'])) {
            $code = $gateway['http_code'] >= 400 ? $gateway['http_code'] : 422;
            $errMsg = $json['message'] ?? 'Gagal membuat kode bayar di PRO NTT.';
            $ex = new Exception($errMsg, $code);
            throw $ex;
        }

        $pay  = $json['pembayaran'];
        $idn  = $json['identitas_kendaraan'] ?? [];
        $now  = now();

        $kodeBayar   = (string)$pay['kode_bayar'];
        $batasBayar  = $pay['batas_bayar'] ?? null;
        $isPaid      = (bool)($pay['is_paid'] ?? false);
        $isExpired   = !$isPaid && $this->isBatasBayarExpired($batasBayar);
        $statusBayar = strtoupper(trim((string)($pay['status_bayar'] ?? 'BELUM BAYAR')));
        if ($isPaid) {
            $statusBayar = 'LUNAS';
        } elseif ($isExpired) {
            $statusBayar = 'KEDALUWARSA';
        }

        $recordData = [
            'user_id'           => $user->id ?? null,
            'username'          => $user->username ?? null,
            'nopol'             => $nopolClean,
            'nopol_display'     => $json['nopol'] ?? $this->formatNopolDisplay($nopolClean),
            'nomor_rangka'      => $json['nomor_rangka'] ?? ($rangkaClean ?: null),
            'digit_5_rangka'    => $json['digit_5_rangka'] ?? ($rangkaClean !== '' ? substr($rangkaClean, -5) : null),
            'sumber_rangka'     => $json['sumber_rangka'] ?? null,
            'nama_pemilik'      => $idn['nama'] ?? null,
            'jenis'             => $idn['jenis'] ?? null,
            'merk'              => $idn['merk'] ?? null,
            'tipe'              => $idn['tipe'] ?? null,
            'tahun_buat'        => isset($idn['tahun_buat']) ? (string)$idn['tahun_buat'] : null,
            'unit'              => $idn['unit'] ?? trim(($idn['merk'] ?? '') . ' / ' . ($idn['tipe'] ?? ''), ' /'),
            'kode_bayar'        => $kodeBayar,
            'total_bayar'       => (int)($pay['total_bayar'] ?? 0),
            'batas_bayar'       => $batasBayar,
            'status_bayar'      => $statusBayar,
            'status_pesan'      => $pay['status_pesan'] ?? null,
            'is_paid'           => $isPaid,
            'is_expired'        => $isExpired,
            'is_pending'        => !$isPaid && !$isExpired,
            'kanal'             => $pay['kanal'] ?? '',
            'kanal_label'       => $pay['kanal_label'] ?? 'Bank NTT',
            'metode'            => $pay['metode'] ?? 'Bayar via mBanking, ATM, EDC, atau QRIS Bank NTT',
            'raw_response'      => json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'last_checked_at'   => $now,
            'updated_at'        => $now,
        ];

        $existingQuery = DB::table('pro_ntt_payments')
            ->where('nopol', $nopolClean)
            ->where('kode_bayar', $kodeBayar);
        if ($user && isset($user->id)) {
            $existingQuery->where('user_id', $user->id);
        }
        $existing = $existingQuery->first();

        if ($existing) {
            DB::table('pro_ntt_payments')->where('id', $existing->id)->update($recordData);
            $paymentId = $existing->id;
        } else {
            $recordData['created_at'] = $now;
            $paymentId = DB::table('pro_ntt_payments')->insertGetId($recordData);
        }

        $saved = DB::table('pro_ntt_payments')->where('id', $paymentId)->first();

        return [
            'payment'             => $this->formatPaymentRow($saved),
            'upstream'            => $json,
        ];
    }

    /**
     * Cek & perbarui status pembayaran terbaru dari endpoint cek_status_bayar.php per nopol.
     */
    public function checkStatus(string $nopol, ?int $paymentId = null, ?object $user = null): array
    {
        $nopolClean = strtoupper((string)preg_replace('/[^A-Za-z0-9]/', '', $nopol));
        if ($nopolClean === '') {
            throw new Exception('Nomor polisi wajib diisi.', 400);
        }

        $existing = $this->findTargetPaymentRow($nopolClean, $paymentId, $user);

        $gateway = $this->postGateway('/cek_status_bayar.php', ['nopol' => $nopolClean]);
        $json = $gateway['json'];
        $now = now();

        if ($gateway['ok'] && ($json['status'] ?? '') === 'success' && !empty($json['pembayaran'])) {
            $pay = $json['pembayaran'];
            $idn = $json['identitas_kendaraan'] ?? [];

            $kodeBayar = (string)($pay['kode_bayar'] ?? '');
            $isPaid    = (bool)($pay['is_paid'] ?? false);
            $isExpired = (bool)($pay['is_expired'] ?? false);
            $isPending = (bool)($pay['is_pending'] ?? (!$isPaid && !$isExpired));
            $batasBayar = $pay['batas_bayar'] ?? ($existing->batas_bayar ?? null);

            if (!$isPaid && !$isExpired && $this->isBatasBayarExpired($batasBayar)) {
                $isExpired = true;
                $isPending = false;
            }

            $statusBayar = strtoupper(trim((string)($pay['status_bayar'] ?? '')));
            if ($isPaid && ($statusBayar === '' || $statusBayar === 'BELUM BAYAR')) {
                $statusBayar = 'LUNAS';
            } elseif ($isExpired && ($statusBayar === '' || $statusBayar === 'BELUM BAYAR')) {
                $statusBayar = 'KEDALUWARSA';
            } elseif ($statusBayar === '') {
                $statusBayar = 'BELUM BAYAR';
            }

            // Pilih baris DB yang cocok dengan kode_bayar atau baris existing bila belum punya / sama
            $targetRow = $existing;
            if ($kodeBayar !== '' && (!$targetRow || (!empty($targetRow->kode_bayar) && $targetRow->kode_bayar !== $kodeBayar))) {
                $matchByKode = DB::table('pro_ntt_payments')
                    ->where('nopol', $nopolClean)
                    ->where('kode_bayar', $kodeBayar)
                    ->when($user && isset($user->id), fn($q) => $q->where('user_id', $user->id))
                    ->first();
                if ($matchByKode) {
                    $targetRow = $matchByKode;
                } elseif ($targetRow && $targetRow->kode_bayar !== $kodeBayar) {
                    // Jangan timpa kode bayar lain jika upstream punya transaksi lebih baru
                    $targetRow = null;
                }
            }

            $updateFields = [
                'user_id'           => $targetRow->user_id ?? ($user->id ?? null),
                'username'          => $targetRow->username ?? ($user->username ?? null),
                'nopol'             => $nopolClean,
                'nopol_display'     => $json['nopol'] ?? $this->formatNopolDisplay($nopolClean),
                'nama_pemilik'      => $idn['nama'] ?? ($targetRow->nama_pemilik ?? null),
                'kode_bayar'        => $kodeBayar ?: ($targetRow->kode_bayar ?? null),
                'total_bayar'       => (int)($pay['total_bayar'] ?? ($targetRow->total_bayar ?? 0)),
                'status_bayar'      => $statusBayar,
                'status_pesan'      => $pay['status_pesan'] ?? null,
                'is_paid'           => $isPaid,
                'is_expired'        => $isExpired,
                'is_pending'        => $isPending,
                'tanggal_transaksi' => $pay['tanggal_transaksi'] ?? ($targetRow->tanggal_transaksi ?? null),
                'batas_bayar'       => $batasBayar,
                'waktu_pembayaran'  => $pay['waktu_pembayaran'] ?? null,
                'ref_bank'          => $pay['ref_bank'] ?? null,
                'keterangan'        => $pay['keterangan'] ?? null,
                'raw_response'      => json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'last_checked_at'   => $now,
                'updated_at'        => $now,
            ];

            if ($targetRow) {
                DB::table('pro_ntt_payments')->where('id', $targetRow->id)->update($updateFields);
                $rowId = $targetRow->id;
            } else {
                $updateFields['kanal_label'] = 'Bank NTT';
                $updateFields['metode']      = 'Bayar via mBanking, ATM, EDC, atau QRIS Bank NTT';
                $updateFields['created_at']  = $now;
                $rowId = DB::table('pro_ntt_payments')->insertGetId($updateFields);
            }

            if ($isPaid) {
                Cache::forget('samsat_nopol_' . md5($nopolClean));
            }

            $saved = DB::table('pro_ntt_payments')->where('id', $rowId)->first();

            return [
                'found_on_upstream' => true,
                'message'           => $isPaid
                    ? 'Pembayaran telah LUNAS terkonfirmasi.'
                    : ($isExpired ? 'Kode billing telah melewati batas waktu pembayaran.' : 'Status terbaru berhasil diperbarui.'),
                'checked_at'        => $json['checked_at'] ?? $now->format('Y-m-d H:i:s'),
                'payment'           => $this->formatPaymentRow($saved),
            ];
        }

        // Jika upstream belum menemukan riwayat (misal HTTP 404 IWANA), evaluasi batas waktu secara lokal tanpa menghapus kode bayar
        $upstreamMsg = $json['message'] ?? 'Riwayat transaksi belum tersedia di server IWANA.';

        if ($existing) {
            $isExpired = !$existing->is_paid && ($existing->is_expired || $this->isBatasBayarExpired($existing->batas_bayar));
            $statusBayar = $existing->is_paid
                ? 'LUNAS'
                : ($isExpired ? 'KEDALUWARSA' : ($existing->status_bayar ?: 'BELUM BAYAR'));

            DB::table('pro_ntt_payments')->where('id', $existing->id)->update([
                'is_expired'      => $isExpired,
                'is_pending'      => !$existing->is_paid && !$isExpired,
                'status_bayar'    => $statusBayar,
                'last_checked_at' => $now,
                'updated_at'      => $now,
            ]);

            $saved = DB::table('pro_ntt_payments')->where('id', $existing->id)->first();

            return [
                'found_on_upstream' => false,
                'message'           => $isExpired
                    ? 'Batas waktu bayar sudah lewat (KEDALUWARSA).'
                    : $upstreamMsg,
                'checked_at'        => $now->format('Y-m-d H:i:s'),
                'payment'           => $this->formatPaymentRow($saved),
            ];
        }

        throw new Exception($upstreamMsg, $gateway['http_code'] >= 400 ? $gateway['http_code'] : 404);
    }

    /**
     * Ambil daftar riwayat pembayaran PRO NTT milik user.
     */
    public function getHistory(?object $user = null, bool $showAll = false): array
    {
        $query = DB::table('pro_ntt_payments');
        if ($user && isset($user->id)) {
            $query->where('user_id', $user->id);
        }

        $total = (clone $query)->count();
        $limit = $showAll ? 50 : 10;

        $rows = $query->orderByDesc('id')->limit($limit)->get();

        $items = $rows->map(fn($row) => $this->formatPaymentRow($row))->values()->all();

        return [
            'payments' => $items,
            'total'    => $total,
            'is_all'   => $showAll,
            'has_more' => !$showAll && $total > $limit,
        ];
    }

    protected function findTargetPaymentRow(string $nopolClean, ?int $paymentId, ?object $user): ?object
    {
        if ($paymentId !== null && $paymentId > 0) {
            $q = DB::table('pro_ntt_payments')
                ->where('id', $paymentId)
                ->where('nopol', $nopolClean);
            if ($user && isset($user->id)) {
                $q->where('user_id', $user->id);
            }
            $row = $q->first();
            if ($row) {
                return $row;
            }
        }

        return DB::table('pro_ntt_payments')
            ->where('nopol', $nopolClean)
            ->when($user && isset($user->id), fn($q) => $q->where('user_id', $user->id))
            ->orderByDesc('id')
            ->first();
    }

    protected function formatPaymentRow(object $row): array
    {
        $isPaid = (bool)$row->is_paid;
        $isExpired = !$isPaid && ((bool)$row->is_expired || $this->isBatasBayarExpired($row->batas_bayar));
        $statusBayar = $isPaid ? 'LUNAS' : ($isExpired ? 'KEDALUWARSA' : ($row->status_bayar ?: 'BELUM BAYAR'));

        return [
            'id'                    => (int)$row->id,
            'nopol'                 => $row->nopol,
            'nopol_display'         => $row->nopol_display ?: $this->formatNopolDisplay($row->nopol),
            'nomor_rangka'          => $row->nomor_rangka,
            'digit_5_rangka'        => $row->digit_5_rangka,
            'sumber_rangka'         => $row->sumber_rangka,
            'nama_pemilik'          => $row->nama_pemilik,
            'jenis'                 => $row->jenis,
            'merk'                  => $row->merk,
            'tipe'                  => $row->tipe,
            'tahun_buat'            => $row->tahun_buat,
            'unit'                  => $row->unit,
            'kode_bayar'            => $row->kode_bayar,
            'total_bayar'           => (int)$row->total_bayar,
            'total_bayar_formatted' => 'Rp ' . number_format((int)$row->total_bayar, 0, ',', '.'),
            'batas_bayar'           => $row->batas_bayar,
            'status_bayar'          => $statusBayar,
            'status_pesan'          => $row->status_pesan,
            'is_paid'               => $isPaid,
            'is_expired'            => $isExpired,
            'is_pending'            => !$isPaid && !$isExpired,
            'kanal'                 => $row->kanal,
            'kanal_label'           => $row->kanal_label ?: 'Bank NTT',
            'metode'                => $row->metode ?: 'Bayar via mBanking, ATM, EDC, atau QRIS Bank NTT',
            'tanggal_transaksi'     => $row->tanggal_transaksi,
            'waktu_pembayaran'      => $row->waktu_pembayaran,
            'ref_bank'              => $row->ref_bank,
            'keterangan'            => $row->keterangan,
            'last_checked_at'       => $row->last_checked_at,
            'created_at'            => $row->created_at,
            'updated_at'            => $row->updated_at,
        ];
    }
}

