<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — Pajak.In NTT</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
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
<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-4 relative overflow-hidden font-sans">
    <!-- Subtle Ambient Glows -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Logo / Brand Header -->
        <div class="text-center mb-7">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-emerald-600 shadow-xl shadow-emerald-500/20 mb-3.5 border border-emerald-400/30 overflow-hidden">
                <img src="{{ asset('icons/logoapps.png') }}" alt="Logo Pajak.In" class="w-full h-full object-cover">
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight flex items-center justify-center">
                <span class="brand-text-gradient">Pajak</span><span class="brand-dot-gold">.</span><span class="brand-text-gradient">In</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Panel Administrasi &amp; Manajemen Tarif Pajak NTT</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white border border-slate-200/90 rounded-3xl p-7 sm:p-8 shadow-xl shadow-slate-200/60">
            @if(!empty($errorLogin))
                <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3 text-xs text-rose-700">
                    <svg class="w-5 h-5 shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $errorLogin }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="login" value="1">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" 
                               name="username" 
                               required 
                               autofocus
                               placeholder="Masukkan username"
                               class="w-full bg-slate-50/70 border border-slate-200 rounded-xl pl-10 pr-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 focus:bg-white transition-all font-medium">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password" 
                               name="password" 
                               required 
                               placeholder="••••••••"
                               class="w-full bg-slate-50/70 border border-slate-200 rounded-xl pl-10 pr-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 focus:bg-white transition-all font-medium">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            class="w-full py-3.5 px-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-600/25 transition-all transform active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2">
                        <span>Masuk ke Panel Admin</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center border-t border-slate-100 pt-4">
                <a href="{{ url('/') }}" class="text-xs text-slate-500 hover:text-emerald-600 transition-colors inline-flex items-center gap-1.5 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Aplikasi Utama</span>
                </a>
            </div>
        </div>

        <p class="text-center text-[11px] text-slate-400 mt-6">
            &copy; {{ date('Y') }} Pajak.In NTT &bull; @sena. Hak Cipta Dilindungi.
        </p>
    </div>
</body>
</html>
