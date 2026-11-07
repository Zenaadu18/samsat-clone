import React, { useState, useEffect } from 'react';
import { IconWifiOff, IconRefresh, IconCar, IconUser, IconLogout } from '@tabler/icons-react';
import { getAssetUrl } from '../utils/config';

export default function MobileFrame({ children, activeTab, setActiveTab, currentUser, onLogout }) {
  const [isOnline, setIsOnline] = useState(navigator.onLine);

  useEffect(() => {
    const handleOnline = () => setIsOnline(true);
    const handleOffline = () => setIsOnline(false);

    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);

    return () => {
      window.removeEventListener('online', handleOnline);
      window.removeEventListener('offline', handleOffline);
    };
  }, []);

  return (
    <div className="fixed inset-0 h-[100dvh] max-h-[100dvh] w-full overflow-hidden bg-slate-100/90 text-slate-800 flex flex-col items-center select-none font-sans antialiased">
      {/* Responsive App Shell: 100% on phone, max-w-4xl on tablet/PC */}
      <div className="w-full max-w-4xl h-full max-h-full bg-slate-50 flex flex-col relative shadow-sm border-x border-slate-200/70 overflow-hidden">

        {/* Offline Alert Strip */}
        {!isOnline && (
          <div className="shrink-0 bg-amber-500 text-white text-xs font-semibold py-1.5 px-4 flex items-center justify-center gap-2 z-50 animate-pulse shadow-sm">
            <IconWifiOff size={15} stroke={2} />
            <span>Koneksi offline. Menampilkan data lokal tersimpan.</span>
          </div>
        )}

        {/* Top App Bar — POSISI TETAP DI ATAS (FIXED / NON-SCROLLING) */}
        <header className="shrink-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 px-4 sm:px-6 py-3 flex items-center justify-between shadow-xs">
          <div className="flex items-center gap-2.5">
            <div className="w-9 h-9 rounded-xl overflow-hidden bg-emerald-600 flex items-center justify-center text-white shadow-sm shadow-emerald-600/20 shrink-0 relative">
              <IconCar size={18} stroke={2.2} className="shrink-0 absolute inset-0 m-auto text-white/80" />
              <img
                src={getAssetUrl('icons/logoapps.png')}
                alt="Logo Pajak.In"
                className="w-full h-full object-cover relative z-10"
                onError={(e) => {
                  e.currentTarget.style.display = 'none';
                }}
              />
            </div>
            <div>
              <div className="flex items-center gap-1.5">
                <h1 className="font-extrabold text-base sm:text-lg tracking-tight leading-none flex items-center">
                  <span className="brand-text-gradient">Pajak</span>
                  <span className="brand-dot-gold">.</span>
                  <span className="brand-text-gradient">In</span>
                </h1>
              </div>
            </div>
          </div>

          <div className="flex items-center gap-2">
            {currentUser && (
              <div className="flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700">
                <IconUser size={14} className="text-emerald-600 shrink-0" />
                <span className="font-bold text-slate-900 max-w-[110px] sm:max-w-none truncate">{currentUser.name || currentUser.username}</span>
                {currentUser.role === 'admin' ? (
                  <span className="text-[9px] bg-purple-100 text-purple-800 font-mono font-bold px-1.5 py-0.5 rounded border border-purple-200 shrink-0">ADMIN</span>
                ) : (
                  <span className="hidden sm:inline-block text-[9px] bg-slate-100 text-slate-600 font-mono font-bold px-1.5 py-0.5 rounded border border-slate-200 shrink-0">USER</span>
                )}
              </div>
            )}

            {currentUser && onLogout && (
              <button
                onClick={onLogout}
                title="Keluar dari akun"
                className="p-1.5 text-rose-500 hover:text-rose-700 rounded-lg hover:bg-rose-50 transition-all cursor-pointer"
              >
                <IconLogout size={17} stroke={2} />
              </button>
            )}

            <button
              onClick={() => window.location.reload()}
              title="Segarkan data"
              className="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 active:rotate-180 transition-all duration-300 cursor-pointer"
            >
              <IconRefresh size={17} stroke={2} />
            </button>
          </div>
        </header>

        {/* Main Content Area — HANYA KONTEN TENGAH INI YANG DAPAT DI-SCROLL */}
        <main
          className="flex-1 w-full overflow-y-auto overflow-x-hidden pb-28 overscroll-y-contain"
          style={{ WebkitOverflowScrolling: 'touch' }}
        >
          {children}

          <footer className="mt-8 mb-4 text-center">
            <p className="text-[11px] font-semibold text-slate-400">
              © {new Date().getFullYear()} <span className="font-bold text-slate-600">@sena</span> • <span className="brand-text-gradient font-bold">Pajak</span><span className="brand-dot-gold font-bold">.</span><span className="brand-text-gradient font-bold">In</span>
            </p>
          </footer>
        </main>
      </div>
    </div>
  );
}
