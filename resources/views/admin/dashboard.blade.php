<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen font-sans flex flex-col selection:bg-emerald-500 selection:text-white">

    <!-- Header Navbar Atas -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200/90 px-4 sm:px-6 py-3 shadow-xs">
        <div class="flex items-center justify-between gap-4">
            <!-- Mobile Toggle & Brand -->
            <div class="flex items-center gap-3">
                <button type="button" onclick="toggleSidebar()" class="md:hidden p-2 text-slate-500 hover:text-slate-800 rounded-xl hover:bg-slate-100 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
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
                        <button type="button" onclick="openModalUser()" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-emerald-600/20 transition-all flex items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Tambah Pengguna Baru</span>
                        </button>
                    </div>

                    <!-- Tabel Users -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">ID</th>
                                    <th class="px-4 py-3">Username</th>
                                    <th class="px-4 py-3">Nama Asli / Lengkap</th>
                                    <th class="px-4 py-3">Role / Akses</th>
                                    <th class="px-4 py-3">Sesi Perangkat</th>
                                    <th class="px-4 py-3">Status Akses</th>
                                    <th class="px-4 py-3">Login Terakhir</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($usersList as $u)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-3 font-mono text-slate-400">#{{ $u->id }}</td>
                                        <td class="px-4 py-3 font-mono font-bold text-slate-900">{{ $u->username }}</td>
                                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $u->name }}</td>
                                        <td class="px-4 py-3">
                                            @if(($u->role ?? 'user') === 'admin')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                                    Administrator
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                    Pengguna Biasa
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if(($u->role ?? 'user') === 'admin' || ($u->role_id ?? 0) == 1)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200" title="Admin dapat login bersamaan di banyak perangkat tanpa batasan">
                                                    <svg class="w-3 h-3 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                    Bebas (Multi-Device)
                                                </span>
                                            @elseif(!empty($u->api_token) && !empty($u->device_id))
                                                <div class="flex items-center gap-1.5" title="Sesi aktif di {{ $u->device_name }} (Terakhir aktif: {{ $u->last_activity_at ? date('d/m/Y H:i', strtotime($u->last_activity_at)) : '-' }})">
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                        <span class="truncate max-w-[130px]">Aktif: {{ $u->device_name ?: 'Perangkat' }}</span>
                                                    </span>
                                                </div>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                    Tidak Ada Sesi Aktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($u->is_blocked)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                    Diblokir
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Aktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-500 font-mono text-[11px]">
                                            {{ $u->last_login_at ? date('d/m/Y H:i', strtotime($u->last_login_at)) : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right space-x-1">
                                            @if((($u->role ?? 'user') !== 'admin' && ($u->role_id ?? 0) != 1) && (!empty($u->api_token) || !empty($u->device_id)))
                                                <button type="button" 
                                                        onclick="resetUserDevice({{ $u->id }}, '{{ $u->username }}')"
                                                        class="px-2.5 py-1.5 rounded-lg border border-purple-200 bg-purple-50 hover:bg-purple-100 text-purple-700 text-[11px] font-semibold cursor-pointer"
                                                        title="Logout / Putuskan Sesi Perangkat Pengguna">
                                                    Logout Sesi
                                                </button>
                                            @endif
                                            <button type="button" 
                                                    onclick="editUser({{ json_encode($u) }})"
                                                    class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-[11px] font-semibold cursor-pointer">
                                                Edit
                                            </button>
                                            <button type="button" 
                                                    onclick="toggleBlockUser({{ $u->id }}, {{ $u->is_blocked }})"
                                                    class="px-2.5 py-1.5 rounded-lg border text-[11px] font-semibold cursor-pointer {{ $u->is_blocked ? 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                                                {{ $u->is_blocked ? 'Aktifkan' : 'Blokir' }}
                                            </button>
                                            <button type="button" 
                                                    onclick="deleteUser({{ $u->id }}, '{{ $u->username }}')"
                                                    class="px-2.5 py-1.5 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] font-semibold cursor-pointer">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                            Belum ada pengguna terdaftar. Klik "Tambah Pengguna Baru" untuk membuat akun.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
                            <p class="text-xs text-slate-500 mt-1">Pencatatan aktivitas login, cek nomor polisi, simulasi perhitungan, dan balik nama BBN 2.</p>
                        </div>

                        <!-- Search Filter -->
                        <div class="relative w-full sm:w-64">
                            <input type="text" id="logSearchInput" oninput="debounceLogSearch(event)" placeholder="Cari nopol, user, IP..."
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 pl-9 text-xs text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:outline-none">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-xs text-slate-700" id="tableLogs">
                            <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">Waktu</th>
                                    <th class="px-4 py-3">Username &amp; Nama</th>
                                    <th class="px-4 py-3">Aktivitas</th>
                                    <th class="px-4 py-3">Detail Data</th>
                                    <th class="px-4 py-3">Total Biaya</th>
                                    <th class="px-4 py-3">IP Address</th>
                                </tr>
                            </thead>
                            <tbody id="logsTableBody" class="divide-y divide-slate-100 bg-white">
                                @forelse($recentActivities as $log)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-3 text-slate-500 font-mono text-[11px] whitespace-nowrap">
                                            {{ date('d/m/Y H:i:s', strtotime($log->created_at)) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-mono font-bold text-slate-900 block">{{ $log->username }}</span>
                                            <span class="text-[10px] text-slate-500">{{ $log->user_name ?? '-' }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                {{ $log->activity }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-700 font-medium max-w-xs truncate" title="{{ $log->detail }}">
                                            {{ $log->detail ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 font-mono font-bold text-emerald-700 text-xs whitespace-nowrap">
                                            @if(!empty($log->total_biaya) && $log->total_biaya > 0)
                                                Rp {{ number_format($log->total_biaya, 0, ',', '.') }}
                                            @else
                                                <span class="text-slate-400 font-normal">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 font-mono text-slate-500 text-[11px]">
                                            {{ $log->ip_address ?? '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                            Belum ada aktivitas tercatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div id="logsPagination" class="flex justify-end gap-2 pt-2"></div>
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
                        <button type="button" onclick="openModalBanner()" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-amber-600/20 transition-all flex items-center gap-2 cursor-pointer shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Tambah Banner Baru</span>
                        </button>
                    </div>

                    <!-- Petunjuk Ringkas -->
                    <div class="p-3.5 bg-amber-50/70 border border-amber-200/80 rounded-2xl flex items-start gap-2.5 text-xs text-amber-900">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>
                            <b>Format Fleksibel:</b> Banner dapat berupa flyer gambar penuh, teks pengumuman, atau keduanya. Jika seluruh banner dinonaktifkan atau dihapus, popup banner di sisi user tidak akan muncul.
                        </span>
                    </div>

                    <!-- Daftar Banner (Tabel) -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-xs text-slate-700">
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
                                        <td class="px-4 py-3 text-center font-mono font-bold text-slate-700">
                                            <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-[11px]">
                                                #{{ $b->order_num }}
                                            </span>
                                        </td>

                                        <!-- Preview Thumbnail Gambar -->
                                        <td class="px-4 py-3">
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
                                        <td class="px-4 py-3">
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
                                        <td class="px-4 py-3 max-w-xs">
                                            <p class="text-slate-600 line-clamp-2 leading-relaxed" title="{{ $b->content }}">
                                                {{ $b->content ?: '-' }}
                                            </p>
                                        </td>

                                        <!-- Tautan Luar -->
                                        <td class="px-4 py-3">
                                            @if($b->link_url)
                                                <a href="{{ $b->link_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline truncate block max-w-[140px] text-[11px]">
                                                    {{ $b->link_url }}
                                                </a>
                                            @else
                                                <span class="text-slate-400 text-[11px]">-</span>
                                            @endif
                                        </td>

                                        <!-- Status Tayang Toggle -->
                                        <td class="px-4 py-3 text-center">
                                            <button type="button" 
                                                    onclick="toggleBannerStatus({{ $b->id }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold cursor-pointer transition-all {{ $b->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $b->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                                <span>{{ $b->is_active ? 'Aktif Tayang' : 'Nonaktif' }}</span>
                                            </button>
                                        </td>

                                        <!-- Aksi -->
                                        <td class="px-4 py-3 text-right space-x-1.5 whitespace-nowrap">
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
                        <div class="overflow-x-auto rounded-2xl border border-slate-200">
                            <table class="w-full text-left text-xs text-slate-700">
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
                                            <td class="px-4 py-2.5 font-bold text-slate-900">{{ $pkb->k_guna }}</td>
                                            <td class="px-4 py-2.5">
                                                <input type="text" name="tarif_pkb[{{ $pkb->k_guna }}][lama]" value="{{ $pkb->tarif_lama }}" class="w-24 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs">
                                                <span class="text-slate-400">({{ (float)$pkb->tarif_lama * 100 }}%)</span>
                                            </td>
                                            <td class="px-4 py-2.5">
                                                <input type="text" name="tarif_pkb[{{ $pkb->k_guna }}][baru]" value="{{ $pkb->tarif_baru }}" class="w-24 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs font-bold text-emerald-700">
                                                <span class="text-emerald-700 font-bold">({{ (float)$pkb->tarif_baru * 100 }}%)</span>
                                            </td>
                                            <td class="px-4 py-2.5 font-sans text-slate-600">
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
                        <div class="overflow-x-auto rounded-2xl border border-slate-200">
                            <table class="w-full text-left text-xs text-slate-700">
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
                                            <td class="px-4 py-2.5 font-bold text-slate-900">{{ $stnk->jenis }}</td>
                                            <td class="px-4 py-2.5">
                                                <input type="number" name="tarif_stnk[{{ $stnk->jenis }}][stnk]" value="{{ $stnk->biaya_stnk }}" class="w-32 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs">
                                            </td>
                                            <td class="px-4 py-2.5">
                                                <input type="number" name="tarif_stnk[{{ $stnk->jenis }}][tnkb]" value="{{ $stnk->biaya_tnkb }}" class="w-32 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs">
                                            </td>
                                            <td class="px-4 py-2.5 font-sans text-slate-600">
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
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">3. Tarif Penerbitan BPKB &amp; Jasa Cek Fisik Bantuan (BBN 2)</h3>
                            <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer">
                                Simpan Parameter BBN 2
                            </button>
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

                        <div class="overflow-x-auto rounded-2xl border border-slate-200">
                            <table class="w-full text-left text-xs text-slate-700">
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
                                            <td class="px-4 py-2.5 font-bold text-slate-900">{{ $sw->golongan }}</td>
                                            <td class="px-4 py-2.5"><input type="number" name="tarif_swdkllj[{{ $sw->golongan }}][tarif]" value="{{ $sw->tarif }}" class="w-24 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs"></td>
                                            <td class="px-4 py-2.5"><input type="number" name="tarif_swdkllj[{{ $sw->golongan }}][admin]" value="{{ $sw->admin }}" class="w-20 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs"></td>
                                            <td class="px-4 py-2.5"><input type="number" name="tarif_swdkllj[{{ $sw->golongan }}][t1]" value="{{ $sw->denda_t1 }}" class="w-20 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs"></td>
                                            <td class="px-4 py-2.5"><input type="number" name="tarif_swdkllj[{{ $sw->golongan }}][t2]" value="{{ $sw->denda_t2 }}" class="w-20 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs"></td>
                                            <td class="px-4 py-2.5"><input type="number" name="tarif_swdkllj[{{ $sw->golongan }}][t3]" value="{{ $sw->denda_t3 }}" class="w-20 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs"></td>
                                            <td class="px-4 py-2.5"><input type="number" name="tarif_swdkllj[{{ $sw->golongan }}][t4]" value="{{ $sw->denda_t4 }}" class="w-20 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs"></td>
                                            <td class="px-4 py-2.5 font-sans text-slate-600"><input type="text" name="tarif_swdkllj[{{ $sw->golongan }}][keterangan]" value="{{ $sw->keterangan }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs"></td>
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
                    <div class="overflow-x-auto rounded-2xl border border-slate-200 max-h-96 custom-scrollbar">
                        <table class="w-full text-left text-xs text-slate-700">
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
                                        <td class="px-4 py-2 font-bold text-slate-900">{{ $klas->keyword }}</td>
                                        <td class="px-4 py-2"><span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold">{{ $klas->golongan_swdkllj }}</span></td>
                                        <td class="px-4 py-2"><span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold">{{ $klas->jenis_kend }}</span></td>
                                        <td class="px-4 py-2 text-right">
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

                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-xs text-slate-700">
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
                    <label class="block font-bold text-slate-700 mb-1">Role / Hak Akses</label>
                    <select id="modalUserRole" name="role" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:bg-white font-semibold">
                        <option value="user">Pengguna Biasa (Hanya Cek Pajak, BBN 2, Riwayat)</option>
                        <option value="admin">Administrator (Akses Penuh termasuk Simulasi & NJKB)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Password</label>
                    <input type="password" id="modalUserPassword" name="password" placeholder="Kosongkan jika tidak ingin mengubah password saat edit"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:bg-white">
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
                    <label class="block font-bold text-slate-700 mb-1">Isi Pesan / Informasi Teks <span class="font-normal text-slate-400">(Opsional jika poster saja)</span></label>
                    <textarea id="modalBannerContent" name="content" rows="3" placeholder="Tuliskan keterangan detail atau pengumuman jika diperlukan..."
                              class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-slate-900 focus:ring-2 focus:ring-amber-500 focus:bg-white"></textarea>
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

            // Close mobile sidebar on select
            if (window.innerWidth < 768) {
                document.getElementById('admin-sidebar').classList.add('-translate-x-full');
            }
        }

        // Set default active menu
        switchSidebarMenu('users');

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

        // User Management Modals & Actions
        function openModalUser() {
            document.getElementById('modalUserTitle').innerText = 'Tambah Pengguna Baru';
            document.getElementById('modalUserId').value = '';
            document.getElementById('modalUserUsername').value = '';
            document.getElementById('modalUserName').value = '';
            document.getElementById('modalUserRole').value = 'user';
            document.getElementById('modalUserPassword').value = '';
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
            document.getElementById('modalUserRole').value = user.role || 'user';
            document.getElementById('modalUserPassword').value = '';
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

        // Banner Management Functions (Multi-Banner Slider)
        function openModalBanner() {
            document.getElementById('modalBannerTitle').innerText = 'Tambah Banner Baru';
            document.getElementById('modalBannerId').value = '';
            document.getElementById('modalBannerRemoveImage').value = '0';
            document.getElementById('modalBannerTitleInput').value = '';
            document.getElementById('modalBannerOrder').value = '0';
            document.getElementById('modalBannerType').value = 'info';
            document.getElementById('modalBannerContent').value = '';
            document.getElementById('modalBannerImageFile').value = '';
            document.getElementById('modalBannerLink').value = '';
            document.getElementById('modalBannerIsActive').checked = true;
            document.getElementById('modalBannerImagePreviewBox').classList.add('hidden');
            document.getElementById('modalBannerImagePreviewImg').src = '';
            document.getElementById('modalBanner').classList.remove('hidden');
        }

        function editBannerItem(banner) {
            document.getElementById('modalBannerTitle').innerText = 'Edit Banner #' + banner.id;
            document.getElementById('modalBannerId').value = banner.id;
            document.getElementById('modalBannerRemoveImage').value = '0';
            document.getElementById('modalBannerTitleInput').value = banner.title || '';
            document.getElementById('modalBannerOrder').value = banner.order_num || 0;
            document.getElementById('modalBannerType').value = banner.type || 'info';
            document.getElementById('modalBannerContent').value = banner.content || '';
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

        // Audit Logs Search & Pagination
        let logSearchTimer = null;
        function debounceLogSearch(e) {
            clearTimeout(logSearchTimer);
            logSearchTimer = setTimeout(() => {
                fetchLogs(e.target.value.trim());
            }, 300);
        }

        function fetchLogs(q = '', page = 1) {
            fetch(`{{ route("admin.activity_logs") }}?q=${encodeURIComponent(q)}&page=${page}`)
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        renderLogs(res.data.data);
                    }
                });
        }

        function renderLogs(logs) {
            const tbody = document.getElementById('logsTableBody');
            if (!logs || logs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Tidak ada riwayat aktivitas ditemukan.</td></tr>';
                return;
            }

            tbody.innerHTML = logs.map(l => `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 text-slate-500 font-mono text-[11px] whitespace-nowrap">
                        ${new Date(l.created_at).toLocaleString('id-ID')}
                    </td>
                    <td class="px-4 py-3">
                        <span class="font-mono font-bold text-slate-900 block">${l.username}</span>
                        <span class="text-[10px] text-slate-500">${l.user_name || '-'}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            ${l.activity}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-700 font-medium max-w-xs truncate" title="${l.detail || ''}">
                        ${l.detail || '-'}
                    </td>
                    <td class="px-4 py-3 font-mono font-bold text-emerald-700 text-xs whitespace-nowrap">
                        ${l.total_biaya && Number(l.total_biaya) > 0 ? 'Rp ' + Number(l.total_biaya).toLocaleString('id-ID') : '<span class="text-slate-400 font-normal">-</span>'}
                    </td>
                    <td class="px-4 py-3 font-mono text-slate-500 text-[11px]">
                        ${l.ip_address || '-'}
                    </td>
                </tr>
            `).join('');
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
                                <td class="px-4 py-2.5 font-bold text-slate-900">${item.merk}</td>
                                <td class="px-4 py-2.5 font-sans">${item.tipe}</td>
                                <td class="px-4 py-2.5">${item.tahun_buat}</td>
                                <td class="px-4 py-2.5 text-slate-600">${parseFloat(item.bobot).toFixed(3)}</td>
                                <td class="px-4 py-2.5 font-bold text-emerald-700">Rp ${parseInt(item.njkb).toLocaleString('id-ID')}</td>
                                <td class="px-4 py-2.5 text-right space-x-2 font-sans">
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
