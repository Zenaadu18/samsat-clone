import React from 'react';
import {
  IconFileText,
  IconCheck,
  IconSparkles,
  IconCalendar,
  IconDevices,
  IconBuildingBank,
  IconShieldCheck,
  IconArrowsExchange
} from '@tabler/icons-react';

export default function InfoTab() {
  return (
    <div className="p-4 sm:p-6 space-y-4 sm:space-y-5 max-w-3xl mx-auto">
      {/* Header Banner: Pergub 64 / 2026 */}
      <div 
        className="card-gradient-emerald-hero rounded-2xl p-5 sm:p-6 text-white shadow-md relative overflow-hidden border border-emerald-600/30"
        style={{
          backgroundColor: '#064e3b',
          background: 'linear-gradient(135deg, #065f46 0%, #047857 50%, #134e4a 100%)',
        }}
      >
        <div className="relative z-10 space-y-2">
          <div className="inline-flex items-center gap-1.5 bg-emerald-600/70 backdrop-blur-md px-3 py-1 rounded-full text-xs font-semibold border border-emerald-400/30">
            <IconCalendar size={14} stroke={2} className="text-amber-300 shrink-0" />
            <span>Masa Berlaku: 1 November s.d. 31 Desember 2026</span>
          </div>

          <h2 className="text-lg sm:text-2xl font-black tracking-tight leading-snug">
            Keringanan Pajak Kendaraan Bermotor (PKB & BBNKB)
          </h2>
          <p className="text-emerald-100/90 text-xs sm:text-sm leading-relaxed">
            Peraturan Gubernur Nusa Tenggara Timur Nomor 64 Tahun 2026 tentang Relaksasi dan Amnesti Pajak Kendaraan Daerah.
          </p>

          <div className="pt-2 flex flex-wrap gap-2">
            <span
              className="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold shrink-0"
              style={{ backgroundColor: '#ffffff', color: '#064e3b' }}
            >
              <IconSparkles size={14} stroke={2.5} className="text-amber-500 shrink-0" />
              <span>Denda PKB Dihapus 100% Seluruh Pendaftaran</span>
            </span>
          </div>
        </div>

        {/* Decorative circle */}
        <div className="absolute -right-10 -bottom-10 w-44 h-44 bg-emerald-500/20 rounded-full blur-2xl pointer-events-none"></div>
      </div>

      {/* Point 1: Denda PKB & Tunggakan */}
      <div className="bg-white rounded-2xl p-5 shadow-xs border border-slate-200/90 space-y-3">
        <div className="flex items-center gap-2.5">
          <div
            className="w-9 h-9 rounded-xl flex items-center justify-center font-bold shrink-0"
            style={{ backgroundColor: '#ecfdf5', color: '#059669' }}
          >
            <IconShieldCheck size={20} stroke={2} className="shrink-0" />
          </div>
          <div>
            <h3 className="font-extrabold text-slate-900 text-sm sm:text-base">
              1. Denda & Pokok Tunggakan PKB
            </h3>
            <p className="text-xs text-slate-500">
              Penghapusan sanksi denda dan pembebasan pokok tahun lalu
            </p>
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
          <div className="p-3.5 rounded-xl border border-emerald-100" style={{ backgroundColor: '#f0fdf4' }}>
            <div className="font-black text-emerald-900 text-xs sm:text-sm flex items-center gap-1.5 mb-1">
              <IconCheck size={16} stroke={2.5} className="text-emerald-600 shrink-0" />
              Denda PKB Bebas 100%
            </div>
            <p className="text-emerald-800 text-[11px] leading-relaxed">
              Denda sanksi administratif keterlambatan dihapus 100% untuk semua jenis pendaftaran kendaraan bermotor.
            </p>
          </div>

          <div className="p-3.5 rounded-xl border border-teal-100" style={{ backgroundColor: '#f0fdfa' }}>
            <div className="font-black text-teal-900 text-xs sm:text-sm flex items-center gap-1.5 mb-1">
              <IconCheck size={16} stroke={2.5} className="text-teal-600 shrink-0" />
              Pokok Tunggakan Dihapus 100%
            </div>
            <p className="text-teal-800 text-[11px] leading-relaxed">
              Masa tunggakan tahun sebelumnya dihapus 100% (cukup bayar pokok berjalan). <em>Via Payment Online</em> dapat diskon tambahan pokok 5% (R2/3) atau 2% (R4+).
            </p>
          </div>
        </div>
      </div>

      {/* Point 2: BBNKB (Bea Balik Nama Kendaraan Bermotor) */}
      <div className="bg-white rounded-2xl p-5 shadow-xs border border-slate-200/90 space-y-3">
        <div className="flex items-center gap-2.5">
          <div
            className="w-9 h-9 rounded-xl flex items-center justify-center font-bold shrink-0"
            style={{ backgroundColor: '#eff6ff', color: '#2563eb' }}
          >
            <IconArrowsExchange size={20} stroke={2} className="shrink-0" />
          </div>
          <div>
            <h3 className="font-extrabold text-slate-900 text-sm sm:text-base">
              2. Keringanan BBNKB (Balik Nama)
            </h3>
            <p className="text-xs text-slate-500">
              Pengurangan Dasar Pengenaan Pajak BBNKB
            </p>
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
          <div className="p-3.5 rounded-xl border border-blue-100" style={{ backgroundColor: '#eff6ff' }}>
            <span className="font-black text-blue-900 block text-xs sm:text-sm">Roda 2 & Roda 3</span>
            <span className="text-blue-700 text-[11px] block mt-0.5">
              Pengurangan Dasar Pengenaan BBNKB sebesar <strong>15%</strong>.
            </span>
          </div>

          <div className="p-3.5 rounded-xl border border-blue-100" style={{ backgroundColor: '#eff6ff' }}>
            <span className="font-black text-blue-900 block text-xs sm:text-sm">Roda 4 dan Seterusnya</span>
            <span className="text-blue-700 text-[11px] block mt-0.5">
              Pengurangan Dasar Pengenaan BBNKB sebesar <strong>20%</strong>.
            </span>
          </div>
        </div>
      </div>

      {/* Point 3: PKB Taat Pajak (Bayar Sebelum Jatuh Tempo) */}
      <div className="bg-white rounded-2xl p-5 shadow-xs border border-slate-200/90 space-y-3">
        <div className="flex items-center gap-2.5">
          <div
            className="w-9 h-9 rounded-xl flex items-center justify-center font-bold shrink-0"
            style={{ backgroundColor: '#faf5ff', color: '#9333ea' }}
          >
            <IconFileText size={20} stroke={2} className="shrink-0" />
          </div>
          <div>
            <h3 className="font-extrabold text-slate-900 text-sm sm:text-base">
              3. PKB Taat Pajak (Sebelum Jatuh Tempo)
            </h3>
            <p className="text-xs text-slate-500">
              Dapat pengurangan Dasar Pengenaan 17,5% + diskon pokok tahun berjalan
            </p>
          </div>
        </div>

        <div className="space-y-2 text-xs">
          <div className="p-3 rounded-xl border border-purple-100 space-y-2" style={{ backgroundColor: '#faf5ff' }}>
            <div className="font-bold text-purple-900 text-xs">
              Roda 2 dan 3 (Dasar Pengenaan Diskon 17,5%):
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[11px]">
              <div className="bg-white/80 p-2.5 rounded-lg border border-purple-200">
                <span className="font-black text-purple-950 block">0 – 30 Hari Sebelum</span>
                <span className="text-purple-700 block mt-0.5">Loket: Diskon Pokok <strong>10%</strong></span>
                <span className="text-emerald-700 font-bold block">Online: Diskon Pokok <strong>15%</strong> (+5%)</span>
              </div>
              <div className="bg-white/80 p-2.5 rounded-lg border border-purple-200">
                <span className="font-black text-purple-950 block">31 – 60 Hari Sebelum</span>
                <span className="text-purple-700 block mt-0.5">Loket: Diskon Pokok <strong>15%</strong></span>
                <span className="text-emerald-700 font-bold block">Online: Diskon Pokok <strong>20%</strong> (+5%)</span>
              </div>
              <div className="bg-white/80 p-2.5 rounded-lg border border-purple-200">
                <span className="font-black text-purple-950 block">61 – 90 Hari Sebelum</span>
                <span className="text-purple-700 block mt-0.5">Loket: Diskon Pokok <strong>20%</strong></span>
                <span className="text-emerald-700 font-bold block">Online: Diskon Pokok <strong>25%</strong> (+5%)</span>
              </div>
            </div>
          </div>

          <div className="p-3 rounded-xl border border-purple-100" style={{ backgroundColor: '#faf5ff' }}>
            <div className="font-bold text-purple-900 text-xs mb-1">
              Roda 4 dan Seterusnya (Dasar Pengenaan Diskon 17,5%):
            </div>
            <div className="bg-white/80 p-2.5 rounded-lg border border-purple-200 text-[11px]">
              <span className="font-black text-purple-950 block">0 – 90 Hari Sebelum Jatuh Tempo</span>
              <span className="text-purple-700 block mt-0.5">Bayar Loket: Diskon Pokok <strong>10%</strong></span>
              <span className="text-emerald-700 font-bold block">Bayar Payment Online: Diskon Pokok <strong>12%</strong> (+2%)</span>
            </div>
          </div>
        </div>
      </div>

      {/* Point 4 & 5: PKB Terlambat & Mutasi Masuk Luar Daerah */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
        {/* PKB Terlambat */}
        <div className="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/90 space-y-2.5">
          <div className="flex items-center gap-2">
            <div
              className="w-8 h-8 rounded-lg flex items-center justify-center font-bold shrink-0"
              style={{ backgroundColor: '#fffbeb', color: '#d97706' }}
            >
              <IconDevices size={18} stroke={2} className="shrink-0" />
            </div>
            <h4 className="font-extrabold text-slate-900 text-xs sm:text-sm">
              4. PKB Terlambat Bayar
            </h4>
          </div>
          <div className="text-xs text-slate-600 space-y-1.5">
            <p className="text-[11px] leading-relaxed">
              • <strong>Bayar Loket</strong>: Pengurangan Dasar Pengenaan PKB sebesar <strong>17,5%</strong>.
            </p>
            <p className="text-[11px] leading-relaxed text-amber-900 font-medium bg-amber-50/80 p-2 rounded-lg border border-amber-200">
              • <strong>Bayar Online</strong>: Dasar Pengenaan diskon <strong>17,5%</strong> + Diskon Pokok <strong>5% (R2/3)</strong> atau <strong>2% (R4+)</strong>.
            </p>
          </div>
        </div>

        {/* PKB Mutasi Masuk */}
        <div className="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/90 space-y-2.5">
          <div className="flex items-center gap-2">
            <div
              className="w-8 h-8 rounded-lg flex items-center justify-center font-bold shrink-0"
              style={{ backgroundColor: '#eef2ff', color: '#4f46e5' }}
            >
              <IconBuildingBank size={18} stroke={2} className="shrink-0" />
            </div>
            <h4 className="font-extrabold text-slate-900 text-xs sm:text-sm">
              5. Mutasi Masuk Luar Daerah
            </h4>
          </div>
          <div className="text-xs text-slate-600 space-y-1.5">
            <p className="text-[11px] leading-relaxed">
              • Pengurangan Dasar Pengenaan PKB sebesar <strong>17,5%</strong>.
            </p>
            <p className="text-[11px] leading-relaxed text-indigo-900 font-medium bg-indigo-50/80 p-2 rounded-lg border border-indigo-200">
              • Ditambah Pengurangan Pokok Pajak Tahun Berjalan sebesar <strong>50%</strong>.
            </p>
          </div>
        </div>
      </div>

      {/* Footer Note */}
      <div className="text-center text-slate-400 text-[11px] pt-3 pb-1 border-t border-slate-200/70 space-y-1">
        <p className="font-semibold text-slate-600">Pajak.In NTT • Berdasarkan Pergub No. 64 Tahun 2026</p>
        <p>© {new Date().getFullYear()} @sena. Seluruh hak cipta dilindungi.</p>
      </div>
    </div>
  );
}
