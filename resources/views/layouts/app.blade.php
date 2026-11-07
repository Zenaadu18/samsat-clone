<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <meta name="description" content="Portal Layanan BAPENDA NTT - Pusat Layanan Informasi Pajak dan Aset Daerah Provinsi NTT">
    <title>@yield('title', 'Portal Layanan BAPENDA NTT')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen text-slate-800 antialiased flex flex-col">
    
    <!-- Top Navigation (Consistent across all pages) -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto max-w-5xl px-4 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-blue-500 text-white flex items-center justify-center shadow-md group-hover:shadow-blue-500/25 transition-all">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
                <span class="font-bold text-lg tracking-tight text-slate-900 group-hover:text-blue-600 transition-colors">BAPENDA NTT</span>
            </a>
            <div class="flex items-center gap-1">
                <a href="/" class="p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900 rounded-lg transition-colors" title="Beranda">
                    <i data-lucide="home" class="w-5 h-5"></i>
                </a>
                <a href="/admin" class="p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900 rounded-lg transition-colors" title="Panel Admin">
                    <i data-lucide="settings" class="w-5 h-5"></i>
                </a>
            </div>
        </div>
    </nav>

    <main class="flex-grow container mx-auto max-w-5xl px-4 py-8 md:py-12">
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-auto">
        <div class="container mx-auto max-w-5xl px-4 py-8 text-center md:text-left md:flex justify-between items-center text-sm text-slate-500">
            <div>
                <p class="font-medium text-slate-900">&copy; {{ date('Y') }} Badan Pendapatan dan Aset Daerah NTT.</p>
                <p class="mt-1">Portal Layanan Informasi Pajak Resmi</p>
            </div>
            <div class="mt-4 md:mt-0 flex justify-center gap-4">
                <a href="/hitung-pajak" class="hover:text-blue-600 transition-colors">Cek Pajak</a>
                <a href="/cek-njkb" class="hover:text-blue-600 transition-colors">Cek NJKB</a>
                <a href="/pajak-massal" class="hover:text-blue-600 transition-colors">Pajak Massal</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
