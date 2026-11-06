@extends('layouts.app')
@section('title', 'Portal Layanan BAPENDA NTT')

@section('content')
<div class="w-full">
    <!-- HERO -->
    <header class="relative overflow-hidden mb-12 p-8 md:p-12 border border-white/80 rounded-[2rem] shadow-sm bg-gradient-to-br from-white/95 to-slate-50 text-center">
        <!-- Background decorative blobs -->
        <div class="absolute w-72 h-72 -top-40 -right-24 bg-blue-600/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute w-60 h-60 -bottom-32 -left-16 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col items-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 mb-8 border border-blue-100 rounded-full bg-blue-50/50 text-blue-700 text-xs font-bold tracking-wider uppercase">
                <span class="relative flex h-2.5 w-2.5">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-blue-500"></span>
                </span>
                LAYANAN DIGITAL TERPADU
            </div>

            <div class="w-20 h-20 mb-6 flex items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/30">
                <i data-lucide="landmark" class="w-10 h-10"></i>
            </div>

            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4 text-slate-900 max-w-3xl">
                Portal Layanan <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">BAPENDA NTT</span>
            </h1>

            <p class="text-slate-500 text-lg max-w-2xl leading-relaxed mb-8">
                Pusat layanan informasi Pajak Kendaraan Bermotor dan Aset Daerah Provinsi Nusa Tenggara Timur yang cepat, transparan, dan terpercaya.
            </p>

            <a href="#layanan" class="inline-flex items-center justify-center h-12 px-8 rounded-full bg-slate-900 text-white font-medium hover:bg-blue-600 transition-colors gap-2 shadow-lg shadow-slate-900/20 group">
                Mulai Gunakan Layanan
                <i data-lucide="arrow-down" class="w-4 h-4 group-hover:translate-y-1 transition-transform"></i>
            </a>
        </div>
    </header>

    <!-- SERVICE MENU -->
    <section id="layanan" class="scroll-mt-24">
        <div class="mb-8 text-center md:text-left">
            <h2 class="text-2xl font-bold text-slate-900 mb-2">Layanan Utama</h2>
            <p class="text-slate-500">Pilih layanan perpajakan yang ingin Anda gunakan hari ini</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
            <!-- PAJAK KENDARAAN -->
            <a href="/hitung-pajak" class="group flex flex-col p-8 rounded-[1.5rem] bg-white border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-blue-500/5 hover:border-blue-100 transition-all duration-300 transform hover:-translate-y-1 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-blue-50 to-transparent rounded-bl-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
                <div class="relative">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-6 bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300 shadow-inner">
                        <i data-lucide="calculator" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Cek Pajak Kendaraan</h3>
                    <p class="text-slate-500 text-sm leading-relaxed mb-8">
                        Hitung estimasi tagihan Pajak Kendaraan Bermotor (PKB), perhitungan denda otomatis, dan biaya SWDKLLJ.
                    </p>
                    <div class="flex items-center gap-2 text-sm font-bold text-blue-600 mt-auto">
                        <span>Buka kalkulator</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            <!-- NJKB -->
            <a href="/cek-njkb" class="group flex flex-col p-8 rounded-[1.5rem] bg-white border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-orange-500/5 hover:border-orange-100 transition-all duration-300 transform hover:-translate-y-1 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-orange-50 to-transparent rounded-bl-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
                <div class="relative">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-6 bg-orange-50 text-orange-500 group-hover:bg-orange-500 group-hover:text-white transition-colors duration-300 shadow-inner">
                        <i data-lucide="file-search" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Data NJKB</h3>
                    <p class="text-slate-500 text-sm leading-relaxed mb-8">
                        Cari Nilai Jual Kendaraan Bermotor berdasarkan merek, tipe kendaraan dan tahun pembuatan secara akurat.
                    </p>
                    <div class="flex items-center gap-2 text-sm font-bold text-orange-500 mt-auto">
                        <span>Lihat database</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            <!-- PAJAK MASSAL -->
            <a href="/pajak-massal" class="group flex flex-col p-8 rounded-[1.5rem] bg-white border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-purple-500/5 hover:border-purple-100 transition-all duration-300 transform hover:-translate-y-1 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-purple-50 to-transparent rounded-bl-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
                <div class="relative">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-6 bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300 shadow-inner">
                        <i data-lucide="file-spreadsheet" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Cek Pajak Massal</h3>
                    <p class="text-slate-500 text-sm leading-relaxed mb-8">
                        Unggah file TXT untuk memproses puluhan hingga ratusan nomor polisi sekaligus dan unduh rekap tagihan.
                    </p>
                    <div class="flex items-center gap-2 text-sm font-bold text-purple-600 mt-auto">
                        <span>Proses batch</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>

            <!-- ADMIN -->
            <a href="/admin" class="group flex flex-col p-8 rounded-[1.5rem] bg-white border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-emerald-500/5 hover:border-emerald-100 transition-all duration-300 transform hover:-translate-y-1 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-emerald-50 to-transparent rounded-bl-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
                <div class="relative">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-6 bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300 shadow-inner">
                        <i data-lucide="settings-2" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Panel Konfigurasi</h3>
                    <p class="text-slate-500 text-sm leading-relaxed mb-8">
                        Kelola tarif dasar, masa amnesti pajak daerah, pengaturan denda, serta sambungan API Samsat pusat.
                    </p>
                    <div class="flex items-center gap-2 text-sm font-bold text-emerald-600 mt-auto">
                        <span>Kelola sistem</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>
        </div>
    </section>
</div>
@endsection
