import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import MobileFrame from './components/MobileFrame';
import BottomNav from './components/BottomNav';
import HomeTab from './pages/HomeTab';
import CheckTaxTab from './pages/CheckTaxTab';
import SimulateTab from './pages/SimulateTab';
import Bbn2Tab from './pages/Bbn2Tab';
import NjkbTab from './pages/NjkbTab';
import InfoTab from './pages/InfoTab';
import UserActivityTab from './pages/UserActivityTab';
import UserLoginModal from './components/UserLoginModal';
import InfoBannerModal from './components/InfoBannerModal';
import { getApiBase } from './utils/config';
import { getDeviceId } from './utils/device';

const ALL_TABS = ['home', 'cek', 'simulasi', 'bbn2', 'njkb', 'riwayat', 'info'];

function App() {
  const [activeTab, setActiveTabState] = useState('home');
  const [slideDirection, setSlideDirection] = useState('right');
  const [targetNopol, setTargetNopol] = useState('');
  const [simulationNopol, setSimulationNopol] = useState('');
  const [simulationPrefill, setSimulationPrefill] = useState(null);
  const [bbn2Nopol, setBbn2Nopol] = useState('');

  // State Autentikasi Pengguna (Dukungan localStorage untuk Ingat Saya & sessionStorage untuk sesi sementara)
  const [currentUser, setCurrentUser] = useState(() => {
    try {
      const stored = localStorage.getItem('infopkb_user') || sessionStorage.getItem('infopkb_user');
      return stored ? JSON.parse(stored) : null;
    } catch {
      return null;
    }
  });

  const isAdmin = currentUser?.role === 'admin';
  const availableTabs = isAdmin
    ? ALL_TABS
    : ['home', 'cek', 'bbn2', 'riwayat', 'info'];

  // State Banner Informasi
  const [banner, setBanner] = useState(null);
  const [showBanner, setShowBanner] = useState(false);

  useEffect(() => {
    // Verifikasi Token User ke server (dari localStorage atau sessionStorage)
    const apiBase = getApiBase();
    const token = localStorage.getItem('infopkb_token') || sessionStorage.getItem('infopkb_token');
    if (token) {
      fetch(`${apiBase}/auth/me`, {
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`,
          'X-Device-Id': getDeviceId(),
        },
      })
        .then((r) => r.json())
        .then((res) => {
          if (res.status === 'success' && res.data) {
            setCurrentUser(res.data);
            if (localStorage.getItem('infopkb_token')) {
              localStorage.setItem('infopkb_user', JSON.stringify(res.data));
            } else {
              sessionStorage.setItem('infopkb_user', JSON.stringify(res.data));
            }
          } else {
            // Token hangus, diblokir, atau perangkat tidak cocok
            handleLogout();
          }
        })
        .catch(() => {});
    }

    // Ambil Data Banner Pengumuman dari server
    fetch(`${apiBase}/banner`)
      .then((r) => r.json())
      .then((res) => {
        if (res.status === 'success' && res.data && res.data.active) {
          setBanner(res.data);
        }
      })
      .catch(() => {});
  }, []);

  const handleLoginSuccess = (user, token) => {
    setCurrentUser(user);
    // Banner HANYA muncul saat user baru saja login
    // Tidak akan ditampilkan lagi ketika halaman direfresh sampai sesi berakhir (logout)
    const bannerSeenInSession = sessionStorage.getItem('infopkb_banner_session_seen');
    if (!bannerSeenInSession) {
      sessionStorage.setItem('infopkb_banner_session_seen', '1');
      setShowBanner(true);
    }
  };

  const handleCloseBanner = () => {
    sessionStorage.setItem('infopkb_banner_session_seen', '1');
    setShowBanner(false);
  };

  const handleLogout = () => {
    const apiBase = getApiBase();
    const token = localStorage.getItem('infopkb_token') || sessionStorage.getItem('infopkb_token');
    if (token) {
      fetch(`${apiBase}/auth/logout`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
      }).catch(() => {});
    }
    localStorage.removeItem('infopkb_token');
    localStorage.removeItem('infopkb_user');
    sessionStorage.removeItem('infopkb_token');
    sessionStorage.removeItem('infopkb_user');
    // Hapus status sesi banner saat logout agar login sesi berikutnya dapat melihat banner
    sessionStorage.removeItem('infopkb_banner_session_seen');
    setCurrentUser(null);
    setShowBanner(false);
  };

  const setActiveTab = (newTab) => {
    if (newTab === activeTab) return;
    if (!isAdmin && (newTab === 'simulasi' || newTab === 'njkb')) {
      newTab = 'home';
    }
    const oldIdx = availableTabs.indexOf(activeTab);
    const newIdx = availableTabs.indexOf(newTab);
    setSlideDirection(newIdx >= oldIdx ? 'right' : 'left');
    setActiveTabState(newTab);
  };

  const handleQuickSearch = (nopol) => {
    setTargetNopol(nopol);
    setActiveTab('cek');
  };

  const handleClearTargetNopol = () => {
    setTargetNopol('');
  };

  const handleSwitchToSimulation = (nopol) => {
    setSimulationNopol(nopol);
    setSimulationPrefill(null);
    setActiveTab('simulasi');
  };

  const handleSwitchToBbn2 = (nopol) => {
    setBbn2Nopol(nopol);
    setActiveTab('bbn2');
  };

  const handleSelectNjkb = (item) => {
    setSimulationPrefill(item);
    setSimulationNopol('');
    setActiveTab('simulasi');
  };

  // Jika belum login, tampilkan layar penuh login (tanpa frame, navigasi, atau tab apapun)
  if (!currentUser) {
    return <UserLoginModal onLoginSuccess={handleLoginSuccess} />;
  }

  return (
    <>
      {/* Pop-up Banner Informasi jika aktif */}
      {showBanner && banner && (
        <InfoBannerModal banner={banner} onClose={handleCloseBanner} />
      )}

      <MobileFrame
        activeTab={activeTab}
        setActiveTab={setActiveTab}
        currentUser={currentUser}
        onLogout={handleLogout}
      >
        <div key={activeTab} className={slideDirection === 'right' ? 'tab-slide-right' : 'tab-slide-left'}>
          {activeTab === 'home' && (
            <HomeTab
              setActiveTab={setActiveTab}
              onQuickSearch={handleQuickSearch}
              currentUser={currentUser}
            />
          )}

          {activeTab === 'cek' && (
            <CheckTaxTab
              initialNopol={targetNopol}
              onClearInitialNopol={handleClearTargetNopol}
              onSwitchToSimulation={handleSwitchToSimulation}
            />
          )}

          {activeTab === 'simulasi' && (
            <SimulateTab
              prefillNopol={simulationNopol}
              prefillData={simulationPrefill}
            />
          )}

          {activeTab === 'bbn2' && (
            <Bbn2Tab
              prefillNopol={bbn2Nopol}
              onSwitchToTaxCheck={handleQuickSearch}
            />
          )}

          {activeTab === 'njkb' && (
            <NjkbTab
              onSelectNjkb={handleSelectNjkb}
              onCekPajak={handleQuickSearch}
              onSimulasi={handleSelectNjkb}
            />
          )}

          {activeTab === 'riwayat' && (
            <UserActivityTab
              onRepeatSearch={handleQuickSearch}
            />
          )}

          {activeTab === 'info' && (
            <InfoTab />
          )}
        </div>

        <BottomNav
          activeTab={activeTab}
          setActiveTab={setActiveTab}
          currentUser={currentUser}
        />
      </MobileFrame>
    </>
  );
}

const rootEl = document.getElementById('root');
if (rootEl) {
  createRoot(rootEl).render(<App />);
}
