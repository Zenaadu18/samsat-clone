<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pajak.In</title>

    <!-- PWA Primary Meta Tags -->
    <meta name="theme-color" content="#059669">
    <meta name="description" content="Aplikasi Pajak.In untuk pengecekan estimasi pajak kendaraan bermotor, simulasi relaksasi denda, dan info NJKB.">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- iOS Meta Tags & Icons -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Pajak.In">
    <link rel="apple-touch-icon" href="{{ asset('icons/logoapps.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">

    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

    <!-- Client API Configuration -->
    <script>
        window.__SAMSAT_CONFIG__ = {
            baseUrl: "{{ url('/') }}",
            apiUrl: "{{ url('/api/pwa') }}",
            assetUrl: "{{ asset('/') }}",
            csrfToken: "{{ csrf_token() }}"
        };
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    <style>
        html, body {
            width: 100%;
            max-width: 100vw;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            overscroll-behavior-x: none;
        }
        *, *::before, *::after {
            box-sizing: border-box;
        }
    </style>
</head>
<body class="bg-slate-100 font-sans antialiased text-slate-800 select-none overflow-x-hidden w-full max-w-full">
    <div id="root" class="w-full max-w-full overflow-x-hidden min-h-screen"></div>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                const swUrl = "{{ asset('sw.js') }}";
                navigator.serviceWorker.register(swUrl)
                    .then(reg => {
                        console.log('[PWA] Service Worker active with scope:', reg.scope);
                    })
                    .catch(err => {
                        console.warn('[PWA] Service Worker registration failed:', err);
                    });
            });
        }
    </script>
</body>
</html>
