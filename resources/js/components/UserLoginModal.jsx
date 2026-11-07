import React, { useState } from 'react';
import {
  IconLock,
  IconAt,
  IconEye,
  IconEyeOff,
  IconBrandWhatsapp,
  IconArrowRight,
  IconAlertCircle,
  IconCar,
  IconLogout,
  IconCheck,
  IconChevronRight
} from '@tabler/icons-react';
import { getApiBase, getAssetUrl } from '../utils/config';
import { getDeviceId, getDeviceName } from '../utils/device';

export default function UserLoginModal({ onLoginSuccess }) {
  const [username, setUsername] = useState(() => {
    try {
      return localStorage.getItem('infopkb_remembered_username') || '';
    } catch {
      return '';
    }
  });
  const [password, setPassword] = useState('');
  const [rememberMe, setRememberMe] = useState(() => {
    try {
      return localStorage.getItem('infopkb_remember_me') !== 'false';
    } catch {
      return true;
    }
  });
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

      // Simpan preferensi Ingat Saya
      try {
        if (rememberMe) {
          localStorage.setItem('infopkb_remembered_username', username.trim());
          localStorage.setItem('infopkb_remember_me', 'true');
        } else {
          localStorage.removeItem('infopkb_remembered_username');
          localStorage.setItem('infopkb_remember_me', 'false');
        }
      } catch (e) {
        // Abaikan jika localStorage terbatas
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
    <div className="fixed inset-0 z-50 bg-[#faf8ff] flex items-center justify-center p-4 sm:p-6 overflow-y-auto selection:bg-[#006948] selection:text-white">
      {/* Background Ambient Glows */}
      <div className="absolute -top-32 -left-32 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
      <div className="absolute -bottom-32 -right-32 w-80 h-80 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

      <div className="flex flex-col w-full max-w-[440px] mx-auto py-6 relative z-10">
        {/* Top Ambient Glow & Logo Deco */}
        <div className="relative w-full flex flex-col items-center pt-2 pb-4 text-center">
          <div className="w-20 h-20 rounded-2xl bg-white shadow-md flex items-center justify-center p-2 mb-4 relative border border-slate-100">
            {/* Fallback Icon di latar belakang */}
            <div className="absolute inset-0 flex items-center justify-center text-emerald-600 bg-emerald-50/50 rounded-2xl">
              <IconCar size={32} stroke={2.2} />
            </div>
            {/* Logo Resmi Pengguna */}
            <img
              src={getAssetUrl('icons/logoapps.png')}
              alt="Logo Pajak.In NTT"
              className="w-full h-full object-contain relative z-10"
              onError={(e) => {
                e.currentTarget.style.display = 'none';
              }}
            />
            {/* Amber Status Ping Badge */}
            <span className="absolute -top-1.5 -right-1.5 flex h-4 w-4 z-20">
              <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-500 opacity-75"></span>
              <span className="relative inline-flex rounded-full h-4 w-4 bg-amber-500 border-2 border-white shadow-xs"></span>
            </span>
          </div>

          <h1 className="text-2xl sm:text-[28px] font-extrabold tracking-tight text-slate-900">
            Selamat Datang Kembali
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 mt-1 px-2">
            Masuk ke akun Anda untuk melanjutkan aktivitas
          </p>
        </div>

        {/* Main Login Card Surface */}
        <div className="w-full bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 sm:p-6 flex flex-col gap-4">
          {/* Error & Sesi Aktif di Perangkat Lain */}
          {error && (
            <div className={`p-3.5 rounded-xl text-xs space-y-2.5 ${sessionActiveElsewhere ? 'bg-amber-50 border border-amber-200 text-amber-900 shadow-xs' : 'bg-rose-50 border border-rose-200 text-rose-700'}`}>
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

          <form onSubmit={handleSubmit} className="flex flex-col gap-4">
            {/* Field: User Name */}
            <div className="flex flex-col gap-1.5">
              <label
                className="text-xs sm:text-sm font-semibold text-slate-800 flex items-center justify-between"
                htmlFor="usernameInput"
              >
                <span>User Name</span>
              </label>
              <div className="relative flex items-center w-full h-[52px] rounded-xl bg-white border border-slate-200/90 shadow-xs focus-within:border-[#006948] focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all">
                <div className="absolute left-3.5 flex items-center justify-center text-slate-400 pointer-events-none">
                  <IconAt size={20} stroke={1.8} />
                </div>
                <input
                  id="usernameInput"
                  type="text"
                  value={username}
                  onChange={(e) => setUsername(e.target.value)}
                  placeholder="nama@domain.com atau username"
                  className="w-full h-full pl-11 pr-4 rounded-xl bg-transparent text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none transition-colors font-medium"
                  required
                />
              </div>
            </div>

            {/* Field: Password */}
            <div className="flex flex-col gap-1.5">
              <div className="flex items-center justify-between">
                <label
                  className="text-xs sm:text-sm font-semibold text-slate-800"
                  htmlFor="passwordInput"
                >
                  Kata Sandi
                </label>
              </div>
              <div className="relative flex items-center w-full h-[52px] rounded-xl bg-white border border-slate-200/90 shadow-xs focus-within:border-[#006948] focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all">
                <div className="absolute left-3.5 flex items-center justify-center text-slate-400 pointer-events-none">
                  <IconLock size={20} stroke={1.8} />
                </div>
                <input
                  id="passwordInput"
                  type={showPassword ? 'text' : 'password'}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="••••••••••••"
                  className="w-full h-full pl-11 pr-11 rounded-xl bg-transparent text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none transition-colors font-medium"
                  required
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  aria-label="Tampilkan atau sembunyikan kata sandi"
                  className="absolute right-3.5 flex items-center justify-center text-slate-400 hover:text-slate-700 transition-colors cursor-pointer"
                >
                  {showPassword ? <IconEyeOff size={20} stroke={1.8} /> : <IconEye size={20} stroke={1.8} />}
                </button>
              </div>
            </div>

            {/* Remember Me & Trust Chip */}
            <div className="flex items-center justify-between pt-0.5">
              <label
                className="flex items-center gap-2 cursor-pointer select-none"
                onClick={(e) => {
                  e.preventDefault();
                  setRememberMe(!rememberMe);
                }}
              >
                <div
                  className={`w-5 h-5 rounded-md flex items-center justify-center transition-all shadow-xs ${
                    rememberMe ? 'bg-[#006948] text-white' : 'bg-slate-200 text-transparent'
                  }`}
                >
                  <IconCheck size={14} stroke={3} className={rememberMe ? 'opacity-100' : 'opacity-0'} />
                </div>
                <span className="text-xs sm:text-sm text-slate-700 font-medium">
                  Ingat Saya
                </span>
              </label>
            </div>

            {/* Primary Action: Masuk Sekarang */}
            <div className="pt-1 flex flex-col gap-2">
              <button
                type="submit"
                disabled={loading}
                className="relative w-full h-[52px] rounded-2xl bg-gradient-to-tr from-[#006948] to-[#00855d] hover:brightness-105 active:scale-[0.99] text-white text-sm font-semibold tracking-wide flex items-center justify-center gap-2 shadow-lg shadow-emerald-700/20 transition-all cursor-pointer overflow-hidden group disabled:opacity-50"
              >
                {loading ? (
                  <span>Memverifikasi...</span>
                ) : (
                  <>
                    <span>Masuk Sekarang</span>
                    <IconArrowRight
                      size={20}
                      stroke={2.2}
                      className="transition-transform group-hover:translate-x-1"
                    />
                  </>
                )}
              </button>
            </div>
          </form>

          {/* Divider */}
          <div className="relative flex items-center justify-center my-1 pt-1">
            <div className="w-full h-[1px] bg-slate-200"></div>
            <span className="absolute px-3 bg-white text-[11px] font-semibold text-slate-400 tracking-wider text-center">
              hubungi developer untuk mendapat akun
            </span>
          </div>
        </div>

        {/* Bottom Registration Hint */}
        <div className="w-full flex items-center justify-center gap-1.5 mt-6 text-center">
          <span className="text-xs sm:text-sm text-slate-500">Belum punya akun?</span>
          <a
            href={waLink}
            target="_blank"
            rel="noopener noreferrer"
            className="relative group inline-flex items-center gap-1 text-xs sm:text-sm text-[#006948] hover:text-[#00855d] transition-colors cursor-pointer"
          >
            <IconBrandWhatsapp size={16} className="text-emerald-600 shrink-0" />
            <span className="font-bold">Whatsapp me</span>
            <IconChevronRight
              size={15}
              stroke={2.5}
              className="text-[#825100] transition-transform group-hover:translate-x-0.5"
            />
            <span className="absolute bottom-0 left-0 w-full h-[2px] bg-[#825100] rounded-full transform scale-x-0 group-hover:scale-x-100 transition-transform"></span>
          </a>
        </div>
      </div>
    </div>
  );
}
