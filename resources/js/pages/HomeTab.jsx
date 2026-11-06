import React, { useState, useRef } from 'react';
import {
  IconSearch,
  IconSparkles,
  IconArrowRight,
  IconShieldCheck,
  IconBolt,
  IconReceiptTax,
  IconHistory,
  IconBook,
  IconRefresh,
  IconCalculator,
  IconDatabase
} from '@tabler/icons-react';

export default function HomeTab({ setActiveTab, onQuickSearch, currentUser }) {
  const isAdmin = currentUser?.role === 'admin';

  // 3 Smart Plate Boxes states
  const [nopolPrefix, setNopolPrefix] = useState('');
  const [nopolNumber, setNopolNumber] = useState('');
  const [nopolSuffix, setNopolSuffix] = useState('');

  const box1Ref = useRef(null);
  const box2Ref = useRef(null);
  const box3Ref = useRef(null);

  // Helper untuk memecah string nopol utuh
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
        prefix: parts[0].slice(0, 2),
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

  const handleSearchSubmit = (e) => {
    e.preventDefault();
    if (!nopolPrefix.trim() || !nopolNumber.trim()) {
      return;
    }
    const fullNopol = `${nopolPrefix.trim()}${nopolNumber.trim()}${nopolSuffix.trim()}`.toUpperCase();
    onQuickSearch(fullNopol);
    setActiveTab('cek');
  };

  return (
    <div className="p-4 sm:p-6 space-y-5 sm:space-y-6">
      {/* Top Section: Hero Card + Quick Search Grid on Tablet/PC */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5 items-stretch">
        {/* Hero Card */}
        <div 
          className="relative overflow-hidden rounded-2xl card-gradient-emerald-hero text-white p-5 sm:p-6 shadow-lg shadow-emerald-950/20 flex flex-col justify-between border border-emerald-600/30"
          style={{
            backgroundColor: '#064e3b',
            background: 'linear-gradient(135deg, #065f46 0%, #047857 50%, #134e4a 100%)',
          }}
        >
          <div className="relative z-10 space-y-3">
            <div className="inline-flex items-center gap-1.5 bg-emerald-600/70 backdrop-blur-md px-2.5 py-1 rounded-full text-[11px] font-semibold border border-emerald-400/30">
              <IconSparkles size={13} stroke={2} className="text-amber-300 shrink-0" />
              <span>Program Keringanan Pajak NTT 2026</span>
            </div>

            <div>
              <h2 className="text-xl sm:text-2xl font-extrabold leading-tight tracking-tight">
                Simulasi Balik Nama (BBN 2)
              </h2>
              <p className="text-emerald-100/90 text-xs sm:text-sm mt-1.5 leading-relaxed">
                Kalkulasi estimasi biaya balik nama kendaraan bermotor, mutasi masuk, STNK &amp; Plat baru.
              </p>
            </div>
          </div>

          <div className="relative z-10 pt-4 flex flex-wrap gap-2.5">
            <button
              type="button"
              onClick={() => setActiveTab('bbn2')}
              className="inline-flex items-center gap-2 bg-white text-emerald-900 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold shadow hover:bg-emerald-50 active:scale-95 transition-all cursor-pointer"
            >
              <span>Bea Balik Nama ke-2</span>
              <IconArrowRight size={16} stroke={2.2} className="shrink-0" />
            </button>

            {isAdmin && (
              <button
                type="button"
                onClick={() => setActiveTab('simulasi')}
                className="inline-flex items-center gap-1.5 bg-emerald-600/80 hover:bg-emerald-500 text-white border border-emerald-400/40 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold shadow active:scale-95 transition-all cursor-pointer"
              >
                <IconCalculator size={16} stroke={2.2} className="shrink-0 text-amber-300" />
                <span>Simulasi Pajak (Admin)</span>
              </button>
            )}
          </div>

          {/* Decorative background glow */}
          <div className="absolute -right-8 -bottom-8 w-44 h-44 bg-emerald-500/20 rounded-full blur-2xl pointer-events-none"></div>
        </div>

        {/* Quick Search Form */}
        <div className="bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-slate-200/80 flex flex-col justify-between">
          <div>
            <h3 className="font-bold text-slate-800 text-sm sm:text-base mb-1">
              Cek Estimasi Pajak
            </h3>
            <p className="text-xs text-slate-500 mb-3">
              Masukkan nomor polisi kendaraan wilayah NTT (DH, EB, ED).
            </p>
          </div>

          <form onSubmit={handleSearchSubmit} className="space-y-3">
            {/* Smart 3-Box Plate Input */}
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

            <button
              type="submit"
              className="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 sm:py-3 px-4 rounded-xl text-xs sm:text-sm shadow-md shadow-emerald-600/20 active:scale-98 transition-all flex items-center justify-center gap-2 cursor-pointer"
            >
              <IconBolt size={17} stroke={2.2} className="shrink-0" />
              <span>Cek Pajak Anda</span>
            </button>
          </form>
        </div>
      </div>

      {/* Quick Menu Grid — Terbuka sesuai Hak Akses Role User / Admin */}
      <div>
        <h4 className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 px-1">
          Layanan Utama {isAdmin && <span className="text-emerald-700 lowercase font-mono font-bold text-[11px]">(mode admin aktif)</span>}
        </h4>
        <div className={`grid gap-3 ${isAdmin ? 'grid-cols-2 sm:grid-cols-3' : 'grid-cols-2 md:grid-cols-4'}`}>
          {/* Card 1: Balik Nama (BBN 2) */}
          <button
            type="button"
            onClick={() => setActiveTab('bbn2')}
            className="w-full bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs text-left hover:border-teal-300 hover:shadow-md transition-all active:scale-95 group cursor-pointer"
          >
            <div
              className="w-10 h-10 rounded-xl flex items-center justify-center font-bold mb-3 group-hover:scale-110 transition-transform shrink-0"
              style={{ backgroundColor: '#f0fdfa', color: '#0d9488' }}
            >
              <IconRefresh size={22} stroke={2} className="shrink-0" />
            </div>
            <div className="font-bold text-slate-800 text-xs sm:text-sm leading-snug">
              Balik Nama (BBN 2)
            </div>
            <div className="text-[11px] sm:text-xs text-slate-500 mt-1">
              BPKB, STNK &amp; Plat Baru
            </div>
          </button>

          {/* Card 2: Cek Pajak Nopol */}
          <button
            type="button"
            onClick={() => setActiveTab('cek')}
            className="w-full bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs text-left hover:border-blue-300 hover:shadow-md transition-all active:scale-95 group cursor-pointer"
          >
            <div
              className="w-10 h-10 rounded-xl flex items-center justify-center font-bold mb-3 group-hover:scale-110 transition-transform shrink-0"
              style={{ backgroundColor: '#eff6ff', color: '#2563eb' }}
            >
              <IconReceiptTax size={22} stroke={2} className="shrink-0" />
            </div>
            <div className="font-bold text-slate-800 text-xs sm:text-sm leading-snug">
              Cek Pajak Nopol
            </div>
            <div className="text-[11px] sm:text-xs text-slate-500 mt-1">
              Tagihan PKB &amp; SWDKLLJ
            </div>
          </button>

          {/* Admin Only Card A: Simulasi Pajak & Amnesti */}
          {isAdmin && (
            <button
              type="button"
              onClick={() => setActiveTab('simulasi')}
              className="w-full bg-white p-4 rounded-2xl border border-amber-200/90 shadow-xs text-left hover:border-amber-400 hover:shadow-md transition-all active:scale-95 group cursor-pointer"
            >
              <div
                className="w-10 h-10 rounded-xl flex items-center justify-center font-bold mb-3 group-hover:scale-110 transition-transform shrink-0"
                style={{ backgroundColor: '#fffbeb', color: '#d97706' }}
              >
                <IconCalculator size={22} stroke={2} className="shrink-0" />
              </div>
              <div className="font-bold text-slate-800 text-xs sm:text-sm leading-snug flex items-center gap-1.5">
                <span>Simulasi Pajak</span>
                <span className="text-[9px] bg-amber-100 text-amber-800 px-1.5 py-0.2 rounded font-mono font-bold">ADMIN</span>
              </div>
              <div className="text-[11px] sm:text-xs text-slate-500 mt-1">
                Komparasi Pergub 64 vs Normal
              </div>
            </button>
          )}

          {/* Admin Only Card B: Katalog NJKB */}
          {isAdmin && (
            <button
              type="button"
              onClick={() => setActiveTab('njkb')}
              className="w-full bg-white p-4 rounded-2xl border border-cyan-200/90 shadow-xs text-left hover:border-cyan-400 hover:shadow-md transition-all active:scale-95 group cursor-pointer"
            >
              <div
                className="w-10 h-10 rounded-xl flex items-center justify-center font-bold mb-3 group-hover:scale-110 transition-transform shrink-0"
                style={{ backgroundColor: '#ecfeff', color: '#0891b2' }}
              >
                <IconDatabase size={22} stroke={2} className="shrink-0" />
              </div>
              <div className="font-bold text-slate-800 text-xs sm:text-sm leading-snug flex items-center gap-1.5">
                <span>Katalog NJKB</span>
                <span className="text-[9px] bg-cyan-100 text-cyan-800 px-1.5 py-0.2 rounded font-mono font-bold">ADMIN</span>
              </div>
              <div className="text-[11px] sm:text-xs text-slate-500 mt-1">
                Basis Nilai Jual NTT
              </div>
            </button>
          )}

          {/* Card 3: Riwayat Aktivitas */}
          <button
            type="button"
            onClick={() => setActiveTab('riwayat')}
            className="w-full bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs text-left hover:border-amber-300 hover:shadow-md transition-all active:scale-95 group cursor-pointer"
          >
            <div
              className="w-10 h-10 rounded-xl flex items-center justify-center font-bold mb-3 group-hover:scale-110 transition-transform shrink-0"
              style={{ backgroundColor: '#fffbeb', color: '#d97706' }}
            >
              <IconHistory size={22} stroke={2} className="shrink-0" />
            </div>
            <div className="font-bold text-slate-800 text-xs sm:text-sm leading-snug">
              Riwayat Aktivitas
            </div>
            <div className="text-[11px] sm:text-xs text-slate-500 mt-1">
              Pencarian Terakhir Anda
            </div>
          </button>

          {/* Card 4: Ketentuan Relaksasi */}
          <button
            type="button"
            onClick={() => setActiveTab('info')}
            className="w-full bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs text-left hover:border-purple-300 hover:shadow-md transition-all active:scale-95 group cursor-pointer"
          >
            <div
              className="w-10 h-10 rounded-xl flex items-center justify-center font-bold mb-3 group-hover:scale-110 transition-transform shrink-0"
              style={{ backgroundColor: '#faf5ff', color: '#9333ea' }}
            >
              <IconBook size={22} stroke={2} className="shrink-0" />
            </div>
            <div className="font-bold text-slate-800 text-xs sm:text-sm leading-snug">
              Ketentuan Relaksasi
            </div>
            <div className="text-[11px] sm:text-xs text-slate-500 mt-1">
              Pergub NTT No. 64/2026
            </div>
          </button>
        </div>
      </div>
    </div>
  );
}
