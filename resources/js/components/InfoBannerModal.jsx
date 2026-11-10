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

function sanitizeBannerHtml(dirtyHtml) {
  if (!dirtyHtml) return '';
  let clean = dirtyHtml
    .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
    .replace(/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi, '')
    .replace(/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/gi, '')
    .replace(/<embed\b[^<]*(?:(?!<\/embed>)<[^<]*)*<\/embed>/gi, '')
    .replace(/<link\b[^>]*>/gi, '')
    .replace(/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/gi, '');

  // Bersihkan inline event handler berbahaya
  clean = clean.replace(/\son\w+\s*=\s*(?:'[^']*'|"[^"]*"|[^\s>]+)/gi, '');
  clean = clean.replace(/href\s*=\s*(?:'javascript:[^']*'|"javascript:[^"]*"|javascript:[^\s>]+)/gi, 'href="#"');

  return clean;
}

function convertLegacyPlainTextToHtml(text) {
  if (!text) return '';
  const lines = text.split('\n');
  let html = '';
  let inOl = false;
  let inUl = false;

  lines.forEach((line) => {
    let trimmed = line.trim();
    if (!trimmed) {
      if (inOl) { html += '</ol>'; inOl = false; }
      if (inUl) { html += '</ul>'; inUl = false; }
      return;
    }

    // Deteksi alignment tag
    let alignAttr = '';
    const alignMatch = trimmed.match(/^<(?:p|div)\s+align="(left|center|right|justify)"\s*>(.*?)(?:<\/(?:p|div)>)?$/i);
    if (alignMatch) {
      alignAttr = ` style="text-align: ${alignMatch[1].toLowerCase()};"`;
      trimmed = alignMatch[2].trim();
    }

    // Format markdown bold/italic
    let formattedLine = trimmed
      .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>')
      .replace(/\*(.*?)\*/g, '<i>$1</i>');

    // Deteksi Numbered List (1. atau 1) )
    const numMatch = formattedLine.match(/^(\d+)[\.\)]\s+(.*)$/);
    if (numMatch) {
      if (inUl) { html += '</ul>'; inUl = false; }
      if (!inOl) { html += '<ol>'; inOl = true; }
      html += `<li>${numMatch[2]}</li>`;
      return;
    }

    // Deteksi Bullet List (•, -, *)
    const bulletMatch = formattedLine.match(/^[•\-\*]\s+(.*)$/);
    if (bulletMatch) {
      if (inOl) { html += '</ol>'; inOl = false; }
      if (!inUl) { html += '<ul>'; inUl = true; }
      html += `<li>${bulletMatch[1]}</li>`;
      return;
    }

    if (inOl) { html += '</ol>'; inOl = false; }
    if (inUl) { html += '</ul>'; inUl = false; }
    html += `<p${alignAttr}>${formattedLine}</p>`;
  });

  if (inOl) html += '</ol>';
  if (inUl) html += '</ul>';
  return html;
}

function BannerContentRenderer({ content }) {
  if (!content) return null;

  const htmlContent = React.useMemo(() => {
    const raw = String(content).trim();
    const isHtml = /<(?:p|div|ol|ul|li|b|strong|i|em|u|s|span|h[1-6]|br)[\s>]/i.test(raw);
    return isHtml ? sanitizeBannerHtml(raw) : convertLegacyPlainTextToHtml(raw);
  }, [content]);

  return (
    <div className="banner-rich-content-wrapper">
      <style>{`
        .banner-rich-content-wrapper {
          color: #334155;
          line-height: 1.625;
          font-size: 0.8125rem;
        }
        @media (min-width: 640px) {
          .banner-rich-content-wrapper {
            font-size: 0.875rem;
          }
        }
        .banner-rich-content-wrapper p {
          margin-bottom: 0.45rem;
          line-height: 1.625;
        }
        .banner-rich-content-wrapper p:last-child {
          margin-bottom: 0;
        }
        .banner-rich-content-wrapper ol {
          list-style-type: decimal !important;
          list-style-position: outside !important;
          padding-left: 1.4rem !important;
          margin: 0.45rem 0 !important;
        }
        .banner-rich-content-wrapper ol li {
          margin-bottom: 0.35rem !important;
          padding-left: 0.2rem !important;
        }
        .banner-rich-content-wrapper ul {
          list-style-type: disc !important;
          list-style-position: outside !important;
          padding-left: 1.4rem !important;
          margin: 0.45rem 0 !important;
        }
        .banner-rich-content-wrapper ul li {
          margin-bottom: 0.35rem !important;
          padding-left: 0.2rem !important;
        }
        .banner-rich-content-wrapper b, .banner-rich-content-wrapper strong {
          font-weight: 800 !important;
          color: #0f172a !important;
        }
        .banner-rich-content-wrapper i, .banner-rich-content-wrapper em {
          font-style: italic !important;
        }
        .banner-rich-content-wrapper u {
          text-decoration: underline !important;
          text-underline-offset: 2.5px !important;
        }
        .banner-rich-content-wrapper s {
          text-decoration: line-through !important;
        }
        .banner-rich-content-wrapper h4 {
          font-size: 1rem;
          font-weight: 800;
          color: #0f172a;
          margin-top: 0.6rem;
          margin-bottom: 0.25rem;
        }
        .banner-rich-content-wrapper [align="center"],
        .banner-rich-content-wrapper [style*="text-align: center"],
        .banner-rich-content-wrapper [style*="text-align:center"] {
          text-align: center !important;
        }
        .banner-rich-content-wrapper [align="right"],
        .banner-rich-content-wrapper [style*="text-align: right"],
        .banner-rich-content-wrapper [style*="text-align:right"] {
          text-align: right !important;
        }
        .banner-rich-content-wrapper [align="justify"],
        .banner-rich-content-wrapper [style*="text-align: justify"],
        .banner-rich-content-wrapper [style*="text-align:justify"] {
          text-align: justify !important;
        }
        .banner-rich-content-wrapper [align="left"],
        .banner-rich-content-wrapper [style*="text-align: left"],
        .banner-rich-content-wrapper [style*="text-align:left"] {
          text-align: left !important;
        }
      `}</style>
      <div dangerouslySetInnerHTML={{ __html: htmlContent }} />
    </div>
  );
}

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

  const imageHeightClass = (hasTitle || hasText)
    ? 'max-h-[54vh] sm:max-h-[62vh]'
    : 'max-h-[68vh] sm:max-h-[76vh]';

  const bannerImageElement = hasImage ? (
    <img
      src={imageUrl}
      alt={currentBanner.title || 'Banner'}
      className={`w-full ${imageHeightClass} object-contain mx-auto transition-transform duration-200 ${currentBanner.link_url ? 'group-hover:opacity-95' : ''}`}
    />
  ) : null;

  return (
    <div 
      className="fixed inset-0 z-[9999] bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6 animate-fade-in"
      onClick={(e) => {
        // Klik di area luar card untuk menutup banner
        if (e.target === e.currentTarget) onClose();
      }}
    >
      <div className="relative w-full max-w-md sm:max-w-xl md:max-w-2xl">
        {/* Tombol Panah Prev di Samping Kiri (hanya jika > 1 banner) */}
        {banners.length > 1 && (
          <button
            type="button"
            onClick={handlePrev}
            aria-label="Slide Sebelumnya"
            className="absolute -left-2 sm:-left-5 top-1/2 -translate-y-1/2 z-30 w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/90 hover:bg-white text-slate-700 hover:text-slate-900 border border-slate-200/90 shadow-md backdrop-blur-md flex items-center justify-center hover:scale-105 active:scale-95 transition-all cursor-pointer"
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
            className="absolute -right-2 sm:-right-5 top-1/2 -translate-y-1/2 z-30 w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/90 hover:bg-white text-slate-700 hover:text-slate-900 border border-slate-200/90 shadow-md backdrop-blur-md flex items-center justify-center hover:scale-105 active:scale-95 transition-all cursor-pointer"
          >
            <IconChevronRight size={20} stroke={2.4} className="shrink-0" />
          </button>
        )}

        {/* Card Banner Bersih & Modern */}
        <div 
          className="bg-white rounded-3xl shadow-2xl border border-slate-200/90 overflow-hidden relative flex flex-col max-h-[90vh] animate-scale-in"
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
                    {bannerImageElement}
                  </a>
                ) : (
                  bannerImageElement
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
                  <BannerContentRenderer content={currentBanner.content} />
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
