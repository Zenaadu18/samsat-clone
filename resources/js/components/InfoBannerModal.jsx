import React, { useState, useRef } from 'react';
import {
  IconInfoCircle,
  IconAlertTriangle,
  IconSparkles,
  IconX,
  IconChevronRight,
  IconChevronLeft,
  IconExternalLink
} from '@tabler/icons-react';

export default function InfoBannerModal({ banner, onClose }) {
  // Ambil daftar banner (array atau fallback single object)
  const banners = React.useMemo(() => {
    if (!banner) return [];
    if (Array.isArray(banner.banners) && banner.banners.length > 0) {
      return banner.banners;
    }
    if (banner.content || banner.image_path || banner.image_url || banner.title) {
      return [banner];
    }
    return [];
  }, [banner]);

  const [currentIndex, setCurrentIndex] = useState(0);

  // Touch gesture support untuk swipe kiri/kanan di smartphone
  const touchStartX = useRef(null);
  const touchEndX = useRef(null);

  if (banners.length === 0) return null;

  const currentBanner = banners[currentIndex] || banners[0];
  const type = currentBanner.type || 'info';

  const themeConfig = {
    warning: {
      badge: 'bg-amber-100 text-amber-800 border-amber-200',
      icon: <IconAlertTriangle size={18} className="text-amber-600 shrink-0" />,
      accent: 'text-amber-600'
    },
    promo: {
      badge: 'bg-emerald-100 text-emerald-800 border-emerald-200',
      icon: <IconSparkles size={18} className="text-emerald-600 shrink-0" />,
      accent: 'text-emerald-600'
    },
    info: {
      badge: 'bg-blue-100 text-blue-800 border-blue-200',
      icon: <IconInfoCircle size={18} className="text-blue-600 shrink-0" />,
      accent: 'text-blue-600'
    }
  }[type] || {
    badge: 'bg-blue-100 text-blue-800 border-blue-200',
    icon: <IconInfoCircle size={18} className="text-blue-600 shrink-0" />,
    accent: 'text-blue-600'
  };

  const handlePrev = (e) => {
    if (e) e.stopPropagation();
    setCurrentIndex((prev) => (prev > 0 ? prev - 1 : banners.length - 1));
  };

  const handleNext = (e) => {
    if (e) e.stopPropagation();
    setCurrentIndex((prev) => (prev < banners.length - 1 ? prev + 1 : 0));
  };

  const handleTouchStart = (e) => {
    touchStartX.current = e.targetTouches[0].clientX;
  };

  const handleTouchMove = (e) => {
    touchEndX.current = e.targetTouches[0].clientX;
  };

  const handleTouchEnd = () => {
    if (!touchStartX.current || !touchEndX.current) return;
    const distance = touchStartX.current - touchEndX.current;
    const minSwipeDistance = 45;

    if (distance > minSwipeDistance && banners.length > 1) {
      handleNext();
    } else if (distance < -minSwipeDistance && banners.length > 1) {
      handlePrev();
    }

    touchStartX.current = null;
    touchEndX.current = null;
  };

  const imageUrl = currentBanner.image_url || currentBanner.image_path;
  const hasImage = Boolean(imageUrl);
  const hasText = Boolean(currentBanner.content && currentBanner.content.trim());
  const hasTitle = Boolean(currentBanner.title && currentBanner.title.trim());

  return (
    <div 
      className="fixed inset-0 z-[9999] bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6 animate-fade-in"
      onClick={(e) => {
        // Klik di area luar card untuk menutup banner
        if (e.target === e.currentTarget) onClose();
      }}
    >
      <div className="relative w-full max-w-sm sm:max-w-md">
        {/* Tombol Panah Prev di Samping Kiri (hanya jika > 1 banner) */}
        {banners.length > 1 && (
          <button
            type="button"
            onClick={handlePrev}
            aria-label="Slide Sebelumnya"
            className="absolute -left-3 sm:-left-5 top-1/2 -translate-y-1/2 z-30 w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/90 hover:bg-white text-slate-700 hover:text-slate-900 border border-slate-200/90 shadow-md backdrop-blur-md flex items-center justify-center hover:scale-105 active:scale-95 transition-all cursor-pointer"
          >
            <IconChevronLeft size={20} stroke={2.4} className="shrink-0" />
          </button>
        )}

        {/* Tombol Panah Next di Samping Kanan (hanya jika > 1 banner) */}
        {banners.length > 1 && (
          <button
            type="button"
            onClick={handleNext}
            aria-label="Slide Berikutnya"
            className="absolute -right-3 sm:-right-5 top-1/2 -translate-y-1/2 z-30 w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/90 hover:bg-white text-slate-700 hover:text-slate-900 border border-slate-200/90 shadow-md backdrop-blur-md flex items-center justify-center hover:scale-105 active:scale-95 transition-all cursor-pointer"
          >
            <IconChevronRight size={20} stroke={2.4} className="shrink-0" />
          </button>
        )}

        {/* Card Banner Bersih & Modern */}
        <div 
          className="bg-white rounded-3xl shadow-2xl border border-slate-200/90 overflow-hidden relative flex flex-col max-h-[85vh] animate-scale-in"
          onTouchStart={handleTouchStart}
          onTouchMove={handleTouchMove}
          onTouchEnd={handleTouchEnd}
        >
          {/* Tombol Tutup X Minimalis di Pojok Kanan Atas */}
          <button
            type="button"
            onClick={onClose}
            aria-label="Tutup Banner"
            className="absolute right-3 top-3 z-20 w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-slate-900/60 hover:bg-slate-900 text-white backdrop-blur-md flex items-center justify-center transition-all cursor-pointer shadow-xs"
          >
            <IconX size={16} stroke={2.4} className="shrink-0" />
          </button>

          {/* Indikator Slide Minimalis di Pojok Kiri Atas */}
          {banners.length > 1 && (
            <div className="absolute left-3.5 top-3.5 z-20">
              <span className="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-slate-900/60 text-white backdrop-blur-md">
                {currentIndex + 1} / {banners.length}
              </span>
            </div>
          )}

          {/* Konten Scrollable jika panjang */}
          <div className="overflow-y-auto custom-scrollbar">
            {/* Tampilan Gambar Banner jika ada */}
            {hasImage && (
              <div className="relative bg-slate-100">
                {currentBanner.link_url ? (
                  <a 
                    href={currentBanner.link_url} 
                    target="_blank" 
                    rel="noopener noreferrer"
                    className="block group cursor-pointer"
                  >
                    <img
                      src={imageUrl}
                      alt={currentBanner.title || 'Banner'}
                      className="w-full max-h-80 sm:max-h-96 object-contain mx-auto transition-transform duration-200 group-hover:opacity-95"
                    />
                  </a>
                ) : (
                  <img
                    src={imageUrl}
                    alt={currentBanner.title || 'Banner'}
                    className="w-full max-h-80 sm:max-h-96 object-contain mx-auto"
                  />
                )}
              </div>
            )}

            {/* Bagian Teks & Judul Banner jika ada */}
            {(hasTitle || hasText) && (
              <div className="p-5 sm:p-6 space-y-3">
                <div className="flex items-start gap-2.5">
                  <div className="p-1.5 rounded-xl bg-slate-100 shrink-0 mt-0.5">
                    {themeConfig.icon}
                  </div>
                  <div className="flex-1 pr-6">
                    <span className={`text-[9px] uppercase font-extrabold tracking-wider px-2 py-0.5 rounded-md inline-block mb-1 border ${themeConfig.badge}`}>
                      {type === 'warning' ? 'Penting' : type === 'promo' ? 'Amnesti / Promo' : 'Pengumuman'}
                    </span>
                    {hasTitle && (
                      <h3 className="text-sm sm:text-base font-extrabold text-slate-900 leading-snug">
                        {currentBanner.title}
                      </h3>
                    )}
                  </div>
                </div>

                {hasText && (
                  <p className="text-xs sm:text-sm text-slate-600 leading-relaxed whitespace-pre-line">
                    {currentBanner.content}
                  </p>
                )}

                {/* Tautan Luar Minimalis jika ada */}
                {currentBanner.link_url && (
                  <div className="pt-1">
                    <a
                      href={currentBanner.link_url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline"
                    >
                      <span>Lihat Informasi Selengkapnya</span>
                      <IconExternalLink size={13} className="shrink-0" />
                    </a>
                  </div>
                )}
              </div>
            )}
          </div>

          {/* Indikator Titik Paginasi Halus di Bagian Bawah (hanya jika > 1 banner) */}
          {banners.length > 1 && (
            <div className="py-3 flex items-center justify-center gap-1.5 bg-slate-50/80 border-t border-slate-100">
              {banners.map((_, idx) => (
                <button
                  key={idx}
                  type="button"
                  onClick={() => setCurrentIndex(idx)}
                  aria-label={`Slide ${idx + 1}`}
                  className={`h-1.5 rounded-full transition-all cursor-pointer ${
                    idx === currentIndex ? 'w-5 bg-slate-900' : 'w-1.5 bg-slate-300 hover:bg-slate-400'
                  }`}
                />
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
