import React, { useState, useEffect, useRef } from 'react';
import {
  IconRefresh,
  IconReceiptTax,
  IconFileCertificate,
  IconLicense,
  IconCrosshair,
  IconSparkles,
  IconCheck,
  IconAlertCircle,
  IconShare,
  IconArrowRight,
  IconCar,
  IconMotorbike,
  IconBuildingBank,
  IconInfoCircle
} from '@tabler/icons-react';
import { formatRupiah, formatDate } from '../utils/formatters';
import { getApiBase } from '../utils/config';

export default function Bbn2Tab({ prefillNopol = '', onSwitchToTaxCheck = null }) {
  const [mode, setMode] = useState('nopol'); // 'nopol' | 'manual'

  // 3-box nopol state (Default Kosong)
  const [nopolPrefix, setNopolPrefix] = useState('');
  const [nopolNumber, setNopolNumber] = useState('');
  const [nopolSuffix, setNopolSuffix] = useState('');

  const box1Ref = useRef(null);
  const box2Ref = useRef(null);
  const box3Ref = useRef(null);

  const [statusPajak, setStatusPajak] = useState('otomatis'); // 'otomatis' | 'lunas' | 'belum_bayar'
  const [includeTembak, setIncludeTembak] = useState(false);

  // Manual Mode State
  const [manualKategori, setManualKategori] = useState('roda4'); // 'roda23' | 'roda4'
  const [manualTagihanPajak, setManualTagihanPajak] = useState('');
  const [manualMerk, setManualMerk] = useState('');
  const [manualTipe, setManualTipe] = useState('');

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [result, setResult] = useState(null);
  const [copied, setCopied] = useState(false);

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

  useEffect(() => {
    if (prefillNopol) {
      const parsed = splitNopol(prefillNopol);
      setNopolPrefix(parsed.prefix);
      setNopolNumber(parsed.number);
      setNopolSuffix(parsed.suffix);
      handleCalculate('nopol', `${parsed.prefix}${parsed.number}${parsed.suffix}`, statusPajak, includeTembak);
    }
  }, [prefillNopol]);

  // Handler smart paste untuk 3 kotak
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

  // Handler Kotak 1: Kode Wilayah (Maks 2 huruf)
  const handlePrefixChange = (e) => {
    let val = e.target.value.toUpperCase().replace(/[^A-Z]/g, '');
    if (val.length > 2) val = val.slice(0, 2);
    setNopolPrefix(val);
    if (val.length === 2) {
      box2Ref.current?.focus();
    }
  };

  // Handler Kotak 2: Angka Nopol (Maks 4 digit angka)
  const handleNumberChange = (e) => {
    let val = e.target.value.replace(/[^0-9]/g, '');
    if (val.length > 4) val = val.slice(0, 4);
    setNopolNumber(val);
    if (val.length === 4) {
      box3Ref.current?.focus();
    }
  };

  const handleNumberKeyDown = (e) => {
    if (e.key === 'Backspace' && !nopolNumber) {
      box1Ref.current?.focus();
    }
  };

  // Handler Kotak 3: Seri Belakang (Maks 3 huruf)
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

  const getCombinedNopol = () => {
    return `${nopolPrefix}${nopolNumber}${nopolSuffix}`.trim().toUpperCase();
  };

  const handleCalculate = async (
    targetMode = mode,
    targetNopol = null,
    targetStatusPajak = statusPajak,
    targetIncludeTembak = includeTembak
  ) => {
    setLoading(true);
    setError(null);

    const fullNopol = targetNopol !== null ? targetNopol : getCombinedNopol();

    const payload = {
      mode: targetMode,
      status_pajak: targetStatusPajak,
      include_tembak: targetIncludeTembak,
    };

    if (targetMode === 'nopol') {
      const clean = fullNopol.replace(/[^A-Z0-9]/g, '');
      if (!clean) {
        setError('Nomor Polisi wajib diisi lengkap.');
        setLoading(false);
        return;
      }
      payload.nopol = clean;
    } else {
      payload.is_roda23 = manualKategori === 'roda23';
      payload.merk = manualMerk || (manualKategori === 'roda23' ? 'SEPEDA MOTOR' : 'MOBIL');
      payload.tipe = manualTipe || 'STANDAR';
      const cleanPajak = parseFloat(manualTagihanPajak.replace(/[^0-9]/g, '')) || 0;
      payload.tagihan_pajak = targetStatusPajak === 'lunas' ? 0 : cleanPajak;
    }

    try {
      const token = localStorage.getItem('infopkb_token') || '';
      const apiBase = getApiBase();
      const res = await fetch(`${apiBase}/simulate-bbn2`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
        body: JSON.stringify(payload),
      });

      const json = await res.json();
      if (!res.ok || json.status !== 'success') {
        throw new Error(json.message || 'Gagal menghitung simulasi BBN 2.');
      }

      setResult(json.data);
    } catch (err) {
      setError(err.message || 'Terjadi kendala saat menghubungi server.');
    } finally {
      setLoading(false);
    }
  };

  const handleCopyShare = () => {
    if (!result) return;
    const k = result.kendaraan;
    const r = result.rincian_biaya;
    const ket = result.keterangan;

    const text = `*ESTIMASI BIAYA BALIK NAMA KE-2 (BBN 2) NTT*
Kendaraan: *${k.nopol || 'Simulasi Manual'}* (${k.unit || k.merk})
Kategori: ${ket.kategori_roda}
Pajak: ${r.tagihan_pajak > 0 ? 'Belum Lunas' : 'Sudah Lunas'}

*RINCIAN ESTIMASI BIAYA:*
1. Tagihan Pajak (PKB/Opsen/SWDKLLJ): ${formatRupiah(r.tagihan_pajak)}
2. Penerbitan BPKB Baru (PP 76/2020): ${formatRupiah(r.biaya_bpkb)}
3. Penerbitan STNK Baru (PP 76/2020): ${formatRupiah(r.biaya_stnk)}
4. Plat Nomor Baru / TNKB (PP 76/2020): ${formatRupiah(r.biaya_tnkb)}
${r.include_tembak ? `5. Cek Fisik Bantuan (Tembak): ${formatRupiah(r.biaya_tembak)}\n` : ''}
*TOTAL ESTIMASI: ${formatRupiah(r.total_biaya)}*

_Catatan: Perhitungan biaya penerbitan BPKB, STNK & TNKB mengacu pada PP No. 76 Tahun 2020 (Tarif PNBP Polri)._
_Simulasi via Pajak.In NTT_`;

    navigator.clipboard.writeText(text);
    setCopied(true);
    setTimeout(() => setCopied(false), 2500);
  };

  return (
    <div className="p-4 sm:p-6 space-y-4">
      {/* Kartu Header Input */}
      <div className="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-slate-200/90 space-y-4">
        <div className="flex items-center justify-between">
          <div>
            <h2 className="font-extrabold text-slate-900 text-base sm:text-lg flex items-center gap-2">
              <span className="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                <IconRefresh size={20} stroke={2.2} />
              </span>
              Simulasi Bea Balik Nama ke-2 (BBN 2)
            </h2>
            <p className="text-xs text-slate-500 mt-0.5">
              Estimasi lengkap BPKB, STNK, Plat TNKB, Cek Fisik, dan Pajak Berjalan
            </p>
          </div>
        </div>

        {/* Mode Selector Segmented Pill Control */}
        <div className="relative grid grid-cols-2 p-1 bg-slate-100/90 rounded-2xl border border-slate-200/80 w-full sm:max-w-xs text-xs font-bold shadow-inner">
          {/* Hardware-accelerated sliding active pill highlight */}
          <div
            className={`absolute top-1 bottom-1 left-1 w-[calc(50%-4px)] bg-white rounded-xl shadow-xs border border-slate-200/90 transition-transform duration-200 ease-out pointer-events-none ${
              mode === 'manual' ? 'translate-x-full' : 'translate-x-0'
            }`}
          />

          <button
            type="button"
            onClick={() => setMode('nopol')}
            className={`relative z-10 py-2.5 px-3 rounded-xl transition-colors duration-200 flex items-center justify-center gap-1.5 cursor-pointer ${
              mode === 'nopol'
                ? 'text-emerald-700 font-extrabold'
                : 'text-slate-500 hover:text-slate-800 font-semibold'
            }`}
          >
            <IconLicense size={15} stroke={2.2} className={`transition-colors shrink-0 ${mode === 'nopol' ? 'text-emerald-600' : 'text-slate-400'}`} />
            <span>Berdasarkan Nopol</span>
          </button>

          <button
            type="button"
            onClick={() => setMode('manual')}
            className={`relative z-10 py-2.5 px-3 rounded-xl transition-colors duration-200 flex items-center justify-center gap-1.5 cursor-pointer ${
              mode === 'manual'
                ? 'text-emerald-700 font-extrabold'
                : 'text-slate-500 hover:text-slate-800 font-semibold'
            }`}
          >
            <IconSparkles size={15} stroke={2.2} className={`transition-colors shrink-0 ${mode === 'manual' ? 'text-emerald-600' : 'text-slate-400'}`} />
            <span>Input Manual</span>
          </button>
        </div>

        <form onSubmit={(e) => { e.preventDefault(); handleCalculate(); }} className="space-y-4">
          {mode === 'nopol' ? (
            <div>
              <div className="flex items-center justify-between mb-2 px-1">
                <span className="text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                  Nomor Polisi Kendaraan (NTT)
                </span>
                <span className="text-[10px] text-slate-400 font-medium">
                  Dukung paste (DH 1446 HS)
                </span>
              </div>

              {/* Smart 3-Box Plate Layout */}
              <div className="bg-slate-50/90 p-3 rounded-2xl border border-slate-200 shadow-inner">
                <div className="grid grid-cols-12 gap-2 sm:gap-3 items-center">
                  {/* Kotak 1: Wilayah */}
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
                      className="w-full bg-white border border-slate-200 rounded-xl py-2.5 sm:py-3 px-1 text-center font-mono-num font-black text-base sm:text-lg text-slate-900 uppercase placeholder:text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-600 shadow-xs"
                    />
                  </div>

                  {/* Kotak 2: 1-4 Angka */}
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
                      placeholder="1446"
                      maxLength={4}
                      className="w-full bg-white border border-slate-200 rounded-xl py-2.5 sm:py-3 px-2 text-center font-mono-num font-black text-base sm:text-lg text-slate-900 tracking-wider placeholder:text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-600 shadow-xs"
                    />
                  </div>

                  {/* Kotak 3: Seri */}
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
                      placeholder="HS"
                      maxLength={3}
                      className="w-full bg-white border border-slate-200 rounded-xl py-2.5 sm:py-3 px-1 text-center font-mono-num font-black text-base sm:text-lg text-slate-900 uppercase placeholder:text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-600 shadow-xs"
                    />
                  </div>
                </div>
              </div>
            </div>
          ) : (
            <div className="space-y-3">
              {/* Kategori Kendaraan */}
              <div>
                <label className="text-[11px] font-bold text-slate-600 block mb-1 uppercase tracking-wider">
                  Kategori Kendaraan
                </label>
                <div className="grid grid-cols-2 gap-2">
                  <button
                    type="button"
                    onClick={() => setManualKategori('roda23')}
                    className={`p-3 rounded-xl border text-left flex items-center gap-3 transition-all cursor-pointer ${manualKategori === 'roda23'
                      ? 'border-emerald-500 bg-emerald-50/50 text-emerald-900 ring-2 ring-emerald-500/20'
                      : 'border-slate-200 bg-slate-50 text-slate-600'
                      }`}
                  >
                    <IconMotorbike size={24} className={manualKategori === 'roda23' ? 'text-emerald-600' : 'text-slate-400'} />
                    <div>
                      <div className="text-xs font-bold">Roda 2 / Roda 3</div>
                      <div className="text-[10px] text-slate-500">Motor &amp; R3</div>
                    </div>
                  </button>

                  <button
                    type="button"
                    onClick={() => setManualKategori('roda4')}
                    className={`p-3 rounded-xl border text-left flex items-center gap-3 transition-all cursor-pointer ${manualKategori === 'roda4'
                      ? 'border-emerald-500 bg-emerald-50/50 text-emerald-900 ring-2 ring-emerald-500/20'
                      : 'border-slate-200 bg-slate-50 text-slate-600'
                      }`}
                  >
                    <IconCar size={24} className={manualKategori === 'roda4' ? 'text-emerald-600' : 'text-slate-400'} />
                    <div>
                      <div className="text-xs font-bold">Roda 4 atau Lebih</div>
                      <div className="text-[10px] text-slate-500">Mobil, Minibus, Truk</div>
                    </div>
                  </button>
                </div>
              </div>

              {/* Tagihan Pajak Manual jika belum lunas */}
              {statusPajak !== 'lunas' && (
                <div>
                  <label className="text-[11px] font-bold text-slate-600 block mb-1 uppercase tracking-wider">
                    Estimasi Pokok Pajak Berjalan / Tunggakan (Rp)
                  </label>
                  <input
                    type="text"
                    value={manualTagihanPajak}
                    onChange={(e) => {
                      const num = e.target.value.replace(/[^0-9]/g, '');
                      setManualTagihanPajak(num ? parseInt(num, 10).toLocaleString('id-ID') : '');
                    }}
                    placeholder="Contoh: 1.500.000 (Kosongkan jika pajak sudah lunas)"
                    className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 font-mono-num font-bold text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                  />
                </div>
              )}
            </div>
          )}

          {/* Opsi Status Pajak Saat Balik Nama */}
          <div>
            <label className="text-[11px] font-bold text-slate-600 block mb-1.5 uppercase tracking-wider">
              Status Tagihan Pajak Kendaraan
            </label>
            <div className="grid grid-cols-3 gap-2 text-xs font-semibold">
              <button
                type="button"
                onClick={() => {
                  setStatusPajak('otomatis');
                  if (result) handleCalculate(mode, null, 'otomatis', includeTembak);
                }}
                className={`py-2 px-2.5 rounded-xl border text-center transition-all cursor-pointer ${statusPajak === 'otomatis'
                  ? 'border-emerald-500 bg-emerald-50 text-emerald-800 font-bold'
                  : 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100'
                  }`}
              >
                Otomatis Cek
              </button>
              <button
                type="button"
                onClick={() => {
                  setStatusPajak('lunas');
                  if (result) handleCalculate(mode, null, 'lunas', includeTembak);
                }}
                className={`py-2 px-2.5 rounded-xl border text-center transition-all cursor-pointer ${statusPajak === 'lunas'
                  ? 'border-emerald-500 bg-emerald-50 text-emerald-800 font-bold'
                  : 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100'
                  }`}
              >
                Sudah Lunas (Rp 0)
              </button>
              <button
                type="button"
                onClick={() => {
                  setStatusPajak('belum_bayar');
                  if (result) handleCalculate(mode, null, 'belum_bayar', includeTembak);
                }}
                className={`py-2 px-2.5 rounded-xl border text-center transition-all cursor-pointer ${statusPajak === 'belum_bayar'
                  ? 'border-emerald-500 bg-emerald-50 text-emerald-800 font-bold'
                  : 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100'
                  }`}
              >
                Sertakan Pajak
              </button>
            </div>
          </div>

          {/* Checkbox Opsi Cek Fisik Bantuan (Tembak Tanpa Gesek) */}
          <div className="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 flex items-start gap-3">
            <input
              type="checkbox"
              id="includeTembak"
              checked={includeTembak}
              onChange={(e) => {
                const checked = e.target.checked;
                setIncludeTembak(checked);
                if (result) handleCalculate(mode, null, statusPajak, checked);
              }}
              className="mt-1 w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 cursor-pointer"
            />
            <label htmlFor="includeTembak" className="text-xs text-slate-700 cursor-pointer select-none">
              <span className="font-bold text-slate-900 block flex items-center gap-1.5">
                <IconCrosshair size={15} className="text-emerald-600" />
                Sertakan Biaya Tembak
              </span>
              <span className="text-[11px] text-slate-500 mt-0.5 block leading-relaxed">
                Pilih opsi ini jika kendaraan berada di luar daerah/tanpa hadir gesek fisik langsung di Samsat (R2/R3: Rp 210.000, R4+: Rp 260.000).
              </span>
            </label>
          </div>

          {/* Error Notice */}
          {error && (
            <div className="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 flex items-center gap-2">
              <IconAlertCircle size={16} className="text-rose-600 shrink-0" />
              <span>{typeof error === 'string' ? error : (error?.message || 'Terjadi kesalahan sistem')}</span>
            </div>
          )}

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white font-bold py-3 px-4 rounded-xl text-xs sm:text-sm shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
          >
            {loading ? (
              <span>Menghitung Estimasi BBN 2...</span>
            ) : (
              <>
                <IconSparkles size={17} stroke={2.2} />
                <span>Hitung Estimasi Biaya BBN 2</span>
              </>
            )}
          </button>

          {/* Catatan Aturan Penetapan Biaya BPKB */}
          <div className="p-3.5 bg-slate-50 border border-slate-200/90 rounded-2xl flex items-start gap-2.5 text-xs text-slate-600">
            <IconInfoCircle size={17} className="text-emerald-600 shrink-0 mt-0.5" />
            <div className="space-y-0.5">
              <span className="font-bold text-slate-800 text-[11px] block">
                Catatan Aturan Biaya BPKB &amp; Dokumen:
              </span>
              <p className="text-[11px] leading-relaxed text-slate-500">
                Perhitungan biaya penerbitan <strong>BPKB Baru</strong> di aplikasi ini mengacu pada <strong>Peraturan Pemerintah (PP) No. 76 Tahun 2020</strong> tentang PNBP Polri (R2/R3: Rp 225.000 &bull; R4+: Rp 375.000).
              </p>
            </div>
          </div>
        </form>
      </div>

      {/* Hasil Perhitungan Simulasi BBN 2 */}
      {result && (
        <div className="space-y-4 animate-fade-in">
          {/* Card Total Biaya */}
          <div 
            className="card-gradient-emerald-action rounded-3xl p-5 sm:p-6 text-white shadow-xl shadow-emerald-900/20 relative overflow-hidden border border-emerald-500/30"
            style={{
              backgroundColor: '#059669',
              background: 'linear-gradient(135deg, #059669 0%, #0d9488 50%, #0f766e 100%)',
            }}
          >
            <div className="flex items-center justify-between mb-2">
              <span className="text-xs uppercase font-extrabold tracking-wider bg-white/20 px-3 py-1 rounded-full backdrop-blur-sm">
                Estimasi Total Balik Nama (BBN 2)
              </span>
              <span className="text-xs text-emerald-100 font-medium">
                {result.keterangan.kategori_roda}
              </span>
            </div>

            <div className="mt-3">
              <div className="text-3xl sm:text-4xl font-black font-mono-num tracking-tight">
                {formatRupiah(result.rincian_biaya.total_biaya)}
              </div>
              <p className="text-xs text-emerald-100 mt-1">
                Estimasi seluruh biaya pengurusan dokumen BPKB, STNK, Plat TNKB, dan Pajak Kendaraan.
              </p>
            </div>

            {/* Quick Summary Pill */}
            <div className="mt-4 pt-4 border-t border-white/15 flex flex-wrap items-center justify-between gap-2 text-xs">
              <div className="flex items-center gap-2">
                <span className="w-2 h-2 rounded-full bg-emerald-300"></span>
                <span>Kendaraan: <strong>{result.kendaraan.unit || result.kendaraan.nopol}</strong></span>
              </div>
              <button
                type="button"
                onClick={handleCopyShare}
                className="bg-white/20 hover:bg-white/30 text-white font-bold px-3 py-1.5 rounded-xl transition-all flex items-center gap-1.5 cursor-pointer text-xs"
              >
                <IconShare size={14} />
                <span>{copied ? 'Tersalin!' : 'Bagikan Rincian'}</span>
              </button>
            </div>
          </div>

          {/* Rincian Komponen Biaya */}
          <div className="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/90 shadow-sm space-y-4">
            <h3 className="font-extrabold text-slate-900 text-sm sm:text-base border-b border-slate-100 pb-3">
              Rincian 5 Komponen Biaya Balik Nama
            </h3>

            <div className="space-y-3 font-mono-num text-xs">
              {/* 1. Tagihan Pajak */}
              <div className="flex items-start justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                <div className="flex items-start gap-3">
                  <div className="p-2 bg-blue-50 text-blue-600 rounded-xl mt-0.5">
                    <IconReceiptTax size={18} />
                  </div>
                  <div>
                    <div className="font-bold font-sans text-slate-800 text-xs sm:text-sm">
                      1. Tagihan Pajak Kendaraan
                    </div>
                    <div className="text-[11px] font-sans text-slate-500 mt-0.5">
                      {result.rincian_biaya.tagihan_pajak > 0
                        ? 'PKB Berjalan + Tunggakan + Opsen (Amnesti Aktif)'
                        : 'Pajak kendaraan berstatus lunas / tidak ada tunggakan'}
                    </div>
                  </div>
                </div>
                <div className="font-black text-slate-900 text-sm">
                  {formatRupiah(result.rincian_biaya.tagihan_pajak)}
                </div>
              </div>

              {/* 2. Biaya BPKB */}
              <div className="flex items-start justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                <div className="flex items-start gap-3">
                  <div className="p-2 bg-purple-50 text-purple-600 rounded-xl mt-0.5">
                    <IconFileCertificate size={18} />
                  </div>
                  <div>
                    <div className="font-bold font-sans text-slate-800 text-xs sm:text-sm">
                      2. Penerbitan BPKB Baru
                    </div>
                    <div className="text-[11px] font-sans text-slate-500 mt-0.5">
                      PNBP Buku BPKB Baru sesuai PP No. 76 Tahun 2020 ({result.keterangan.kategori_roda})
                    </div>
                  </div>
                </div>
                <div className="font-black text-slate-900 text-sm">
                  {formatRupiah(result.rincian_biaya.biaya_bpkb)}
                </div>
              </div>

              {/* 3. Biaya STNK */}
              <div className="flex items-start justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                <div className="flex items-start gap-3">
                  <div className="p-2 bg-emerald-50 text-emerald-600 rounded-xl mt-0.5">
                    <IconLicense size={18} />
                  </div>
                  <div>
                    <div className="font-bold font-sans text-slate-800 text-xs sm:text-sm">
                      3. Penerbitan STNK Baru (5 Tahunan)
                    </div>
                    <div className="text-[11px] font-sans text-slate-500 mt-0.5">
                      PNBP Cetak STNK Lembar Baru sesuai PP No. 76/2020
                    </div>
                  </div>
                </div>
                <div className="font-black text-slate-900 text-sm">
                  {formatRupiah(result.rincian_biaya.biaya_stnk)}
                </div>
              </div>

              {/* 4. Biaya TNKB (Plat Nomor) */}
              <div className="flex items-start justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                <div className="flex items-start gap-3">
                  <div className="p-2 bg-teal-50 text-teal-600 rounded-xl mt-0.5">
                    <IconCar size={18} />
                  </div>
                  <div>
                    <div className="font-bold font-sans text-slate-800 text-xs sm:text-sm">
                      4. Biaya Cetak Plat Nomor (TNKB)
                    </div>
                    <div className="text-[11px] font-sans text-slate-500 mt-0.5">
                      PNBP Cetak Sepasang Plat Nomor Kaleng Baru
                    </div>
                  </div>
                </div>
                <div className="font-black text-slate-900 text-sm">
                  {formatRupiah(result.rincian_biaya.biaya_tnkb)}
                </div>
              </div>

              {/* 5. Biaya Tembak / Cek Fisik */}
              <div className={`flex items-start justify-between p-3 rounded-2xl border transition-all ${result.rincian_biaya.include_tembak ? 'bg-amber-50/70 border-amber-200' : 'bg-slate-50 border-slate-100'
                }`}>
                <div className="flex items-start gap-3">
                  <div className={`p-2 rounded-xl mt-0.5 ${result.rincian_biaya.include_tembak ? 'bg-amber-100 text-amber-700' : 'bg-slate-200 text-slate-500'
                    }`}>
                    <IconCrosshair size={18} />
                  </div>
                  <div>
                    <div className="font-bold font-sans text-slate-800 text-xs sm:text-sm">
                      5. Biaya Cek Fisik (Tembak )
                    </div>
                    <div className="text-[11px] font-sans text-slate-500 mt-0.5">
                      {result.rincian_biaya.include_tembak
                        ? 'Biaya bantuan proses tanpa gesek nomor rangka/mesin di Samsat'
                        : 'Tidak digunakan (Hadir gesek fisik langsung di Samsat)'}
                    </div>
                  </div>
                </div>
                <div className={`font-black text-sm ${result.rincian_biaya.include_tembak ? 'text-amber-800' : 'text-slate-400'
                  }`}>
                  {formatRupiah(result.rincian_biaya.biaya_tembak)}
                </div>
              </div>
            </div>

            {/* Total Footer Banner */}
            <div className="p-4 rounded-2xl bg-slate-900 text-white flex items-center justify-between">
              <div>
                <span className="text-[11px] text-slate-400 block font-medium">TOTAL BIAYA BALIK NAMA</span>
                <span className="text-xs text-emerald-400 font-bold">Lengkap Dokumen &amp; Plat</span>
              </div>
              <div className="text-xl sm:text-2xl font-black font-mono-num text-emerald-400">
                {formatRupiah(result.rincian_biaya.total_biaya)}
              </div>
            </div>

            {/* Catatan Dasar Hukum Penetapan Tarif BPKB & Dokumen Registrasi */}
            <div className="p-4 bg-blue-50/70 border border-blue-200/80 rounded-2xl flex items-start gap-3 text-xs text-blue-950">
              <div className="p-1.5 bg-blue-100 text-blue-700 rounded-xl shrink-0 mt-0.5">
                <IconInfoCircle size={18} />
              </div>
              <div className="space-y-1">
                <span className="font-bold text-slate-900 block text-xs">
                  Dasar Hukum Penetapan Biaya BPKB &amp; Dokumen Registrasi:
                </span>
                <p className="text-slate-600 leading-relaxed text-[11px]">
                  Perhitungan biaya penerbitan <strong>BPKB</strong>, <strong>STNK</strong>, dan <strong>TNKB (Plat Nomor)</strong> pada aplikasi ini mengacu secara resmi pada <strong>Peraturan Pemerintah Republik Indonesia Nomor 76 Tahun 2020</strong> tentang <em>Jenis dan Tarif atas Jenis Penerimaan Negara Bukan Pajak (PNBP) yang Berlaku pada Kepolisian Negara Republik Indonesia</em>.
                </p>
                <div className="text-[10px] text-slate-500 pt-0.5 flex flex-wrap gap-x-3 gap-y-1 font-mono">
                  <span>&bull; BPKB Roda 2/3: <strong>Rp 225.000</strong></span>
                  <span>&bull; BPKB Roda 4+: <strong>Rp 375.000</strong></span>
                  <span>&bull; STNK 5 Thn: <strong>Rp 100.000 / Rp 200.000</strong></span>
                  <span>&bull; TNKB / Plat: <strong>Rp 60.000 / Rp 100.000</strong></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

