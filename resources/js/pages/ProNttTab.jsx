import React, { useState, useEffect, useRef } from 'react';
import {
  IconBuildingBank,
  IconRefresh,
  IconCheck,
  IconCopy,
  IconAlertTriangle,
  IconAlertCircle,
  IconClock,
  IconSearch,
  IconShieldCheck,
  IconHistory,
  IconReceiptTax
} from '@tabler/icons-react';
import { formatRupiah, formatDate, getKeteranganJatuhTempo } from '../utils/formatters';
import { getApiBase, getAuthToken } from '../utils/config';

export default function ProNttTab({ prefillNopol = '', initialTaxData = null, onSwitchToTaxCheck = null }) {
  // 3 Smart Plate Boxes states
  const [nopolPrefix, setNopolPrefix] = useState('');
  const [nopolNumber, setNopolNumber] = useState('');
  const [nopolSuffix, setNopolSuffix] = useState('');
  const [nomorRangka, setNomorRangka] = useState('');
  const [showRangkaInput, setShowRangkaInput] = useState(false);

  const box1Ref = useRef(null);
  const box2Ref = useRef(null);
  const box3Ref = useRef(null);

  const [checkingTax, setCheckingTax] = useState(false);
  const [paying, setPaying] = useState(false);
  const [error, setError] = useState(null);
  const [taxData, setTaxData] = useState(null);
  const [activePayment, setActivePayment] = useState(null);
  const [statusNotice, setStatusNotice] = useState(null);
  const [copiedKode, setCopiedKode] = useState(false);

  // Riwayat pembayaran PRO NTT per nopol
  const [history, setHistory] = useState([]);
  const [loadingHistory, setLoadingHistory] = useState(true);
  const [historyAll, setHistoryAll] = useState(false);
  const [historyHasMore, setHistoryHasMore] = useState(false);
  const [historyTotal, setHistoryTotal] = useState(0);
  const [refreshingId, setRefreshingId] = useState(null);
  const [copiedHistoryId, setCopiedHistoryId] = useState(null);
  const [itemMessages, setItemMessages] = useState({});

  const buildAuthHeaders = (includeJson = false) => {
    const token = getAuthToken();
    const headers = {
      'Accept': 'application/json',
      'Authorization': token ? `Bearer ${token}` : '',
      'X-Api-Token': token || '',
    };
    if (includeJson) {
      headers['Content-Type'] = 'application/json';
      headers['X-CSRF-TOKEN'] = window.__SAMSAT_CONFIG__?.csrfToken || '';
    }
    return headers;
  };

  const splitNopol = (str) => {
    if (!str) return { prefix: '', number: '', suffix: '' };
    const clean = str.toUpperCase().replace(/[^A-Z0-9]/g, '');
    const match = clean.match(/^([A-Z]{1,2})([0-9]{1,4})([A-Z]{0,3})$/);
    if (match) {
      return {
        prefix: match[1] || '',
        number: match[2] || '',
        suffix: match[3] || '',
      };
    }
    const parts = str.trim().toUpperCase().split(/\s+/);
    if (parts.length >= 2) {
      return {
        prefix: parts[0].slice(0, 2) || '',
        number: parts[1].replace(/[^0-9]/g, '').slice(0, 4),
        suffix: (parts[2] || '').replace(/[^A-Z]/g, '').slice(0, 3),
      };
    }
    return { prefix: '', number: '', suffix: '' };
  };

  const fetchHistory = async (showAll = false) => {
    setLoadingHistory(true);
    try {
      const url = `${getApiBase()}/pro-ntt/history${showAll ? '?all=1' : ''}`;
      const res = await fetch(url, { headers: buildAuthHeaders(false) });
      const json = await res.json();
      if (res.ok && json.status === 'success') {
        setHistory(json.data.payments || []);
        setHistoryTotal(json.data.total || 0);
        setHistoryHasMore(Boolean(json.data.has_more));
        setHistoryAll(showAll);
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoadingHistory(false);
    }
  };

  useEffect(() => {
    fetchHistory(false);
  }, []);

  useEffect(() => {
    if (!prefillNopol) return;
    const parsed = splitNopol(prefillNopol);
    setNopolPrefix(parsed.prefix);
    setNopolNumber(parsed.number);
    setNopolSuffix(parsed.suffix);
    setActivePayment(null);
    setStatusNotice(null);
    setError(null);

    const cleanTarget = `${parsed.prefix}${parsed.number}${parsed.suffix}`.toUpperCase();
    const cleanInitial = (initialTaxData?.nopol || '').toUpperCase().replace(/[^A-Z0-9]/g, '');

    if (initialTaxData && cleanInitial === cleanTarget) {
      setTaxData(initialTaxData);
    } else {
      handleCheckTagihan(cleanTarget);
    }
  }, [prefillNopol, initialTaxData]);

  const handlePaste = (e) => {
    e.preventDefault();
    const pasted = (e.clipboardData || window.clipboardData).getData('text');
    if (!pasted) return;
    const parsed = splitNopol(pasted);
    if (parsed.number) {
      setNopolPrefix(parsed.prefix);
      setNopolNumber(parsed.number);
      setNopolSuffix(parsed.suffix);
      box3Ref.current?.focus();
    }
  };

  const handlePrefixChange = (e) => {
    let val = e.target.value.toUpperCase().replace(/[^A-Z]/g, '');
    if (val.length > 2) val = val.slice(0, 2);
    setNopolPrefix(val);
    if (val.length === 2) box2Ref.current?.focus();
  };

  const handleNumberChange = (e) => {
    let val = e.target.value.replace(/[^0-9]/g, '');
    if (val.length > 4) val = val.slice(0, 4);
    setNopolNumber(val);
    if (val.length === 4) box3Ref.current?.focus();
  };

  const handleNumberKeyDown = (e) => {
    if (e.key === 'Backspace' && !nopolNumber) {
      box1Ref.current?.focus();
    }
  };

  const handleSuffixChange = (e) => {
    let val = e.target.value.toUpperCase().replace(/[^A-Z]/g, '');
    if (val.length > 3) val = val.slice(0, 3);
    setNopolSuffix(val);
  };

  const handleSuffixKeyDown = (e) => {
    if (e.key === 'Backspace' && !nopolSuffix) {
      box2Ref.current?.focus();
    }
  };

  const getCurrentNopol = () => {
    return `${nopolPrefix.trim()}${nopolNumber.trim()}${nopolSuffix.trim()}`.toUpperCase();
  };

  const handleCheckTagihan = async (overrideNopol = null) => {
    const cleanNopol = (overrideNopol ?? getCurrentNopol()).toUpperCase().replace(/[^A-Z0-9]/g, '');
    if (!cleanNopol || cleanNopol.length < 4) {
      setError('Masukkan nomor polisi kendaraan yang valid terlebih dahulu.');
      return;
    }

    setCheckingTax(true);
    setError(null);
    setStatusNotice(null);
    setActivePayment(null);

    try {
      const res = await fetch(`${getApiBase()}/check`, {
        method: 'POST',
        headers: buildAuthHeaders(true),
        body: JSON.stringify({ nopol: cleanNopol, is_mutasi: false }),
      });
      const json = await res.json();
      if (!res.ok || json.status !== 'success') {
        throw new Error(json.message || 'Gagal memeriksa tagihan kendaraan.');
      }
      setTaxData(json.data);
    } catch (e) {
      setTaxData(null);
      setError(e.message || 'Data pajak kendaraan tidak ditemukan.');
      // Buka input rangka cadangan apabila cek nopol malam hari gagal mengambil nomor rangka
      setShowRangkaInput(true);
    } finally {
      setCheckingTax(false);
    }
  };

  const handleBayar = async () => {
    const cleanNopol = (taxData?.nopol || getCurrentNopol()).toUpperCase().replace(/[^A-Z0-9]/g, '');
    if (!cleanNopol || cleanNopol.length < 4) {
      setError('Nomor polisi belum diisi.');
      return;
    }

    setPaying(true);
    setError(null);
    setStatusNotice(null);

    try {
      const payload = { nopol: cleanNopol };
      const cleanRangka = nomorRangka.trim().toUpperCase().replace(/[^A-Z0-9]/g, '');
      if (cleanRangka.length >= 5) {
        payload.nomor_rangka = cleanRangka;
      }

      const res = await fetch(`${getApiBase()}/pro-ntt/bayar`, {
        method: 'POST',
        headers: buildAuthHeaders(true),
        body: JSON.stringify(payload),
      });
      const json = await res.json();

      if (!res.ok || json.status !== 'success') {
        if ((json.message || '').toLowerCase().includes('rangka')) {
          setShowRangkaInput(true);
        }
        throw new Error(json.message || 'Gagal membuat kode bayar PRO NTT.');
      }

      const payment = json.data;
      setActivePayment(payment);
      setStatusNotice('Kode bayar Bank NTT berhasil diterbitkan. Segera lakukan pembayaran sebelum batas waktu.');
      fetchHistory(historyAll);
    } catch (e) {
      setError(e.message || 'Gagal terhubung ke endpoint pembayaran PRO NTT.');
    } finally {
      setPaying(false);
    }
  };

  const handleRefreshStatus = async (nopol, paymentId = null, itemKey = 'active') => {
    setRefreshingId(itemKey);
    try {
      const res = await fetch(`${getApiBase()}/pro-ntt/status`, {
        method: 'POST',
        headers: buildAuthHeaders(true),
        body: JSON.stringify({ nopol, payment_id: paymentId }),
      });
      const json = await res.json();

      if (!res.ok || json.status !== 'success') {
        throw new Error(json.message || 'Gagal memeriksa status pembayaran.');
      }

      const updated = json.data?.payment;
      const msg = json.message || 'Status diperbarui.';

      if (updated) {
        if (activePayment && (activePayment.id === updated.id || activePayment.kode_bayar === updated.kode_bayar)) {
          setActivePayment(updated);
          setStatusNotice(msg);
        }

        setHistory((prev) => {
          const exists = prev.some((p) => p.id === updated.id);
          if (exists) {
            return prev.map((p) => (p.id === updated.id ? updated : p));
          }
          return [updated, ...prev];
        });

        setItemMessages((prev) => ({
          ...prev,
          [itemKey]: {
            text: msg,
            isPaid: Boolean(updated.is_paid),
            isFound: Boolean(json.data?.found_on_upstream),
          },
        }));
      }
    } catch (e) {
      setItemMessages((prev) => ({
        ...prev,
        [itemKey]: {
          text: e.message || 'Gagal menyegarkan status.',
          isError: true,
        },
      }));
    } finally {
      setRefreshingId(null);
    }
  };

  const copyKodeBayar = (kode, historyId = null) => {
    if (!kode) return;
    navigator.clipboard?.writeText(kode).then(() => {
      if (historyId !== null) {
        setCopiedHistoryId(historyId);
        setTimeout(() => setCopiedHistoryId(null), 2000);
      } else {
        setCopiedKode(true);
        setTimeout(() => setCopiedKode(false), 2000);
      }
    }).catch(() => {});
  };

  const getStatusBadgeStyle = (payment) => {
    if (payment?.is_paid) {
      return { backgroundColor: '#dcfce7', color: '#15803d', borderColor: '#86efac' };
    }
    if (payment?.is_expired) {
      return { backgroundColor: '#fee2e2', color: '#b91c1c', borderColor: '#fca5a5' };
    }
    return { backgroundColor: '#fef3c7', color: '#b45309', borderColor: '#fde68a' };
  };

  const stnkExpired = Boolean(taxData && (taxData.stnk_wajib || (taxData.biaya_stnk_total || 0) > 0));
  const pajakAktif = Boolean(taxData && taxData.is_pajak_aktif);
  const canPayOnline = Boolean(taxData && !stnkExpired && !pajakAktif);
  const estimasiOnline = taxData
    ? ((taxData.total_setelah_diskon > 0 ? taxData.total_setelah_diskon : taxData.total_pajak) || 0)
    : 0;
  const tempoInfo = taxData ? getKeteranganJatuhTempo(taxData.sd_notice, taxData) : null;

  return (
    <div className="p-4 sm:p-6 space-y-5 max-w-2xl mx-auto">
      {/* Input Nopol & Cek Tagihan */}
      <div className="bg-white rounded-2xl p-4 sm:p-6 shadow-xs border border-slate-200/90 space-y-4">
        <div className="flex items-start justify-between gap-3">
          <div>
            <h2 className="text-base sm:text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
              <span
                className="p-1.5 rounded-lg shrink-0 flex items-center justify-center"
                style={{ backgroundColor: '#eff6ff', color: '#1d4ed8' }}
              >
                <IconBuildingBank size={19} stroke={2.2} className="shrink-0" />
              </span>
              Pembayaran Pajak Online (PRO NTT)
            </h2>
            <p className="text-xs text-slate-500 mt-1">
              Terbitkan kode bayar Bank NTT khusus pengesahan pajak tahunan (STNK belum jatuh tempo).
            </p>
          </div>
        </div>

        <form
          onSubmit={(e) => {
            e.preventDefault();
            handleCheckTagihan();
          }}
          className="space-y-3"
        >
          <div className="bg-slate-50/90 p-3 rounded-2xl border border-slate-200 shadow-inner">
            <div className="grid grid-cols-12 gap-2 sm:gap-3 items-center">
              <div className="col-span-3">
                <label className="block text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center mb-1.5">
                  Wilayah
                </label>
                <input
                  ref={box1Ref}
                  type="text"
                  value={nopolPrefix}
                  onChange={handlePrefixChange}
                  onPaste={handlePaste}
                  placeholder="DH"
                  maxLength={2}
                  className="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-1 text-center font-mono-num font-black text-base sm:text-lg text-slate-900 uppercase placeholder:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-600 shadow-xs"
                />
              </div>
              <div className="col-span-6">
                <label className="block text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center mb-1.5">
                  1-4 Angka
                </label>
                <input
                  ref={box2Ref}
                  type="text"
                  inputMode="numeric"
                  pattern="[0-9]*"
                  value={nopolNumber}
                  onChange={handleNumberChange}
                  onKeyDown={handleNumberKeyDown}
                  onPaste={handlePaste}
                  placeholder="2347"
                  maxLength={4}
                  className="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-2 text-center font-mono-num font-black text-base sm:text-lg text-slate-900 tracking-wider placeholder:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-600 shadow-xs"
                />
              </div>
              <div className="col-span-3">
                <label className="block text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center mb-1.5">
                  Seri
                </label>
                <input
                  ref={box3Ref}
                  type="text"
                  value={nopolSuffix}
                  onChange={handleSuffixChange}
                  onKeyDown={handleSuffixKeyDown}
                  onPaste={handlePaste}
                  placeholder="CS"
                  maxLength={3}
                  className="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-1 text-center font-mono-num font-black text-base sm:text-lg text-slate-900 uppercase placeholder:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-600 shadow-xs"
                />
              </div>
            </div>
          </div>

          <div className="flex items-center justify-between px-1">
            <button
              type="button"
              onClick={() => setShowRangkaInput((v) => !v)}
              className="text-[11px] font-semibold text-slate-500 hover:text-blue-600 cursor-pointer"
            >
              {showRangkaInput ? 'Sembunyikan input No. Rangka' : '+ Input 5 digit terakhir No. Rangka (opsional)'}
            </button>
          </div>

          {showRangkaInput && (
            <div>
              <label className="block text-[11px] font-bold text-slate-600 mb-1">
                5 Digit Terakhir Nomor Rangka (Fallback jika diperlukan)
              </label>
              <input
                type="text"
                value={nomorRangka}
                onChange={(e) => setNomorRangka(e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, ''))}
                placeholder="Contoh: 83112"
                maxLength={25}
                className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/40"
              />
            </div>
          )}

          <button
            type="submit"
            disabled={checkingTax || paying}
            className="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-bold py-2.5 sm:py-3 px-4 rounded-xl text-xs sm:text-sm shadow-md shadow-blue-600/20 active:scale-98 transition-all flex items-center justify-center gap-2 cursor-pointer"
          >
            {checkingTax ? (
              <>
                <IconRefresh size={17} className="animate-spin shrink-0" />
                <span>Memeriksa Tagihan...</span>
              </>
            ) : (
              <>
                <IconSearch size={17} stroke={2.2} className="shrink-0" />
                <span>Cek Tagihan Siap Bayar</span>
              </>
            )}
          </button>
        </form>

        {error && (
          <div className="bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-3 text-xs flex items-start gap-2">
            <IconAlertCircle size={17} className="text-rose-600 shrink-0 mt-0.5" />
            <div className="flex-1 space-y-2">
              <p className="font-semibold leading-relaxed">{error}</p>
              {!taxData && nomorRangka.trim().length >= 5 && (
                <button
                  type="button"
                  onClick={handleBayar}
                  disabled={paying}
                  className="bg-rose-600 hover:bg-rose-700 text-white font-bold px-3 py-1.5 rounded-lg text-[11px] cursor-pointer"
                >
                  {paying ? 'Memproses...' : 'Lanjut Buat Kode Bayar dengan No. Rangka Manual'}
                </button>
              )}
            </div>
          </div>
        )}
      </div>

      {/* Ringkasan Tagihan Pra-Bayar */}
      {taxData && (
        <div className="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/90 space-y-4">
          <div className="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
              <div className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                Tagihan Pajak Kendaraan
              </div>
              <div className="font-mono-num text-lg font-black text-slate-900 mt-0.5">
                {taxData.nopol}
              </div>
              <div className="text-xs font-semibold text-slate-700">{taxData.pemilik || '-'}</div>
              <div className="text-[11px] text-slate-500">{taxData.unit || '-'} ({taxData.tahun || '-'})</div>
            </div>
            {tempoInfo && (
              <span className="px-2.5 py-1 rounded-lg text-[10px] font-bold border bg-slate-50 text-slate-700 border-slate-200 shrink-0">
                {tempoInfo.teks}
              </span>
            )}
          </div>

          <div className="grid grid-cols-2 gap-2.5 text-xs bg-slate-50 p-3 rounded-xl border border-slate-200/70">
            <div>
              <span className="text-[10px] text-slate-400 block">Jatuh Tempo Pajak</span>
              <span className="font-bold text-slate-800">{formatDate(taxData.sd_notice)}</span>
            </div>
            <div>
              <span className="text-[10px] text-slate-400 block">Jatuh Tempo STNK (5 Thn)</span>
              <span className={`font-bold ${stnkExpired ? 'text-rose-600' : 'text-emerald-700'}`}>
                {formatDate(taxData.sd_stnk)}
              </span>
            </div>
          </div>

          {stnkExpired ? (
            <div className="bg-rose-50 border border-rose-200 rounded-xl p-3.5 text-xs text-rose-800 flex items-start gap-2.5">
              <IconAlertTriangle size={18} className="text-rose-600 shrink-0 mt-0.5" />
              <div>
                <div className="font-bold">Wajib Perpanjangan STNK 5 Tahunan di Samsat</div>
                <p className="mt-0.5 leading-relaxed text-[11px]">
                  Masa berlaku STNK kendaraan ini sudah jatuh tempo/termasuk biaya penerbitan STNK &amp; TNKB ({formatRupiah(taxData.biaya_stnk_total)}). Pembayaran online PRO NTT hanya melayani pengesahan pajak tahunan.
                </p>
              </div>
            </div>
          ) : pajakAktif ? (
            <div className="bg-emerald-50 border border-emerald-200 rounded-xl p-3.5 text-xs text-emerald-800 flex items-start gap-2.5">
              <IconShieldCheck size={18} className="text-emerald-600 shrink-0 mt-0.5" />
              <div>
                <div className="font-bold">Pajak Kendaraan Masih Aktif</div>
                <p className="mt-0.5 text-[11px]">
                  Masa pajak masih berlaku lebih dari 90 hari ke depan sehingga belum dapat diterbitkan kode bayar.
                </p>
              </div>
            </div>
          ) : (
            <div className="space-y-3">
              <div
                className="rounded-xl p-3.5 border border-emerald-200 flex items-center justify-between"
                style={{ backgroundColor: '#ecfdf5' }}
              >
                <div>
                  <span className="text-[11px] font-bold text-emerald-800 block">
                    Estimasi Bayar Online (PRO NTT)
                  </span>
                  <span className="text-[10px] text-emerald-700">
                    STNK masih berlaku • Pengesahan tahunan
                  </span>
                </div>
                <span className="font-mono-num text-lg sm:text-xl font-black text-emerald-800">
                  {formatRupiah(estimasiOnline)}
                </span>
              </div>

              <div className="flex flex-col sm:flex-row gap-2.5">
                {onSwitchToTaxCheck && (
                  <button
                    type="button"
                    onClick={() => onSwitchToTaxCheck(taxData.nopol)}
                    className="sm:w-auto px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs flex items-center justify-center gap-1.5 cursor-pointer"
                  >
                    <IconReceiptTax size={16} className="text-blue-600" />
                    <span>Lihat Rincian</span>
                  </button>
                )}
                <button
                  type="button"
                  onClick={handleBayar}
                  disabled={!canPayOnline || paying}
                  className="flex-1 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white font-bold py-2.5 sm:py-3 px-4 rounded-xl text-xs sm:text-sm shadow-md shadow-emerald-600/20 active:scale-98 transition-all flex items-center justify-center gap-2 cursor-pointer"
                >
                  {paying ? (
                    <>
                      <IconRefresh size={17} className="animate-spin shrink-0" />
                      <span>Menerbitkan Kode Bayar...</span>
                    </>
                  ) : (
                    <>
                      <IconBuildingBank size={18} stroke={2.2} className="shrink-0" />
                      <span>Bayar Sekarang (Buat Kode Bayar)</span>
                    </>
                  )}
                </button>
              </div>
            </div>
          )}
        </div>
      )}

      {/* Kartu Hasil Kode Bayar Aktif */}
      {activePayment && (
        <div
          className="rounded-2xl p-4 sm:p-5 shadow-sm border-2 border-blue-500/80 space-y-4"
          style={{
            backgroundColor: '#eff6ff',
            background: 'linear-gradient(135deg, #eff6ff 0%, #ffffff 70%)',
          }}
        >
          <div className="flex items-start justify-between gap-2">
            <div>
              <span className="text-[10px] font-bold uppercase tracking-wider text-blue-700">
                Kode Billing Pembayaran ({activePayment.kanal_label || 'Bank NTT'})
              </span>
              <div className="font-mono-num text-sm font-black text-slate-900 mt-0.5">
                {activePayment.nopol_display || activePayment.nopol} • {activePayment.nama_pemilik || '-'}
              </div>
              {activePayment.unit && (
                <div className="text-[11px] text-slate-500">{activePayment.unit}</div>
              )}
            </div>
            <span
              className="px-2.5 py-1 rounded-lg text-[10px] font-extrabold border shrink-0"
              style={getStatusBadgeStyle(activePayment)}
            >
              {activePayment.status_bayar}
            </span>
          </div>

          {/* Box Kode Bayar */}
          <div className="bg-white rounded-xl p-3.5 border border-blue-200 flex items-center justify-between gap-3 shadow-2xs">
            <div>
              <span className="text-[10px] font-bold text-slate-400 uppercase block">Kode Bayar</span>
              <span className="font-mono-num text-xl sm:text-2xl font-black text-blue-800 tracking-wider select-all">
                {activePayment.kode_bayar}
              </span>
            </div>
            <button
              type="button"
              onClick={() => copyKodeBayar(activePayment.kode_bayar)}
              className="px-3 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs flex items-center gap-1.5 border border-blue-200 cursor-pointer shrink-0"
            >
              {copiedKode ? <IconCheck size={15} className="text-emerald-600" /> : <IconCopy size={15} />}
              <span>{copiedKode ? 'Disalin!' : 'Salin'}</span>
            </button>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs bg-white/90 p-3 rounded-xl border border-slate-200/80">
            <div>
              <span className="text-[10px] text-slate-400 block">Total Pembayaran</span>
              <span className="font-mono-num font-black text-slate-900 text-sm">
                {activePayment.total_bayar_formatted || formatRupiah(activePayment.total_bayar)}
              </span>
            </div>
            <div>
              <span className="text-[10px] text-slate-400 block">Batas Waktu Bayar</span>
              <span className="font-bold text-slate-800">{activePayment.batas_bayar || '-'}</span>
            </div>
            <div>
              <span className="text-[10px] text-slate-400 block">Kanal Pembayaran</span>
              <span className="font-semibold text-slate-700">{activePayment.metode}</span>
            </div>
            {activePayment.waktu_pembayaran && (
              <div>
                <span className="text-[10px] text-slate-400 block">Waktu Lunas / Ref Bank</span>
                <span className="font-bold text-emerald-700">
                  {activePayment.waktu_pembayaran} {activePayment.ref_bank ? `(${activePayment.ref_bank})` : ''}
                </span>
              </div>
            )}
          </div>

          {statusNotice && (
            <p className="text-[11px] font-medium text-blue-800 leading-relaxed">{statusNotice}</p>
          )}

          <button
            type="button"
            onClick={() => handleRefreshStatus(activePayment.nopol, activePayment.id, 'active')}
            disabled={refreshingId === 'active'}
            className="w-full bg-white hover:bg-blue-50 border border-blue-300 text-blue-700 font-bold py-2.5 px-4 rounded-xl text-xs flex items-center justify-center gap-2 cursor-pointer"
          >
            <IconRefresh size={16} className={refreshingId === 'active' ? 'animate-spin' : ''} />
            <span>{refreshingId === 'active' ? 'Memeriksa Status Bayar...' : 'Refresh Status Pembayaran'}</span>
          </button>
        </div>
      )}

      {/* Riwayat Pembayaran PRO NTT per Nopol */}
      <div className="bg-white rounded-2xl shadow-xs border border-slate-200/90 overflow-hidden">
        <div className="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between gap-2">
          <div>
            <h3 className="text-sm sm:text-base font-black text-slate-900 flex items-center gap-2">
              <IconHistory size={18} className="text-blue-600 shrink-0" />
              <span>Riwayat Pembayaran PRO NTT</span>
            </h3>
            <p className="text-[11px] text-slate-500 mt-0.5">
              Klik ikon refresh di tiap nopol untuk cek &amp; perbarui status pembayaran terbaru.
            </p>
          </div>
          <button
            type="button"
            onClick={() => fetchHistory(historyAll)}
            title="Muat ulang daftar riwayat"
            className="p-2 text-slate-400 hover:text-blue-600 rounded-xl hover:bg-slate-100 cursor-pointer shrink-0"
          >
            <IconRefresh size={18} className={loadingHistory ? 'animate-spin text-blue-600' : ''} />
          </button>
        </div>

        <div className="divide-y divide-slate-100">
          {loadingHistory && history.length === 0 ? (
            <div className="p-8 text-center text-xs text-slate-400">
              <IconRefresh size={22} className="animate-spin mx-auto mb-2 text-slate-300" />
              Memuat riwayat pembayaran PRO NTT...
            </div>
          ) : history.length === 0 ? (
            <div className="p-8 text-center text-xs text-slate-400 space-y-1">
              <IconBuildingBank size={26} className="mx-auto text-slate-300 mb-1.5" />
              <p className="font-semibold text-slate-700">Belum ada riwayat billing PRO NTT</p>
              <p>Kode bayar yang Anda buat akan tampil dan bisa di-refresh statusnya di sini.</p>
            </div>
          ) : (
            history.map((item) => {
              const itemMsg = itemMessages[item.id];
              const isSpinning = refreshingId === item.id;

              return (
                <div key={item.id} className="p-4 hover:bg-slate-50/70 transition-colors space-y-2">
                  <div className="flex items-start justify-between gap-3">
                    <div className="space-y-1 min-w-0 flex-1">
                      <div className="flex flex-wrap items-center gap-2">
                        <span className="font-mono-num font-black text-xs sm:text-sm text-slate-900">
                          {item.nopol_display || item.nopol}
                        </span>
                        <span
                          className="px-2 py-0.5 rounded-md text-[10px] font-extrabold border"
                          style={getStatusBadgeStyle(item)}
                        >
                          {item.status_bayar}
                        </span>
                      </div>

                      <div className="text-xs font-semibold text-slate-700 truncate">
                        {item.nama_pemilik || '-'} {item.unit ? `• ${item.unit}` : ''}
                      </div>

                      <div className="flex flex-wrap items-center gap-2 pt-0.5">
                        <span className="text-[11px] font-mono font-bold text-blue-800 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-md select-all">
                          {item.kode_bayar}
                        </span>
                        <button
                          type="button"
                          onClick={() => copyKodeBayar(item.kode_bayar, item.id)}
                          title="Salin kode bayar"
                          className="text-[10px] font-bold text-slate-500 hover:text-blue-700 flex items-center gap-1 cursor-pointer"
                        >
                          {copiedHistoryId === item.id ? (
                            <>
                              <IconCheck size={12} className="text-emerald-600" />
                              <span className="text-emerald-700">Tersalin</span>
                            </>
                          ) : (
                            <>
                              <IconCopy size={12} />
                              <span>Salin</span>
                            </>
                          )}
                        </button>
                      </div>

                      <div className="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-slate-500 pt-0.5">
                        <span className="font-mono-num font-bold text-emerald-700">
                          {item.total_bayar_formatted || formatRupiah(item.total_bayar)}
                        </span>
                        {item.batas_bayar && (
                          <span className="flex items-center gap-1">
                            <IconClock size={12} />
                            Batas: {item.batas_bayar}
                          </span>
                        )}
                      </div>

                      {item.waktu_pembayaran && (
                        <div className="text-[11px] font-semibold text-emerald-700">
                          Lunas: {item.waktu_pembayaran} {item.ref_bank ? `(Ref: ${item.ref_bank})` : ''}
                        </div>
                      )}

                      {itemMsg && (
                        <div
                          className={`text-[11px] font-medium ${
                            itemMsg.isError
                              ? 'text-rose-600'
                              : itemMsg.isPaid
                                ? 'text-emerald-700'
                                : 'text-blue-700'
                          }`}
                        >
                          {itemMsg.text}
                        </div>
                      )}
                    </div>

                    {/* Icon Refresh Status per Nopol */}
                    <button
                      type="button"
                      onClick={() => handleRefreshStatus(item.nopol, item.id, item.id)}
                      disabled={isSpinning}
                      title={`Cek & update status bayar ${item.nopol}`}
                      className="p-2.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 active:scale-95 transition-all cursor-pointer shrink-0"
                    >
                      <IconRefresh size={18} stroke={2.2} className={isSpinning ? 'animate-spin' : ''} />
                    </button>
                  </div>
                </div>
              );
            })
          )}
        </div>

        {historyHasMore && (
          <div className="p-3 bg-slate-50 border-t border-slate-100 text-center">
            <button
              type="button"
              onClick={() => fetchHistory(true)}
              className="text-xs font-bold text-blue-700 hover:underline cursor-pointer"
            >
              Lihat Semua Riwayat ({historyTotal})
            </button>
          </div>
        )}
      </div>
    </div>
  );
}

