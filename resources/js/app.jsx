import React, { useState, useEffect, useCallback } from 'react';
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
import ProNttTab from './pages/ProNttTab';
import UserLoginModal from './components/UserLoginModal';
import InfoBannerModal from './components/InfoBannerModal';
import { getApiBase, getAuthToken } from './utils/config';
import { getDeviceId } from './utils/device';

const ALL_TABS = ['home', 'cek', 'prontt', 'simulasi', 'bbn2', 'njkb', 'riwayat', 'info'];

const DEFAULT_MENU_CONFIG = {
  home: true,
  cek: true,
  prontt: true,
  tombol_bayar_prontt: true,
  bbn2: true,
  simulasi: false,
  njkb: false,
  riwayat: true,
  info: true,
};

function App() {
  const [activeTab, setActiveTabState] = useState('home');
  const [slideDirection, setSlideDirection] = useState('right');
  const [targetNopol, setTargetNopol] = useState('');
  const [simulationNopol, setSimulationNopol] = useState('');
  const [simulationPrefill, setSimulationPrefill] = useState(null);
  const [bbn2Nopol, setBbn2Nopol] = useState('');
  const [proNttNopol, setProNttNopol] = useState('');
  const [proNttTaxData, setProNttTaxData] = useState(null);

  // State Autentikasi Pengguna (Dukungan localStorage untuk Ingat Saya & sessionStorage untuk sesi sementara)
  const [currentUser, setCurrentUser] = useState(() => {
    try {
      const stored = localStorage.getItem('infopkb_user') || sessionStorage.getItem('infopkb_user');
      return stored ? JSON.parse(stored) : null;
    } catch {
      return null;
    }
  });

  const [menuConfig, setMenuConfig] = useState(DEFAULT_MENU_CONFIG);

  const isAdmin = currentUser?.role === 'admin';
  const canAccessMenu = useCallback((key) => {
    if (key === 'home') return true;
    if (isAdmin) return true;
    if (key === 'tombol_bayar_prontt') {
      return menuConfig.prontt !== false && menuConfig.tombol_bayar_prontt !== false;
    }
    return menuConfig[key] !== false;
  }, [isAdmin, menuConfig]);

  const availableTabs = ALL_TABS.filter((tab) => canAccessMenu(tab));

  const fetchAppConfig = useCallback(() => {
    const apiBase = getApiBase();
    fetch(`${apiBase}/config`, {
      headers: { 'Accept': 'application/json' },
    })
      .then((r) => r.json())
      .then((res) => {
        if (res?.status === 'success' && res?.data?.menus) {
          setMenuConfig({ ...DEFAULT_MENU_CONFIG, ...res.data.menus });
        }
      })
      .catch(() => {});
  }, []);

  // State Banner Informasi
  const [banner, setBanner] = useState(null);
  const [showBanner, setShowBanner] = useState(false);

  const handleLogout = useCallback(() => {
    const apiBase = getApiBase();
    const token = getAuthToken();
    if (token) {
      fetch(`${apiBase}/auth/logout`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`,
          'X-Api-Token': token,
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
  }, []);

  useEffect(() => {
    // Verifikasi Token User ke server (dari localStorage atau sessionStorage)
    const apiBase = getApiBase();

    const verifySession = () => {
      const token = getAuthToken();
      if (!token) return;

      fetch(`${apiBase}/auth/me`, {
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`,
          'X-Api-Token': token,
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
            // Token hangus, direset massal oleh admin, diblokir, atau perangkat tidak cocok
            handleLogout();
          }
        })
        .catch(() => {});
    };

    // Verifikasi awal saat pertama kali mount
    verifySession();
    fetchAppConfig();

    // Verifikasi berkala setiap 30 detik & saat window focus (saat tab aktif / layar hp dibuka kembali)
    const syncAll = () => {
      verifySession();
      fetchAppConfig();
    };
    const intervalId = setInterval(syncAll, 30000);
    window.addEventListener('focus', syncAll);

    // Ambil Data Banner Pengumuman dari server
    fetch(`${apiBase}/banner`)
      .then((r) => r.json())
      .then((res) => {
        if (res.status === 'success' && res.data && res.data.active) {
          setBanner(res.data);
        }
      })
      .catch(() => {});

    return () => {
      clearInterval(intervalId);
      window.removeEventListener('focus', syncAll);
    };
  }, [handleLogout, fetchAppConfig]);

  useEffect(() => {
    if (activeTab !== 'home' && !canAccessMenu(activeTab)) {
      setActiveTabState('home');
    }
  }, [activeTab, canAccessMenu]);

  const handleLoginSuccess = (user, token) => {
    setCurrentUser(user);
    fetchAppConfig();
    // Refresh data banner terbaru dari server agar update yang dipasang admin langsung muncul
    const apiBase = getApiBase();
    fetch(`${apiBase}/banner`)
      .then((r) => r.json())
      .then((res) => {
        if (res.status === 'success' && res.data && res.data.active) {
          setBanner(res.data);
          sessionStorage.setItem('infopkb_banner_session_seen', '1');
          setShowBanner(true);
        } else {
          sessionStorage.setItem('infopkb_banner_session_seen', '1');
        }
      })
      .catch(() => {
        const bannerSeenInSession = sessionStorage.getItem('infopkb_banner_session_seen');
        if (!bannerSeenInSession) {
          sessionStorage.setItem('infopkb_banner_session_seen', '1');
          setShowBanner(true);
        }
      });
  };

  const handleCloseBanner = () => {
    sessionStorage.setItem('infopkb_banner_session_seen', '1');
    setShowBanner(false);
  };

  const setActiveTab = (newTab) => {
    if (!canAccessMenu(newTab)) {
      newTab = 'home';
    }
    if (newTab === activeTab) return;
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

  const handleSwitchToProNtt = (nopol, initialTax = null) => {
    setProNttNopol(nopol);
    setProNttTaxData(initialTax);
    setActiveTab('prontt');
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
              canAccessMenu={canAccessMenu}
              menuConfig={menuConfig}
            />
          )}

          {activeTab === 'cek' && canAccessMenu('cek') && (
            <CheckTaxTab
              initialNopol={targetNopol}
              onClearInitialNopol={handleClearTargetNopol}
              onSwitchToSimulation={canAccessMenu('simulasi') ? handleSwitchToSimulation : null}
              onSwitchToProNtt={canAccessMenu('tombol_bayar_prontt') ? handleSwitchToProNtt : null}
              showProNttInfo={canAccessMenu('prontt')}
            />
          )}

          {activeTab === 'prontt' && canAccessMenu('prontt') && (
            <ProNttTab
              prefillNopol={proNttNopol}
              initialTaxData={proNttTaxData}
              onSwitchToTaxCheck={canAccessMenu('cek') ? handleQuickSearch : null}
            />
          )}

          {activeTab === 'simulasi' && canAccessMenu('simulasi') && (
            <SimulateTab
              prefillNopol={simulationNopol}
              prefillData={simulationPrefill}
            />
          )}

          {activeTab === 'bbn2' && canAccessMenu('bbn2') && (
            <Bbn2Tab
              prefillNopol={bbn2Nopol}
              onSwitchToTaxCheck={canAccessMenu('cek') ? handleQuickSearch : null}
            />
          )}

          {activeTab === 'njkb' && canAccessMenu('njkb') && (
            <NjkbTab
              onSelectNjkb={handleSelectNjkb}
              onCekPajak={canAccessMenu('cek') ? handleQuickSearch : null}
              onSimulasi={canAccessMenu('simulasi') ? handleSelectNjkb : null}
            />
          )}

          {activeTab === 'riwayat' && canAccessMenu('riwayat') && (
            <UserActivityTab
              onRepeatSearch={canAccessMenu('cek') ? handleQuickSearch : null}
            />
          )}

          {activeTab === 'info' && canAccessMenu('info') && (
            <InfoTab />
          )}
        </div>

        <BottomNav
          activeTab={activeTab}
          setActiveTab={setActiveTab}
          currentUser={currentUser}
          canAccessMenu={canAccessMenu}
        />
      </MobileFrame>
    </>
  );
}

const rootEl = document.getElementById('root');
if (rootEl) {
  createRoot(rootEl).render(<App />);
}
