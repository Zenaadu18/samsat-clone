import React from 'react';
import {
  IconHome2,
  IconReceiptTax,
  IconCalculator,
  IconRefresh,
  IconDatabase,
  IconHistory
} from '@tabler/icons-react';

export default function BottomNav({ activeTab, setActiveTab, currentUser }) {
  const isAdmin = currentUser?.role === 'admin';

  const tabs = [
    { id: 'home', label: 'Beranda', icon: IconHome2 },
    { id: 'cek', label: 'Cek Pajak', icon: IconReceiptTax },
    ...(isAdmin ? [
      { id: 'simulasi', label: 'Simulasi', icon: IconCalculator },
    ] : []),
    { id: 'bbn2', label: 'Balik Nama', icon: IconRefresh },
    ...(isAdmin ? [
      { id: 'njkb', label: 'NJKB', icon: IconDatabase },
    ] : []),
    { id: 'riwayat', label: 'Riwayat', icon: IconHistory },
  ];

  return (
    <div className="fixed bottom-0 left-0 right-0 z-30 flex justify-center pointer-events-none">
      <nav className="pointer-events-auto w-full max-w-4xl bg-white/95 backdrop-blur-md border-t border-slate-200/90 px-2 sm:px-6 py-2 shadow-lg sm:rounded-t-2xl">
        <div className="flex items-center justify-around max-w-lg mx-auto sm:max-w-none">
          {tabs.map((tab) => {
            const Icon = tab.icon;
            const isActive = activeTab === tab.id;

            return (
              <button
                key={tab.id}
                type="button"
                onClick={() => setActiveTab(tab.id)}
                className={`relative flex flex-col items-center justify-center py-1 px-1.5 sm:px-4 rounded-xl transition-all duration-150 active:scale-90 cursor-pointer ${
                  isActive
                    ? 'text-emerald-700 font-bold'
                    : 'text-slate-400 hover:text-slate-600 font-medium'
                }`}
              >
                <div className="relative shrink-0 flex items-center justify-center">
                  <Icon size={21} stroke={isActive ? 2.4 : 1.8} className="shrink-0" />
                </div>
                <span className="text-[10px] sm:text-xs mt-1 tracking-tight text-center leading-none">
                  {tab.label}
                </span>
                {isActive && (
                  <div className="w-1.5 h-1.5 rounded-full bg-emerald-600 mt-1 shrink-0"></div>
                )}
              </button>
            );
          })}
        </div>
      </nav>
    </div>
  );
}
