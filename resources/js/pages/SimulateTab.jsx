import React, { useState, useEffect, useRef } from 'react';
import { 
  IconCalculator, 
  IconSparkles, 
  IconTrendingDown, 
  IconCircleCheck, 
  IconAlertCircle, 
  IconShare, 
  IconShieldCheck,
  IconArrowsExchange,
  IconLicense
} from '@tabler/icons-react';
import { formatRupiah, formatDate } from '../utils/formatters';
import { SimulateSkeleton } from '../components/SkeletonLoader';
import { getApiBase, getAuthToken } from '../utils/config';

const VEHICLE_TYPES = [
  { value: 'SEPEDA MOTOR R2', label: 'SPM / Sepeda Motor Roda 2', bobot: '1.000', kategori: 'roda23', cc: 125 },
  { value: 'SEPEDA MOTOR R3', label: 'SPM / Sepeda Motor Roda 3', bobot: '1.000', kategori: 'roda23', cc: 150 },
  { value: 'MINIBUS', label: 'Minibus s.d 1.600 cc (Avanza, Xenia, Ertiga, dll)', bobot: '1.050', kategori: 'roda4', cc: 1500 },
  { value: 'MINIBUS > 1600 CC', label: 'Minibus > 1.600 cc (Innova, Panther, HiAce, dll)', bobot: '1.050', kategori: 'roda4', cc: 2000 },
  { value: 'SEDAN', label: 'Sedan', bobot: '1.025', kategori: 'roda4', cc: 1500 },
  { value: 'JEEP', label: 'Jeep', bobot: '1.050', kategori: 'roda4', cc: 2000 },
  { value: 'STATION WAGON', label: 'Station Wagon', bobot: '1.075', kategori: 'roda4', cc: 1800 },
  { value: 'PICK UP', label: 'Pick Up', bobot: '1.085', kategori: 'roda4', cc: 1500 },
  { value: 'MICROBUS', label: 'Microbus', bobot: '1.085', kategori: 'roda4', cc: 2500 },
  { value: 'BLIND VAN', label: 'Blind Van', bobot: '1.085', kategori: 'roda4', cc: 1500 },
  { value: 'BUS', label: 'Bus', bobot: '1.100', kategori: 'roda4', cc: 4000 },
  { value: 'LIGHT TRUCK', label: 'Light Truck', bobot: '1.300', kategori: 'roda4', cc: 3500 },
  { value: 'TRUCK', label: 'Truck / Truk', bobot: '1.400', kategori: 'roda4', cc: 5000 },
  { value: 'ALAT BERAT', label: 'Alat Berat / Khusus', bobot: '1.000', kategori: 'roda4', cc: 3000 },
];

