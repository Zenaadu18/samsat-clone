import React, { useState } from 'react';
import {
  IconLock,
  IconUser,
  IconEye,
  IconEyeOff,
  IconBrandWhatsapp,
  IconLogin,
  IconAlertCircle,
  IconCar,
  IconLogout,
  IconArrowsExchange
} from '@tabler/icons-react';
import { getApiBase, getAssetUrl } from '../utils/config';
import { getDeviceId, getDeviceName } from '../utils/device';

export default function UserLoginModal({ onLoginSuccess }) {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [sessionActiveElsewhere, setSessionActiveElsewhere] = useState(false);
  const [activeDeviceLabel, setActiveDeviceLabel] = useState('');

  const handleSubmit = async (e, forceLogout = false) => {
    if (e && typeof e.preventDefault === 'function') {
      e.preventDefault();
    }
    if (!username.trim() || !password) {
      setError('Username dan Password wajib diisi.');
      setSessionActiveElsewhere(false);
      return;
    }

    setLoading(true);
    setError(null);
    if (!forceLogout) {
      setSessionActiveElsewhere(false);
    }

    try {
      const apiBase = getApiBase();
      const res = await fetch(`${apiBase}/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          username: username.trim(),
          password,
          device_id: getDeviceId(),
          device_name: getDeviceName(),
          force: Boolean(forceLogout),
        }),
      });

      // Baca respon sebagai teks dahulu untuk mencegah SyntaxError Safari pada respon HTML/404
      const responseText = await res.text();
      let json = null;
      try {
        json = JSON.parse(responseText);
      } catch (parseErr) {
        if (res.status === 404) {
          throw new Error(`Endpoint login tidak ditemukan (HTTP 404 pada ${apiBase}/auth/login).`);
        } else if (res.status === 419) {
          throw new Error('Sesi halaman telah kedaluwarsa (419). Silakan refresh browser Anda.');
        } else if (res.status >= 500) {
          throw new Error(`Terjadi kendala pada server (HTTP ${res.status}). Silakan coba beberapa saat lagi.`);
        } else {
          throw new Error(`Gagal memproses respon server: ${responseText.slice(0, 80)}`);
        }
      }

      if (!res.ok || json.status !== 'success') {
        if (json?.error_code === 'SESSION_ACTIVE_ELSEWHERE') {
          setSessionActiveElsewhere(true);
          setActiveDeviceLabel(json?.active_device || 'perangkat lain');
        } else {
          setSessionActiveElsewhere(false);
        }
        throw new Error(json?.message || 'Username atau Password salah.');
      }

      // Simpan session token & info user dengan perlindungan fallback
      try {
        localStorage.setItem('infopkb_token', json.data.token);
        localStorage.setItem('infopkb_user', JSON.stringify(json.data.user));
      } catch (storageErr) {
        sessionStorage.setItem('infopkb_token', json.data.token);
        sessionStorage.setItem('infopkb_user', JSON.stringify(json.data.user));
      }

      if (onLoginSuccess) {
        onLoginSuccess(json.data.user, json.data.token);
      }
    } catch (err) {
      setError(err.message || 'Terjadi kesalahan saat masuk ke sistem.');
    } finally {
      setLoading(false);
    }
  };

  const waLink = `https://wa.me/6285333728954?text=${encodeURIComponent(
    'Halo Developer Pajak.In NTT, saya ingin meminta akun username & password untuk akses aplikasi.'
  )}`;

  return (
    <div className="fixed inset-0 z-50 bg-slate-100 flex items-center justify-center p-4 selection:bg-emerald-500 selection:text-white">
      {/* Background Glows */}
      <div className="absolute -top-32 -left-32 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
      <div className="absolute -bottom-32 -right-32 w-80 h-80 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

      <div className="bg-white border border-slate-200/90 w-full max-w-sm rounded-3xl p-6 sm:p-7 shadow-xl shadow-slate-200/60 space-y-5 relative z-10">
        {/* Logo / Icon — Kompatibel 100% di iPhone / Subfolder / Safari */}
        <div className="text-center">
          <div
            className="inline-flex items-center justify-center w-20 h-20 rounded-2xl shadow-lg mb-3 shrink-0 overflow-hidden relative"
            style={{ backgroundColor: '#059669', color: '#ffffff' }}
          >
            {/* Fallback ikon mobil di latar belakang */}
            <div className="absolute inset-0 flex items-center justify-center">
              <IconCar size={34} stroke={2.2} className="shrink-0 text-white" />
            </div>
            {/* Gambar logo resmi Pajak.In */}
            <img
              src={getAssetUrl('icons/logoapps.png')}
              alt="Logo Pajak.In NTT"
              className="w-full h-full object-cover relative z-10"
              onError={(e) => {
                e.currentTarget.style.display = 'none';
              }}
            />
          </div>
          <h2 className="text-2xl font-black tracking-tight flex items-center justify-center">
            <span className="brand-text-gradient">Pajak</span>
            <span className="brand-dot-gold">.</span>
            <span className="brand-text-gradient">In</span>
          </h2>
          <p className="text-xs text-slate-500 mt-1">Informasi Pajak Kendaraan &amp; BBNKB II</p>
        </div>

        {/* Form Login */}
        <form onSubmit={handleSubmit} className="space-y-3.5">
          {error && (
            <div className={`p-3.5 rounded-2xl text-xs space-y-2.5 ${sessionActiveElsewhere ? 'bg-amber-50 border border-amber-200 text-amber-900 shadow-xs' : 'bg-rose-50 border border-rose-200 text-rose-700'}`}>
              <div className="flex items-start gap-2">
                <IconAlertCircle size={16} className={`shrink-0 mt-0.5 ${sessionActiveElsewhere ? 'text-amber-600' : 'text-rose-600'}`} />
                <span className="leading-relaxed font-medium">{error}</span>
              </div>

              {sessionActiveElsewhere && (
                <div className="pt-1">
                  <button
                    type="button"
                    onClick={() => handleSubmit(null, true)}
                    disabled={loading}
                    className="w-full py-2.5 px-3 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-xs transition-all cursor-pointer disabled:opacity-50"
                  >
                    <IconLogout size={16} className="shrink-0" />
                    <span>Keluarkan Sesi di Perangkat Lain &amp; Masuk di Sini</span>
                  </button>
                </div>
              )}
            </div>
          )}

          <div>
            <label className="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
              Username
            </label>
            <div className="relative">
              <input
                type="text"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                placeholder="Masukkan username"
                required
                className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 pl-9 font-semibold text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 focus:bg-white"
              />
              <IconUser size={16} className="text-slate-400 absolute left-3 top-3 shrink-0" />
            </div>
          </div>

          <div>
            <label className="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
              Password
            </label>
            <div className="relative">
              <input
                type={showPassword ? 'text' : 'password'}
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="Masukkan password"
                required
                className="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 pl-9 pr-10 font-semibold text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 focus:bg-white"
              />
              <IconLock size={16} className="text-slate-400 absolute left-3 top-3 shrink-0" />
              <button
                type="button"
                onClick={() => setShowPassword(!showPassword)}
                className="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 p-0.5 cursor-pointer shrink-0"
                title={showPassword ? 'Sembunyikan password' : 'Lihat password'}
              >
                {showPassword ? <IconEyeOff size={16} className="shrink-0" /> : <IconEye size={16} className="shrink-0" />}
              </button>
            </div>
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 active:scale-98 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
          >
            {loading ? (
              <span>Memverifikasi...</span>
            ) : (
              <>
                <IconLogin size={18} stroke={2.2} className="shrink-0" />
                <span>Masuk Sekarang</span>
              </>
            )}
          </button>
        </form>

        {/* Pesan Resmi Keamanan & Kontak Developer */}
        <div className="pt-3 border-t border-slate-100 text-center space-y-2">
          <p className="text-[11px] text-slate-500 leading-relaxed">
            Demi alasan keamanan silakan hubungi Developer untuk mendapat username &amp; password:
          </p>
          <a
            href={waLink}
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-xl text-xs font-bold transition-all shadow-xs"
            style={{ backgroundColor: '#ecfdf5', color: '#065f46' }}
          >
            <IconBrandWhatsapp size={16} className="text-emerald-600 shrink-0" />
            <span>Hubungi via WA 085333728954</span>
          </a>
        </div>
      </div>
    </div>
  );
}
