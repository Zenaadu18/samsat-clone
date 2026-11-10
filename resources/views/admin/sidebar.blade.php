<!-- Sidebar Admin Modular (Light Clean Theme) -->
<aside id="admin-sidebar" class="w-64 bg-white border-r border-slate-200 flex flex-col shrink-0 transition-all duration-300 z-40 fixed inset-y-0 left-0 -translate-x-full md:relative md:translate-x-0 shadow-sm md:shadow-none select-none">
    <!-- Inline state check to prevent layout flicker -->
    <script>
        (function() {
            try {
                if (localStorage.getItem('admin_sidebar_minimized') === 'true') {
                    document.getElementById('admin-sidebar').classList.add('sidebar-minimized');
                }
            } catch(e) {}
        })();
    </script>

    <!-- Sidebar Header / Logo -->
    <div class="sidebar-header h-16 px-4 border-b border-slate-100 flex items-center justify-between gap-2">
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="w-9 h-9 rounded-xl overflow-hidden bg-emerald-600 flex items-center justify-center text-white shadow-md shadow-emerald-500/20 shrink-0">
                <img src="{{ asset('icons/logoapps.png') }}" alt="Logo Pajak.In" class="w-full h-full object-cover">
            </div>
            <div class="sidebar-text truncate">
                <span class="font-extrabold text-sm tracking-tight block truncate">
                    <span class="brand-text-gradient">Pajak</span><span class="brand-dot-gold">.</span><span class="brand-text-gradient">In</span> <span class="text-slate-800">NTT</span>
                </span>
                <span class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider block">Admin Panel</span>
            </div>
        </div>

        <div class="flex items-center gap-1">
            <!-- Tombol Minimize / Expand Sidebar (Desktop) -->
            <button type="button" onclick="toggleSidebarMinimize()" id="sidebarMinimizeBtn"
                    class="hidden md:flex p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                    title="Perkecil / Perluas Sidebar (Ctrl+B)">
                <svg id="collapseChevron" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>
            <!-- Tombol Tutup Sidebar (Mobile) -->
            <button type="button" onclick="toggleSidebar()" class="md:hidden p-1.5 text-slate-400 hover:text-slate-600 rounded-lg cursor-pointer" title="Tutup Menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <!-- Navigation Menu Groups (Diklasifikasikan Berdasarkan Fungsi) -->
    <nav class="flex-1 overflow-y-auto p-3 space-y-6 custom-scrollbar text-xs font-semibold">
        <!-- GRUP 1: PENGGUNA & AUDIT -->
        <div>
            <div class="sidebar-group-header px-3 mb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
                <span class="sidebar-text sidebar-group-title">Pengguna &amp; Akses</span>
                <span class="sidebar-group-dot w-1.5 h-1.5 rounded-full bg-emerald-500" title="Pengguna &amp; Akses"></span>
            </div>
            <div class="space-y-1">
                <button type="button" onclick="switchSidebarMenu('users')" id="menu-btn-users" title="Daftar Pengguna ({{ $stats['total_users'] ?? 0 }})"
                        class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-50 cursor-pointer">
                    <div class="nav-item-content flex items-center gap-2.5 truncate">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span class="sidebar-text truncate">Daftar Pengguna</span>
                    </div>
                    <span class="sidebar-badge text-[10px] px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold font-mono">{{ $stats['total_users'] ?? 0 }}</span>
                </button>

                <button type="button" onclick="switchSidebarMenu('logs')" id="menu-btn-logs" title="Riwayat Aktivitas Audit ({{ $stats['total_activities'] ?? 0 }})"
                        class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-50 cursor-pointer">
                    <div class="nav-item-content flex items-center gap-2.5 truncate">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="sidebar-text truncate">Riwayat Aktivitas</span>
                    </div>
                    <span class="sidebar-badge text-[10px] px-1.5 py-0.5 rounded-md bg-blue-50 text-blue-700 font-bold font-mono">{{ $stats['total_activities'] ?? 0 }}</span>
                </button>

                <button type="button" onclick="switchSidebarMenu('menus')" id="menu-btn-menus" title="Kontrol Menu User ({{ $activeMenusCount ?? 6 }}/8 Aktif)"
                        class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-50 cursor-pointer">
                    <div class="nav-item-content flex items-center gap-2.5 truncate">
                        <svg class="w-4 h-4 text-violet-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                        <span class="sidebar-text truncate">Kontrol Menu User</span>
                    </div>
                    <span class="sidebar-badge text-[10px] px-1.5 py-0.5 rounded-md bg-violet-50 text-violet-700 font-bold font-mono">{{ $activeMenusCount ?? 6 }}/8</span>
                </button>
            </div>
        </div>

        <!-- GRUP 2: KOMUNIKASI & INFORMASI -->
        <div>
            <div class="sidebar-group-header px-3 mb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
                <span class="sidebar-text sidebar-group-title">Informasi &amp; Info Publik</span>
                <span class="sidebar-group-dot w-1.5 h-1.5 rounded-full bg-amber-500" title="Informasi &amp; Info Publik"></span>
            </div>
            <div class="space-y-1">
                <button type="button" onclick="switchSidebarMenu('banner')" id="menu-btn-banner" title="Kontrol Banner Info ({{ count($bannersList ?? []) }} Banner)"
                        class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-50 cursor-pointer">
                    <div class="nav-item-content flex items-center gap-2.5 truncate">
                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        <span class="sidebar-text truncate">Kontrol Banner Info</span>
                    </div>
                    <span class="sidebar-badge text-[10px] px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-700 font-bold font-mono">
                        {{ count($bannersList ?? []) }} Banner
                    </span>
                </button>
            </div>
        </div>

        <!-- GRUP 3: MASTER TARIF & PAJAK -->
        <div>
            <div class="sidebar-group-header px-3 mb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
                <span class="sidebar-text sidebar-group-title">Master Tarif &amp; Pajak</span>
                <span class="sidebar-group-dot w-1.5 h-1.5 rounded-full bg-teal-500" title="Master Tarif &amp; Pajak"></span>
            </div>
            <div class="space-y-1">
                <button type="button" onclick="switchSidebarMenu('tarif')" id="menu-btn-tarif" title="Tarif PKB, STNK & BBN 2"
                        class="nav-item w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-50 cursor-pointer">
                    <div class="nav-item-content flex items-center gap-2.5 truncate">
                        <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span class="sidebar-text truncate">Tarif PKB, STNK &amp; BBN 2</span>
                    </div>
                </button>

                <button type="button" onclick="switchSidebarMenu('simulasi')" id="menu-btn-simulasi" title="Simulasi Pajak & Amnesti"
                        class="nav-item w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-50 cursor-pointer">
                    <div class="nav-item-content flex items-center gap-2.5 truncate">
                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span class="sidebar-text truncate">Simulasi Pajak &amp; Amnesti</span>
                    </div>
                </button>

                <button type="button" onclick="switchSidebarMenu('swdkllj')" id="menu-btn-swdkllj" title="Tarif SWDKLLJ & Denda"
                        class="nav-item w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-50 cursor-pointer">
                    <div class="nav-item-content flex items-center gap-2.5 truncate">
                        <svg class="w-4 h-4 text-cyan-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span class="sidebar-text truncate">Tarif SWDKLLJ &amp; Denda</span>
                    </div>
                </button>

                <button type="button" onclick="switchSidebarMenu('bobot')" id="menu-btn-bobot" title="Bobot Kendaraan"
                        class="nav-item w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-50 cursor-pointer">
                    <div class="nav-item-content flex items-center gap-2.5 truncate">
                        <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                        <span class="sidebar-text truncate">Bobot Kendaraan</span>
                    </div>
                </button>

                <button type="button" onclick="switchSidebarMenu('klasifikasi')" id="menu-btn-klasifikasi" title="Klasifikasi Kendaraan ({{ $stats['total_klasifikasi'] ?? 0 }})"
                        class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-50 cursor-pointer">
                    <div class="nav-item-content flex items-center gap-2.5 truncate">
                        <svg class="w-4 h-4 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <span class="sidebar-text truncate">Klasifikasi Kendaraan</span>
                    </div>
                    <span class="sidebar-badge text-[10px] px-1.5 py-0.5 rounded-md bg-purple-50 text-purple-700 font-bold font-mono">{{ $stats['total_klasifikasi'] ?? 0 }}</span>
                </button>
            </div>
        </div>

        <!-- GRUP 4: BASIS DATA NILAI JUAL -->
        <div>
            <div class="sidebar-group-header px-3 mb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
                <span class="sidebar-text sidebar-group-title">Basis Data Nilai Jual</span>
                <span class="sidebar-group-dot w-1.5 h-1.5 rounded-full bg-blue-500" title="Basis Data Nilai Jual"></span>
            </div>
            <div class="space-y-1">
                <button type="button" onclick="switchSidebarMenu('njkb')" id="menu-btn-njkb" title="Katalog NJKB & NJUB ({{ number_format($stats['total_njkb'] ?? 0, 0, ',', '.') }})"
                        class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-50 cursor-pointer">
                    <div class="nav-item-content flex items-center gap-2.5 truncate">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                        <span class="sidebar-text truncate">Katalog NJKB &amp; NJUB</span>
                    </div>
                    <span class="sidebar-badge text-[10px] px-1.5 py-0.5 rounded-md bg-blue-50 text-blue-700 font-bold font-mono">{{ number_format($stats['total_njkb'] ?? 0, 0, ',', '.') }}</span>
                </button>
            </div>
        </div>

        <!-- GRUP 5: PENGATURAN & KEAMANAN -->
        <div>
            <div class="sidebar-group-header px-3 mb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
                <span class="sidebar-text sidebar-group-title">Pengaturan &amp; Keamanan</span>
                <span class="sidebar-group-dot w-1.5 h-1.5 rounded-full bg-rose-500" title="Pengaturan &amp; Keamanan"></span>
            </div>
            <div class="space-y-1">
                <button type="button" onclick="switchSidebarMenu('amnesti')" id="menu-btn-amnesti" title="Amnesti & Relaksasi"
                        class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-50 cursor-pointer">
                    <div class="nav-item-content flex items-center gap-2.5 truncate">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span class="sidebar-text truncate">Amnesti &amp; Relaksasi</span>
                    </div>
                </button>

                <button type="button" onclick="switchSidebarMenu('sistem')" id="menu-btn-sistem" title="Gateway API & Sistem"
                        class="nav-item w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-50 cursor-pointer">
                    <div class="nav-item-content flex items-center gap-2.5 truncate">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="sidebar-text truncate">Gateway API &amp; Sistem</span>
                    </div>
                </button>

                <button type="button" onclick="switchSidebarMenu('keamanan')" id="menu-btn-keamanan" title="Keamanan Admin"
                        class="nav-item w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition-all text-slate-600 hover:text-slate-900 hover:bg-slate-50 cursor-pointer">
                    <div class="nav-item-content flex items-center gap-2.5 truncate">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span class="sidebar-text truncate">Keamanan Admin</span>
                    </div>
                </button>
            </div>
        </div>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer p-3 border-t border-slate-100 bg-slate-50/60 flex items-center justify-between text-[11px] text-slate-500">
        <span class="sidebar-text">Server NTT</span>
        <span class="font-mono font-bold text-emerald-600 flex items-center gap-1.5" title="Sistem Server Aktif">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
            <span class="sidebar-text">Online</span>
        </span>
    </div>
</aside>