export default function SimulateTab({ prefillNopol = '', prefillData = null }) {
  const [mode, setMode] = useState('nopol'); // 'nopol' | 'manual'
  
  // 3-box nopol state (Default Kosong)
  const [nopolPrefix, setNopolPrefix] = useState('');
  const [nopolNumber, setNopolNumber] = useState('');
  const [nopolSuffix, setNopolSuffix] = useState('');

  const box1Ref = useRef(null);
  const box2Ref = useRef(null);
  const box3Ref = useRef(null);

  const [kanalBayar, setKanalBayar] = useState('loket'); // 'loket' | 'online'
  const [tanggalSimulasi, setTanggalSimulasi] = useState(new Date().toISOString().split('T')[0]);
  const [isMutasi, setIsMutasi] = useState(false);

  // Manual mode state
  const [manualNjkb, setManualNjkb] = useState('');
  const [manualJenisKendaraan, setManualJenisKendaraan] = useState('SEPEDA MOTOR R2');
  const [manualBobot, setManualBobot] = useState('1.000');
  const [manualKategori, setManualKategori] = useState('roda23'); // 'roda23' | 'roda4'
  const [manualSdNotice, setManualSdNotice] = useState('');
  const [manualSdStnk, setManualSdStnk] = useState('');
  const [manualGuna, setManualGuna] = useState('PRIBADI');

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [simResult, setSimResult] = useState(null);
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

  const handleJenisKendaraanChange = (value) => {
    setManualJenisKendaraan(value);
    const found = VEHICLE_TYPES.find((t) => t.value === value);
    if (found) {
      setManualBobot(found.bobot);
      setManualKategori(found.kategori);
    }
  };

  useEffect(() => {
    if (prefillNopol) {
      const parsed = splitNopol(prefillNopol);
      setNopolPrefix(parsed.prefix);
      setNopolNumber(parsed.number);
      setNopolSuffix(parsed.suffix);
      setMode('nopol');
      handleSimulate('nopol', `${parsed.prefix}${parsed.number}${parsed.suffix}`);
    }
  }, [prefillNopol]);

  useEffect(() => {
    if (prefillData) {
      setMode('manual');

      // 1. Nilai Jual (NJKB)
      const rawNjkb = prefillData.nilai_jual || prefillData.njkb || 0;
      if (rawNjkb > 0) {
        setManualNjkb(String(rawNjkb));
      }

      // 2. Bobot & Jenis Kendaraan
      const bobotNum = prefillData.bobot ? parseFloat(prefillData.bobot) : 1.0;
      const bobotStr = bobotNum.toFixed(3);
      setManualBobot(bobotStr);

      // Cari jenis kendaraan yang cocok di VEHICLE_TYPES
      let matched = null;
      if (prefillData.jenis_kendaraan) {
        const rawJenis = prefillData.jenis_kendaraan.toUpperCase();
        matched = VEHICLE_TYPES.find((t) => t.value === rawJenis || rawJenis.includes(t.value));
      }

      if (!matched) {
        const searchStr = `${prefillData.merk || ''} ${prefillData.tipe || ''}`.toUpperCase();
        const isMotor = /BEAT|VARIO|MIO|SCOOPY|NMAX|PCX|AEROX|SUPRA|JUPITER|VIXION|CBR|CB150|KLX|YZF|GSX|R15|SATRIA|MOTOR|SPM/i.test(searchStr);

        if (isMotor) {
          matched = VEHICLE_TYPES.find((t) => t.value === 'SEPEDA MOTOR R2');
        } else {
          matched = VEHICLE_TYPES.find((t) => Math.abs(parseFloat(t.bobot) - bobotNum) < 0.005);
          if (!matched) {
            matched = VEHICLE_TYPES.find((t) => t.value === 'MINIBUS') || VEHICLE_TYPES[0];
          }
        }
      }

      if (matched) {
        setManualJenisKendaraan(matched.value);
        setManualKategori(matched.kategori);
        if (!prefillData.bobot) {
          setManualBobot(matched.bobot);
        }
      }

      // 3. Penggunaan
      if (prefillData.k_guna) {
        setManualGuna(prefillData.k_guna);
      }

      // 4. Tanggal Jatuh Tempo jika tersedia
      if (prefillData.sd_notice) {
        setManualSdNotice(prefillData.sd_notice);
      }
      if (prefillData.sd_stnk) {
        setManualSdStnk(prefillData.sd_stnk);
      }
    }
  }, [prefillData]);

  const handleSimulate = async (targetMode = mode, targetNopol = null) => {
    setLoading(true);
    setError(null);

    const fullNopol = targetNopol !== null ? targetNopol : getCombinedNopol();

    const payload = {
      mode: targetMode,
      kanal_bayar: kanalBayar,
      tanggal_simulasi: tanggalSimulasi,
      is_mutasi: isMutasi,
    };

    if (targetMode === 'nopol') {
      const cleanNopol = fullNopol.replace(/[^A-Z0-9]/g, '');
      if (!cleanNopol) {
        setError('Nomor polisi wajib diisi.');
        setLoading(false);
        return;
      }
      payload.nopol = cleanNopol;
    } else {
      const njkbVal = parseFloat(manualNjkb.replace(/[^0-9]/g, ''));
      if (!njkbVal || njkbVal <= 0) {
        setError('Nilai NJKB manual harus diisi dan lebih dari 0.');
        setLoading(false);
        return;
      }
      const selectedType = VEHICLE_TYPES.find((t) => t.value === manualJenisKendaraan) || VEHICLE_TYPES[0];
      payload.njkb = njkbVal;
      payload.bobot = parseFloat(manualBobot) || parseFloat(selectedType.bobot) || 1.0;
      payload.is_roda23 = selectedType.kategori === 'roda23';
      payload.jenis_kendaraan = manualJenisKendaraan;
      payload.isi_cylinder = selectedType.cc;
      payload.k_guna = manualGuna;
      payload.sd_notice = manualSdNotice;
      payload.sd_stnk = manualSdStnk;
    }

    try {
      const token = getAuthToken();
      const response = await fetch(`${getApiBase()}/simulate`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': token ? `Bearer ${token}` : '',
          'X-Api-Token': token || '',
          'X-CSRF-TOKEN': window.__SAMSAT_CONFIG__?.csrfToken || '',
        },
        body: JSON.stringify(payload),
      });

      const res = await response.json();
      if (!response.ok || res.status !== 'success') {
        throw new Error(res.message || 'Simulasi perhitungan gagal.');
      }

      setSimResult(res.data);
    } catch (err) {
      setError(err.message || 'Terjadi kesalahan sistem.');
      setSimResult(null);
    } finally {
      setLoading(false);
    }
  };

  const handleCopyWhatsapp = () => {
    if (!simResult) return;
    const k = simResult.kendaraan;
    const norm = simResult.simulasi.komparasi.normal;
    const ran = simResult.simulasi.komparasi.ranpergub;

    const text = `*SIMULASI ESTIMASI KERINGANAN PAJAK NTT*
Kendaraan: *${k.nopol || 'Simulasi Manual'}* (${k.unit || k.merk})
Status: ${simResult.simulasi.metadata.status_pembayaran} (${simResult.simulasi.metadata.jumlah_tahun} thn periode)
Kanal Bayar: ${kanalBayar.toUpperCase()}

*1. PENETAPAN NORMAL (TANPA KERINGANAN)*
- PKB Berjalan: ${formatRupiah(norm.pkb_berjalan)}
- PKB Tunggakan: ${formatRupiah(norm.pkb_tunggakan)}
- Opsen PKB: ${formatRupiah(norm.opsen_pkb_total)}
- Denda PKB/Opsen: ${formatRupiah(norm.denda_total)}
- SWDKLLJ: ${formatRupiah(norm.swdkllj_total)}
- Biaya STNK/Plat: ${formatRupiah(norm.biaya_stnk_tnkb)}
*Total Normal: ${formatRupiah(norm.total_bayar)}*

*2. SKENARIO RELAKSASI / KERINGANAN*
- PKB Berjalan: ${formatRupiah(ran.pkb_berjalan)} (Diskon ${ran.diskon_pkb_berjalan_pct}%)
- PKB Tunggakan: ${formatRupiah(ran.pkb_tunggakan)} *(DIHAPUS 100%)*
- Opsen PKB: ${formatRupiah(ran.opsen_pkb_total)}
- Denda PKB/Opsen: ${formatRupiah(0)} *(DIHAPUS 100%)*
- SWDKLLJ: ${formatRupiah(ran.swdkllj_total)}
- Biaya STNK/Plat: ${formatRupiah(ran.biaya_stnk_tnkb)}
*Total Keringanan: ${formatRupiah(ran.total_bayar)}*

🎉 *TOTAL ESTIMASI PENGHEMATAN: ${formatRupiah(ran.total_hemat)}*

_Simulasi melalui AutoTax NTT_`;

    navigator.clipboard.writeText(text);
    setCopied(true);
    setTimeout(() => setCopied(false), 2500);
  };

  return (
    <div className="p-4 sm:p-6 space-y-4">
      {/* Simulation Form Card */}
      <div className="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-200/80">
        <div className="flex items-center justify-between mb-3">
          <div>
            <h2 className="font-bold text-slate-800 text-sm sm:text-base">
              Simulasi Komparasi Pajak
            </h2>
            <p className="text-xs text-slate-500">
              Perbandingan Penetapan Normal vs Relaksasi Keringanan Pajak
            </p>
          </div>
          <span className="p-2 bg-emerald-50 text-emerald-700 rounded-xl">
            <IconSparkles size={18} stroke={2} />
          </span>
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

        <form onSubmit={(e) => { e.preventDefault(); handleSimulate(); }} className="space-y-3.5">
          {mode === 'nopol' ? (
            <div>
              <div className="flex items-center justify-between mb-2 px-1">
                <span className="text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                  Nomor Polisi (NTT)
                </span>
                <span className="text-[10px] text-slate-400 font-medium">
                  Dukung paste (DH 5678 AA)
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
                      placeholder="5678"
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
                      placeholder="AA"
                      maxLength={3}
                      className="w-full bg-white border border-slate-200 rounded-xl py-2.5 sm:py-3 px-1 text-center font-mono-num font-black text-base sm:text-lg text-slate-900 uppercase placeholder:text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-600 shadow-xs"
                    />
                  </div>
                </div>
              </div>
            </div>
          ) : (
            <div className="space-y-3">
              {prefillData && (
                <div className="bg-emerald-50 border border-emerald-200/80 rounded-xl p-2.5 text-xs text-emerald-800 flex items-center gap-2">
                  <IconCircleCheck size={16} className="text-emerald-600 shrink-0" />
                  <span>
                    Data NJKB &amp; Bobot ({prefillData.merk} {prefillData.tipe || ''}) telah terisi otomatis. Silakan tentukan penggunaan dan tanggal jatuh tempo.
                  </span>
                </div>
              )}

              {/* Row 1: Nilai Jual (NJKB) & Jenis Kendaraan */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="text-[11px] font-bold text-slate-600 block mb-1">
                    Nilai Jual (NJKB) Rp
                  </label>
                  <input
                    type="number"
                    value={manualNjkb}
                    onChange={(e) => setManualNjkb(e.target.value)}
                    placeholder="Contoh: 18000000"
                    className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-mono-num font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                  />
                </div>

                <div>
                  <label className="text-[11px] font-bold text-slate-600 block mb-1">
                    Jenis Kendaraan
                  </label>
                  <select
                    value={manualJenisKendaraan}
                    onChange={(e) => handleJenisKendaraanChange(e.target.value)}
                    className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                  >
                    {VEHICLE_TYPES.map((type) => (
                      <option key={type.value} value={type.value}>
                        {type.label} (Bobot {type.bobot})
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              {/* Row 2: Bobot Kendaraan (Otomatis) & Penggunaan */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="text-[11px] font-bold text-slate-600 flex items-center justify-between mb-1">
                    <span>Bobot Kendaraan</span>
                    <span className="text-[10px] text-emerald-600 font-semibold">(Otomatis)</span>
                  </label>
                  <input
                    type="number"
                    step="0.001"
                    value={manualBobot}
                    onChange={(e) => setManualBobot(e.target.value)}
                    placeholder="1.000"
                    className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-mono-num font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                  />
                </div>

                <div>
                  <label className="text-[11px] font-bold text-slate-600 block mb-1">
                    Penggunaan
                  </label>
                  <select
                    value={manualGuna}
                    onChange={(e) => setManualGuna(e.target.value)}
                    className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                  >
                    <option value="PRIBADI">Pribadi</option>
                    <option value="UMUM">Umum</option>
                    <option value="PEMERINTAH">Pemerintah</option>
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="text-[11px] font-bold text-slate-600 block mb-1">
                    SD Notice (Pajak Lalu)
                  </label>
                  <input
                    type="date"
                    value={manualSdNotice}
                    onChange={(e) => setManualSdNotice(e.target.value)}
                    className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700"
                  />
                </div>
                <div>
                  <label className="text-[11px] font-bold text-slate-600 block mb-1">
                    SD STNK (5 Tahunan)
                  </label>
                  <input
                    type="date"
                    value={manualSdStnk}
                    onChange={(e) => setManualSdStnk(e.target.value)}
                    className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700"
                  />
                </div>
              </div>
            </div>
          )}

          {/* Payment Channel and Simulation Date */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 border-t border-slate-100">
            <div>
              <label className="text-[11px] font-bold text-slate-600 block mb-1">
                Kanal Pembayaran
              </label>
              <select
                value={kanalBayar}
                onChange={(e) => setKanalBayar(e.target.value)}
                className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700"
              >
                <option value="loket">Loket Langsung</option>
                <option value="online">Online / Digital (+5% Diskon)</option>
              </select>
            </div>
            <div>
              <label className="text-[11px] font-bold text-slate-600 block mb-1">
                Tanggal Simulasi
              </label>
              <input
                type="date"
                value={tanggalSimulasi}
                onChange={(e) => setTanggalSimulasi(e.target.value)}
                className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700"
              />
            </div>
          </div>

          <div className="flex items-center gap-2 pt-1">
            <input
              type="checkbox"
              id="simMutasi"
              checked={isMutasi}
              onChange={(e) => setIsMutasi(e.target.checked)}
              className="rounded text-emerald-600 focus:ring-emerald-500"
            />
            <label htmlFor="simMutasi" className="text-xs text-slate-600 cursor-pointer">
              Simulasi Mutasi Masuk Luar Daerah NTT
            </label>
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white font-bold py-2.5 px-4 rounded-xl text-xs sm:text-sm shadow-md shadow-emerald-600/20 active:scale-98 transition-all flex items-center justify-center gap-2 cursor-pointer"
          >
            <IconCalculator size={17} stroke={2.2} />
            <span>{loading ? 'Menghitung Komparasi...' : 'Mulai Simulasi Komparasi'}</span>
          </button>
        </form>
      </div>

      {/* Error Banner */}
      {error && (
        <div className="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-2xl flex items-start gap-2.5 text-xs">
          <IconAlertCircle size={18} className="shrink-0 mt-0.5 text-rose-500" />
          <div>
            <div className="font-bold">Gagal Menghitung</div>
            <div>{error}</div>
          </div>
        </div>
      )}

      {/* Loading Skeleton */}
      {loading && <SimulateSkeleton />}

      {/* Simulation Results Section */}
      {simResult && !loading && (
        <div className="space-y-4">
          {/* Identity & Metadata Banner */}
          <div className="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80">
            <div className="flex items-center justify-between border-b border-slate-100 pb-2 mb-2">
              <div className="font-bold text-xs text-slate-800">
                Data Identitas Kendaraan
              </div>
              <span className="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                {simResult.simulasi.metadata.status_pembayaran}
              </span>
            </div>
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
              <div>
                <span className="text-[10px] text-slate-400 block">Nomor Polisi</span>
                <span className="font-mono-num font-bold text-slate-800">
                  {simResult.kendaraan.nopol}
                </span>
              </div>
              <div>
                <span className="text-[10px] text-slate-400 block">Unit Kendaraan</span>
                <span className="font-semibold text-slate-700 truncate block">
                  {simResult.kendaraan.unit || simResult.kendaraan.merk}
                </span>
              </div>
              <div>
                <span className="text-[10px] text-slate-400 block">DP PKB Normal</span>
                <span className="font-mono-num font-semibold text-slate-700">
                  {formatRupiah(simResult.simulasi.metadata.dp_normal)}
                </span>
              </div>
              <div>
                <span className="text-[10px] text-slate-400 block">Periode Pajak</span>
                <span className="font-semibold text-slate-700">
                  {simResult.simulasi.metadata.jumlah_tahun} Tahun
                </span>
              </div>
            </div>
          </div>

          {/* Comparison Cards Grid: Normal vs Ranpergub (Pergub 64) */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {/* Card 1: Normal (Tanpa Amnesti) */}
            <div className="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col justify-between">
              <div>
                <div className="flex items-center justify-between mb-3">
                  <h3 className="font-bold text-slate-700 text-xs sm:text-sm">
                    1. Penetapan Normal
                  </h3>
                  <span className="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md font-semibold">
                    Tanpa Relaksasi
                  </span>
                </div>

                <div className="space-y-2 text-xs divide-y divide-slate-100">
                  <div className="flex justify-between pt-1">
                    <span className="text-slate-500">PKB Berjalan</span>
                    <span className="font-mono-num font-semibold text-slate-700">
                      {formatRupiah(simResult.simulasi.komparasi.normal.pkb_berjalan)}
                    </span>
                  </div>
                  <div className="flex justify-between pt-1.5">
                    <span className="text-slate-500">PKB Masa Tunggakan</span>
                    <span className="font-mono-num font-semibold text-slate-700">
                      {formatRupiah(simResult.simulasi.komparasi.normal.pkb_tunggakan)}
                    </span>
                  </div>
                  <div className="flex justify-between pt-1.5">
                    <span className="text-slate-500">Opsen PKB (66%)</span>
                    <span className="font-mono-num font-semibold text-slate-700">
                      {formatRupiah(simResult.simulasi.komparasi.normal.opsen_pkb_total)}
                    </span>
                  </div>
                  <div className="flex justify-between pt-1.5">
                    <span className="text-slate-500">Denda PKB & Opsen</span>
                    <span className="font-mono-num font-semibold text-rose-600">
                      {formatRupiah(simResult.simulasi.komparasi.normal.denda_total)}
                    </span>
                  </div>
                  <div className="flex justify-between pt-1.5">
                    <span className="text-slate-500">SWDKLLJ</span>
                    <span className="font-mono-num font-semibold text-slate-700">
                      {formatRupiah(simResult.simulasi.komparasi.normal.swdkllj_total)}
                    </span>
                  </div>
                  <div className="flex justify-between pt-1.5">
                    <span className="text-slate-500">Biaya STNK & Plat</span>
                    <span className="font-mono-num font-semibold text-slate-700">
                      {formatRupiah(simResult.simulasi.komparasi.normal.biaya_stnk_tnkb)}
                    </span>
                  </div>
                </div>
              </div>

              <div className="mt-4 pt-3 border-t border-slate-200/80 flex items-baseline justify-between">
                <span className="font-bold text-xs text-slate-700">Total Normal:</span>
                <span className="font-mono-num font-extrabold text-base text-slate-900">
                  {formatRupiah(simResult.simulasi.komparasi.normal.total_bayar)}
                </span>
              </div>
            </div>

            {/* Card 2: Pergub 64 / Ranpergub */}
            <div 
              className="rounded-2xl p-4 shadow-sm border-2 border-emerald-500 flex flex-col justify-between relative overflow-hidden"
              style={{
                backgroundColor: '#ecfdf5',
                background: 'linear-gradient(180deg, #d1fae5 0%, #ecfdf5 50%, #f0fdf4 100%)',
              }}
            >
              <div className="absolute top-0 right-0 bg-emerald-600 text-white text-[9px] font-black uppercase tracking-wider px-3 py-0.5 rounded-bl-xl shadow-xs">
                Rekomendasi Keringanan
              </div>

              <div>
                <div className="flex items-center justify-between mb-3 mt-1">
                  <h3 className="font-bold text-emerald-950 text-xs sm:text-sm">
                    2. Skenario Relaksasi Pajak
                  </h3>
                  <span className="text-[10px] bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md font-bold">
                    Pergub No. 64
                  </span>
                </div>

                <div className="space-y-2 text-xs divide-y divide-emerald-100/60">
                  <div className="flex justify-between pt-1">
                    <span className="text-slate-600">PKB Berjalan</span>
                    <span className="font-mono-num font-bold text-slate-800">
                      {formatRupiah(simResult.simulasi.komparasi.ranpergub.pkb_berjalan)}
                    </span>
                  </div>
                  <div className="flex justify-between pt-1.5 items-center">
                    <span className="text-slate-600">PKB Masa Tunggakan</span>
                    <span className="font-mono-num font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.2 rounded text-[11px]">
                      DIHAPUS 100%
                    </span>
                  </div>
                  <div className="flex justify-between pt-1.5">
                    <span className="text-slate-600">Opsen PKB (66%)</span>
                    <span className="font-mono-num font-bold text-slate-800">
                      {formatRupiah(simResult.simulasi.komparasi.ranpergub.opsen_pkb_total)}
                    </span>
                  </div>
                  <div className="flex justify-between pt-1.5 items-center">
                    <span className="text-slate-600">Denda PKB & Opsen</span>
                    <span className="font-mono-num font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.2 rounded text-[11px]">
                      BEBAS DENDA 100%
                    </span>
                  </div>
                  <div className="flex justify-between pt-1.5">
                    <span className="text-slate-600">SWDKLLJ</span>
                    <span className="font-mono-num font-bold text-slate-800">
                      {formatRupiah(simResult.simulasi.komparasi.ranpergub.swdkllj_total)}
                    </span>
                  </div>
                  <div className="flex justify-between pt-1.5">
                    <span className="text-slate-600">Biaya STNK & Plat</span>
                    <span className="font-mono-num font-bold text-slate-800">
                      {formatRupiah(simResult.simulasi.komparasi.ranpergub.biaya_stnk_tnkb)}
                    </span>
                  </div>
                </div>
              </div>

              <div className="mt-4 pt-3 border-t border-emerald-200 flex items-baseline justify-between">
                <span className="font-bold text-xs text-emerald-950">Total Keringanan:</span>
                <span className="font-mono-num font-extrabold text-base text-emerald-700">
                  {formatRupiah(simResult.simulasi.komparasi.ranpergub.total_bayar)}
                </span>
              </div>
            </div>
          </div>

          {/* Savings Highlight Banner */}
          <div 
            className="rounded-2xl card-gradient-emerald-action text-white p-4 shadow-md flex items-center justify-between border border-emerald-500/30"
            style={{
              backgroundColor: '#059669',
              background: 'linear-gradient(90deg, #059669 0%, #0d9488 50%, #0f766e 100%)',
            }}
          >
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center shrink-0">
                <IconTrendingDown size={22} stroke={2.5} className="text-amber-300" />
              </div>
              <div>
                <div className="text-[11px] font-semibold text-emerald-100">
                  Total Estimasi Penghematan Anda:
                </div>
                <div className="font-mono-num font-black text-xl sm:text-2xl text-white">
                  {formatRupiah(simResult.simulasi.komparasi.ranpergub.total_hemat)}
                </div>
              </div>
            </div>

            <button
              onClick={handleCopyWhatsapp}
              className="bg-white hover:bg-emerald-50 text-emerald-900 font-bold px-3 py-2 rounded-xl text-xs flex items-center gap-1.5 active:scale-95 transition-all cursor-pointer shadow-sm shrink-0"
            >
              {copied ? <IconCircleCheck size={16} className="text-emerald-600" /> : <IconShare size={16} />}
              <span>{copied ? 'Tersalin!' : 'Bagikan'}</span>
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
