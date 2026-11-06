import React, { useState, useEffect, useRef } from 'react';
import {
  IconSearch,
  IconDatabase,
  IconRefresh,
  IconArrowRight,
  IconId,
  IconCar,
  IconCalendar,
  IconGauge,
  IconTag,
  IconCash,
  IconScale,
  IconCalculator,
  IconClock,
  IconAlertTriangle,
  IconFileText,
  IconInfoCircle
} from '@tabler/icons-react';
import { formatRupiah, formatDate } from '../utils/formatters';
import { NjkbListSkeleton, TaxCheckSkeleton } from '../components/SkeletonLoader';
import { getApiBase } from '../utils/config';

export default function NjkbTab({ onSelectNjkb, onCekPajak, onSimulasi }) {
  const [activeMode, setActiveMode] = useState('plat'); // 'plat' | 'merek'

  // State untuk Pencarian Berdasarkan Plat Nomor (3-Box Plate - Default Kosong)
  const [nopolPrefix, setNopolPrefix] = useState('');
  const [nopolNumber, setNopolNumber] = useState('');
  const [nopolSuffix, setNopolSuffix] = useState('');

  const box1Ref = useRef(null);
  const box2Ref = useRef(null);
  const box3Ref = useRef(null);

  const [loadingPlat, setLoadingPlat] = useState(false);
  const [errorPlat, setErrorPlat] = useState(null);
  const [hasilPlat, setHasilPlat] = useState(null);

  // State untuk Pencarian Berdasarkan Merek & Tipe
  const [query, setQuery] = useState('');
  const [tahun, setTahun] = useState('');
  const [loadingMerek, setLoadingMerek] = useState(false);
  const [items, setItems] = useState([]);
  const [searched, setSearched] = useState(false);

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
    if (val.length === 2) {
      box2Ref.current?.focus();
    }
  };

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

  // Fungsi Cek NJKB via Plat Nomor
  const handleCheckPlat = async (e) => {
    if (e) e.preventDefault();
    const cleanNopol = getCombinedNopol().replace(/[^A-Z0-9]/g, '');
    if (!cleanNopol) return;

    setLoadingPlat(true);
    setErrorPlat(null);
    setHasilPlat(null);

    try {
      const token = localStorage.getItem('infopkb_token') || '';
      const res = await fetch(
        `${getApiBase()}/njkb/nopol?nopol=${encodeURIComponent(cleanNopol)}`,
        {
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
          }
        }
      );
      const data = await res.json();

      if (data.status === 'success' && data.data) {
        setHasilPlat(data.data);
      } else {
        setErrorPlat(data.message || 'Data plat kendaraan tidak ditemukan.');
      }
    } catch (err) {
      console.error(err);
      setErrorPlat('Terjadi kesalahan jaringan saat mengambil data NJKB.');
    } finally {
      setLoadingPlat(false);
    }
  };

  // Fungsi Cari NJKB via Merek & Tipe
  const fetchNjkbCatalog = async (q = query, yr = tahun) => {
    setLoadingMerek(true);
    setSearched(true);
    try {
      const params = new URLSearchParams();
      if (q.trim()) params.append('q', q.trim());
      if (yr) params.append('tahun', yr);

      const token = localStorage.getItem('infopkb_token') || '';
      const res = await fetch(`${getApiBase()}/njkb?${params.toString()}`, {
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      });
      const data = await res.json();
      if (data.status === 'success') {
        setItems(data.data || []);
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoadingMerek(false);
    }
  };

  useEffect(() => {
    // Muat data awal katalog di background
    fetchNjkbCatalog('HONDA', '2022');
  }, []);

  const handleSearchMerek = (e) => {
    e.preventDefault();
    fetchNjkbCatalog();
  };

  return (
    <div className="p-4 sm:p-6 space-y-4 sm:space-y-6">
      {/* Header & Mode Switcher */}
      <div className="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-200/80">
        <div className="flex items-center gap-2 mb-3">
          <div className="p-2 bg-emerald-50 text-emerald-700 rounded-xl">
            <IconDatabase size={20} stroke={2} />
          </div>
          <div>
            <h2 className="font-bold text-slate-800 text-sm sm:text-base">
              Data NJKB Kendaraan
            </h2>
            <p className="text-xs text-slate-500">
              Cek Nilai Jual &amp; Bobot via Nomor Polisi atau cari manual berdasarkan Merek &amp; Tipe.
            </p>
          </div>
        </div>

        {/* Tab Switcher Pills */}
        <div className="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl mt-3">
          <button
            type="button"
            onClick={() => setActiveMode('plat')}
            className={`py-2 px-3 rounded-lg text-xs font-bold flex items-center justify-center gap-1.5 transition-all ${activeMode === 'plat'
              ? 'bg-white text-emerald-700 shadow-xs'
              : 'text-slate-600 hover:text-slate-900'
              }`}
          >
            <IconId size={16} stroke={2} />
            <span>Cek Plat Nomor</span>
          </button>

          <button
            type="button"
            onClick={() => setActiveMode('merek')}
            className={`py-2 px-3 rounded-lg text-xs font-bold flex items-center justify-center gap-1.5 transition-all ${activeMode === 'merek'
              ? 'bg-white text-emerald-700 shadow-xs'
              : 'text-slate-600 hover:text-slate-900'
              }`}
          >
            <IconCar size={16} stroke={2} />
            <span>Katalog Merek &amp; Tipe</span>
          </button>
        </div>

        {/* ============================================================== */}
        {/* MODE 1: CEK PLAT NOMOR FORM                                    */}
        {/* ============================================================== */}
        {activeMode === 'plat' && (
          <form onSubmit={handleCheckPlat} className="mt-4 space-y-3">
            <div>
              <div className="flex items-center justify-between mb-2 px-1">
                <span className="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                  Nomor Polisi (Plat Kendaraan NTT)
                </span>
                <span className="text-[10px] text-slate-400 font-medium">
                  Dukung paste (DH 2565 LB)
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
                      placeholder="2565"
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
                      placeholder="LB"
                      maxLength={3}
                      className="w-full bg-white border border-slate-200 rounded-xl py-2.5 sm:py-3 px-1 text-center font-mono-num font-black text-base sm:text-lg text-slate-900 uppercase placeholder:text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-600 shadow-xs"
                    />
                  </div>
                </div>
              </div>

              <p className="text-[11px] text-slate-500 mt-2 flex items-start gap-1">
                <IconInfoCircle size={14} className="shrink-0 mt-0.5 text-emerald-600" />
                <span>Masukkan plat wilayah NTT (DH, EB, ED). Sistem akan mencocokkan data Samsat dan Nilai Jual (NJKB).</span>
              </p>
            </div>

            <button
              type="submit"
              disabled={loadingPlat || !nopolNumber.trim()}
              className="w-full bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white font-bold py-3 px-4 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all cursor-pointer"
            >
              {loadingPlat ? (
                <>
                  <IconRefresh className="animate-spin" size={18} stroke={2} />
                  <span>Memeriksa Data Plat...</span>
                </>
              ) : (
                <>
                  <IconSearch size={18} stroke={2} />
                  <span>Cek Nilai Jual</span>
                </>
              )}
            </button>
          </form>
        )}

        {/* ============================================================== */}
        {/* MODE 2: CARI MEREK & TIPE FORM                                 */}
        {/* ============================================================== */}
        {activeMode === 'merek' && (
          <form onSubmit={handleSearchMerek} className="mt-4 space-y-3">
            <div className="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
              <div className="relative sm:col-span-7">
                <input
                  type="text"
                  value={query}
                  onChange={(e) => setQuery(e.target.value)}
                  placeholder="Cari merk atau tipe (mis. AVANZA, BEAT)"
                  className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 pl-10 text-xs sm:text-sm font-semibold text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 uppercase"
                />
                <IconSearch className="absolute left-3 top-3 text-slate-400" size={17} stroke={2} />
              </div>

              <div className="sm:col-span-3">
                <input
                  type="number"
                  value={tahun}
                  onChange={(e) => setTahun(e.target.value)}
                  placeholder="Tahun (opsional)"
                  className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs sm:text-sm font-semibold text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                />
              </div>

              <button
                type="submit"
                disabled={loadingMerek}
                className="sm:col-span-2 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white font-bold py-2.5 px-4 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-1.5 shadow-sm active:scale-95 transition-all"
              >
                {loadingMerek ? <IconRefresh className="animate-spin" size={16} stroke={2} /> : <IconSearch size={16} stroke={2} />}
                <span>Cari</span>
              </button>
            </div>
          </form>
        )}
      </div>

      {/* ============================================================== */}
      {/* HASIL MODE 1: PLAT NOMOR                                       */}
      {/* ============================================================== */}
      {activeMode === 'plat' && (
        <div className="space-y-4">
          {loadingPlat && (
            <div className="space-y-3">
              <div className="text-xs text-emerald-600 font-semibold animate-pulse flex items-center gap-1.5 px-1">
                <IconRefresh className="animate-spin" size={14} />
                <span>Menghubungi gateway data kendaraan dan mencocokkan NJKB...</span>
              </div>
              <TaxCheckSkeleton />
            </div>
          )}

          {errorPlat && (
            <div className="bg-red-50 border border-red-200 rounded-2xl p-5 text-center space-y-2">
              <div className="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto">
                <IconAlertTriangle size={24} stroke={2} />
              </div>
              <h3 className="font-bold text-red-800 text-sm sm:text-base">Data Tidak Ditemukan</h3>
              <p className="text-xs text-red-600 max-w-sm mx-auto">{errorPlat}</p>
            </div>
          )}

          {hasilPlat && !loadingPlat && (
            <div className="space-y-4 animate-in fade-in duration-300">
              {/* Header Plat & Unit */}
              <div className="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-200/80">
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3.5 border-b border-slate-100">
                  <div className="flex items-center gap-3">
                    {/* Badge Plat Nomor Indonesia */}
                    <div className="bg-slate-900 border-2 border-slate-700 text-white px-3.5 py-1.5 rounded-xl font-mono font-black text-sm sm:text-base tracking-widest shadow-inner shrink-0">
                      {hasilPlat.nopol}
                    </div>
                    <div>
                      <h3 className="font-extrabold text-slate-800 text-sm sm:text-base leading-tight">
                        {hasilPlat.merk} {hasilPlat.tipe}
                      </h3>
                      <p className="text-xs text-slate-400 mt-0.5">
                        Wilayah NTT • {hasilPlat.warna_tnkb || 'HITAM'}
                      </p>
                    </div>
                  </div>
                </div>

                {/* Tags Spesifikasi */}
                <div className="flex flex-wrap gap-1.5 mt-3">
                  <span className="inline-flex items-center gap-1 bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg text-xs font-semibold">
                    <IconCalendar size={13} className="text-slate-500" />
                    Tahun {hasilPlat.tahun}
                  </span>
                  <span className="inline-flex items-center gap-1 bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg text-xs font-semibold">
                    <IconCar size={13} className="text-slate-500" />
                    {hasilPlat.jenis_kend}
                  </span>
                  {hasilPlat.isi_cylinder > 0 && (
                    <span className="inline-flex items-center gap-1 bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg text-xs font-semibold">
                      <IconGauge size={13} className="text-slate-500" />
                      {hasilPlat.isi_cylinder} CC
                    </span>
                  )}
                  <span className="inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 px-2.5 py-1 rounded-lg text-xs font-bold border border-emerald-200/50">
                    <IconTag size={13} className="text-emerald-600" />
                    {hasilPlat.k_guna}
                  </span>
                </div>
              </div>

              {/* 3 Box Highlight NJKB & Bobot */}
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                {/* Box 1: NJKB */}
                <div 
                  className="rounded-2xl p-4 shadow-sm border border-emerald-300"
                  style={{
                    backgroundColor: '#f0fdf4',
                    background: 'linear-gradient(135deg, #dcfce7 0%, #f0fdf4 60%, #ffffff 100%)',
                  }}
                >
                  <div className="text-[11px] font-bold text-emerald-800 flex items-center gap-1.5 uppercase tracking-wider">
                    <IconCash size={16} className="text-emerald-600" />
                    <span>Nilai Jual (NJKB)</span>
                  </div>
                  <div className="font-mono-num font-extrabold text-emerald-700 text-lg sm:text-xl mt-2">
                    {formatRupiah(hasilPlat.njkb)}
                  </div>
                  <div className="text-[11px] text-slate-500 mt-1">
                    {hasilPlat.sumber ? `Acuan: ${hasilPlat.sumber}` : 'Nilai jual acuan penetapan PKB'}
                  </div>
                </div>

                {/* Box 2: Bobot */}
                <div className="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80">
                  <div className="text-[11px] font-bold text-slate-600 flex items-center gap-1.5 uppercase tracking-wider">
                    <IconScale size={16} className="text-slate-500" />
                    <span>Bobot Kendaraan</span>
                  </div>
                  <div className="font-mono-num font-extrabold text-slate-800 text-lg sm:text-xl mt-2">
                    {Number(hasilPlat.bobot).toFixed(3)}
                  </div>
                  <div className="text-[11px] text-slate-500 mt-1">
                    Koefisien bobot jalan &amp; dampak
                  </div>
                </div>

                {/* Box 3: Dasar Pengenaan PKB */}
                <div 
                  className="rounded-2xl p-4 shadow-sm border border-blue-300"
                  style={{
                    backgroundColor: '#eff6ff',
                    background: 'linear-gradient(135deg, #dbeafe 0%, #eff6ff 60%, #ffffff 100%)',
                  }}
                >
                  <div className="text-[11px] font-bold text-blue-800 flex items-center gap-1.5 uppercase tracking-wider">
                    <IconCalculator size={16} className="text-blue-600" />
                    <span>Dasar Pengenaan PKB</span>
                  </div>
                  <div className="font-mono-num font-extrabold text-blue-700 text-lg sm:text-xl mt-2">
                    {formatRupiah(hasilPlat.dasar_pkb)}
                  </div>
                  <div className="text-[11px] text-slate-500 mt-1">
                    (NJKB × Bobot){hasilPlat.njub > 0 ? ' + NJUB' : ''}
                  </div>
                </div>
              </div>

              {/* Rincian Spesifikasi Tabel */}
              <div className="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-200/80 space-y-3">
                <h4 className="text-xs font-bold text-slate-400 uppercase tracking-wider">
                  Rincian Spesifikasi Kendaraan
                </h4>

                <div className="divide-y divide-slate-100 text-xs sm:text-sm">
                  <div className="py-2.5 flex justify-between">
                    <span className="text-slate-500">Nomor Polisi</span>
                    <span className="font-bold text-slate-800">{hasilPlat.nopol}</span>
                  </div>
                  <div className="py-2.5 flex justify-between">
                    <span className="text-slate-500">Merek Kendaraan</span>
                    <span className="font-bold text-slate-800">{hasilPlat.merk}</span>
                  </div>
                  <div className="py-2.5 flex justify-between">
                    <span className="text-slate-500">Tipe / Model</span>
                    <span className="font-bold text-slate-800">{hasilPlat.tipe}</span>
                  </div>
                  <div className="py-2.5 flex justify-between">
                    <span className="text-slate-500">Tahun Pembuatan</span>
                    <span className="font-bold text-slate-800">{hasilPlat.tahun}</span>
                  </div>
                  <div className="py-2.5 flex justify-between">
                    <span className="text-slate-500">Jenis Kendaraan</span>
                    <span className="font-bold text-slate-800">{hasilPlat.jenis_kend}</span>
                  </div>
                  <div className="py-2.5 flex justify-between">
                    <span className="text-slate-500">Isi Silinder</span>
                    <span className="font-bold text-slate-800">{hasilPlat.isi_cylinder} CC</span>
                  </div>
                  <div className="py-2.5 flex justify-between">
                    <span className="text-slate-500">Penggunaan / Fungsi</span>
                    <span className="font-bold text-slate-800">{hasilPlat.k_guna}</span>
                  </div>
                  {hasilPlat.sd_notice && (
                    <div className="py-2.5 flex justify-between">
                      <span className="text-slate-500 flex items-center gap-1">
                        <IconClock size={13} className="text-slate-400" />
                        Jatuh Tempo Pajak
                      </span>
                      <span className="font-bold text-slate-800">{formatDate(hasilPlat.sd_notice)}</span>
                    </div>
                  )}
                  {hasilPlat.sd_stnk && (
                    <div className="py-2.5 flex justify-between">
                      <span className="text-slate-500 flex items-center gap-1">
                        <IconFileText size={13} className="text-slate-400" />
                        Masa Berlaku STNK
                      </span>
                      <span className="font-bold text-slate-800">{formatDate(hasilPlat.sd_stnk)}</span>
                    </div>
                  )}
                </div>
              </div>

              {/* Tombol Tindakan Cepat (Navigasi Antar Tab) */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                <button
                  type="button"
                  onClick={() => onCekPajak && onCekPajak(hasilPlat.nopol)}
                  className="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 shadow-sm active:scale-95 transition-all"
                >
                  <IconCalculator size={18} stroke={2} />
                  <span>Hitung Estimasi Pajak Kendaraan Ini</span>
                </button>

                <button
                  type="button"
                  onClick={() => onSimulasi && onSimulasi({
                    merk: hasilPlat.merk,
                    tipe: hasilPlat.tipe,
                    tahun: hasilPlat.tahun,
                    nilai_jual: hasilPlat.njkb,
                    bobot: hasilPlat.bobot,
                    jenis_kendaraan: hasilPlat.jenis_kend,
                    k_guna: hasilPlat.k_guna,
                    sd_notice: hasilPlat.sd_notice,
                    sd_stnk: hasilPlat.sd_stnk,
                  })}
                  className="w-full bg-white hover:bg-slate-50 text-emerald-800 border border-emerald-300 font-bold py-3 px-4 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 shadow-xs active:scale-95 transition-all"
                >
                  <IconCash size={18} stroke={2} />
                  <span>Buka Simulasi Keringanan</span>
                </button>
              </div>
            </div>
          )}
        </div>
      )}

      {/* ============================================================== */}
      {/* HASIL MODE 2: KATALOG MEREK & TIPE                             */}
      {/* ============================================================== */}
      {activeMode === 'merek' && (
        <div className="space-y-3">
          <div className="flex justify-between items-center px-1">
            <span className="text-xs font-bold text-slate-400 uppercase tracking-wider">
              Hasil Katalog ({items.length})
            </span>
            {loadingMerek && <span className="text-xs text-emerald-600 font-semibold animate-pulse">Memuat katalog...</span>}
          </div>

          {loadingMerek ? (
            <NjkbListSkeleton count={6} />
          ) : items.length === 0 && searched ? (
            <div className="bg-white rounded-2xl p-8 text-center text-slate-400 text-xs sm:text-sm border border-slate-200/80">
              Tidak ada data NJKB yang cocok dengan kriteria pencarian.
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
              {items.map((item, idx) => (
                <div
                  key={idx}
                  className="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex items-center justify-between hover:border-emerald-300 hover:shadow-md transition-all"
                >
                  <div className="pr-2">
                    <div className="font-bold text-xs sm:text-sm text-slate-800">
                      {item.merk} {item.tipe}
                    </div>
                    <div className="text-[11px] sm:text-xs text-slate-500 flex items-center gap-2 mt-1">
                      <span className="bg-slate-100 text-slate-700 font-semibold px-2 py-0.5 rounded-md">
                        Tahun {item.tahun}
                      </span>
                      <span>•</span>
                      <span className="bg-slate-100 text-slate-700 font-semibold px-2 py-0.5 rounded-md">
                        Bobot {item.bobot}
                      </span>
                    </div>
                    <div className="font-mono-num font-extrabold text-emerald-700 text-sm mt-1.5">
                      {formatRupiah(item.nilai_jual)}
                    </div>
                  </div>

                  <button
                    onClick={() => onSelectNjkb && onSelectNjkb(item)}
                    title="Simulasikan Kendaraan Ini"
                    className="shrink-0 px-3 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white rounded-xl active:scale-95 transition-all flex items-center gap-1.5 text-xs font-bold shadow-xs"
                  >
                    <span>Simulasi</span>
                    <IconArrowRight size={14} stroke={2.2} />
                  </button>
                </div>
              ))}
            </div>
          )}
        </div>
      )}
    </div>
  );
}
