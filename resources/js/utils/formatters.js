export const formatRupiah = (number) => {
  if (number === null || number === undefined || isNaN(number)) return 'Rp 0';
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(number);
};

export const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  try {
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return new Intl.DateTimeFormat('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    }).format(d);
  } catch (e) {
    return dateStr;
  }
};

/**
 * Menghitung status jatuh tempo pajak:
 * - Terlambat: "Terlambat {x bulan x hari}" (atau tahun jika ada)
 * - Sebelum jatuh tempo: "Bayar {x hari} lebih awal"
 * - Hari H: "Jatuh Tempo Hari Ini"
 */
export const getKeteranganJatuhTempo = (sdNoticeStr, taxData = null) => {
  if (taxData?.keterangan_tempo && taxData?.status_tempo_kategori) {
    return {
      teks: taxData.keterangan_tempo,
      kategori: taxData.status_tempo_kategori,
      isTerlambat: Boolean(taxData.is_terlambat || taxData.status_tempo_kategori === 'terlambat'),
    };
  }

  if (!sdNoticeStr) {
    return { teks: 'Tepat Waktu', kategori: 'tepat_waktu', isTerlambat: false };
  }

  try {
    const parts = String(sdNoticeStr).split('-');
    if (parts.length < 3) return { teks: 'Tepat Waktu', kategori: 'tepat_waktu', isTerlambat: false };
    const [year, month, day] = parts.map(Number);
    const noticeDate = new Date(year, month - 1, day);
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

    const diffDays = Math.round((noticeDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));

    if (diffDays > 0) {
      return {
        teks: `Bayar ${diffDays} Hari Lebih Awal`,
        kategori: 'lebih_awal',
        diffDays,
        isTerlambat: false,
      };
    } else if (diffDays === 0) {
      return {
        teks: 'Jatuh Tempo Hari Ini',
        kategori: 'hari_h',
        diffDays: 0,
        isTerlambat: false,
      };
    } else {
      let temp = new Date(noticeDate.getTime());
      let y = 0, m = 0;

      while (true) {
        const nextYear = new Date(temp.getFullYear() + 1, temp.getMonth(), temp.getDate());
        if (nextYear <= today) {
          y++;
          temp = nextYear;
        } else {
          break;
        }
      }

      while (true) {
        const nextMonth = new Date(temp.getFullYear(), temp.getMonth() + 1, temp.getDate());
        if (nextMonth <= today) {
          m++;
          temp = nextMonth;
        } else {
          break;
        }
      }

      const d = Math.round((today.getTime() - temp.getTime()) / (1000 * 60 * 60 * 24));
      const strParts = [];
      if (y > 0) strParts.push(`${y} Tahun`);
      if (m > 0) strParts.push(`${m} Bulan`);
      if (d > 0) strParts.push(`${d} Hari`);
      if (strParts.length === 0) strParts.push('0 Hari');

      return {
        teks: `Terlambat ${strParts.join(' ')}`,
        kategori: 'terlambat',
        diffDays: Math.abs(diffDays),
        isTerlambat: true,
      };
    }
  } catch (e) {
    return { teks: 'Tepat Waktu', kategori: 'tepat_waktu', isTerlambat: false };
  }
};


