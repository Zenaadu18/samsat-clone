<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Dashboard Admin — Pajak.In NTT</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(241, 245, 249, 0.8); }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(203, 213, 225, 0.9); border-radius: 999px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(148, 163, 184, 1); }

        .brand-text-gradient {
            background: -webkit-linear-gradient(45deg, #059669 0%, #0d9488 50%, #14b8a6 100%) !important;
            background: linear-gradient(45deg, #059669 0%, #0d9488 50%, #14b8a6 100%) !important;
            -webkit-background-clip: text !important;
            background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            color: #059669;
            display: inline-block;
        }
        .brand-dot-gold {
            color: #f59e0b !important;
            -webkit-text-fill-color: #f59e0b !important;
            display: inline-block;
        }

        /* Anti Auto-Zoom pada Browser Mobile / iOS saat mengetik di input/select/textarea */
        @media screen and (max-width: 768px) {
            input, select, textarea {
                font-size: 16px !important;
            }
        }

        /* Konversi Tabel ke Card Responsif di Layar Mobile (< 768px) */
        @media screen and (max-width: 768px) {
            .table-to-card-container {
                overflow-x: visible !important;
                border: none !important;
                background: transparent !important;
                border-radius: 0 !important;
            }
            .table-to-card {
                display: block !important;
                width: 100% !important;
                border: none !important;
            }
            .table-to-card thead {
                display: none !important;
            }
            .table-to-card tbody {
                display: flex !important;
                flex-direction: column !important;
                gap: 0.875rem !important;
                background: transparent !important;
            }
            .table-to-card tbody tr {
                display: block !important;
                background-color: #ffffff !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 1.25rem !important;
                padding: 1rem !important;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05) !important;
            }
            .table-to-card tbody tr:hover {
                background-color: #ffffff !important;
            }
            .table-to-card tbody td {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                padding: 0.45rem 0 !important;
                border-bottom: 1px dashed #f1f5f9 !important;
                text-align: right !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
            .table-to-card tbody td:last-child {
                border-bottom: none !important;
                padding-bottom: 0 !important;
                padding-top: 0.65rem !important;
                justify-content: flex-end !important;
                gap: 0.5rem !important;
            }
            .table-to-card tbody td::before {
                content: attr(data-label);
                font-size: 10px !important;
                font-weight: 700 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.03em !important;
                color: #64748b !important;
                text-align: left !important;
                margin-right: 0.75rem !important;
                flex-shrink: 0 !important;
            }
            /* Stacked cells (misal pesan panjang, preview flyer, dll) */
            .table-to-card tbody td[data-stacked="true"] {
                flex-direction: column !important;
                align-items: flex-start !important;
                text-align: left !important;
            }
            .table-to-card tbody td[data-stacked="true"]::before {
                margin-bottom: 0.25rem !important;
            }
            .table-to-card tbody td[data-stacked="true"] > * {
                width: 100% !important;
                text-align: left !important;
            }
            /* Aksi / Action buttons */
            .table-to-card tbody td[data-action="true"] {
                flex-wrap: wrap !important;
                justify-content: flex-end !important;
            }
            /* Empty state cell */
            .table-to-card tbody td[colspan] {
                display: block !important;
                text-align: center !important;
                border-bottom: none !important;
                padding: 1.5rem 0.5rem !important;
            }
            .table-to-card tbody td[colspan]::before {
                display: none !important;
            }
        }

        /* Sidebar Minimized Desktop Mode (~76px) */
        @media (min-width: 768px) {
            #admin-sidebar.sidebar-minimized {
                width: 4.75rem !important; /* 76px */
            }
            #admin-sidebar.sidebar-minimized .sidebar-text,
            #admin-sidebar.sidebar-minimized .sidebar-badge,
            #admin-sidebar.sidebar-minimized .sidebar-group-title {
                display: none !important;
            }
            #admin-sidebar.sidebar-minimized .sidebar-group-header {
                justify-content: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            #admin-sidebar.sidebar-minimized .sidebar-group-dot {
                margin: 0 auto !important;
            }
            #admin-sidebar.sidebar-minimized .nav-item {
                justify-content: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                width: 2.75rem !important;
                height: 2.75rem !important;
                margin-left: auto !important;
                margin-right: auto !important;
                border-radius: 0.875rem !important;
            }
            #admin-sidebar.sidebar-minimized .nav-item-content {
                justify-content: center !important;
                gap: 0 !important;
            }
            #admin-sidebar.sidebar-minimized .sidebar-header {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
                justify-content: center !important;
            }
            #admin-sidebar.sidebar-minimized .sidebar-footer {
                justify-content: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            #admin-sidebar.sidebar-minimized #collapseChevron {
                transform: rotate(180deg);
            }
        }

        /* Word-Style Rich Banner Editor Canvas & User-Side Preview */
        .banner-editor-canvas {
            outline: none !important;
        }
        .banner-editor-canvas:empty:before,
        .banner-editor-canvas.is-empty:before {
            content: attr(data-placeholder);
            color: #94a3b8;
            pointer-events: none;
            display: block;
        }
        .banner-editor-canvas p, .banner-rich-content p {
            margin-bottom: 0.45rem;
            line-height: 1.625;
        }
        .banner-editor-canvas p:last-child, .banner-rich-content p:last-child {
            margin-bottom: 0;
        }
        .banner-editor-canvas ol, .banner-rich-content ol {
            list-style-type: decimal !important;
            padding-left: 1.4rem !important;
            margin: 0.5rem 0 !important;
        }
        .banner-editor-canvas ol li, .banner-rich-content ol li {
            margin-bottom: 0.35rem !important;
            padding-left: 0.2rem !important;
        }
        .banner-editor-canvas ul, .banner-rich-content ul {
            list-style-type: disc !important;
            padding-left: 1.4rem !important;
            margin: 0.5rem 0 !important;
        }
        .banner-editor-canvas ul li, .banner-rich-content ul li {
            margin-bottom: 0.35rem !important;
            padding-left: 0.2rem !important;
        }
        .banner-editor-canvas b, .banner-editor-canvas strong,
        .banner-rich-content b, .banner-rich-content strong {
            font-weight: 800 !important;
            color: #0f172a !important;
        }
        .banner-editor-canvas i, .banner-editor-canvas em,
        .banner-rich-content i, .banner-rich-content em {
            font-style: italic !important;
        }
        .banner-editor-canvas u, .banner-rich-content u {
            text-decoration: underline !important;
            text-underline-offset: 2px !important;
        }
        .banner-editor-canvas s, .banner-rich-content s {
            text-decoration: line-through !important;
        }
        .banner-editor-canvas [align="center"], .banner-rich-content [align="center"],
        .banner-editor-canvas [style*="text-align: center"], .banner-rich-content [style*="text-align: center"],
        .banner-editor-canvas [style*="text-align:center"], .banner-rich-content [style*="text-align:center"] {
            text-align: center !important;
        }
        .banner-editor-canvas [align="right"], .banner-rich-content [align="right"],
        .banner-editor-canvas [style*="text-align: right"], .banner-rich-content [style*="text-align: right"],
        .banner-editor-canvas [style*="text-align:right"], .banner-rich-content [style*="text-align:right"] {
            text-align: right !important;
        }
        .banner-editor-canvas [align="justify"], .banner-rich-content [align="justify"],
        .banner-editor-canvas [style*="text-align: justify"], .banner-rich-content [style*="text-align: justify"],
        .banner-editor-canvas [style*="text-align:justify"], .banner-rich-content [style*="text-align:justify"] {
            text-align: justify !important;
        }
        .banner-editor-canvas [align="left"], .banner-rich-content [align="left"],
        .banner-editor-canvas [style*="text-align: left"], .banner-rich-content [style*="text-align: left"],
        .banner-editor-canvas [style*="text-align:left"], .banner-rich-content [style*="text-align:left"] {
            text-align: left !important;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen font-sans flex flex-col selection:bg-emerald-500 selection:text-white">

    <!-- Header Navbar Atas -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200/90 px-4 sm:px-6 py-3 shadow-xs">
        <div class="flex items-center justify-between gap-4">
            <!-- Mobile Toggle, Desktop Minimize Toggle & Brand -->
            <div class="flex items-center gap-3">
                <!-- Mobile Drawer Toggle -->
                <button type="button" onclick="toggleSidebar()" class="md:hidden p-2 text-slate-500 hover:text-slate-800 rounded-xl hover:bg-slate-100 cursor-pointer" title="Menu Sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <!-- Desktop Minimize / Expand Toggle -->
                <button type="button" onclick="toggleSidebarMinimize()" 
                        class="hidden md:flex p-2 text-slate-500 hover:text-slate-800 rounded-xl hover:bg-slate-100 cursor-pointer transition-colors"
                        title="Perkecil / Perbesar Sidebar (Ctrl+B)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                </button>
                <div class="flex items-center gap-2">
                    <span class="font-extrabold text-sm sm:text-base text-slate-900 tracking-tight">Portal Manajemen <span class="brand-text-gradient">Pajak</span><span class="brand-dot-gold">.</span><span class="brand-text-gradient">In</span> NTT</span>
                    <span class="hidden sm:inline-block text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Pro Administrator
                    </span>
                </div>
            </div>

            <!-- Action Buttons Header -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Live Real-Time Clock WITA -->
                <div class="hidden lg:flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-[11px] font-mono font-bold" title="Waktu Nyata Sistem Aplikasi (Zona WITA - UTC+8)">
                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span id="realtimeClockWita">--:--:-- WITA</span>
                </div>

                <!-- Flush Cache -->
                <form action="{{ route('admin.clear_cache') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin me-refresh seluruh cache sistem & OPcache?');">
                    @csrf
                    <button type="submit" 
                            class="px-2.5 sm:px-3 py-1.5 sm:py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold border border-slate-200 transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs"
                            title="Hapus cache sistem & OPcache">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span class="hidden sm:inline">Flush Cache</span>
                    </button>
                </form>

                <!-- Buka Web User -->
                <a href="{{ url('/') }}" target="_blank"
                   class="px-2.5 sm:px-3 py-1.5 sm:py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold border border-slate-200 transition-all flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    <span class="hidden sm:inline">Web User</span>
                </a>

                <!-- Indikator Auto-Logout Timer -->
                <div class="flex items-center gap-1.5 px-2.5 py-1.5 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-[11px] font-mono font-bold" title="Sesi login otomatis berakhir jika tidak aktif selama 30 menit">
                    <svg class="w-3.5 h-3.5 text-amber-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span><span id="sessionTimerText">30:00</span></span>
                </div>

                <!-- User Profile & Logout -->
                <div class="flex items-center pl-2 border-l border-slate-200">
                    <a href="{{ route('admin.logout') }}" 
                       class="px-3 py-1.5 sm:py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span class="hidden sm:inline">Keluar</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container with Sidebar -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Sidebar Navigation -->
        @include('admin.sidebar')

        <!-- Content Area -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 custom-scrollbar">
            <!-- Pesan Notifikasi Sukses / Error -->
            @if(!empty($pesanStatus))
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between gap-3 text-xs sm:text-sm text-emerald-800 shadow-xs animate-fade-in">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span class="font-semibold">{{ $pesanStatus }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold cursor-pointer">&times;</button>
                </div>
            @endif

            @if(!empty($pesanError))
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center justify-between gap-3 text-xs sm:text-sm text-rose-800 shadow-xs animate-fade-in">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span class="font-semibold">{{ $pesanError }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold cursor-pointer">&times;</button>
                </div>
            @endif

            <!-- ===============================================================
                 SECTION 1: DAFTAR PENGGUNA (USERS)
                 =============================================================== -->
            <section id="section-users" class="admin-section space-y-4">
                <div class="bg-white border border-slate-200 rounded-3xl p-5 sm:p-7 shadow-xs space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </span>
                                Manajemen Pengguna Aplikasi <span class="brand-text-gradient">Pajak</span><span class="brand-dot-gold">.</span><span class="brand-text-gradient">In</span>
                            </h2>
                            <p class="text-xs text-slate-500 mt-1">Daftar pengguna terdaftar yang memiliki izin akses masuk ke aplikasi user.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" onclick="confirmResetAllUserSessions()" class="px-3.5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs" title="Putuskan seluruh sesi aktif pengguna (role user) agar user login kembali dan melihat banner terbaru">
                                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span>Reset Sesi Massal (Role User)</span>
                            </button>
                            <button type="button" onclick="openModalUser()" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-emerald-600/20 transition-all flex items-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Tambah Pengguna Baru</span>
                            </button>
                        </div>
                    </div>

                    <!-- Filter Bar Pengguna -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 bg-slate-50/70 p-3.5 rounded-2xl border border-slate-200/80">
                        <!-- Search Input -->
                        <div class="relative lg:col-span-2">
                            <input type="text" id="userSearchInput" oninput="debounceUserFilter()" placeholder="Cari username atau nama lengkap..."
                                   class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 pl-9 text-xs text-slate-900 focus:ring-2 focus:ring-emerald-500/30 focus:outline-none">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <!-- Filter UPTD -->
                        <div>
                            <select id="userUptdFilter" onchange="applyUserFilters()" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500/30 focus:outline-none cursor-pointer">
                                <option value="">Semua Asal UPTD</option>
                                @foreach($listUptd as $uptdOpt)
                                    <option value="{{ $uptdOpt }}">{{ $uptdOpt }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter Status -->
                        <div>
                            <select id="userStatusFilter" onchange="applyUserFilters()" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500/30 focus:outline-none cursor-pointer">
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="blocked">Diblokir</option>
                            </select>
                        </div>

                        <!-- Rows Per Page & Reset -->
                        <div class="flex items-center gap-2">
                            <select id="userPerPageSelect" onchange="changeUserPerPage(this.value)" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500/30 focus:outline-none cursor-pointer font-semibold">
                                <option value="10">10 / Hal</option>
                                <option value="25">25 / Hal</option>
                                <option value="50">50 / Hal</option>
                                <option value="all">Semua Data</option>
                            </select>
                            <button type="button" onclick="resetUserFilters()" class="p-2 bg-white hover:bg-slate-100 text-slate-600 hover:text-slate-900 rounded-xl border border-slate-200 transition-colors shrink-0 cursor-pointer" title="Reset Filter">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Tabel Users -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-200 table-to-card-container">
                        <table class="w-full text-left text-xs text-slate-700 table-to-card" id="tableUsers">
                            <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">ID</th>
                                    <th class="px-4 py-3">Username</th>
                                    <th class="px-4 py-3">Nama Asli / Lengkap</th>
                                    <th class="px-4 py-3">Asal UPTD</th>
                                    <th class="px-4 py-3">Role / Akses</th>
                                    <th class="px-4 py-3">Sesi Perangkat</th>
                                    <th class="px-4 py-3">Status Akses</th>
                                    <th class="px-4 py-3">Login Terakhir</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody" class="divide-y divide-slate-100 bg-white">
                                <!-- Konten pengguna dirender secara reaktif melalui JS renderUsersTable() -->
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-slate-400">
                                        Memuat data pengguna...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination & Summary Users -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs text-slate-600">
                        <div id="usersSummary" class="font-medium text-slate-500">
                            Menampilkan <span class="font-bold text-slate-800 font-mono" id="usersFrom">0</span> - <span class="font-bold text-slate-800 font-mono" id="usersTo">0</span> dari <span class="font-bold text-slate-800 font-mono" id="usersTotal">0</span> pengguna
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Tombol Pintas Tampilkan Semua -->
                            <button type="button" id="btnUserShowAll" onclick="toggleUserShowAll()" 
                                    class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition-all cursor-pointer">
                                Tampilkan Semua
                            </button>
                            <!-- Kontrol Halaman -->
                            <div id="usersPagination" class="flex items-center gap-1"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===============================================================
                 SECTION 2: RIWAYAT AKTIVITAS (AUDIT LOGS)
                 =============================================================== -->
            <section id="section-logs" class="admin-section hidden space-y-4">
                <div class="bg-white border border-slate-200 rounded-3xl p-5 sm:p-7 shadow-xs space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                <span class="p-1.5 bg-blue-50 text-blue-600 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </span>
                                Audit Log Riwayat Aktivitas Pengguna
                            </h2>
                            <p class="text-xs text-slate-500 mt-1">Pencatatan real-time seluruh aktivitas cek nopol, BBN 2, dan simulasi pajak dalam zona waktu WITA.</p>
                        </div>
                    </div>

                    <!-- Filter Bar Riwayat Aktivitas -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 bg-slate-50/70 p-3.5 rounded-2xl border border-slate-200/80">
                        <!-- Search Input -->
                        <div class="relative lg:col-span-2">
                            <input type="text" id="logSearchInput" oninput="debounceLogSearch(event)" placeholder="Cari nopol, user, nama, detail, IP..."
                                   class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 pl-9 text-xs text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:outline-none">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <!-- Filter Jenis Aktivitas -->
                        <div>
                            <select id="logActivityFilter" onchange="handleLogActivityChange(this.value)" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-blue-500/30 focus:outline-none cursor-pointer">
                                <option value="">Semua Jenis Aktivitas</option>
                                @foreach($distinctActivities ?? [] as $act)
                                    <option value="{{ $act }}">{{ $act }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Rows Per Page & Reset -->
                        <div class="flex items-center gap-2">
                            <select id="logPerPageSelect" onchange="changeLogPerPage(this.value)" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-blue-500/30 focus:outline-none cursor-pointer font-semibold">
                                <option value="15">15 / Hal</option>
                                <option value="25">25 / Hal</option>
                                <option value="50">50 / Hal</option>
                                <option value="all">Semua Data</option>
                            </select>
                            <button type="button" onclick="resetLogFilters()" class="p-2 bg-white hover:bg-slate-100 text-slate-600 hover:text-slate-900 rounded-xl border border-slate-200 transition-colors shrink-0 cursor-pointer" title="Reset Filter">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-200 table-to-card-container">
                        <table class="w-full text-left text-xs text-slate-700 table-to-card" id="tableLogs">
                            <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">Waktu (WITA)</th>
                                    <th class="px-4 py-3">Username &amp; Nama</th>
                                    <th class="px-4 py-3">Aktivitas</th>
                                    <th class="px-4 py-3">Detail Data</th>
                                    <th class="px-4 py-3">Total Biaya</th>
                                    <th class="px-4 py-3">IP Address</th>
                                </tr>
                            </thead>
                            <tbody id="logsTableBody" class="divide-y divide-slate-100 bg-white">
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                        Memuat riwayat aktivitas...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination & Summary Logs -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs text-slate-600">
                        <div id="logsSummary" class="font-medium text-slate-500">
                            Menampilkan <span class="font-bold text-slate-800 font-mono" id="logsFrom">0</span> - <span class="font-bold text-slate-800 font-mono" id="logsTo">0</span> dari <span class="font-bold text-slate-800 font-mono" id="logsTotal">0</span> riwayat
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Tombol Pintas Tampilkan Semua -->
                            <button type="button" id="btnLogShowAll" onclick="toggleLogShowAll()" 
                                    class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition-all cursor-pointer">
                                Tampilkan Semua
                            </button>
                            <!-- Kontrol Halaman -->
                            <div id="logsPagination" class="flex items-center gap-1"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===============================================================
                 SECTION 3: KONTROL BANNER INFORMASI (MULTI-BANNER SLIDER)
                 =============================================================== -->
            <section id="section-banner" class="admin-section hidden space-y-4">
                <div class="bg-white border border-slate-200 rounded-3xl p-5 sm:p-7 shadow-xs space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                <span class="p-1.5 bg-amber-50 text-amber-600 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                </span>
                                Manajemen Banner Informasi Pengguna
                            </h2>
                            <p class="text-xs text-slate-500 mt-1">
                                Kelola pengumuman berupa <b>Gambar (Poster/Flyer)</b>, <b>Pesan Teks</b>, atau <b>Kombinasi Keduanya</b>. Jika terdapat lebih dari 1 banner aktif, aplikasi user otomatis menampilkan slider interaktif dengan tombol navigasi dan gesture geser (swipe).
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <button type="button" onclick="confirmResetAllUserSessions()" class="px-3.5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs" title="Putuskan seluruh sesi aktif pengguna (role user) agar user login kembali dan melihat banner terbaru">
                                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span>Reset Sesi Massal User</span>
                            </button>
                            <button type="button" onclick="openModalBanner()" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-amber-600/20 transition-all flex items-center gap-2 cursor-pointer shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Tambah Banner Baru</span>
                            </button>
                        </div>
                    </div>

                    <!-- Petunjuk Ringkas -->
                    <div class="p-3.5 bg-amber-50/70 border border-amber-200/80 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-amber-900">
                        <div class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>
                                <b>Format Fleksibel:</b> Banner dapat berupa flyer poster penuh, teks pengumuman, atau keduanya. Pengguna akan melihat banner saat sesi login baru. Untuk memunculkan banner pengumuman terbaru kepada seluruh pengguna aktif secara langsung, gunakan tombol <b>Reset Sesi Massal User</b>.
                            </span>
                        </div>
                        <button type="button" onclick="confirmResetAllUserSessions()" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center gap-1.5 cursor-pointer whitespace-nowrap self-start sm:self-center shrink-0" title="Putuskan seluruh sesi aktif pengguna agar langsung logout">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>Reset Sesi Sekarang</span>
                        </button>
                    </div>

                    <!-- Daftar Banner (Tabel) -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-200 table-to-card-container">
                        <table class="w-full text-left text-xs text-slate-700 table-to-card">
                            <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 text-center w-16">Urutan</th>
                                    <th class="px-4 py-3 w-28">Preview</th>
                                    <th class="px-4 py-3">Judul &amp; Tipe</th>
                                    <th class="px-4 py-3">Isi Pesan / Konten</th>
                                    <th class="px-4 py-3">Tautan Luar</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($bannersList as $b)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <!-- Urutan Slide -->
                                        <td class="px-4 py-3 text-center font-mono font-bold text-slate-700" data-label="Urutan">
                                            <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-[11px]">
                                                #{{ $b->order_num }}
                                            </span>
                                        </td>

                                        <!-- Preview Thumbnail Gambar -->
                                        <td class="px-4 py-3" data-label="Preview">
                                            @if($b->image_path)
                                                <a href="{{ asset(ltrim($b->image_path, '/')) }}" target="_blank" class="block w-20 h-14 rounded-xl overflow-hidden border border-slate-200 bg-slate-100 group relative">
                                                    <img src="{{ asset(ltrim($b->image_path, '/')) }}" alt="Banner" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                                </a>
                                            @else
                                                <div class="w-20 h-14 rounded-xl border border-dashed border-slate-200 bg-slate-50 flex items-center justify-center text-slate-400 text-[10px] text-center p-1 font-semibold">
                                                    Teks Saja
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Judul & Tipe Badge -->
                                        <td class="px-4 py-3" data-label="Judul & Tipe">
                                            <div class="space-y-1">
                                                <span class="font-bold text-slate-900 block text-xs">
                                                    {{ $b->title ?: '(Tanpa Judul)' }}
                                                </span>
                                                @if($b->type === 'warning')
                                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                        Jatuh Tempo / Penting
                                                    </span>
                                                @elseif($b->type === 'promo')
                                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        Amnesti / Keringanan
                                                    </span>
                                                @else
                                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                        Informasi Umum
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Isi Pesan -->
                                        <td class="px-4 py-3 max-w-xs" data-label="Isi Pesan" data-stacked="true">
                                            <div class="text-slate-600 line-clamp-3 leading-relaxed text-xs" title="{{ strip_tags($b->content) }}">
                                                {!! nl2br(e($b->content ?: '-')) !!}
                                            </div>
                                        </td>

                                        <!-- Tautan Luar -->
                                        <td class="px-4 py-3" data-label="Tautan">
                                            @if($b->link_url)
                                                <a href="{{ $b->link_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline truncate block max-w-[140px] text-[11px]">
                                                    {{ $b->link_url }}
                                                </a>
                                            @else
                                                <span class="text-slate-400 text-[11px]">-</span>
                                            @endif
                                        </td>

                                        <!-- Status Tayang Toggle -->
                                        <td class="px-4 py-3 text-center" data-label="Status">
                                            <button type="button" 
                                                    onclick="toggleBannerStatus({{ $b->id }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold cursor-pointer transition-all {{ $b->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $b->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                                <span>{{ $b->is_active ? 'Aktif Tayang' : 'Nonaktif' }}</span>
                                            </button>
                                        </td>

                                        <!-- Aksi -->
                                        <td class="px-4 py-3 text-right space-x-1.5 whitespace-nowrap" data-label="Aksi" data-action="true">
                                            <button type="button" 
                                                    onclick="editBannerItem({{ json_encode($b) }})"
                                                    class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-[11px] font-semibold cursor-pointer">
                                                Edit
                                            </button>
                                            <button type="button" 
                                                    onclick="deleteBannerItem({{ $b->id }})"
                                                    class="px-2.5 py-1.5 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] font-semibold cursor-pointer">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-10 text-center text-slate-400">
                                            <div class="max-w-xs mx-auto space-y-2">
                                                <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <p class="font-bold text-slate-600">Belum Ada Banner</p>
                                                <p class="text-[11px] text-slate-400">Klik "Tambah Banner Baru" untuk memasang flyer poster atau pengumuman ke aplikasi pengguna.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>


            <!-- ===============================================================
                 SECTION: SIMULASI PAJAK & AMNESTI (ADMIN PANEL)
                 =============================================================== -->
            <section id="section-simulasi" class="admin-section hidden space-y-4">
                <div class="bg-white border border-slate-200 rounded-3xl p-5 sm:p-7 shadow-xs space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                <span class="p-1.5 bg-amber-50 text-amber-600 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </span>
                                Simulasi Komparasi Penetapan Pajak (Normal vs Pergub 64)
                            </h2>
                            <p class="text-xs text-slate-500 mt-1">
                                Perhitungan komparasi penetapan pajak kendaraan secara instan khusus untuk Administrator.
                            </p>
                        </div>
                    </div>

                    <!-- Input Simulasi -->
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 sm:p-5">
                        <form id="formAdminSimulasi" onsubmit="runAdminSimulasi(event)" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Polisi Kendaraan (NTT)</label>
                                    <input type="text" id="adminSimulasiNopol" placeholder="Contoh: DH 2565 LB" required
                                           class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-900 uppercase font-mono font-bold focus:ring-2 focus:ring-amber-500 focus:outline-none">
                                </div>
                                <div>
                                    <button type="submit" id="btnAdminSimulasi"
                                            class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-2 cursor-pointer shadow-xs">
                                        <span>Hitung Komparasi</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Hasil Simulasi -->
                    <div id="adminSimulasiResult" class="hidden space-y-4">
                        <div id="adminSimulasiContent"></div>
                    </div>
                </div>
            </section>

            <!-- ===============================================================
                 SECTION 4: TARIF PKB, STNK & BBN 2
                 =============================================================== -->
            <section id="section-tarif" class="admin-section hidden space-y-4">
                <div class="bg-white border border-slate-200 rounded-3xl p-5 sm:p-7 shadow-xs space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                <span class="p-1.5 bg-teal-50 text-teal-600 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </span>
                                Tarif Pokok PKB, PNBP STNK/TNKB, &amp; Bea Balik Nama (BBN 2)
                            </h2>
                            <p class="text-xs text-slate-500 mt-1">Tarif resmi Perda NTT No. 1/2024 dan PP 76/2020.</p>
                        </div>
                    </div>

                    <!-- Form Tarif PKB -->
                    <form action="{{ route('admin.save_pkb') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">1. Tarif Pajak Kendaraan Bermotor (PKB)</h3>
                            <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer">
                                Simpan Tarif PKB
                            </button>
                        </div>
                        <div class="overflow-x-auto rounded-2xl border border-slate-200 table-to-card-container">
                            <table class="w-full text-left text-xs text-slate-700 table-to-card">
                                <thead class="bg-slate-50 uppercase text-[10px] font-bold text-slate-500 border-b border-slate-200">
                                    <tr>
                                        <th class="px-4 py-3">Kode Guna</th>
                                        <th class="px-4 py-3">Tarif Lama</th>
                                        <th class="px-4 py-3">Tarif Baru (Perda 1/2024)</th>
                                        <th class="px-4 py-3">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white font-mono">
                                    @foreach($dataPkb as $pkb)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-4 py-2.5 font-bold text-slate-900" data-label="Kode Guna">{{ $pkb->k_guna }}</td>
                                            <td class="px-4 py-2.5" data-label="Tarif Lama">
                                                <input type="text" name="tarif_pkb[{{ $pkb->k_guna }}][lama]" value="{{ $pkb->tarif_lama }}" class="w-24 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs">
                                                <span class="text-slate-400">({{ (float)$pkb->tarif_lama * 100 }}%)</span>
                                            </td>
                                            <td class="px-4 py-2.5" data-label="Tarif Baru">
                                                <input type="text" name="tarif_pkb[{{ $pkb->k_guna }}][baru]" value="{{ $pkb->tarif_baru }}" class="w-24 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs font-bold text-emerald-700">
                                                <span class="text-emerald-700 font-bold">({{ (float)$pkb->tarif_baru * 100 }}%)</span>
                                            </td>
                                            <td class="px-4 py-2.5 font-sans text-slate-600" data-label="Keterangan">
                                                <input type="text" name="tarif_pkb[{{ $pkb->k_guna }}][keterangan]" value="{{ $pkb->keterangan }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <!-- Form Tarif STNK / TNKB -->
                    <form action="{{ route('admin.save_stnk') }}" method="POST" class="space-y-4 pt-4 border-t border-slate-100">
                        @csrf
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">2. Tarif Penerbitan STNK &amp; TNKB (PP 76/2020)</h3>
                            <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer">
                                Simpan Tarif STNK
                            </button>
                        </div>
                        <div class="overflow-x-auto rounded-2xl border border-slate-200 table-to-card-container">
                            <table class="w-full text-left text-xs text-slate-700 table-to-card">
                                <thead class="bg-slate-50 uppercase text-[10px] font-bold text-slate-500 border-b border-slate-200">
                                    <tr>
                                        <th class="px-4 py-3">Golongan Kendaraan</th>
                                        <th class="px-4 py-3">Biaya STNK (Rp)</th>
                                        <th class="px-4 py-3">Biaya TNKB (Rp)</th>
                                        <th class="px-4 py-3">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white font-mono">
                                    @foreach($dataStnk as $stnk)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-4 py-2.5 font-bold text-slate-900" data-label="Golongan">{{ $stnk->jenis }}</td>
                                            <td class="px-4 py-2.5" data-label="Biaya STNK">
                                                <input type="number" name="tarif_stnk[{{ $stnk->jenis }}][stnk]" value="{{ $stnk->biaya_stnk }}" class="w-32 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs">
                                            </td>
                                            <td class="px-4 py-2.5" data-label="Biaya TNKB">
                                                <input type="number" name="tarif_stnk[{{ $stnk->jenis }}][tnkb]" value="{{ $stnk->biaya_tnkb }}" class="w-32 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs">
                                            </td>
                                            <td class="px-4 py-2.5 font-sans text-slate-600" data-label="Keterangan">
                                                <input type="text" name="tarif_stnk[{{ $stnk->jenis }}][keterangan]" value="{{ $stnk->keterangan }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <!-- Form Parameter BBN 2 -->
                    <form action="{{ route('admin.save_config') }}" method="POST" class="space-y-4 pt-4 border-t border-slate-100">
                        @csrf
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">3. Tarif Penerbitan BPKB (PP 76/2020) &amp; Jasa Cek Fisik Bantuan (BBN 2)</h3>
                                <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer">
                                    Simpan Parameter BBN 2
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-0.5">Penetapan tarif BPKB mengacu pada PP No. 76 Tahun 2020 (Roda 2/3: Rp 225.000, Roda 4+: Rp 375.000).</p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            @foreach($grupTarifDasar as $row)
                                @if(str_contains($row['kunci'], 'bpkb') || str_contains($row['kunci'], 'tembak'))
                                    @include('admin._field', ['row' => $row])
                                @endif
                            @endforeach
                        </div>
                    </form>
                </div>
            </section>

            <!-- ===============================================================
                 SECTION 5: TARIF SWDKLLJ
                 =============================================================== -->
            <section id="section-swdkllj" class="admin-section hidden space-y-4">
                <form action="{{ route('admin.save_swdkllj') }}" method="POST">
                    @csrf
                    <div class="bg-white border border-slate-200 rounded-3xl p-5 sm:p-7 shadow-xs space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                    <span class="p-1.5 bg-cyan-50 text-cyan-600 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    </span>
                                    Tarif Pokok SWDKLLJ &amp; Denda Progresif Jasa Raharja
                                </h2>
                                <p class="text-xs text-slate-500 mt-1">PMK No. 16/PMK.010/2017 tentang Tarif Sumbangan Wajib Dana Kecelakaan Lalu Lintas Jalan.</p>
                            </div>
                            <button type="submit" class="px-5 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Simpan SWDKLLJ</span>
                            </button>
                        </div>

                        <div class="overflow-x-auto rounded-2xl border border-slate-200 table-to-card-container">
                            <table class="w-full text-left text-xs text-slate-700 table-to-card">
                                <thead class="bg-slate-50 uppercase text-[10px] font-bold text-slate-500 border-b border-slate-200">
                                    <tr>
                                        <th class="px-4 py-3">Golongan</th>
                                        <th class="px-4 py-3">Pokok (Rp)</th>
                                        <th class="px-4 py-3">Biaya Admin</th>
                                        <th class="px-4 py-3">Denda T1</th>
                                        <th class="px-4 py-3">Denda T2</th>
                                        <th class="px-4 py-3">Denda T3</th>
                                        <th class="px-4 py-3">Denda T4</th>
                                        <th class="px-4 py-3">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white font-mono">
                                    @foreach($dataSwdkllj as $sw)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-4 py-2.5 font-bold text-slate-900" data-label="Golongan">{{ $sw->golongan }}</td>
                                            <td class="px-4 py-2.5" data-label="Pokok"><input type="number" name="tarif_swdkllj[{{ $sw->golongan }}][tarif]" value="{{ $sw->tarif }}" class="w-24 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs"></td>
                                            <td class="px-4 py-2.5" data-label="Admin"><input type="number" name="tarif_swdkllj[{{ $sw->golongan }}][admin]" value="{{ $sw->admin }}" class="w-20 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs"></td>
                                            <td class="px-4 py-2.5" data-label="Denda T1"><input type="number" name="tarif_swdkllj[{{ $sw->golongan }}][t1]" value="{{ $sw->denda_t1 }}" class="w-20 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs"></td>
                                            <td class="px-4 py-2.5" data-label="Denda T2"><input type="number" name="tarif_swdkllj[{{ $sw->golongan }}][t2]" value="{{ $sw->denda_t2 }}" class="w-20 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs"></td>
                                            <td class="px-4 py-2.5" data-label="Denda T3"><input type="number" name="tarif_swdkllj[{{ $sw->golongan }}][t3]" value="{{ $sw->denda_t3 }}" class="w-20 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs"></td>
                                            <td class="px-4 py-2.5" data-label="Denda T4"><input type="number" name="tarif_swdkllj[{{ $sw->golongan }}][t4]" value="{{ $sw->denda_t4 }}" class="w-20 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs"></td>
                                            <td class="px-4 py-2.5 font-sans text-slate-600" data-label="Keterangan"><input type="text" name="tarif_swdkllj[{{ $sw->golongan }}][keterangan]" value="{{ $sw->keterangan }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </section>

            <!-- ===============================================================
                 SECTION 6: BOBOT KOEFISIEN KENDARAAN
                 =============================================================== -->
            <section id="section-bobot" class="admin-section hidden space-y-4">
                <form action="{{ route('admin.save_config') }}" method="POST">
                    @csrf
                    <div class="bg-white border border-slate-200 rounded-3xl p-5 sm:p-7 shadow-xs space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                    <span class="p-1.5 bg-indigo-50 text-indigo-600 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                                    </span>
                                    Bobot Koefisien Kendaraan (Permendagri)
                                </h2>
                                <p class="text-xs text-slate-500 mt-1">Koefisien pengali bobot NJKB berdasarkan kategori dan fungsi kendaraan.</p>
                            </div>
                            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Simpan Bobot</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($grupBobot as $row)
                                @include('admin._field', ['row' => $row])
                            @endforeach
                        </div>
                    </div>
                </form>
            </section>

            <!-- ===============================================================
                 SECTION 7: KLASIFIKASI KENDARAAN
                 =============================================================== -->
            <section id="section-klasifikasi" class="admin-section hidden space-y-4">
                <div class="bg-white border border-slate-200 rounded-3xl p-5 sm:p-7 shadow-xs space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                <span class="p-1.5 bg-purple-50 text-purple-600 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                </span>
                                Aturan Klasifikasi Jenis Kendaraan &amp; SWDKLLJ
                            </h2>
                            <p class="text-xs text-slate-500 mt-1">Pemetaan otomatis kata kunci pada tipe kendaraan ke golongan SWDKLLJ dan STNK/TNKB.</p>
                        </div>
                    </div>

                    <!-- Form Tambah Klasifikasi -->
                    <form action="{{ route('admin.save_klasifikasi') }}" method="POST" class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="text-[11px] font-bold text-slate-600 block mb-1">Kata Kunci (Keyword Tipe)</label>
                                <input type="text" name="keyword" placeholder="Contoh: AVANZA, BEAT, TRUK" required
                                       class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs uppercase font-mono">
                            </div>
                            <div>
                                <label class="text-[11px] font-bold text-slate-600 block mb-1">Golongan SWDKLLJ</label>
                                <select name="golongan_swdkllj" required class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs">
                                    @foreach($dataSwdkllj as $sw)
                                        <option value="{{ $sw->golongan }}">{{ $sw->golongan }} - {{ $sw->keterangan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-[11px] font-bold text-slate-600 block mb-1">Jenis PNBP STNK</label>
                                <select name="jenis_kend" required class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs">
                                    @foreach($dataStnk as $stnk)
                                        <option value="{{ $stnk->jenis }}">{{ $stnk->jenis }} - {{ $stnk->keterangan }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer">
                                + Tambah Aturan Klasifikasi
                            </button>
                        </div>
                    </form>

                    <!-- Daftar Klasifikasi -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-200 max-h-96 custom-scrollbar table-to-card-container">
                        <table class="w-full text-left text-xs text-slate-700 table-to-card">
                            <thead class="bg-slate-50 uppercase text-[10px] font-bold text-slate-500 border-b border-slate-200 sticky top-0">
                                <tr>
                                    <th class="px-4 py-3">Keyword Tipe</th>
                                    <th class="px-4 py-3">Golongan SWDKLLJ</th>
                                    <th class="px-4 py-3">Jenis STNK/TNKB</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white font-mono">
                                @foreach($dataKlasifikasi as $klas)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-2 font-bold text-slate-900" data-label="Keyword">{{ $klas->keyword }}</td>
                                        <td class="px-4 py-2" data-label="SWDKLLJ"><span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold">{{ $klas->golongan_swdkllj }}</span></td>
                                        <td class="px-4 py-2" data-label="STNK/TNKB"><span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold">{{ $klas->jenis_kend }}</span></td>
                                        <td class="px-4 py-2 text-right" data-label="Aksi" data-action="true">
                                            <form action="{{ route('admin.delete_klasifikasi') }}" method="POST" class="inline" onsubmit="return confirm('Hapus klasifikasi ini?');">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $klas->id }}">
                                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold text-[11px] cursor-pointer">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- ===============================================================
                 SECTION 8: KATALOG NJKB & NJUB
                 =============================================================== -->
            <section id="section-njkb" class="admin-section hidden space-y-4">
                <div class="bg-white border border-slate-200 rounded-3xl p-5 sm:p-7 shadow-xs space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                <span class="p-1.5 bg-blue-50 text-blue-600 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                                </span>
                                Basis Data Nilai Jual Kendaraan Bermotor (NJKB)
                            </h2>
                            <p class="text-xs text-slate-500 mt-1">Data master {{ number_format($stats['total_njkb'], 0, ',', '.') }} kendaraan untuk perhitungan PKB dasar.</p>
                        </div>
                        <button type="button" onclick="openModalNjkb()" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Tambah NJKB Baru</span>
                        </button>
                    </div>

                    <!-- Search Input NJKB -->
                    <div class="relative">
                        <input type="text" id="njkbSearchInput" oninput="handleNjkbSearch(event)" placeholder="Ketik minimal 2 huruf merk atau tipe (mis. AVANZA, HONDA BEAT, INNOVA)..."
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 pl-10 text-xs sm:text-sm text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:bg-white focus:outline-none">
                        <svg class="w-5 h-5 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-200 table-to-card-container">
                        <table class="w-full text-left text-xs text-slate-700 table-to-card">
                            <thead class="bg-slate-50 uppercase text-[10px] font-bold text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">Merk</th>
                                    <th class="px-4 py-3">Tipe Kendaraan</th>
                                    <th class="px-4 py-3">Tahun</th>
                                    <th class="px-4 py-3">Bobot</th>
                                    <th class="px-4 py-3">Nilai Jual (Rp)</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="njkbTableBody" class="divide-y divide-slate-100 bg-white font-mono">
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400 font-sans">
                                        Ketik nama merk atau tipe kendaraan pada kolom pencarian di atas untuk memuat data.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- ===============================================================
                 SECTION 9: AMNESTI & RELAKSASI PAJAK
                 =============================================================== -->
            <section id="section-amnesti" class="admin-section hidden space-y-4">
                <form action="{{ route('admin.save_config') }}" method="POST">
                    @csrf
                    <div class="bg-white border border-slate-200 rounded-3xl p-5 sm:p-7 shadow-xs space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                    <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    </span>
                                    Parameter Program Amnesti &amp; Relaksasi Pajak NTT
                                </h2>
                                <p class="text-xs text-slate-500 mt-1">Konfigurasi pembebasan denda, diskon pokok berjalan, dan penghapusan tunggakan PKB/SWDKLLJ.</p>
                            </div>
                            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Simpan Amnesti</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($grupAmnesti as $row)
                                @include('admin._field', ['row' => $row])
                            @endforeach
                        </div>
                    </div>
                </form>
            </section>

            <!-- ===============================================================
                 SECTION 10: GATEWAY API & SISTEM GLOBAL
                 =============================================================== -->
            <section id="section-sistem" class="admin-section hidden space-y-4">
                <form action="{{ route('admin.save_config') }}" method="POST">
                    @csrf
                    <div class="bg-white border border-slate-200 rounded-3xl p-5 sm:p-7 shadow-xs space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                    <span class="p-1.5 bg-amber-50 text-amber-600 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </span>
                                    Gateway Data Samsat &amp; Parameter Global
                                </h2>
                                <p class="text-xs text-slate-500 mt-1">Konfigurasi URL API Samsat Utama, API Cadangan, Jam Operasional, dan Rasio Opsen.</p>
                            </div>
                            <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Simpan Sistem</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($grupSistem as $row)
                                @include('admin._field', ['row' => $row])
                            @endforeach

                            @foreach($grupTarifDasar as $row)
                                @if(!str_contains($row['kunci'], 'bpkb') && !str_contains($row['kunci'], 'tembak'))
                                    @include('admin._field', ['row' => $row])
                                @endif
                            @endforeach
                        </div>
                    </div>
                </form>
            </section>

            <!-- ===============================================================
                 SECTION 10B: KONTROL MENU & FITUR USER (PWA)
                 =============================================================== -->
            <section id="section-menus" class="admin-section hidden space-y-4">
                <form action="{{ route('admin.save_config') }}" method="POST">
                    @csrf
                    <input type="hidden" name="active_section" value="menus">
                    <div class="bg-white border border-slate-200 rounded-3xl p-5 sm:p-7 shadow-xs space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                                    <span class="p-1.5 bg-violet-50 text-violet-600 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                    </span>
                                    Kontrol Visibilitas Menu &amp; Tombol User
                                </h2>
                                <p class="text-xs text-slate-500 mt-1">Atur menu apa saja yang muncul untuk pengguna biasa (role <strong>user</strong>). Akun role <strong>admin</strong> di aplikasi tetap dapat melihat seluruh menu untuk pengujian.</p>
                            </div>
                            <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Simpan Menu User</span>
                            </button>
                        </div>

                        <div class="bg-blue-50/70 border border-blue-200/80 rounded-2xl p-3.5 text-xs text-blue-800 flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div>
                                Pengaturan ini hanya mengontrol tampilan UI &amp; akses endpoint fitur untuk user non-admin. <strong>Perhitungan pajak, tarif, amnesti, dan SWDKLLJ tetap identik dan tidak terpengaruh.</strong> Tab <em>Beranda</em> selalu tampil sebagai halaman utama.
                            </div>
                        </div>

                        @php
                            $menuItems = [
                                ['kunci' => 'menu_cek', 'judul' => 'Menu Cek Pajak Nopol', 'desk' => 'Menampilkan tab & card Cek Pajak, form cari cepat di Beranda, serta shortcut Cek Pajak di tab lain.', 'grup' => 'Menu Publik / User'],
                                ['kunci' => 'menu_prontt', 'judul' => 'Menu Pembayaran PRO NTT', 'desk' => 'Menampilkan tab & card PRO NTT untuk pembuatan kode bayar dan refresh status pembayaran Bank NTT.', 'grup' => 'Menu Publik / User'],
                                ['kunci' => 'fitur_tombol_bayar_prontt', 'judul' => 'Tombol "Bayar lewat PRO NTT" (di Cek Pajak)', 'desk' => 'Menampilkan tombol biru "Bayar lewat PRO NTT" di hasil Cek Pajak (otomatis ikut sembunyi bila Menu PRO NTT OFF).', 'grup' => 'Aksi Halaman Cek Pajak'],
                                ['kunci' => 'menu_bbn2', 'judul' => 'Menu Balik Nama (BBN 2)', 'desk' => 'Menampilkan tab, tombol Hero Beranda & card simulasi Bea Balik Nama ke-2.', 'grup' => 'Menu Publik / User'],
                                ['kunci' => 'menu_riwayat', 'judul' => 'Menu Riwayat Aktivitas', 'desk' => 'Menampilkan tab & card riwayat pencarian nopol pengguna.', 'grup' => 'Menu Publik / User'],
                                ['kunci' => 'menu_info', 'judul' => 'Menu Ketentuan Relaksasi / Info', 'desk' => 'Menampilkan card informasi ketentuan Pergub & relaksasi pajak.', 'grup' => 'Menu Publik / User'],
                                ['kunci' => 'menu_simulasi', 'judul' => 'Menu Simulasi Pajak (User Non-Admin)', 'desk' => 'Default NONAKTIF (khusus Admin PWA). Aktifkan jika ingin membuka menu Simulasi ke user biasa juga.', 'grup' => 'Fitur Lanjutan'],
                                ['kunci' => 'menu_njkb', 'judul' => 'Menu Katalog NJKB (User Non-Admin)', 'desk' => 'Default NONAKTIF (khusus Admin PWA). Aktifkan jika ingin membuka Katalog NJKB ke user biasa juga.', 'grup' => 'Fitur Lanjutan'],
                            ];
                        @endphp

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($menuItems as $mItem)
                                @php
                                    $mKunci = $mItem['kunci'];
                                    $mVal   = (string)($grupMenu[$mKunci] ?? '0');
                                    $isOn   = in_array(strtolower(trim($mVal)), ['1', 'true', 'on', 'yes'], true);
                                @endphp
                                <div class="bg-slate-50/60 border border-slate-200/90 rounded-2xl p-4 flex items-start justify-between gap-4 hover:border-violet-300 transition-colors">
                                    <div class="space-y-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="text-xs sm:text-sm font-bold text-slate-900">{{ $mItem['judul'] }}</span>
                                            <span class="text-[10px] font-mono text-slate-500 bg-white px-1.5 py-0.5 rounded border border-slate-200">{{ $mKunci }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $mItem['desk'] }}</p>
                                    </div>
                                    <div class="flex flex-col items-end gap-1.5 shrink-0 pt-0.5">
                                        <input type="hidden" name="config[{{ $mKunci }}]" value="0">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox"
                                                   id="{{ $mKunci }}"
                                                   name="config[{{ $mKunci }}]"
                                                   value="1"
                                                   class="sr-only peer"
                                                   {{ $isOn ? 'checked' : '' }}
                                                   onchange="updateToggleLabel(this)">
                                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                        </label>
                                        <span class="text-[10px] font-bold font-mono transition-colors {{ $isOn ? 'text-emerald-600' : 'text-slate-400' }}" id="label-{{ $mKunci }}">
                                            {{ $isOn ? 'AKTIF' : 'NONAKTIF' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </form>
            </section>

            <!-- ===============================================================
                 SECTION 11: KEAMANAN & AKUN ADMIN
                 =============================================================== -->
            <section id="section-keamanan" class="admin-section hidden space-y-4">
                <div class="max-w-xl mx-auto bg-white border border-slate-200 rounded-3xl p-5 sm:p-7 shadow-xs space-y-5">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                            <span class="p-1.5 bg-rose-50 text-rose-600 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </span>
                            Ganti Password Administrator
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">Akun admin aktif: <strong class="text-slate-900 font-mono">{{ $adminUsername }}</strong></p>
                    </div>

                    <form action="{{ route('admin.change_password') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password Saat Ini</label>
                            <input type="password" name="current_password" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password Baru (Min 5 Karakter)</label>
                            <input type="password" name="new_password" required minlength="5"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" required minlength="5"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:ring-2 focus:ring-rose-500 focus:bg-white focus:outline-none">
                        </div>

                        <div class="pt-2">
                            <button type="submit" 
                                    class="w-full py-3 bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Perbarui Password Admin</span>
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal Form Tambah / Edit User -->
    <div id="modalUser" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white border border-slate-200 w-full max-w-md rounded-3xl p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 id="modalUserTitle" class="text-base font-bold text-slate-900">Tambah Pengguna Baru</h3>
                <button type="button" onclick="closeModalUser()" class="text-slate-400 hover:text-slate-700 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('admin.save_user') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" id="modalUserId" name="user_id">

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Username Login</label>
                    <input type="text" id="modalUserUsername" name="username" placeholder="Contoh: agus_kupang" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-900 font-mono focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Asli / Lengkap Pengguna</label>
                    <input type="text" id="modalUserName" name="name" placeholder="Contoh: Agustinus Fernandez" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Asal UPTD <span class="font-normal text-slate-400">(Pemetaan Lokasi)</span></label>
                    <select id="modalUserUptd" name="uptd" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:bg-white font-semibold">
                        @foreach($listUptd as $upt)
                            <option value="{{ $upt }}">{{ $upt }}</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1">Pilih Kantor Pusat atau salah satu UPTD dari 22 Kab/Kota di NTT.</p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Role / Hak Akses</label>
                    <select id="modalUserRole" name="role" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:bg-white font-semibold">
                        <option value="user">Pengguna Biasa (Hanya Cek Pajak, BBN 2, Riwayat)</option>
                        <option value="admin">Administrator (Akses Penuh termasuk Simulasi & NJKB)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" id="modalUserPassword" name="password" placeholder="Kosongkan jika tidak ingin mengubah password saat edit"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-3.5 pr-11 py-2.5 text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:bg-white">
                        <button type="button" 
                                onclick="togglePasswordVisibility('modalUserPassword', 'iconEyeOpenUser', 'iconEyeClosedUser')"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer focus:outline-none transition-colors"
                                title="Tampilkan / Sembunyikan Password"
                                aria-label="Toggle password visibility">
                            <!-- Eye Open -->
                            <svg id="iconEyeOpenUser" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <!-- Eye Closed / Slash -->
                            <svg id="iconEyeClosedUser" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    <p id="modalUserPassHint" class="text-[10px] text-slate-400 mt-1">Minimal 4 karakter.</p>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-800 block text-xs">Blokir Pengguna Ini</span>
                        <span class="text-[10px] text-slate-500">Jika dicentang, pengguna tidak bisa login ke aplikasi</span>
                    </div>
                    <input type="checkbox" id="modalUserBlocked" name="is_blocked" value="1" class="w-4 h-4 text-rose-600 rounded">
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                    <button type="button" onclick="closeModalUser()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <span>Simpan Pengguna</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form Tambah / Edit NJKB -->
    <div id="modalNjkb" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white border border-slate-200 w-full max-w-lg rounded-3xl p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 id="modalNjkbTitle" class="text-base font-bold text-slate-900">Tambah Data NJKB</h3>
                <button type="button" onclick="closeModalNjkb()" class="text-slate-400 hover:text-slate-700 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form id="formNjkb" onsubmit="submitFormNjkb(event)" class="space-y-3.5 text-xs">
                <input type="hidden" id="modalNjkbId" name="id">

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Merk Kendaraan</label>
                    <input type="text" id="modalNjkbMerk" name="merk" placeholder="Contoh: TOYOTA, HONDA, MITSUBISHI" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-900 uppercase focus:ring-2 focus:ring-sky-500 focus:bg-white font-mono">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tipe Kendaraan</label>
                    <input type="text" id="modalNjkbTipe" name="tipe" placeholder="Contoh: AVANZA 1.3 G M/T" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-900 uppercase focus:ring-2 focus:ring-sky-500 focus:bg-white font-mono">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tahun Pembuatan</label>
                        <input type="number" id="modalNjkbTahun" name="tahun_buat" min="1980" max="2035" value="{{ date('Y') }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-900 focus:ring-2 focus:ring-sky-500 focus:bg-white font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Bobot Koefisien</label>
                        <input type="number" id="modalNjkbBobot" name="bobot" step="0.001" min="0.5" max="2.0" value="1.000" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-900 focus:ring-2 focus:ring-sky-500 focus:bg-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nilai NJKB (Rp)</label>
                    <input type="number" id="modalNjkbNilai" name="njkb" min="100000" step="50000" placeholder="Contoh: 150000000" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-900 focus:ring-2 focus:ring-sky-500 focus:bg-white font-mono font-bold">
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                    <button type="button" onclick="closeModalNjkb()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold cursor-pointer">Batal</button>
                    <button type="submit" id="btnSaveNjkb" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl font-bold flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <span>Simpan Data</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form Tambah / Edit Banner (Multi-Banner Slider) -->
    <div id="modalBanner" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white border border-slate-200 w-full max-w-lg rounded-3xl p-6 shadow-2xl space-y-4 max-h-[92vh] flex flex-col">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 shrink-0">
                <h3 id="modalBannerTitle" class="text-base font-bold text-slate-900">Tambah Banner Baru</h3>
                <button type="button" onclick="closeModalBanner()" class="text-slate-400 hover:text-slate-700 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form id="formBanner" action="{{ route('admin.save_banner_item') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5 text-xs overflow-y-auto pr-1 custom-scrollbar">
                @csrf
                <input type="hidden" id="modalBannerId" name="banner_id">
                <input type="hidden" id="modalBannerRemoveImage" name="remove_image" value="0">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">Judul Banner <span class="font-normal text-slate-400">(Opsional)</span></label>
                        <input type="text" id="modalBannerTitleInput" name="title" placeholder="Contoh: Pemutihan Pajak Kendaraan 2026"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-900 focus:ring-2 focus:ring-amber-500 focus:bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Urutan Slide</label>
                        <input type="number" id="modalBannerOrder" name="order_num" min="0" value="0" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-900 font-mono focus:ring-2 focus:ring-amber-500 focus:bg-white">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tipe Banner / Warna Aksen</label>
                    <select id="modalBannerType" name="type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-900 focus:ring-2 focus:ring-amber-500 focus:bg-white">
                        <option value="info">Informasi Umum (Aksen Biru)</option>
                        <option value="warning">Pemberitahuan Penting / Jatuh Tempo (Aksen Amber)</option>
                        <option value="promo">Program Keringanan / Amnesti (Aksen Hijau)</option>
                    </select>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block font-bold text-slate-700">Isi Pesan / Informasi Teks <span class="font-normal text-slate-400">(Opsional jika poster saja)</span></label>
                        <button type="button" onclick="toggleBannerTextPreview()" id="btnToggleBannerPreview" class="text-[11px] font-bold text-amber-700 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 px-2.5 py-1 rounded-lg border border-amber-200/80 flex items-center gap-1 cursor-pointer transition-colors shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Preview Tampilan</span>
                        </button>
                    </div>

                    <!-- Toolbar Word-Style Format Teks Banner -->
                    <div class="flex flex-wrap items-center gap-1 bg-slate-100 p-1.5 rounded-t-xl border border-b-0 border-slate-200 text-slate-700 select-none">
                        <!-- Bold -->
                        <button type="button" onmousedown="event.preventDefault(); execBannerEditor('bold');" id="tbBtnBold" title="Tebal / Bold (Ctrl+B)" class="p-1.5 hover:bg-white rounded-lg text-xs font-black transition-all cursor-pointer w-7 h-7 flex items-center justify-center border border-transparent hover:border-slate-200 hover:shadow-2xs">
                            B
                        </button>
                        <!-- Italic -->
                        <button type="button" onmousedown="event.preventDefault(); execBannerEditor('italic');" id="tbBtnItalic" title="Miring / Italic (Ctrl+I)" class="p-1.5 hover:bg-white rounded-lg text-xs italic font-serif font-bold transition-all cursor-pointer w-7 h-7 flex items-center justify-center border border-transparent hover:border-slate-200 hover:shadow-2xs">
                            I
                        </button>
                        <!-- Underline -->
                        <button type="button" onmousedown="event.preventDefault(); execBannerEditor('underline');" id="tbBtnUnderline" title="Garis Bawah / Underline (Ctrl+U)" class="p-1.5 hover:bg-white rounded-lg text-xs underline font-bold transition-all cursor-pointer w-7 h-7 flex items-center justify-center border border-transparent hover:border-slate-200 hover:shadow-2xs">
                            U
                        </button>
                        <!-- Strikethrough -->
                        <button type="button" onmousedown="event.preventDefault(); execBannerEditor('strikeThrough');" id="tbBtnStrike" title="Coret / Strikethrough" class="p-1.5 hover:bg-white rounded-lg text-xs line-through font-bold transition-all cursor-pointer w-7 h-7 flex items-center justify-center border border-transparent hover:border-slate-200 hover:shadow-2xs">
                            S
                        </button>

                        <span class="w-[1px] h-4 bg-slate-300 mx-1"></span>

                        <!-- Penomoran Otomatis 1. 2. 3. (Word Style: Enter langsung nomor berikutnya) -->
                        <button type="button" onmousedown="event.preventDefault(); execBannerEditor('insertOrderedList');" id="tbBtnOl" title="Penomoran Otomatis 1. 2. 3. (Tekan Enter nomor berikutnya muncul otomatis seperti di Word)" class="px-2 py-1 hover:bg-white rounded-lg text-[11px] font-bold font-mono transition-all cursor-pointer h-7 flex items-center gap-1.5 border border-transparent hover:border-slate-200 hover:shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 6h13M7 12h13M7 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                            <span>1. 2. 3.</span>
                        </button>
                        <!-- Daftar Simbol / Poin (Word Style: Enter langsung simbol berikutnya) -->
                        <button type="button" onmousedown="event.preventDefault(); execBannerEditor('insertUnorderedList');" id="tbBtnUl" title="Daftar Simbol / Poin (Tekan Enter simbol berikutnya muncul otomatis seperti di Word)" class="px-2 py-1 hover:bg-white rounded-lg text-[11px] font-bold transition-all cursor-pointer h-7 flex items-center gap-1.5 border border-transparent hover:border-slate-200 hover:shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <span>• Simbol</span>
                        </button>

                        <span class="w-[1px] h-4 bg-slate-300 mx-1"></span>

                        <!-- Rata Kiri -->
                        <button type="button" onmousedown="event.preventDefault(); execBannerEditor('justifyLeft');" id="tbBtnAlignLeft" title="Rata Kiri" class="p-1.5 hover:bg-white rounded-lg text-xs transition-all cursor-pointer w-7 h-7 flex items-center justify-center border border-transparent hover:border-slate-200 hover:shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h14"/></svg>
                        </button>
                        <!-- Rata Tengah -->
                        <button type="button" onmousedown="event.preventDefault(); execBannerEditor('justifyCenter');" id="tbBtnAlignCenter" title="Rata Tengah" class="p-1.5 hover:bg-white rounded-lg text-xs transition-all cursor-pointer w-7 h-7 flex items-center justify-center border border-transparent hover:border-slate-200 hover:shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M5 18h14"/></svg>
                        </button>
                        <!-- Rata Kanan -->
                        <button type="button" onmousedown="event.preventDefault(); execBannerEditor('justifyRight');" id="tbBtnAlignRight" title="Rata Kanan" class="p-1.5 hover:bg-white rounded-lg text-xs transition-all cursor-pointer w-7 h-7 flex items-center justify-center border border-transparent hover:border-slate-200 hover:shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M6 18h14"/></svg>
                        </button>
                        <!-- Rata Kiri-Kanan (Justify) -->
                        <button type="button" onmousedown="event.preventDefault(); execBannerEditor('justifyFull');" id="tbBtnAlignJustify" title="Rata Kiri Kanan (Justify)" class="p-1.5 hover:bg-white rounded-lg text-xs transition-all cursor-pointer w-7 h-7 flex items-center justify-center border border-transparent hover:border-slate-200 hover:shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>

                        <span class="w-[1px] h-4 bg-slate-300 mx-1"></span>

                        <!-- Format Paragraf / Heading -->
                        <button type="button" onmousedown="event.preventDefault(); execBannerEditor('formatBlock', '<p>');" title="Paragraf Biasa" class="px-2 py-0.5 hover:bg-white rounded-lg text-[10px] font-bold border border-transparent hover:border-slate-200 cursor-pointer">
                            Teks
                        </button>
                        <button type="button" onmousedown="event.preventDefault(); execBannerEditor('formatBlock', '<h4>');" title="Subjudul (Teks Sedang)" class="px-2 py-0.5 hover:bg-white rounded-lg text-[10px] font-black border border-transparent hover:border-slate-200 cursor-pointer">
                            H4
                        </button>

                        <!-- Clear Formatting -->
                        <button type="button" onmousedown="event.preventDefault(); execBannerEditor('removeFormat');" title="Hapus Format / Normal" class="p-1.5 hover:bg-white text-slate-400 hover:text-rose-600 rounded-lg text-xs transition-all cursor-pointer w-7 h-7 flex items-center justify-center ml-auto border border-transparent hover:border-slate-200">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Area Ketik Word-Style (ContentEditable Canvas) -->
                    <div id="modalBannerRichEditor"
                         contenteditable="true"
                         spellcheck="false"
                         role="textbox"
                         aria-multiline="true"
                         class="banner-editor-canvas min-h-[140px] max-h-[260px] overflow-y-auto bg-white border border-slate-200 rounded-b-xl p-3.5 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all custom-scrollbar leading-relaxed"
                         data-placeholder="Ketik isi pengumuman seperti di Word... Klik '1. 2. 3.' untuk penomoran otomatis atau '• Simbol' untuk poin (tekan Enter untuk nomor/simbol selanjutnya).">
                    </div>
                    <!-- Hidden input penampung data HTML yang dikirim ke server -->
                    <input type="hidden" id="modalBannerContent" name="content">

                    <!-- Live Preview Box -->
                    <div id="modalBannerPreviewWrapper" class="hidden mt-2.5 p-3.5 bg-amber-50/50 border border-amber-200/80 rounded-2xl space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] font-bold text-amber-900 pb-1 border-b border-amber-200/50">
                            <span>Preview Tampilan Sisi User:</span>
                            <span class="text-[10px] text-amber-700 font-normal">Tampilan identik dengan aplikasi user</span>
                        </div>
                        <div id="modalBannerPreviewContent" class="banner-rich-content text-xs text-slate-700 leading-relaxed min-h-[40px]">
                            <span class="text-slate-400 italic">Belum ada konten untuk ditampilkan.</span>
                        </div>
                    </div>
                </div>

                <!-- Upload Gambar Banner -->
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="block font-bold text-slate-800 text-xs">File Gambar / Poster / Flyer <span class="font-normal text-slate-400">(Opsional)</span></label>
                        <span class="text-[10px] text-slate-400">JPG, PNG, WEBP</span>
                    </div>

                    <input type="file" id="modalBannerImageFile" name="image" accept="image/png, image/jpeg, image/jpg, image/webp"
                           onchange="previewBannerFileInput(this)"
                           class="w-full text-slate-600 text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200 cursor-pointer">

                    <!-- Preview Container -->
                    <div id="modalBannerImagePreviewBox" class="hidden relative rounded-xl overflow-hidden border border-slate-200 bg-white p-2">
                        <img id="modalBannerImagePreviewImg" src="" alt="Preview" class="max-h-40 w-full object-contain mx-auto rounded-lg">
                        <button type="button" onclick="removeModalBannerImage()" class="absolute top-3 right-3 px-2 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-[10px] font-bold shadow-xs cursor-pointer">
                            Hapus Gambar
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tautan / Link Tujuan <span class="font-normal text-slate-400">(Opsional)</span></label>
                    <input type="url" id="modalBannerLink" name="link_url" placeholder="https://..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-900 focus:ring-2 focus:ring-amber-500 focus:bg-white font-mono text-[11px]">
                    <p class="text-[10px] text-slate-400 mt-0.5">Jika diisi, tombol 'Info Selengkapnya' akan muncul pada banner sisi user.</p>
                </div>

                <!-- Checkbox Status Aktif -->
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-800 block text-xs">Aktifkan Banner Ini</span>
                        <span class="text-[10px] text-slate-500">Banner akan muncul pada slider pengguna jika aktif</span>
                    </div>
                    <input type="checkbox" id="modalBannerIsActive" name="is_active" value="1" checked class="w-4 h-4 text-amber-600 rounded">
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100 shrink-0">
                    <button type="button" onclick="closeModalBanner()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <span>Simpan Banner</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Sidebar Mobile Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }

        // Sidebar Desktop Minimize Toggle
        function toggleSidebarMinimize() {
            const sidebar = document.getElementById('admin-sidebar');
            if (!sidebar) return;
            sidebar.classList.toggle('sidebar-minimized');
            const isMinimized = sidebar.classList.contains('sidebar-minimized');
            try {
                localStorage.setItem('admin_sidebar_minimized', isMinimized ? 'true' : 'false');
            } catch(e) {}
            
            const btn = document.getElementById('sidebarMinimizeBtn');
            if (btn) {
                btn.title = isMinimized ? 'Perluas Sidebar (Ctrl+B)' : 'Perkecil Sidebar (Ctrl+B)';
            }
        }

        // Shortcut Keyboard Ctrl+B untuk Minimize Sidebar
        window.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'b' || e.key === 'B')) {
                if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) return;
                e.preventDefault();
                toggleSidebarMinimize();
            }
        });

        // Real-Time Clock Sistem WITA (UTC+8)
        function updateRealtimeWitaClock() {
            const el = document.getElementById('realtimeClockWita');
            if (!el) return;
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', {
                timeZone: 'Asia/Makassar',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
            el.textContent = `${timeStr} WITA`;
        }
        setInterval(updateRealtimeWitaClock, 1000);
        updateRealtimeWitaClock();

        // Sidebar Menu Switcher
        function switchSidebarMenu(menuKey) {
            document.querySelectorAll('.admin-section').forEach(s => s.classList.add('hidden'));
            document.querySelectorAll('.nav-item').forEach(btn => {
                btn.classList.remove('bg-emerald-600', 'text-white', 'shadow-xs');
                btn.classList.add('text-slate-600', 'hover:text-slate-900', 'hover:bg-slate-50');
            });

            const targetSection = document.getElementById('section-' + menuKey);
            const targetBtn = document.getElementById('menu-btn-' + menuKey);

            if (targetSection) targetSection.classList.remove('hidden');
            if (targetBtn) {
                targetBtn.classList.remove('text-slate-600', 'hover:text-slate-900', 'hover:bg-slate-50');
                targetBtn.classList.add('bg-emerald-600', 'text-white', 'shadow-xs');
            }

            // Hook otomatis refresh data saat tab diklik
            if (menuKey === 'users') {
                renderUsersTable();
            } else if (menuKey === 'logs') {
                renderLogsTable();
            }

            // Close mobile sidebar on select
            if (window.innerWidth < 768) {
                document.getElementById('admin-sidebar').classList.add('-translate-x-full');
            }
        }

        // =====================================================================
        // MANAJEMEN PENGGUNA (FILTER, PAGINASI, & TAMPILKAN SEMUA)
        // =====================================================================
        const rawUsersData = @json($usersList);
        let userFilterState = {
            search: '',
            uptd: '',
            status: '',
            perPage: 10,
            currentPage: 1,
            showAll: false
        };

        let userSearchDebounceTimer = null;
        function debounceUserFilter() {
            clearTimeout(userSearchDebounceTimer);
            userSearchDebounceTimer = setTimeout(() => {
                const val = document.getElementById('userSearchInput')?.value.trim().toLowerCase() || '';
                userFilterState.search = val;
                userFilterState.currentPage = 1;
                renderUsersTable();
            }, 250);
        }

        function applyUserFilters() {
            userFilterState.uptd = document.getElementById('userUptdFilter')?.value || '';
            userFilterState.status = document.getElementById('userStatusFilter')?.value || '';
            userFilterState.currentPage = 1;
            renderUsersTable();
        }

        function changeUserPerPage(val) {
            if (val === 'all') {
                userFilterState.showAll = true;
                userFilterState.perPage = 'all';
            } else {
                userFilterState.showAll = false;
                userFilterState.perPage = parseInt(val, 10) || 10;
            }
            userFilterState.currentPage = 1;
            renderUsersTable();
        }

        function toggleUserShowAll() {
            userFilterState.showAll = !userFilterState.showAll;
            const selectEl = document.getElementById('userPerPageSelect');
            if (userFilterState.showAll) {
                userFilterState.perPage = 'all';
                if (selectEl) selectEl.value = 'all';
            } else {
                userFilterState.perPage = 10;
                if (selectEl) selectEl.value = '10';
            }
            userFilterState.currentPage = 1;
            renderUsersTable();
        }

        function resetUserFilters() {
            userFilterState = {
                search: '',
                uptd: '',
                status: '',
                perPage: 10,
                currentPage: 1,
                showAll: false
            };
            const sInput = document.getElementById('userSearchInput');
            if (sInput) sInput.value = '';
            const uFilter = document.getElementById('userUptdFilter');
            if (uFilter) uFilter.value = '';
            const stFilter = document.getElementById('userStatusFilter');
            if (stFilter) stFilter.value = '';
            const ppSelect = document.getElementById('userPerPageSelect');
            if (ppSelect) ppSelect.value = '10';
            renderUsersTable();
        }

        function goToUserPage(page) {
            userFilterState.currentPage = page;
            renderUsersTable();
        }

        function renderUsersTable() {
            const tbody = document.getElementById('usersTableBody');
            if (!tbody) return;

            // Filter
            const filtered = rawUsersData.filter(u => {
                if (userFilterState.search) {
                    const uname = (u.username || '').toLowerCase();
                    const name = (u.name || '').toLowerCase();
                    if (!uname.includes(userFilterState.search) && !name.includes(userFilterState.search)) {
                        return false;
                    }
                }
                if (userFilterState.uptd) {
                    if ((u.uptd || '') !== userFilterState.uptd) {
                        return false;
                    }
                }
                if (userFilterState.status === 'active' && parseInt(u.is_blocked) === 1) {
                    return false;
                }
                if (userFilterState.status === 'blocked' && parseInt(u.is_blocked) !== 1) {
                    return false;
                }
                return true;
            });

            const total = filtered.length;
            const isAll = userFilterState.showAll || userFilterState.perPage === 'all';
            const perPage = isAll ? (total || 1) : userFilterState.perPage;
            const totalPages = Math.ceil(total / perPage) || 1;

            if (userFilterState.currentPage > totalPages) {
                userFilterState.currentPage = totalPages;
            }
            if (userFilterState.currentPage < 1) {
                userFilterState.currentPage = 1;
            }

            const fromIdx = total === 0 ? 0 : (userFilterState.currentPage - 1) * perPage;
            const toIdx = isAll ? total : Math.min(fromIdx + perPage, total);
            const pageData = total === 0 ? [] : filtered.slice(fromIdx, toIdx);

            // Summary
            const fromEl = document.getElementById('usersFrom');
            const toEl = document.getElementById('usersTo');
            const totalEl = document.getElementById('usersTotal');
            if (fromEl) fromEl.textContent = total === 0 ? 0 : fromIdx + 1;
            if (toEl) toEl.textContent = toIdx;
            if (totalEl) totalEl.textContent = total;

            // Show All Button text & style
            const btnShowAll = document.getElementById('btnUserShowAll');
            if (btnShowAll) {
                if (isAll) {
                    btnShowAll.textContent = 'Mode Paginasi';
                    btnShowAll.className = 'px-3 py-1.5 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 text-xs font-bold shadow-2xs transition-all cursor-pointer';
                } else {
                    btnShowAll.textContent = 'Tampilkan Semua';
                    btnShowAll.className = 'px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition-all cursor-pointer';
                }
            }

            if (pageData.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <span>Tidak ada pengguna yang cocok dengan kriteria filter.</span>
                                <button type="button" onclick="resetUserFilters()" class="text-xs text-emerald-600 hover:text-emerald-800 font-bold underline cursor-pointer mt-1">Reset Filter</button>
                            </div>
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = pageData.map(u => {
                    const isBlocked = parseInt(u.is_blocked) === 1;
                    const isAdmin = (u.role === 'admin' || parseInt(u.role_id) === 1);
                    const hasActiveSession = (!isAdmin && (u.api_token || u.device_id));

                    // UPTD Badge
                    let uptdBadge = '';
                    if (u.uptd) {
                        const isPusat = u.uptd === 'Kantor Pusat';
                        uptdBadge = `
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold ${isPusat ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-blue-50 text-blue-800 border border-blue-200'}">
                                <svg class="w-3 h-3 ${isPusat ? 'text-amber-600' : 'text-blue-600'} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="truncate max-w-[130px]" title="${u.uptd}">${u.uptd}</span>
                            </span>
                        `;
                    } else {
                        uptdBadge = `
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-dashed border-slate-300">
                                <span class="truncate max-w-[130px]">Belum Diisi</span>
                            </span>
                        `;
                    }

                    // Role Badge
                    const roleBadge = isAdmin ? `
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                            Administrator
                        </span>
                    ` : `
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            Pengguna Biasa
                        </span>
                    `;

                    // Device Badge
                    let devBadge = '';
                    if (isAdmin) {
                        devBadge = `
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200" title="Admin dapat login bersamaan di banyak perangkat tanpa batasan">
                                <svg class="w-3 h-3 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Bebas (Multi-Device)
                            </span>
                        `;
                    } else if (hasActiveSession) {
                        devBadge = `
                            <div class="flex items-center gap-1.5" title="Sesi aktif di ${u.device_name || 'Perangkat'}">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="truncate max-w-[130px]">Aktif: ${u.device_name || 'Perangkat'}</span>
                                </span>
                            </div>
                        `;
                    } else {
                        devBadge = `
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                Tidak Ada Sesi Aktif
                            </span>
                        `;
                    }

                    // Status Badge
                    const statusBadge = isBlocked ? `
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            Diblokir
                        </span>
                    ` : `
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Aktif
                        </span>
                    `;

                    // Format Last Login (WITA)
                    let lastLogin = '-';
                    if (u.last_login_at) {
                        try {
                            const d = new Date(u.last_login_at);
                            lastLogin = d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' +
                                        d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }) + ' WITA';
                        } catch(e) {
                            lastLogin = u.last_login_at;
                        }
                    }

                    const jsonUserStr = JSON.stringify(u).replace(/"/g, '&quot;');

                    return `
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-mono text-slate-400" data-label="ID">#${u.id}</td>
                            <td class="px-4 py-3 font-mono font-bold text-slate-900" data-label="Username">${u.username}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800" data-label="Nama Lengkap">${u.name || '-'}</td>
                            <td class="px-4 py-3" data-label="Asal UPTD">${uptdBadge}</td>
                            <td class="px-4 py-3" data-label="Role">${roleBadge}</td>
                            <td class="px-4 py-3" data-label="Perangkat">${devBadge}</td>
                            <td class="px-4 py-3" data-label="Status">${statusBadge}</td>
                            <td class="px-4 py-3 text-slate-500 font-mono text-[11px]" data-label="Login Terakhir">${lastLogin}</td>
                            <td class="px-4 py-3 text-right space-x-1" data-label="Aksi" data-action="true">
                                ${hasActiveSession ? `
                                    <button type="button" onclick="resetUserDevice(${u.id}, '${u.username}')"
                                            class="px-2.5 py-1.5 rounded-lg border border-purple-200 bg-purple-50 hover:bg-purple-100 text-purple-700 text-[11px] font-semibold cursor-pointer"
                                            title="Logout / Putuskan Sesi Perangkat Pengguna">
                                        Logout Sesi
                                    </button>
                                ` : ''}
                                <button type="button" onclick="editUser(${jsonUserStr})"
                                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-[11px] font-semibold cursor-pointer">
                                    Edit
                                </button>
                                <button type="button" onclick="toggleBlockUser(${u.id}, ${isBlocked ? 1 : 0})"
                                        class="px-2.5 py-1.5 rounded-lg border text-[11px] font-semibold cursor-pointer ${isBlocked ? 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100'}">
                                    ${isBlocked ? 'Aktifkan' : 'Blokir'}
                                </button>
                                <button type="button" onclick="deleteUser(${u.id}, '${u.username}')"
                                        class="px-2.5 py-1.5 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] font-semibold cursor-pointer">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            renderUsersPagination(totalPages, userFilterState.currentPage, isAll);
        }

        function renderUsersPagination(totalPages, currentPage, isAll) {
            const container = document.getElementById('usersPagination');
            if (!container) return;

            if (isAll || totalPages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = '';
            // Prev Button
            html += `
                <button type="button" onclick="goToUserPage(${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''}
                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-all">
                    &laquo; Prev
                </button>
            `;

            for (let i = 1; i <= totalPages; i++) {
                if (totalPages > 7) {
                    if (i !== 1 && i !== totalPages && Math.abs(i - currentPage) > 2) {
                        if (i === 2 || i === totalPages - 1) {
                            html += `<span class="px-1 text-slate-400">...</span>`;
                        }
                        continue;
                    }
                }
                const isActive = i === currentPage;
                html += `
                    <button type="button" onclick="goToUserPage(${i})"
                            class="px-2.5 py-1.5 rounded-lg border text-xs font-bold transition-all cursor-pointer ${isActive ? 'bg-emerald-600 text-white border-emerald-600 shadow-2xs' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-700'}">
                        ${i}
                    </button>
                `;
            }

            // Next Button
            html += `
                <button type="button" onclick="goToUserPage(${currentPage + 1})" ${currentPage >= totalPages ? 'disabled' : ''}
                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-all">
                    Next &raquo;
                </button>
            `;

            container.innerHTML = html;
        }

        // =====================================================================
        // RIWAYAT AKTIVITAS (AUDIT LOGS) FILTER, PAGINASI & TAMPILKAN SEMUA
        // =====================================================================
        let rawLogsData = @json($allActivities ?? []);
        let logFilterState = {
            search: '',
            activity: '',
            perPage: 15,
            currentPage: 1,
            showAll: false
        };

        let logSearchTimer = null;
        function debounceLogSearch(e) {
            clearTimeout(logSearchTimer);
            logSearchTimer = setTimeout(() => {
                logFilterState.search = e.target.value.trim().toLowerCase();
                logFilterState.currentPage = 1;
                renderLogsTable();
            }, 250);
        }

        function handleLogActivityChange(val) {
            logFilterState.activity = val;
            logFilterState.currentPage = 1;
            renderLogsTable();
        }

        function changeLogPerPage(val) {
            if (val === 'all') {
                logFilterState.showAll = true;
                logFilterState.perPage = 'all';
            } else {
                logFilterState.showAll = false;
                logFilterState.perPage = parseInt(val, 10) || 15;
            }
            logFilterState.currentPage = 1;
            renderLogsTable();
        }

        function toggleLogShowAll() {
            logFilterState.showAll = !logFilterState.showAll;
            const selectEl = document.getElementById('logPerPageSelect');
            if (logFilterState.showAll) {
                logFilterState.perPage = 'all';
                if (selectEl) selectEl.value = 'all';
            } else {
                logFilterState.perPage = 15;
                if (selectEl) selectEl.value = '15';
            }
            logFilterState.currentPage = 1;
            renderLogsTable();
        }

        function resetLogFilters() {
            logFilterState = {
                search: '',
                activity: '',
                perPage: 15,
                currentPage: 1,
                showAll: false
            };
            const sInput = document.getElementById('logSearchInput');
            if (sInput) sInput.value = '';
            const aFilter = document.getElementById('logActivityFilter');
            if (aFilter) aFilter.value = '';
            const ppSelect = document.getElementById('logPerPageSelect');
            if (ppSelect) ppSelect.value = '15';
            renderLogsTable();
        }

        function goToLogPage(page) {
            logFilterState.currentPage = page;
            renderLogsTable();
        }

        function formatLogTime(created_at, created_at_formatted) {
            if (created_at_formatted) return created_at_formatted;
            if (!created_at) return '-';
            const cleaned = String(created_at).trim();
            const parts = cleaned.match(/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2}):(\d{2})$/);
            if (parts) {
                return `${parts[3]}/${parts[2]}/${parts[1]} ${parts[4]}:${parts[5]}:${parts[6]} WITA`;
            }
            return cleaned + ' WITA';
        }

        function renderLogsTable() {
            const tbody = document.getElementById('logsTableBody');
            if (!tbody) return;

            const search = (logFilterState.search || '').toLowerCase();
            const actFilter = logFilterState.activity;

            const filtered = (rawLogsData || []).filter(l => {
                if (actFilter && actFilter !== '' && actFilter !== 'all' && l.activity !== actFilter) {
                    return false;
                }
                if (search) {
                    const matchUser = (l.username || '').toLowerCase().includes(search);
                    const matchName = (l.user_name || '').toLowerCase().includes(search);
                    const matchAct = (l.activity || '').toLowerCase().includes(search);
                    const matchDetail = (l.detail || '').toLowerCase().includes(search);
                    const matchIp = (l.ip_address || '').toLowerCase().includes(search);
                    if (!matchUser && !matchName && !matchAct && !matchDetail && !matchIp) {
                        return false;
                    }
                }
                return true;
            });

            const total = filtered.length;
            const isAll = logFilterState.showAll || logFilterState.perPage === 'all';
            const perPage = isAll ? Math.max(total, 1) : (parseInt(logFilterState.perPage, 10) || 15);
            const totalPages = Math.max(1, Math.ceil(total / perPage));

            if (logFilterState.currentPage > totalPages) {
                logFilterState.currentPage = totalPages;
            }
            const currentPage = logFilterState.currentPage;

            const startIndex = (currentPage - 1) * perPage;
            const pageData = isAll ? filtered : filtered.slice(startIndex, startIndex + perPage);

            const from = total > 0 ? (startIndex + 1) : 0;
            const to = isAll ? total : Math.min(startIndex + perPage, total);

            // Summary
            const fromEl = document.getElementById('logsFrom');
            const toEl = document.getElementById('logsTo');
            const totalEl = document.getElementById('logsTotal');
            if (fromEl) fromEl.textContent = from;
            if (toEl) toEl.textContent = to;
            if (totalEl) totalEl.textContent = total;

            // Show All Button text & style
            const btnShowAll = document.getElementById('btnLogShowAll');
            if (btnShowAll) {
                if (isAll) {
                    btnShowAll.textContent = 'Mode Paginasi';
                    btnShowAll.className = 'px-3 py-1.5 rounded-xl border border-blue-300 bg-blue-50 text-blue-700 text-xs font-bold shadow-2xs transition-all cursor-pointer';
                } else {
                    btnShowAll.textContent = 'Tampilkan Semua';
                    btnShowAll.className = 'px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition-all cursor-pointer';
                }
            }

            if (pageData.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Tidak ada riwayat aktivitas ditemukan sesuai filter.</span>
                                <button type="button" onclick="resetLogFilters()" class="text-xs text-blue-600 hover:text-blue-800 font-bold underline cursor-pointer mt-1">Reset Filter</button>
                            </div>
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = pageData.map(l => {
                    const timeFormatted = formatLogTime(l.created_at, l.created_at_formatted);
                    const totalBiayaFormatted = (l.total_biaya && Number(l.total_biaya) > 0)
                        ? 'Rp ' + Number(l.total_biaya).toLocaleString('id-ID')
                        : '<span class="text-slate-400 font-normal">-</span>';

                    return `
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 text-slate-500 font-mono text-[11px] whitespace-nowrap" data-label="Waktu">
                                ${timeFormatted}
                            </td>
                            <td class="px-4 py-3" data-label="User">
                                <span class="font-mono font-bold text-slate-900 block">${l.username || '-'}</span>
                                <span class="text-[10px] text-slate-500">${l.user_name || '-'}</span>
                            </td>
                            <td class="px-4 py-3" data-label="Aktivitas">
                                <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    ${l.activity || '-'}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-700 font-medium max-w-xs truncate" data-label="Detail" data-stacked="true" title="${l.detail || ''}">
                                ${l.detail || '-'}
                            </td>
                            <td class="px-4 py-3 font-mono font-bold text-emerald-700 text-xs whitespace-nowrap" data-label="Total Biaya">
                                ${totalBiayaFormatted}
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-500 text-[11px]" data-label="IP Address">
                                ${l.ip_address || '-'}
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            renderLogsPagination(totalPages, currentPage, isAll);
        }

        function renderLogsPagination(totalPages, currentPage, isAll) {
            const container = document.getElementById('logsPagination');
            if (!container) return;

            if (isAll || totalPages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = '';
            // Prev Button
            html += `
                <button type="button" onclick="goToLogPage(${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''}
                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-all">
                    &laquo; Prev
                </button>
            `;

            for (let i = 1; i <= totalPages; i++) {
                if (totalPages > 7) {
                    if (i !== 1 && i !== totalPages && Math.abs(i - currentPage) > 2) {
                        if (i === 2 || i === totalPages - 1) {
                            html += `<span class="px-1 text-slate-400">...</span>`;
                        }
                        continue;
                    }
                }
                const isActive = i === currentPage;
                html += `
                    <button type="button" onclick="goToLogPage(${i})"
                            class="px-2.5 py-1.5 rounded-lg border text-xs font-bold transition-all cursor-pointer ${isActive ? 'bg-blue-600 text-white border-blue-600 shadow-2xs' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-700'}">
                        ${i}
                    </button>
                `;
            }

            // Next Button
            html += `
                <button type="button" onclick="goToLogPage(${currentPage + 1})" ${currentPage >= totalPages ? 'disabled' : ''}
                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-all">
                    Next &raquo;
                </button>
            `;

            container.innerHTML = html;
        }

        function fetchLogs(isBackground = false) {
            renderLogsTable();
            // Optional background refresh
            fetch('{{ route("admin.activity_logs", [], false) }}?per_page=100', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success' && res.data) {
                    const newLogs = Array.isArray(res.data) ? res.data : (res.data.data || []);
                    if (newLogs.length > 0) {
                        rawLogsData = newLogs;
                        renderLogsTable();
                    }
                }
            })
            .catch(() => {});
        }

        // Set default active menu & inisialisasi tabel
        switchSidebarMenu(@json($activeSection ?? 'users'));
        renderLogsTable();

        // Toggle Switch Label Updater
        function updateToggleLabel(checkbox) {
            const label = document.getElementById('label-' + checkbox.id);
            if (label) {
                if (checkbox.checked) {
                    label.textContent = 'AKTIF';
                    label.className = 'text-xs font-bold font-mono text-emerald-600';
                } else {
                    label.textContent = 'NONAKTIF';
                    label.className = 'text-xs font-bold font-mono text-slate-400';
                }
            }
        }

        // Toggle Password Visibility Helper
        function togglePasswordVisibility(inputId, eyeOpenId, eyeClosedId) {
            const input = document.getElementById(inputId);
            const eyeOpen = document.getElementById(eyeOpenId);
            const eyeClosed = document.getElementById(eyeClosedId);
            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                if (eyeOpen) eyeOpen.classList.add('hidden');
                if (eyeClosed) eyeClosed.classList.remove('hidden');
            } else {
                input.type = 'password';
                if (eyeOpen) eyeOpen.classList.remove('hidden');
                if (eyeClosed) eyeClosed.classList.add('hidden');
            }
        }

        // User Management Modals & Actions
        function openModalUser() {
            document.getElementById('modalUserTitle').innerText = 'Tambah Pengguna Baru';
            document.getElementById('modalUserId').value = '';
            document.getElementById('modalUserUsername').value = '';
            document.getElementById('modalUserName').value = '';
            document.getElementById('modalUserUptd').value = 'Kantor Pusat';
            document.getElementById('modalUserRole').value = 'user';
            document.getElementById('modalUserPassword').value = '';
            document.getElementById('modalUserPassword').type = 'password';
            document.getElementById('iconEyeOpenUser').classList.remove('hidden');
            document.getElementById('iconEyeClosedUser').classList.add('hidden');
            document.getElementById('modalUserPassword').required = true;
            document.getElementById('modalUserPassHint').innerText = 'Minimal 4 karakter.';
            document.getElementById('modalUserBlocked').checked = false;
            document.getElementById('modalUser').classList.remove('hidden');
        }

        function editUser(user) {
            document.getElementById('modalUserTitle').innerText = 'Edit Pengguna: ' + user.username;
            document.getElementById('modalUserId').value = user.id;
            document.getElementById('modalUserUsername').value = user.username;
            document.getElementById('modalUserName').value = user.name;
            document.getElementById('modalUserUptd').value = user.uptd || 'Kantor Pusat';
            document.getElementById('modalUserRole').value = user.role || 'user';
            document.getElementById('modalUserPassword').value = '';
            document.getElementById('modalUserPassword').type = 'password';
            document.getElementById('iconEyeOpenUser').classList.remove('hidden');
            document.getElementById('iconEyeClosedUser').classList.add('hidden');
            document.getElementById('modalUserPassword').required = false;
            document.getElementById('modalUserPassHint').innerText = 'Kosongkan jika tidak ingin mengubah password saat ini.';
            document.getElementById('modalUserBlocked').checked = parseInt(user.is_blocked) === 1;
            document.getElementById('modalUser').classList.remove('hidden');
        }

        function closeModalUser() {
            document.getElementById('modalUser').classList.add('hidden');
        }

        function resetUserDevice(id, username) {
            if (!confirm(`Putuskan / Logout sesi aktif pengguna "${username}"?\n\nSetelah diputuskan, pengguna dapat login di perangkat lain mana pun.`)) return;

            fetch('{{ route("admin.reset_device_user") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id: id })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + (res.message || ''));
                }
            })
            .catch(() => alert('Terjadi kendala jaringan saat mereset perangkat.'));
        }

        function confirmResetAllUserSessions() {
            const msg = "Konfirmasi Reset Sesi Massal Seluruh Pengguna:\n\n" +
                        "Apakah Anda yakin ingin memutuskan (logout) sesi aktif seluruh akun dengan role 'user'?\n\n" +
                        "• Seluruh akun pengguna (non-admin) akan langsung logout dari perangkat mereka.\n" +
                        "• Sesi akun Administrator TIDAK akan terpengaruh (tetap aktif login).\n" +
                        "• Saat pengguna login kembali, mereka langsung melihat pop-up pembaruan banner pengumuman terbaru.\n\n" +
                        "Lanjutkan proses reset sesi massal?";
            if (!confirm(msg)) return;

            fetch('{{ route("admin.reset_all_user_sessions") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + (res.message || 'Terjadi kesalahan saat memproses permintaan.'));
                }
            })
            .catch(() => alert('Terjadi kendala jaringan saat mereset sesi massal pengguna.'));
        }

        function toggleBlockUser(id, currentBlocked) {
            const actionText = currentBlocked ? 'mengaktifkan kembali' : 'memblokir';
            if (!confirm(`Apakah Anda yakin ingin ${actionText} akses pengguna ini?`)) return;

            fetch('{{ route("admin.toggle_block_user") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id: id })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + (res.message || ''));
                }
            })
            .catch(() => alert('Terjadi kendala jaringan.'));
        }

        function deleteUser(id, username) {
            if (!confirm(`Hapus akun pengguna "${username}" secara permanen beserta riwayat aktivitasnya?`)) return;

            fetch('{{ route("admin.delete_user") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id: id })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + (res.message || ''));
                }
            })
            .catch(() => alert('Terjadi kendala jaringan.'));
        }

        // =====================================================================
        // WORD-STYLE RICH TEXT BANNER EDITOR (PENGALAMAN MENGETIK SEPERTI DI WORD)
        // =====================================================================
        function execBannerEditor(cmd, val = null) {
            const editor = document.getElementById('modalBannerRichEditor');
            if (!editor) return;
            editor.focus();
            try {
                document.execCommand(cmd, false, val);
            } catch(e) {}
            syncBannerEditorContent();
            updateToolbarStates();
        }

        function syncBannerEditorContent() {
            const editor = document.getElementById('modalBannerRichEditor');
            const hiddenInput = document.getElementById('modalBannerContent');
            if (!editor || !hiddenInput) return;

            const html = editor.innerHTML.trim();
            const text = editor.innerText.trim();

            if (!text && !editor.querySelector('img')) {
                hiddenInput.value = '';
            } else {
                hiddenInput.value = html;
            }

            updateBannerLivePreview();
        }

        function updateToolbarStates() {
            try {
                const isBold = document.queryCommandState('bold');
                const isItalic = document.queryCommandState('italic');
                const isUnderline = document.queryCommandState('underline');
                const isStrike = document.queryCommandState('strikeThrough');
                const isOl = document.queryCommandState('insertOrderedList');
                const isUl = document.queryCommandState('insertUnorderedList');
                const isLeft = document.queryCommandState('justifyLeft');
                const isCenter = document.queryCommandState('justifyCenter');
                const isRight = document.queryCommandState('justifyRight');
                const isJustify = document.queryCommandState('justifyFull');

                toggleBtnActive('tbBtnBold', isBold);
                toggleBtnActive('tbBtnItalic', isItalic);
                toggleBtnActive('tbBtnUnderline', isUnderline);
                toggleBtnActive('tbBtnStrike', isStrike);
                toggleBtnActive('tbBtnOl', isOl);
                toggleBtnActive('tbBtnUl', isUl);
                toggleBtnActive('tbBtnAlignLeft', isLeft);
                toggleBtnActive('tbBtnAlignCenter', isCenter);
                toggleBtnActive('tbBtnAlignRight', isRight);
                toggleBtnActive('tbBtnAlignJustify', isJustify);
            } catch(e) {}
        }

        function toggleBtnActive(btnId, isActive) {
            const el = document.getElementById(btnId);
            if (!el) return;
            if (isActive) {
                el.classList.add('bg-white', 'text-amber-800', 'border-amber-300', 'shadow-2xs');
            } else {
                el.classList.remove('bg-white', 'text-amber-800', 'border-amber-300', 'shadow-2xs');
            }
        }

        function toggleBannerTextPreview() {
            const wrapper = document.getElementById('modalBannerPreviewWrapper');
            if (!wrapper) return;
            const isHidden = wrapper.classList.toggle('hidden');
            if (!isHidden) {
                updateBannerLivePreview();
            }
        }

        function updateBannerLivePreview() {
            const editor = document.getElementById('modalBannerRichEditor');
            const previewEl = document.getElementById('modalBannerPreviewContent');
            if (!editor || !previewEl) return;

            const html = editor.innerHTML.trim();
            const text = editor.innerText.trim();

            if (!text && !editor.querySelector('img')) {
                previewEl.innerHTML = '<span class="text-slate-400 italic">Belum ada konten untuk ditampilkan.</span>';
                return;
            }

            previewEl.innerHTML = html;
        }

        function convertPlainTextToEditorHtml(text) {
            if (!text) return '';
            const lines = text.split('\n');
            let html = '';
            let inOl = false, inUl = false;

            lines.forEach(line => {
                let trimmed = line.trim();
                if (!trimmed) {
                    if (inOl) { html += '</ol>'; inOl = false; }
                    if (inUl) { html += '</ul>'; inUl = false; }
                    return;
                }

                // Cek alignment
                let alignAttr = '';
                const alignMatch = trimmed.match(/^<(?:p|div)\s+align="(left|center|right|justify)"\s*>(.*?)(?:<\/(?:p|div)>)?$/i);
                if (alignMatch) {
                    alignAttr = ` style="text-align: ${alignMatch[1].toLowerCase()};"`;
                    trimmed = alignMatch[2].trim();
                }

                // Inline markdown
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

        function checkEditorPlaceholder() {
            const editor = document.getElementById('modalBannerRichEditor');
            if (!editor) return;
            const text = editor.innerText.trim();
            if (!text && !editor.querySelector('img')) {
                editor.classList.add('is-empty');
            } else {
                editor.classList.remove('is-empty');
            }
        }

        // Inisialisasi event listener editor dan form banner
        document.addEventListener('DOMContentLoaded', function() {
            const editor = document.getElementById('modalBannerRichEditor');
            const form = document.getElementById('formBanner');

            if (editor) {
                editor.addEventListener('focus', function() {
                    try {
                        document.execCommand('defaultParagraphSeparator', false, 'p');
                    } catch(e) {}
                });

                ['input', 'keyup', 'mouseup', 'paste', 'blur'].forEach(evt => {
                    editor.addEventListener(evt, function() {
                        syncBannerEditorContent();
                        updateToolbarStates();
                        checkEditorPlaceholder();
                    });
                });

                // Auto-detection format penomoran/simbol saat mengetik "1. " atau "- " atau "• "
                editor.addEventListener('keydown', function(e) {
                    if (e.key === ' ') {
                        const sel = window.getSelection();
                        if (sel && sel.isCollapsed && sel.anchorNode) {
                            const node = sel.anchorNode;
                            const text = node.textContent || '';
                            const offset = sel.anchorOffset;
                            const beforeText = text.substring(0, offset).trim();

                            if (beforeText === '1.' || beforeText === '1)') {
                                e.preventDefault();
                                node.textContent = text.substring(offset);
                                execBannerEditor('insertOrderedList');
                                return;
                            } else if (beforeText === '-' || beforeText === '*' || beforeText === '•') {
                                e.preventDefault();
                                node.textContent = text.substring(offset);
                                execBannerEditor('insertUnorderedList');
                                return;
                            }
                        }
                    }
                });
            }

            if (form) {
                form.addEventListener('submit', function() {
                    syncBannerEditorContent();
                });
            }
        });

        function openModalBanner() {
            document.getElementById('modalBannerTitle').innerText = 'Tambah Banner Baru';
            document.getElementById('modalBannerId').value = '';
            document.getElementById('modalBannerRemoveImage').value = '0';
            document.getElementById('modalBannerTitleInput').value = '';
            document.getElementById('modalBannerOrder').value = '0';
            document.getElementById('modalBannerType').value = 'info';
            
            const editor = document.getElementById('modalBannerRichEditor');
            if (editor) {
                editor.innerHTML = '';
                checkEditorPlaceholder();
            }
            document.getElementById('modalBannerContent').value = '';
            
            document.getElementById('modalBannerImageFile').value = '';
            document.getElementById('modalBannerLink').value = '';
            document.getElementById('modalBannerIsActive').checked = true;
            document.getElementById('modalBannerImagePreviewBox').classList.add('hidden');
            document.getElementById('modalBannerImagePreviewImg').src = '';
            
            const previewWrapper = document.getElementById('modalBannerPreviewWrapper');
            if (previewWrapper) previewWrapper.classList.add('hidden');
            
            updateBannerLivePreview();
            document.getElementById('modalBanner').classList.remove('hidden');
        }

        function editBannerItem(banner) {
            document.getElementById('modalBannerTitle').innerText = 'Edit Banner #' + banner.id;
            document.getElementById('modalBannerId').value = banner.id;
            document.getElementById('modalBannerRemoveImage').value = '0';
            document.getElementById('modalBannerTitleInput').value = banner.title || '';
            document.getElementById('modalBannerOrder').value = banner.order_num || 0;
            document.getElementById('modalBannerType').value = banner.type || 'info';
            
            const rawContent = banner.content || '';
            const editor = document.getElementById('modalBannerRichEditor');
            if (editor) {
                if (!rawContent.trim()) {
                    editor.innerHTML = '';
                } else if (/<(?:p|div|ol|ul|li|b|strong|i|em|u|br|span|h[1-6])[\s>]/i.test(rawContent)) {
                    editor.innerHTML = rawContent;
                } else {
                    editor.innerHTML = convertPlainTextToEditorHtml(rawContent);
                }
                checkEditorPlaceholder();
            }
            document.getElementById('modalBannerContent').value = editor ? editor.innerHTML : rawContent;

            document.getElementById('modalBannerImageFile').value = '';
            document.getElementById('modalBannerLink').value = banner.link_url || '';
            document.getElementById('modalBannerIsActive').checked = parseInt(banner.is_active) === 1;

            const previewBox = document.getElementById('modalBannerImagePreviewBox');
            const previewImg = document.getElementById('modalBannerImagePreviewImg');
            if (banner.image_path) {
                previewImg.src = banner.image_path.startsWith('/') ? banner.image_path : '/' + banner.image_path;
                previewBox.classList.remove('hidden');
            } else {
                previewImg.src = '';
                previewBox.classList.add('hidden');
            }

            const previewWrapper = document.getElementById('modalBannerPreviewWrapper');
            if (previewWrapper && !previewWrapper.classList.contains('hidden')) {
                updateBannerLivePreview();
            }

            document.getElementById('modalBanner').classList.remove('hidden');
        }

        function closeModalBanner() {
            document.getElementById('modalBanner').classList.add('hidden');
        }

        function previewBannerFileInput(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('modalBannerImagePreviewImg').src = e.target.result;
                    document.getElementById('modalBannerImagePreviewBox').classList.remove('hidden');
                    document.getElementById('modalBannerRemoveImage').value = '0';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function removeModalBannerImage() {
            document.getElementById('modalBannerImageFile').value = '';
            document.getElementById('modalBannerImagePreviewImg').src = '';
            document.getElementById('modalBannerImagePreviewBox').classList.add('hidden');
            document.getElementById('modalBannerRemoveImage').value = '1';
        }

        function toggleBannerStatus(id) {
            fetch('{{ route("admin.toggle_banner_status") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id: id })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    location.reload();
                } else {
                    alert('Gagal: ' + (res.message || ''));
                }
            })
            .catch(() => alert('Terjadi kendala jaringan.'));
        }

        function deleteBannerItem(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus banner ini secara permanen?')) return;

            fetch('{{ route("admin.delete_banner_item") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id: id })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + (res.message || ''));
                }
            })
            .catch(() => alert('Terjadi kendala jaringan.'));
        }

        // NJKB Search & Modal
        let searchTimeout = null;
        function handleNjkbSearch(event) {
            clearTimeout(searchTimeout);
            const q = event.target.value.trim();
            if (q.length < 2) return;

            searchTimeout = setTimeout(() => {
                fetch('{{ route("admin.search_njkb") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ q: q })
                })
                .then(r => r.json())
                .then(res => {
                    const tbody = document.getElementById('njkbTableBody');
                    if (res.status === 'success' && res.data.length > 0) {
                        tbody.innerHTML = res.data.map(item => `
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-2.5 font-bold text-slate-900" data-label="Merk">${item.merk}</td>
                                <td class="px-4 py-2.5 font-sans" data-label="Tipe">${item.tipe}</td>
                                <td class="px-4 py-2.5" data-label="Tahun">${item.tahun_buat}</td>
                                <td class="px-4 py-2.5 text-slate-600" data-label="Bobot">${parseFloat(item.bobot).toFixed(3)}</td>
                                <td class="px-4 py-2.5 font-bold text-emerald-700" data-label="Nilai Jual">Rp ${parseInt(item.njkb).toLocaleString('id-ID')}</td>
                                <td class="px-4 py-2.5 text-right space-x-2 font-sans" data-label="Aksi" data-action="true">
                                    <button type="button" onclick="editNjkbItem(${JSON.stringify(item).replace(/"/g, '&quot;')})" class="text-blue-600 hover:text-blue-800 font-bold">Edit</button>
                                    <button type="button" onclick="deleteNjkbItem(${item.id})" class="text-rose-600 hover:text-rose-800 font-bold">Hapus</button>
                                </td>
                            </tr>
                        `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-slate-400 font-sans">Tidak ada data NJKB yang sesuai.</td></tr>';
                    }
                });
            }, 300);
        }

        function openModalNjkb() {
            document.getElementById('modalNjkbTitle').innerText = 'Tambah Data NJKB';
            document.getElementById('modalNjkbId').value = '';
            document.getElementById('modalNjkbMerk').value = '';
            document.getElementById('modalNjkbTipe').value = '';
            document.getElementById('modalNjkbTahun').value = new Date().getFullYear();
            document.getElementById('modalNjkbBobot').value = '1.000';
            document.getElementById('modalNjkbNilai').value = '';
            document.getElementById('modalNjkb').classList.remove('hidden');
        }

        function editNjkbItem(item) {
            document.getElementById('modalNjkbTitle').innerText = 'Edit Data NJKB #' + item.id;
            document.getElementById('modalNjkbId').value = item.id;
            document.getElementById('modalNjkbMerk').value = item.merk;
            document.getElementById('modalNjkbTipe').value = item.tipe;
            document.getElementById('modalNjkbTahun').value = item.tahun_buat;
            document.getElementById('modalNjkbBobot').value = parseFloat(item.bobot).toFixed(3);
            document.getElementById('modalNjkbNilai').value = item.njkb;
            document.getElementById('modalNjkb').classList.remove('hidden');
        }

        function closeModalNjkb() {
            document.getElementById('modalNjkb').classList.add('hidden');
        }

        function submitFormNjkb(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSaveNjkb');
            btn.disabled = true;
            btn.innerText = 'Menyimpan...';

            const payload = {
                id: document.getElementById('modalNjkbId').value,
                merk: document.getElementById('modalNjkbMerk').value,
                tipe: document.getElementById('modalNjkbTipe').value,
                tahun_buat: document.getElementById('modalNjkbTahun').value,
                bobot: document.getElementById('modalNjkbBobot').value,
                njkb: document.getElementById('modalNjkbNilai').value,
            };

            fetch('{{ route("admin.save_njkb") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                btn.innerText = 'Simpan Data';
                if (res.status === 'success') {
                    closeModalNjkb();
                    alert(res.message);
                    handleNjkbSearch({ target: { value: payload.tipe } });
                } else {
                    alert('Gagal: ' + (res.message || 'Terjadi kesalahan'));
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerText = 'Simpan Data';
                alert('Gagal menyimpan data NJKB.');
            });
        }

        function deleteNjkbItem(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus data NJKB ini?')) return;

            fetch('{{ route("admin.delete_njkb") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id: id })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message);
                    const q = document.getElementById('njkbSearchInput').value;
                    handleNjkbSearch({ target: { value: q } });
                } else {
                    alert('Gagal menghapus: ' + (res.message || ''));
                }
            })
            .catch(() => alert('Gagal menghapus data.'));
        }

        function runAdminSimulasi(e) {
            e.preventDefault();
            const nopol = document.getElementById('adminSimulasiNopol').value.trim();
            if (!nopol) return;

            const btn = document.getElementById('btnAdminSimulasi');
            btn.disabled = true;
            btn.innerText = 'Menghitung...';

            const resultContainer = document.getElementById('adminSimulasiResult');
            const content = document.getElementById('adminSimulasiContent');

            fetch('{{ url("/api/pwa/simulate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ nopol: nopol })
            })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                btn.innerText = 'Hitung Komparasi';

                if (res.status === 'success' && res.data) {
                    const kd = res.data.kendaraan;
                    const sim = res.data.simulasi;
                    resultContainer.classList.remove('hidden');

                    content.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-2 text-xs">
                                <span class="font-bold text-slate-800 uppercase block tracking-wider text-[11px]">Identitas Kendaraan</span>
                                <div class="font-mono font-black text-sm text-slate-900">${kd.nopol} - ${kd.unit || '-'}</div>
                                <div class="text-slate-600">Pemilik: <b>${kd.pemilik || '-'}</b></div>
                                <div class="text-slate-600">UPT Asal: <b>${kd.upt_asal || '-'}</b></div>
                                <div class="text-slate-600">Masa Pajak: <b>${kd.sd_notice || '-'}</b></div>
                                <div class="text-slate-600">NJKB: <b>Rp ${parseInt(kd.njkb || 0).toLocaleString('id-ID')}</b> (Bobot: ${kd.bobot})</div>
                            </div>

                            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl space-y-2 text-xs text-emerald-950">
                                <span class="font-bold uppercase tracking-wider text-[11px] text-emerald-800 block">Komparasi Relaksasi</span>
                                <div class="flex justify-between items-center py-1 border-b border-emerald-200/60">
                                    <span>Penetapan Normal:</span>
                                    <span class="font-bold font-mono">Rp ${parseInt(sim.normal?.total_biaya || 0).toLocaleString('id-ID')}</span>
                                </div>
                                <div class="flex justify-between items-center py-1 border-b border-emerald-200/60">
                                    <span>Penetapan Relaksasi:</span>
                                    <span class="font-bold font-mono text-emerald-700 text-sm">Rp ${parseInt(sim.relaksasi?.total_biaya || 0).toLocaleString('id-ID')}</span>
                                </div>
                                <div class="flex justify-between items-center py-1 text-emerald-700 font-bold">
                                    <span>Total Penghematan WP:</span>
                                    <span class="font-mono text-sm">Rp ${parseInt(sim.selisih_penghematan || 0).toLocaleString('id-ID')}</span>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    resultContainer.classList.remove('hidden');
                    content.innerHTML = `<div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-semibold">${res.message || 'Data nopol tidak ditemukan.'}</div>`;
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerText = 'Hitung Komparasi';
                alert('Terjadi kesalahan jaringan.');
            });
        }

        /* Auto-Logout Countdown & Inactivity Monitor (30 Menit = 1800 Detik) */
        (function() {
            let timeLeft = 1800;
            const timerEl = document.getElementById('sessionTimerText');

            function formatTime(sec) {
                const m = Math.floor(sec / 60);
                const s = sec % 60;
                return `${m}:${s < 10 ? '0' : ''}${s}`;
            }

            function resetInactivity() {
                timeLeft = 1800;
                if (timerEl) timerEl.innerText = formatTime(timeLeft);
            }

            ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll'].forEach(evt => {
                window.addEventListener(evt, resetInactivity, { passive: true });
            });

            setInterval(function() {
                timeLeft--;
                if (timerEl) {
                    timerEl.innerText = formatTime(Math.max(0, timeLeft));
                    if (timeLeft <= 60) {
                        timerEl.parentElement.className = 'flex items-center gap-1.5 px-2.5 py-1.5 bg-rose-50 border border-rose-300 text-rose-700 rounded-xl text-[11px] font-mono font-bold animate-pulse';
                    } else {
                        timerEl.parentElement.className = 'flex items-center gap-1.5 px-2.5 py-1.5 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-[11px] font-mono font-bold';
                    }
                }

                if (timeLeft <= 0) {
                    window.location.href = "{{ route('admin.logout') }}";
                }
            }, 1000);
        })();
    </script>
</body>
</html>
