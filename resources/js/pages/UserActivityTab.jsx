import React, { useState, useEffect } from 'react';
import {
  IconHistory,
  IconRefresh,
  IconReceiptTax,
  IconCalculator,
  IconSearch,
  IconCalendar,
  IconClock,
  IconCheck,
  IconArrowRight
} from '@tabler/icons-react';
import { getApiBase, getAuthToken } from '../utils/config';
import { formatRupiah } from '../utils/formatters';

export default function UserActivityTab({ onRepeatSearch }) {
  const [logs, setLogs] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isAll, setIsAll] = useState(false);
  const [total, setTotal] = useState(0);
  const [hasMore, setHasMore] = useState(false);

  const fetchActivities = async (showAll = false) => {
    setLoading(true);
    try {
      const apiBase = getApiBase();
      const token = getAuthToken();
      const url = `${apiBase}/user/activities${showAll ? '?all=1' : ''}`;
      const res = await fetch(url, {
        headers: {
          'Accept': 'application/json',
          'Authorization': token ? `Bearer ${token}` : '',
          'X-Api-Token': token || '',
        },
      });

      const json = await res.json();
      if (res.ok && json.status === 'success') {
        setLogs(json.data.logs || []);
        setTotal(json.data.total || 0);
        setHasMore(json.data.has_more || false);
        setIsAll(showAll);
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchActivities(false);
  }, []);

  const getActivityBadgeStyle = (act) => {
    switch (act) {
      case 'Cek Pajak':
        return { backgroundColor: '#eff6ff', color: '#1d4ed8', borderColor: '#bfdbfe' };
      case 'Mulai Simulasi':
        return { backgroundColor: '#fffbeb', color: '#b45309', borderColor: '#fde68a' };
      case 'Cek Estimasi BBNKB':
        return { backgroundColor: '#f0fdfa', color: '#0f766e', borderColor: '#99f6e4' };
      case 'Cek Nilai Jual':
        return { backgroundColor: '#faf5ff', color: '#7e22ce', borderColor: '#e9d5ff' };
      case 'Bayar PRO NTT':
        return { backgroundColor: '#dcfce7', color: '#15803d', borderColor: '#86efac' };
      default:
        return { backgroundColor: '#f8fafc', color: '#334155', borderColor: '#e2e8f0' };
    }
  };

  const formatActivityDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleString('id-ID', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  return (
    <div className="p-4 sm:p-6 space-y-4 max-w-2xl mx-auto">
      {/* Header Card */}
      <div className="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200/90 flex items-center justify-between">
        <div>
          <h2 className="text-base font-black text-slate-900 tracking-tight flex items-center gap-2">
            <span
              className="p-1.5 rounded-lg shrink-0 flex items-center justify-center"
              style={{ backgroundColor: '#eff6ff', color: '#2563eb' }}
            >
              <IconHistory size={18} stroke={2.2} className="shrink-0" />
            </span>
            Riwayat Aktivitas Saya
          </h2>
          <p className="text-xs text-slate-500 mt-1">
            {isAll ? `Menampilkan ${logs.length} dari total ${total} riwayat Anda` : `5 aktivitas terakhir yang pernah Anda lakukan`}
          </p>
        </div>

        <button
          type="button"
          onClick={() => fetchActivities(isAll)}
          title="Refresh riwayat"
          className="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition-all cursor-pointer shrink-0"
        >
          <IconRefresh size={18} className={`shrink-0 ${loading ? 'animate-spin text-blue-600' : ''}`} />
        </button>
      </div>

      {/* Activity List */}
      <div className="bg-white rounded-2xl shadow-xs border border-slate-200/90 overflow-hidden divide-y divide-slate-100">
        {loading && logs.length === 0 ? (
          <div className="p-8 text-center text-xs text-slate-400">
            <IconRefresh size={24} className="animate-spin mx-auto mb-2 text-slate-300 shrink-0" />
            Memuat riwayat aktivitas...
          </div>
        ) : logs.length === 0 ? (
          <div className="p-8 text-center text-xs text-slate-400 space-y-1">
            <IconHistory size={28} className="mx-auto text-slate-300 mb-2 shrink-0" />
            <p className="font-semibold text-slate-700">Belum ada riwayat tercatat</p>
            <p>Aktivitas pengecekan pajak dan simulasi Anda akan tercatat di sini.</p>
          </div>
        ) : (
          logs.map((item) => (
            <div key={item.id} className="p-4 hover:bg-slate-50/70 transition-colors flex items-start justify-between gap-3">
              <div className="space-y-1">
                <div className="flex items-center gap-2">
                  <span
                    className="px-2 py-0.5 rounded-md text-[10px] font-bold border font-mono shrink-0"
                    style={getActivityBadgeStyle(item.activity)}
                  >
                    {item.activity}
                  </span>
                  <span className="text-[10px] text-slate-400 flex items-center gap-1 font-mono">
                    <IconClock size={12} className="shrink-0" />
                    {formatActivityDate(item.created_at)}
                  </span>
                </div>
                <p className="text-xs font-semibold text-slate-800 leading-relaxed">
                  {item.detail || '-'}
                </p>
                {item.total_biaya && Number(item.total_biaya) > 0 ? (
                  <div className="mt-1 flex items-center gap-1.5 text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200/80 px-2.5 py-1 rounded-lg w-fit shadow-2xs">
                    <span className="text-[10px] font-semibold text-emerald-600 uppercase tracking-wider">
                      {item.activity === 'Cek Nilai Jual' ? 'Nilai Jual:' : 'Total Biaya:'}
                    </span>
                    <span className="font-mono text-xs font-extrabold text-emerald-700">
                      {formatRupiah(item.total_biaya)}
                    </span>
                  </div>
                ) : null}
              </div>

              {/* Tombol Ulangi Cek jika aktivitas mengandung nopol */}
              {item.detail && item.detail.match(/DH\s?[0-9]{1,4}\s?[A-Z]{0,3}/i) && onRepeatSearch && (
                <button
                  type="button"
                  onClick={() => {
                    const match = item.detail.match(/DH\s?[0-9]{1,4}\s?[A-Z]{0,3}/i);
                    if (match) onRepeatSearch(match[0].replace(/\s+/g, ''));
                  }}
                  className="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-[10px] font-bold shrink-0 flex items-center gap-1 cursor-pointer shadow-xs"
                >
                  <span>Cek Ulang</span>
                  <IconArrowRight size={12} className="shrink-0" />
                </button>
              )}
            </div>
          ))
        )}
      </div>

      {/* Footer Action: Tampilkan Semua */}
      {hasMore && !isAll && (
        <div className="text-center pt-1">
          <button
            type="button"
            onClick={() => fetchActivities(true)}
            className="w-full py-2.5 px-4 rounded-xl border border-blue-200 text-blue-800 text-xs font-bold transition-all cursor-pointer shadow-xs"
            style={{ backgroundColor: '#eff6ff' }}
          >
            Tampilkan Semua Riwayat ({total} Aktivitas)
          </button>
        </div>
      )}

      {isAll && total > 5 && (
        <div className="text-center pt-1">
          <button
            type="button"
            onClick={() => fetchActivities(false)}
            className="text-xs text-slate-500 hover:text-slate-800 font-semibold underline cursor-pointer"
          >
            Tampilkan 5 Terakhir Saja
          </button>
        </div>
      )}
    </div>
  );
}
