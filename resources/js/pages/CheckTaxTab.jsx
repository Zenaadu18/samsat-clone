import React, { useState, useEffect, useRef } from 'react';
import {
  IconSearch,
  IconRefresh,
  IconAlertTriangle,
  IconCheck,
  IconCopy,
  IconDownload,
  IconCalculator,
  IconChevronDown,
  IconChevronUp,
  IconInfoCircle,
  IconClock,
  IconShieldCheck
} from '@tabler/icons-react';
import { formatRupiah, formatDate } from '../utils/formatters';
import { TaxCheckSkeleton } from '../components/SkeletonLoader';
import { getApiBase } from '../utils/config';

export default function CheckTaxTab({ initialNopol = '', onClearInitialNopol, onSwitchToSimulation }) {
  // 3 Smart Plate Boxes states
  const [nopolPrefix, setNopolPrefix] = useState('');
  const [nopolNumber, setNopolNumber] = useState('');
  const [nopolSuffix, setNopolSuffix] = useState('');

  const [isMutasi, setIsMutasi] = useState(false);
  const [loading, setLoading] = useState(false);
  const [downloadingImg, setDownloadingImg] = useState(false);
  const [error, setError] = useState(null);
  const [taxData, setTaxData] = useState(null);
  const [copied, setCopied] = useState(false);

  const box1Ref = useRef(null);
  const box2Ref = useRef(null);
  const box3Ref = useRef(null);
  const resultRef = useRef(null);

  // Accordion toggle states
  const [openSections, setOpenSections] = useState({
    pkb: false,
    opsen: false,
    denda: false,
    swdkllj: false,
    stnk: false,
  });

  const toggleSection = (key) => {
    setOpenSections((prev) => ({ ...prev, [key]: !prev[key] }));
  };

  // Helper untuk memecah string nopol utuh menjadi 3 komponen
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

  useEffect(() => {
    if (initialNopol) {
      const parsed = splitNopol(initialNopol);
      setNopolPrefix(parsed.prefix);
      setNopolNumber(parsed.number);
      setNopolSuffix(parsed.suffix);
      handleCheck(`${parsed.prefix}${parsed.number}${parsed.suffix}`);
    }
  }, [initialNopol]);

  // Handler smart paste untuk semua kotak
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

  const handleCheck = async (nopolToCheck = null) => {
    let targetNopol = '';
    if (nopolToCheck) {
      targetNopol = nopolToCheck.trim().toUpperCase().replace(/\s+/g, '');
    } else {
      if (!nopolPrefix.trim() || !nopolNumber.trim()) {
        setError('Nomor polisi harus diisi dengan lengkap (Wilayah & Angka).');
        return;
      }
      targetNopol = `${nopolPrefix}${nopolNumber}${nopolSuffix}`.trim().toUpperCase();
    }

    // Reset seluruh kotak input agar kosong setelah dicek
    setNopolPrefix('');
    setNopolNumber('');
    setNopolSuffix('');
    if (onClearInitialNopol) {
      onClearInitialNopol();
    }

    setLoading(true);
    setError(null);

    try {
      const token = localStorage.getItem('infopkb_token') || '';
      const response = await fetch(`${getApiBase()}/check`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`,
          'X-CSRF-TOKEN': window.__SAMSAT_CONFIG__?.csrfToken || '',
        },
        body: JSON.stringify({
          nopol: targetNopol,
          is_mutasi: isMutasi,
        }),
      });

      const res = await response.json();

      if (!response.ok || res.status !== 'success') {
        throw new Error(res.message || 'Pemeriksaan pajak gagal.');
      }

      setTaxData(res.data);
    } catch (err) {
      setError(err.message || 'Gagal memuat data dari server.');
      setTaxData(null);
    } finally {
      setLoading(false);
    }
  };

  const handleCopyText = () => {
    if (!taxData) return;
    const d = taxData;

    let text = `PAJAK.IN - DATA KENDARAAN NTT
Nomor Polisi : ${d.nopol}
Nama Pemilik : ${d.pemilik || '-'}
Unit : ${d.unit || `${d.merk} ${d.tipe}`}
Tahun Pembuatan : ${d.tahun || '-'}
Masa Pajak : ${formatDate(d.sd_notice)}
Masa STNK : ${formatDate(d.sd_stnk)}
`;

    if (d.is_pajak_aktif) {
      text += `\nSTATUS: PAJAK KENDARAAN MASIH AKTIF
Sisa Masa Berlaku: ${d.sisa_hari_aktif} hari lagi (s.d. ${formatDate(d.sd_notice)})
Catatan: Pembayaran perpanjangan pajak dapat diproses mulai 90 hari sebelum tanggal jatuh tempo.
--------------------------------
TOTAL ESTIMASI BAYAR: Rp 0\n`;
    } else {
      text += `\nRINCIAN ESTIMASI TAGIHAN:
- PKB Pokok: ${formatRupiah(d.pkb_total)} ${d.pkb_tunggakan > 0 ? `(Berjalan: ${formatRupiah(d.pkb_berjalan)}, Tunggakan: ${formatRupiah(d.pkb_tunggakan)})` : ''}
- Opsen PKB: ${formatRupiah(d.opsen_pkb)}
- Denda PKB: ${formatRupiah(d.denda_pkb || 0)}
- Denda Opsen PKB: ${formatRupiah(d.denda_opsen_pkb || d.denda_opsen || 0)}
- SWDKLLJ: ${formatRupiah(d.swdkllj || 0)} ${d.denda_swdkllj > 0 ? `(Denda: ${formatRupiah(d.denda_swdkllj)})` : ''}
${d.biaya_stnk_total > 0 ? `- Biaya STNK/TNKB: ${formatRupiah(d.biaya_stnk_total)}\n` : ''}--------------------------------
TOTAL TAGIHAN: ${formatRupiah(d.total_pajak || d.total_tagihan || d.total_bayar)}\n`;

      if (d.diskon_digital > 0 || (d.total_setelah_diskon && d.total_setelah_diskon < (d.total_pajak || d.total_bayar))) {
        const diskon = d.diskon_digital || ((d.total_pajak || d.total_bayar) - d.total_setelah_diskon);
        const bayarNet = d.total_setelah_diskon || ((d.total_pajak || d.total_bayar) - diskon);
        text += `- Diskon Pembayaran Digital PRO NTT: -${formatRupiah(diskon)}\n`;
        text += `TOTAL BAYAR (SETELAH DISKON): ${formatRupiah(bayarNet)}\n`;
      }
    }

    text += `\nDihitung via Aplikasi Pajak.In • © ${new Date().getFullYear()} @sena`;

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(() => {
        setCopied(true);
        setTimeout(() => setCopied(false), 2500);
      }).catch(() => fallbackCopy(text));
    } else {
      fallbackCopy(text);
    }
  };

  const fallbackCopy = (text) => {
    const el = document.createElement('textarea');
    el.value = text;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    setCopied(true);
    setTimeout(() => setCopied(false), 2500);
  };

  const handleDownloadImage = async () => {
    if (!taxData) return;
    setDownloadingImg(true);

    try {
      if (resultRef.current) {
        const { toPng } = await import('html-to-image');
        const dataUrl = await toPng(resultRef.current, {
          pixelRatio: 2,
          backgroundColor: '#f8fafc',
          cacheBust: true,
        });

        const link = document.createElement('a');
        link.download = `Info_Pajak_${taxData.nopol}.png`;
        link.href = dataUrl;
        link.click();
        return;
      }
      downloadReceiptCanvas(taxData);
    } catch (err) {
      console.warn('html-to-image dialihkan ke canvas renderer:', err);
      try {
        downloadReceiptCanvas(taxData);
      } catch (canvasErr) {
        console.error('Canvas download gagal:', canvasErr);
        alert('Gagal mendownload gambar. Silakan gunakan tombol Salin Hasil.');
      }
    } finally {
      setDownloadingImg(false);
    }
  };

  const downloadReceiptCanvas = (d) => {
    const canvas = document.createElement('canvas');
    canvas.width = 800;
    canvas.height = 1000;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Latar belakang
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, 800, 1000);

    // Header strip
    ctx.fillStyle = '#059669';
    ctx.fillRect(0, 0, 800, 100);

    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 24px sans-serif';
    ctx.fillText('PAJAK.IN', 40, 48);

    ctx.font = '14px sans-serif';
    ctx.fillText('Informasi Kendaraan & Rincian Penetapan Pajak', 40, 78);

    // Badge Plat Nomor
    ctx.fillStyle = '#0f172a';
    if (ctx.roundRect) {
      ctx.beginPath();
      ctx.roundRect(40, 130, 240, 52, 10);
      ctx.fill();
    } else {
      ctx.fillRect(40, 130, 240, 52);
    }

    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 22px monospace';
    ctx.fillText(d.nopol, 60, 164);

    // Identitas Kendaraan
    ctx.fillStyle = '#1e293b';
    ctx.font = '15px sans-serif';
    ctx.fillText(`Nama Pemilik : ${d.pemilik || '-'}`, 40, 225);
    ctx.fillText(`Unit : ${d.unit || `${d.merk} ${d.tipe}`}`, 40, 252);
    ctx.fillText(`Tahun Pembuatan : ${d.tahun || '-'}`, 40, 279);
    ctx.fillStyle = '#047857';
    ctx.font = 'bold 15px sans-serif';
    ctx.fillText(`Masa Pajak : ${formatDate(d.sd_notice)}`, 40, 306);
    ctx.fillStyle = '#1e293b';
    ctx.font = '15px sans-serif';
    ctx.fillText(`Masa STNK : ${formatDate(d.sd_stnk)}`, 40, 333);

    // Garis Pemisah
    ctx.strokeStyle = '#e2e8f0';
    ctx.lineWidth = 1.5;
    ctx.beginPath();
    ctx.moveTo(40, 355);
    ctx.lineTo(760, 355);
    ctx.stroke();

    // Rincian Tagihan
    ctx.fillStyle = '#334155';
    ctx.font = 'bold 17px sans-serif';
    ctx.fillText('RINCIAN PENETAPAN PAJAK', 40, 385);

    ctx.font = '15px sans-serif';
    let y = 425;

    if (d.is_pajak_aktif) {
      ctx.fillStyle = '#f0fdf4';
      ctx.strokeStyle = '#22c55e';
      ctx.lineWidth = 1.5;
      if (ctx.roundRect) {
        ctx.beginPath();
        ctx.roundRect(40, 410, 720, 85, 10);
        ctx.fill();
        ctx.stroke();
      } else {
        ctx.fillRect(40, 410, 720, 85);
        ctx.strokeRect(40, 410, 720, 85);
      }

      ctx.fillStyle = '#15803d';
      ctx.font = 'bold 18px sans-serif';
      ctx.fillText('PAJAK KENDARAAN MASIH AKTIF', 60, 445);

      ctx.font = '14px sans-serif';
      ctx.fillStyle = '#166534';
      ctx.fillText(`Masa aktif s.d. ${formatDate(d.sd_notice)} (${d.sisa_hari_aktif} hari lagi). Pembayaran dibuka 90 hari sebelum jatuh tempo.`, 60, 472);

      y = 520;
    } else {
      const items = [
        ['1. PKB Pokok (Pajak Kendaraan)', formatRupiah(d.pkb_total)],
        ['2. Opsen PKB (66%)', formatRupiah(d.opsen_pkb)],
        ['3. Denda Keterlambatan PKB', formatRupiah(d.denda_pkb || 0)],
        ['4. Denda Opsen PKB', formatRupiah(d.denda_opsen_pkb || d.denda_opsen || 0)],
        ['5. SWDKLLJ (Jasa Raharja)', formatRupiah(d.swdkllj || 0)],
        ['6. Denda SWDKLLJ', formatRupiah(d.denda_swdkllj || 0)],
        ['7. PNBP STNK & TNKB', formatRupiah(d.biaya_stnk_total || 0)],
      ];

      items.forEach(([label, val]) => {
        ctx.fillStyle = '#475569';
        ctx.fillText(label, 40, y);
        const textWidth = ctx.measureText(val).width;
        ctx.fillText(val, 760 - textWidth, y);
        y += 36;
      });
    }

    // Box Total Tagihan (Nilai Sebelum Diskon)
    ctx.fillStyle = '#f8fafc';
    ctx.strokeStyle = '#cbd5e1';
    ctx.lineWidth = 2;
    if (ctx.roundRect) {
      ctx.beginPath();
      ctx.roundRect(40, y + 15, 720, 75, 12);
      ctx.fill();
      ctx.stroke();
    } else {
      ctx.fillRect(40, y + 15, 720, 75);
      ctx.strokeRect(40, y + 15, 720, 75);
    }

    ctx.fillStyle = '#1e293b';
    ctx.font = 'bold 16px sans-serif';
    ctx.fillText('TOTAL ESTIMASI TAGIHAN', 65, y + 58);

    ctx.font = 'bold 24px monospace';
    const totStr = formatRupiah(d.total_pajak || d.total_tagihan || d.total_bayar);
    const totWidth = ctx.measureText(totStr).width;
    ctx.fillText(totStr, 735 - totWidth, y + 59);

    y += 95;

    // Box Diskon Pembayaran Digital jika ada
    const hasDiskon = !d.is_pajak_aktif && (d.diskon_digital > 0 || (d.total_setelah_diskon && d.total_setelah_diskon < (d.total_pajak || d.total_bayar)));
    if (hasDiskon) {
      const diskonVal = d.diskon_digital || ((d.total_pajak || d.total_bayar) - d.total_setelah_diskon);
      const bayarNet = d.total_setelah_diskon || ((d.total_pajak || d.total_bayar) - diskonVal);

      ctx.fillStyle = '#ecfdf5';
      ctx.strokeStyle = '#10b981';
      ctx.lineWidth = 2;
      if (ctx.roundRect) {
        ctx.beginPath();
        ctx.roundRect(40, y + 5, 720, 80, 12);
        ctx.fill();
        ctx.stroke();
      } else {
        ctx.fillRect(40, y + 5, 720, 80);
        ctx.strokeRect(40, y + 5, 720, 80);
      }

      ctx.fillStyle = '#065f46';
      ctx.font = 'bold 15px sans-serif';
      ctx.fillText('TOTAL BAYAR (SETELAH DISKON)', 65, y + 42);
      ctx.font = '11px sans-serif';
      ctx.fillStyle = '#047857';
      ctx.fillText(`Diskon Pembayaran Digital PRO NTT: -${formatRupiah(diskonVal)}`, 65, y + 66);

      ctx.font = 'bold 24px monospace';
      ctx.fillStyle = '#065f46';
      const netStr = formatRupiah(bayarNet);
      const netWidth = ctx.measureText(netStr).width;
      ctx.fillText(netStr, 735 - netWidth, y + 52);
    }

    // Footer Catatan & Copyright
    ctx.fillStyle = '#94a3b8';
    ctx.font = '12px sans-serif';
    ctx.fillText(`Pajak.In • © ${new Date().getFullYear()} @sena`, 40, 960);

    const link = document.createElement('a');
    link.download = `PajakIn_${d.nopol}.png`;
    link.href = canvas.toDataURL('image/png');
    link.click();
  };

  return (
    <div className="p-4 sm:p-6 space-y-4">
      {/* Search Input Card */}
      <div className="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-200/80">
        <h2 className="font-bold text-slate-800 text-sm sm:text-base mb-1">
          Pengecekan Pajak Kendaraan
        </h2>

        <form onSubmit={(e) => { e.preventDefault(); handleCheck(); }} className="space-y-4">
          <div>
            <div className="flex items-center justify-between mb-2 px-1">
              <span className="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                Nomor Polisi Kendaraan
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
          </div>

          <div className="pt-2">
            <button
              type="submit"
              disabled={loading}
              className="w-full bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white font-bold py-2.5 sm:py-3 px-4 rounded-xl text-xs sm:text-sm shadow-md shadow-emerald-600/20 active:scale-98 transition-all flex items-center justify-center gap-2 cursor-pointer"
            >
              {loading ? (
                <>
                  <IconRefresh className="animate-spin" size={16} stroke={2} />
                  <span>Menghitung Pajak Kendaraan...</span>
                </>
              ) : (
                <>
                  <IconSearch size={16} stroke={2} />
                  <span>Cek Pajak Sekarang</span>
                </>
              )}
            </button>
          </div>
        </form>
      </div>

      {/* Loading Skeleton */}
      {loading && <TaxCheckSkeleton />}

      {/* Error Alert */}
      {error && !loading && (
        <div className="bg-rose-50 border border-rose-200 rounded-2xl p-4 flex items-start gap-3">
          <IconAlertTriangle className="text-rose-600 shrink-0 mt-0.5" size={20} stroke={2} />
          <div className="text-xs text-rose-800">
            <p className="font-bold">Pemeriksaan Gagal</p>
            <p className="mt-0.5 leading-relaxed">{error}</p>
          </div>
        </div>
      )}

      {/* Result Tax Breakdown */}
      {taxData && !loading && (
        <div className="space-y-4">
          {/* Target Capture Area: HANYA Informasi Kendaraan & Rincian Penetapan Pajak */}
          <div ref={resultRef} className="space-y-4 bg-slate-100/70 p-2 sm:p-3 rounded-2xl">
            {/* Banner Pajak Masih Aktif (> 90 Hari) */}
            {taxData.is_pajak_aktif && (
              <div 
                className="border-2 border-emerald-500 rounded-2xl p-4 sm:p-5 flex items-start gap-3.5 shadow-sm"
                style={{
                  backgroundColor: '#ecfdf5',
                  background: 'linear-gradient(135deg, #d1fae5 0%, #ecfdf5 50%, #f0fdf4 100%)',
                }}
              >
                <div className="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-600/30">
                  <IconCheck size={22} stroke={3} />
                </div>
                <div className="text-xs sm:text-sm text-emerald-950 flex-1">
                  <div className="flex flex-wrap items-center gap-2 mb-1">
                    <h3 className="font-black text-sm sm:text-base text-emerald-800 tracking-wide uppercase">
                      Pajak Kendaraan Masih Aktif
                    </h3>
                    <span className="bg-emerald-600 text-white text-[10px] font-extrabold px-2.5 py-0.5 rounded-full shadow-xs">
                      {taxData.sisa_hari_aktif} Hari Tersisa
                    </span>
                  </div>
                  <p className="text-slate-600 text-xs sm:text-sm leading-relaxed">
                    Masa berlaku pajak kendaraan Anda masih aktif sampai dengan <strong className="text-emerald-900 font-bold">{formatDate(taxData.sd_notice)}</strong>.
                    Pembayaran pajak tahunan berikutnya baru dapat diproses mulai <strong>90 hari</strong> sebelum tanggal jatuh tempo.
                  </p>
                </div>
              </div>
            )}

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {/* Left Column: Vehicle Info Card */}
              <div className="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-200/80 flex flex-col justify-between">
                <div>
                  <div className="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                    <div>
                      <span className="text-slate-400 text-[10px] font-bold block uppercase tracking-wider">
                        Nomor Polisi :
                      </span>
                      <span className="font-mono-num font-black text-xl text-slate-900 tracking-wider">
                        {taxData.nopol}
                      </span>
                    </div>
                    {taxData.is_pajak_aktif ? (
                      <span className="bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-2.5 py-1 rounded-lg flex items-center gap-1.5">
                        <span className="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                        Pajak Aktif
                      </span>
                    ) : taxData.tunggakan_ada ? (
                      <span className="bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2.5 py-1 rounded-lg">
                        {taxData.tahun_tunggakan} Thn Tunggakan
                      </span>
                    ) : (
                      <span className="bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-2.5 py-1 rounded-lg">
                        Tepat Waktu
                      </span>
                    )}
                  </div>

                  <div className="space-y-2.5 text-xs text-slate-700">
                    <div>
                      <span className="text-slate-400 text-[10px] font-semibold block uppercase tracking-wider">
                        Nama Pemilik :
                      </span>
                      <span className="font-bold text-slate-800 text-sm">
                        {taxData.pemilik || '-'}
                      </span>
                    </div>

                    <div>
                      <span className="text-slate-400 text-[10px] font-semibold block uppercase tracking-wider">
                        Unit :
                      </span>
                      <span className="font-semibold text-slate-800 text-sm">
                        {taxData.unit || `${taxData.merk} ${taxData.tipe}`}
                      </span>
                    </div>

                    <div className="pt-2 border-t border-slate-100 space-y-2.5">
                      <div>
                        <span className="text-slate-400 text-[10px] font-semibold block uppercase tracking-wider">
                          Tahun Pembuatan :
                        </span>
                        <span className="font-bold text-slate-800 text-sm">
                          {taxData.tahun || '-'}
                        </span>
                      </div>

                      <div className="grid grid-cols-2 gap-2">
                        <div>
                          <span className="text-slate-400 text-[10px] font-semibold block uppercase tracking-wider">
                            Masa Pajak :
                          </span>
                          <span className="font-bold text-emerald-700 text-sm">
                            {formatDate(taxData.sd_notice)}
                          </span>
                        </div>
                        <div>
                          <span className="text-slate-400 text-[10px] font-semibold block uppercase tracking-wider">
                            Masa STNK :
                          </span>
                          <span className="font-bold text-slate-800 text-sm">
                            {formatDate(taxData.sd_stnk)}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {/* Right Column: Detailed Native Calculation Rows */}
              <div className="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-200/80 space-y-2.5 flex flex-col justify-between">
                <div>
                  <h3 className="font-bold text-xs uppercase tracking-wider text-slate-400 mb-2">
                    Rincian Penetapan Pajak
                  </h3>

                  {taxData.is_pajak_aktif ? (
                    <div className="p-3.5 bg-emerald-50/70 border border-emerald-200/90 rounded-xl space-y-2.5 mb-2">
                      <div className="flex items-center gap-2 text-emerald-800 font-bold text-xs">
                        <IconCheck size={16} stroke={2.5} />
                        <span>Status: Masa Pajak Masih Aktif</span>
                      </div>
                      <div className="text-[11px] text-slate-600 space-y-1.5">
                        <div className="flex justify-between py-1 border-b border-emerald-100">
                          <span>Batas Akhir PKB:</span>
                          <span className="font-bold text-slate-800">{formatDate(taxData.sd_notice)}</span>
                        </div>
                        <div className="flex justify-between py-1 border-b border-emerald-100">
                          <span>Sisa Masa Berlaku:</span>
                          <span className="font-bold text-emerald-700">{taxData.sisa_hari_aktif} Hari Lagi</span>
                        </div>
                        <div className="flex justify-between py-1 border-b border-emerald-100">
                          <span>Tagihan Saat Ini:</span>
                          <span className="font-bold text-emerald-700">Rp 0 (Belum Jatuh Tempo)</span>
                        </div>
                      </div>
                      <p className="text-[10px] text-slate-500 italic leading-snug">
                        * Kendaraan Anda tidak memiliki tagihan pajak saat ini. Loket perpanjangan PKB tahunan dibuka 90 hari sebelum tanggal jatuh tempo.
                      </p>
                    </div>
                  ) : (
                    <>
                      {/* 1. PKB POKOK */}
                      <div className="border border-slate-100 rounded-xl p-2.5 bg-slate-50/50">
                        <div
                          onClick={() => toggleSection('pkb')}
                          className="flex justify-between items-center cursor-pointer select-none"
                        >
                          <div>
                            <div className="text-xs font-bold text-slate-800 flex items-center gap-1">
                              <span>1. PKB Pokok</span>
                              {taxData.pkb_tunggakan > 0 && (
                                <span className="text-[9px] bg-slate-200 text-slate-700 px-1 py-0.2 rounded font-semibold">
                                  Berjalan + Tunggakan
                                </span>
                              )}
                            </div>
                            <span className="text-[10px] text-slate-400">Pajak Kendaraan Bermotor</span>
                          </div>
                          <div className="flex items-center gap-1.5">
                            <span className="font-mono-num font-bold text-xs text-slate-900">
                              {formatRupiah(taxData.pkb_total)}
                            </span>
                            {openSections.pkb ? <IconChevronUp size={14} className="text-slate-400" /> : <IconChevronDown size={14} className="text-slate-400" />}
                          </div>
                        </div>

                        {openSections.pkb && (
                          <div className="mt-2.5 pt-2 border-t border-slate-200/60 text-[11px] space-y-1 text-slate-600">
                            <div className="flex justify-between">
                              <span>PKB Tahun Berjalan:</span>
                              <span className="font-mono-num font-medium">{formatRupiah(taxData.pkb_berjalan)}</span>
                            </div>
                            {taxData.pkb_tunggakan > 0 && (
                              <div className="flex justify-between text-amber-700">
                                <span>PKB Masa Tunggakan:</span>
                                <span className="font-mono-num font-medium">{formatRupiah(taxData.pkb_tunggakan)}</span>
                              </div>
                            )}
                            {(taxData.diskon_digital_pkb > 0 || taxData.diskon_pkb_berjalan > 0 || taxData.diskon_pkb_tunggakan > 0) && (
                              <div className="flex justify-between text-emerald-700 font-semibold">
                                <span>Diskon Digital PKB:</span>
                                <span className="font-mono-num">- {formatRupiah(taxData.diskon_digital_pkb || (taxData.diskon_pkb_berjalan || 0) + (taxData.diskon_pkb_tunggakan || 0))}</span>
                              </div>
                            )}
                          </div>
                        )}
                      </div>

                      {/* 2. OPSEN PKB */}
                      <div className="border border-slate-100 rounded-xl p-2.5 bg-slate-50/50">
                        <div
                          onClick={() => toggleSection('opsen')}
                          className="flex justify-between items-center cursor-pointer select-none"
                        >
                          <div>
                            <div className="text-xs font-bold text-slate-800">2. Opsen PKB (66%)</div>
                            <span className="text-[10px] text-slate-400">Alokasi Kabupaten/Kota</span>
                          </div>
                          <div className="flex items-center gap-1.5">
                            <span className="font-mono-num font-bold text-xs text-slate-900">
                              {formatRupiah(taxData.opsen_pkb)}
                            </span>
                            {openSections.opsen ? <IconChevronUp size={14} className="text-slate-400" /> : <IconChevronDown size={14} className="text-slate-400" />}
                          </div>
                        </div>

                        {openSections.opsen && (
                          <div className="mt-2.5 pt-2 border-t border-slate-200/60 text-[11px] space-y-1 text-slate-600">
                            <div className="flex justify-between">
                              <span>Opsen Tahun Berjalan:</span>
                              <span className="font-mono-num font-medium">{formatRupiah(taxData.opsen_pkb_berjalan)}</span>
                            </div>
                            {taxData.opsen_pkb_tunggakan > 0 && (
                              <div className="flex justify-between text-amber-700">
                                <span>Opsen Masa Tunggakan:</span>
                                <span className="font-mono-num font-medium">{formatRupiah(taxData.opsen_pkb_tunggakan)}</span>
                              </div>
                            )}
                            {(taxData.diskon_digital_opsen > 0 || taxData.diskon_opsen_pkb_berjalan > 0) && (
                              <div className="flex justify-between text-emerald-700 font-semibold">
                                <span>Diskon Digital Opsen:</span>
                                <span className="font-mono-num">- {formatRupiah(taxData.diskon_digital_opsen || taxData.diskon_opsen_pkb_berjalan)}</span>
                              </div>
                            )}
                          </div>
                        )}
                      </div>

                      {/* 3. DENDA PKB & DENDA OPSEN */}
                      {(taxData.denda_pkb > 0 || (taxData.denda_opsen_pkb || taxData.denda_opsen) > 0) && (
                        <div className="border border-rose-100 rounded-xl p-2.5 bg-rose-50/40">
                          <div
                            onClick={() => toggleSection('denda')}
                            className="flex justify-between items-center cursor-pointer select-none"
                          >
                            <div>
                              <div className="text-xs font-bold text-rose-800">3. Denda Keterlambatan</div>
                              <span className="text-[10px] text-rose-500">Denda PKB &amp; Denda Opsen</span>
                            </div>
                            <div className="flex items-center gap-1.5">
                              <span className="font-mono-num font-bold text-xs text-rose-700">
                                {formatRupiah(taxData.denda_pkb + (taxData.denda_opsen_pkb || taxData.denda_opsen || 0))}
                              </span>
                              {openSections.denda ? <IconChevronUp size={14} className="text-rose-400" /> : <IconChevronDown size={14} className="text-rose-400" />}
                            </div>
                          </div>

                          {openSections.denda && (
                            <div className="mt-2.5 pt-2 border-t border-rose-200/60 text-[11px] space-y-1 text-rose-800">
                              <div className="flex justify-between">
                                <span>Denda PKB Berjalan:</span>
                                <span className="font-mono-num font-medium">{formatRupiah(taxData.denda_pkb_berjalan)}</span>
                              </div>
                              {taxData.denda_pkb_tunggakan > 0 && (
                                <div className="flex justify-between">
                                  <span>Denda PKB Tunggakan:</span>
                                  <span className="font-mono-num font-medium">{formatRupiah(taxData.denda_pkb_tunggakan)}</span>
                                </div>
                              )}
                              <div className="flex justify-between">
                                <span>Denda Opsen PKB:</span>
                                <span className="font-mono-num font-medium">{formatRupiah(taxData.denda_opsen_pkb || taxData.denda_opsen || 0)}</span>
                              </div>
                            </div>
                          )}
                        </div>
                      )}

                      {/* 4. SWDKLLJ */}
                      <div className="border border-slate-100 rounded-xl p-2.5 bg-slate-50/50">
                        <div
                          onClick={() => toggleSection('swdkllj')}
                          className="flex justify-between items-center cursor-pointer select-none"
                        >
                          <div>
                            <div className="text-xs font-bold text-slate-800">
                              4. SWDKLLJ
                            </div>
                            <span className="text-[10px] text-slate-400">Sumbangan Wajib Jasa Raharja</span>
                          </div>
                          <div className="flex items-center gap-1.5">
                            <span className="font-mono-num font-bold text-xs text-slate-900">
                              {formatRupiah(taxData.swdkllj + (taxData.denda_swdkllj || 0))}
                            </span>
                            {openSections.swdkllj ? <IconChevronUp size={14} className="text-slate-400" /> : <IconChevronDown size={14} className="text-slate-400" />}
                          </div>
                        </div>

                        {openSections.swdkllj && (
                          <div className="mt-2.5 pt-2 border-t border-slate-200/60 text-[11px] space-y-1 text-slate-600">
                            <div className="flex justify-between">
                              <span>Pokok SWDKLLJ Berjalan:</span>
                              <span className="font-mono-num font-medium">{formatRupiah(taxData.swdkllj_berjalan || taxData.swdkllj)}</span>
                            </div>
                            {taxData.swdkllj_tunggakan > 0 && (
                              <div className="flex justify-between text-amber-700">
                                <span>Pokok SWDKLLJ Tunggakan:</span>
                                <span className="font-mono-num font-medium">{formatRupiah(taxData.swdkllj_tunggakan)}</span>
                              </div>
                            )}
                            {taxData.denda_swdkllj > 0 && (
                              <div className="flex justify-between text-rose-700">
                                <span>Denda SWDKLLJ:</span>
                                <span className="font-mono-num font-medium">{formatRupiah(taxData.denda_swdkllj)}</span>
                              </div>
                            )}
                          </div>
                        )}
                      </div>

                      {/* 5. STNK & TNKB */}
                      {taxData.biaya_stnk_total > 0 && (
                        <div className="border border-slate-100 rounded-xl p-2.5 bg-slate-50/50">
                          <div
                            onClick={() => toggleSection('stnk')}
                            className="flex justify-between items-center cursor-pointer select-none"
                          >
                            <div>
                              <div className="text-xs font-bold text-slate-800">5. Biaya STNK &amp; Plat TNKB</div>
                              <span className="text-[10px] text-slate-400">PNBP 5 Tahunan / Mutasi</span>
                            </div>
                            <div className="flex items-center gap-1.5">
                              <span className="font-mono-num font-bold text-xs text-slate-900">
                                {formatRupiah(taxData.biaya_stnk_total)}
                              </span>
                              {openSections.stnk ? <IconChevronUp size={14} className="text-slate-400" /> : <IconChevronDown size={14} className="text-slate-400" />}
                            </div>
                          </div>

                          {openSections.stnk && (
                            <div className="mt-2.5 pt-2 border-t border-slate-200/60 text-[11px] space-y-1 text-slate-600">
                              <div className="flex justify-between">
                                <span>Penerbitan STNK:</span>
                                <span className="font-mono-num font-medium">{formatRupiah(taxData.biaya_stnk)}</span>
                              </div>
                              <div className="flex justify-between">
                                <span>Penerbitan Plat TNKB:</span>
                                <span className="font-mono-num font-medium">{formatRupiah(taxData.biaya_tnkb)}</span>
                              </div>
                            </div>
                          )}
                        </div>
                      )}

                    </>
                  )}
                </div>

                {/* Total Highlight (Tarif Normal Sebelum Diskon) */}
                <div className="border-t border-slate-200 pt-3 mt-2 flex justify-between items-baseline">
                  <div>
                    <span className="text-xs font-bold text-slate-800 block">TOTAL TAGIHAN</span>
                    {taxData.is_pajak_aktif && (
                      <span className="text-[10px] text-emerald-600 font-semibold block">Masa Pajak Masih Aktif</span>
                    )}
                  </div>
                  <span className="font-mono-num text-xl sm:text-2xl font-black text-slate-900">
                    {formatRupiah(taxData.total_pajak)}
                  </span>
                </div>

                {/* Diskon Pembayaran Digital & Total Bayar Online (Diletakkan di Bawah Total Tagihan) */}
                {!taxData.is_pajak_aktif && (taxData.diskon_digital > 0 || (taxData.total_setelah_diskon && taxData.total_setelah_diskon < taxData.total_pajak)) && (
                  <div className="mt-3 p-3.5 rounded-2xl bg-gradient-to-br from-emerald-50 via-teal-50 to-emerald-50 border border-emerald-200/90 shadow-2xs space-y-2.5">
                    <div className="flex justify-between items-center text-xs">
                      <span className="font-bold text-emerald-800 flex items-center gap-1.5">
                        <span className="inline-flex items-center justify-center w-4 h-4 rounded-full bg-emerald-200 text-emerald-800 text-[10px] font-black">%</span>
                        Diskon Pembayaran Digital PRO NTT
                      </span>
                      <span className="font-mono-num font-bold text-emerald-700">
                        - {formatRupiah(taxData.diskon_digital || (taxData.total_pajak - taxData.total_setelah_diskon))}
                      </span>
                    </div>

                    <div className="pt-2 border-t border-emerald-200/70 flex justify-between items-baseline">
                      <div>
                        <span className="text-xs font-black text-emerald-950 block">TOTAL BAYAR (SETELAH DISKON)</span>
                        <span className="text-[10px] text-emerald-700 font-medium block">Berlaku untuk pembayaran via QRIS / Kanal Digital Bank NTT</span>
                      </div>
                      <span className="font-mono-num text-lg sm:text-xl font-black text-emerald-700">
                        {formatRupiah(taxData.total_setelah_diskon || (taxData.total_pajak - (taxData.diskon_digital || 0)))}
                      </span>
                    </div>
                  </div>
                )}
              </div>
            </div>
          </div>

          {/* Action Buttons */}
          <div className="flex flex-col sm:flex-row gap-3 pt-1">
            <button
              onClick={handleCopyText}
              className="flex-1 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold py-2.5 sm:py-3 px-3 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 shadow-sm active:scale-95 transition-all cursor-pointer"
            >
              {copied ? (
                <>
                  <IconCheck size={18} stroke={2.4} className="text-emerald-600" />
                  <span className="text-emerald-700">Hasil Tersalin!</span>
                </>
              ) : (
                <>
                  <IconCopy size={18} stroke={2} className="text-emerald-600" />
                  <span>Salin Hasil Pengecekan</span>
                </>
              )}
            </button>

            <button
              onClick={handleDownloadImage}
              disabled={downloadingImg}
              className="flex-1 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white font-bold py-2.5 sm:py-3 px-4 rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 shadow-sm active:scale-95 transition-all cursor-pointer"
            >
              {downloadingImg ? (
                <>
                  <IconRefresh className="animate-spin" size={18} stroke={2} />
                  <span>Memproses Gambar...</span>
                </>
              ) : (
                <>
                  <IconDownload size={18} stroke={2} />
                  <span>Download Gambar</span>
                </>
              )}
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
