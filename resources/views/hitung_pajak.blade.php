@extends('layouts.app')
@section('title', 'Kalkulator Pajak Kendaraan Bermotor NTT')

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
@endpush

@section('content')
<div class="max-w-2xl mx-auto w-full">
    <!-- BACK LINK -->
    <a href="/" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium mb-6 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Beranda
    </a>

    <!-- HEADER -->
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-400 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-500/30">
            <i data-lucide="calculator" class="w-8 h-8 text-white"></i>
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Kalkulator Pajak Kendaraan</h1>
        <p class="text-slate-500 mt-2 font-medium">BAPENDA Provinsi Nusa Tenggara Timur</p>
    </div>

    <!-- MAIN CARD -->
    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-xl shadow-slate-200/50 border border-slate-100">
        
        <!-- FORM -->
        <form id="taxForm" class="space-y-6">
            <div>
                <label for="nopol" class="block text-sm font-semibold text-slate-700 mb-2">Nomor Polisi</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="car" class="w-5 h-5 text-slate-400"></i>
                    </div>
                    <input type="text" id="nopol" placeholder="Contoh: DH3156PF" required autocomplete="off" 
                           class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold tracking-widest text-lg uppercase focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 outline-none transition-all placeholder:text-slate-400 placeholder:font-normal placeholder:tracking-normal">
                </div>
            </div>

            <label class="flex items-center gap-3 cursor-pointer group p-3 rounded-xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
                <input type="checkbox" id="isMutasi" name="is_mutasi" class="w-5 h-5 rounded text-blue-600 focus:ring-blue-500 cursor-pointer">
                <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">Kendaraan Mutasi Masuk ke Wilayah NTT</span>
            </label>

            <button type="submit" id="submitBtn" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold text-lg py-4 rounded-xl shadow-lg shadow-blue-600/30 hover:shadow-blue-600/40 transform hover:-translate-y-0.5 transition-all flex items-center justify-center">
                <span id="btnText">Hitung Pajak</span>
                <i data-lucide="loader-2" id="btnLoader" class="w-6 h-6 animate-spin hidden"></i>
            </button>
        </form>

        <!-- ERROR -->
        <div id="errorMsg" class="hidden mt-6 bg-red-50 text-red-600 border border-red-200 rounded-xl p-4 text-sm font-medium flex items-start gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
            <span id="errorMsgText"></span>
        </div>

        <!-- RESULT -->
        <div id="resultContainer" class="hidden mt-10 space-y-8 animate-fade-in-up">
            
            <!-- IDENTITAS -->
            <div class="space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800">Identitas Kendaraan</h2>
                </div>
                
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                        <div class="divide-y divide-slate-100">
                            <div class="flex flex-col p-4">
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Nopol</span>
                                <span id="resNopol" class="font-bold text-lg text-blue-700 bg-blue-50 border border-blue-100 rounded-lg px-3 py-1 self-start">-</span>
                            </div>
                            <div class="flex flex-col p-4">
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Pemilik</span>
                                <span id="resPemilik" class="font-bold text-slate-900">-</span>
                            </div>
                            <div class="flex flex-col p-4">
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Unit Kendaraan</span>
                                <span id="resUnit" class="font-semibold text-slate-800">-</span>
                            </div>
                        </div>
                        <div class="divide-y divide-slate-100">
                            <div class="flex flex-col p-4">
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tahun</span>
                                <span id="resTahun" class="font-semibold text-slate-800">-</span>
                            </div>
                            <div class="flex flex-col p-4 bg-orange-50/50">
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Masa Pajak (PKB)</span>
                                <span id="resSdNotice" class="font-bold text-orange-700">-</span>
                            </div>
                            <div class="flex flex-col p-4 bg-blue-50/50">
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Masa Berlaku STNK</span>
                                <span id="resSdStnk" class="font-bold text-blue-700">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RINCIAN TAGIHAN -->
            <div class="space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800">Rincian Tagihan</h2>
                </div>
                
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm divide-y divide-slate-100">
                    
                    <div id="rowPkbBerjalanWrap" class="p-4 hover:bg-slate-50 transition-colors cursor-pointer group">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-blue-800">PKB</span>
                                <span id="badgePkbBerjalan" class="text-[10px] font-bold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full uppercase">Berjalan</span>
                                <i data-lucide="chevron-down" id="pkbToggleIcon" class="w-4 h-4 text-blue-500 hidden transition-transform"></i>
                            </div>
                            <span id="resPkbBerjalan" class="font-bold text-slate-900">Rp 0</span>
                        </div>
                        
                        <div id="pkbDetail" class="hidden mt-3 pt-3 border-t border-dashed border-slate-200 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Tarif Normal (Kotor)</span>
                                <span id="resPkbKotor" class="font-semibold text-slate-600">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-emerald-600 font-medium">Diskon Taat Bayar</span>
                                <span id="resPkbDiskon" class="font-bold text-emerald-600">- Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <div id="rowPkbTunggakan" class="hidden p-4 bg-orange-50/30">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-orange-800">Tunggakan PKB</span>
                                <span id="badgeTunggakan" class="text-[10px] font-bold bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full uppercase">-</span>
                            </div>
                            <span id="resPkbTunggakan" class="font-bold text-slate-900">Rp 0</span>
                        </div>
                    </div>

                    <div id="rowDendaPkb" class="hidden p-4">
                        <div class="flex justify-between items-center text-red-700">
                            <span class="font-medium">Denda PKB</span>
                            <span id="resDendaPkb" class="font-bold">Rp 0</span>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-slate-700">SWDKLLJ</span>
                            <span id="resSwdkllj" class="font-bold text-slate-900">Rp 0</span>
                        </div>
                    </div>

                    <div id="rowDendaSwdkllj" class="hidden p-4">
                        <div class="flex justify-between items-center text-red-700">
                            <span class="font-medium">Denda SWDKLLJ</span>
                            <span id="resDendaSwdkllj" class="font-bold">Rp 0</span>
                        </div>
                    </div>
                    
                    <div id="rowOpsen" class="hidden p-4">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-slate-700">Opsen PKB</span>
                            <span id="resOpsen" class="font-bold text-slate-900">Rp 0</span>
                        </div>
                    </div>

                    <div id="rowDendaOpsen" class="hidden p-4">
                        <div class="flex justify-between items-center text-red-700">
                            <span class="font-medium">Denda Opsen PKB</span>
                            <span id="resDendaOpsen" class="font-bold">Rp 0</span>
                        </div>
                    </div>

                    <div id="rowBiayaStnk" class="hidden p-4 bg-slate-50">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-slate-700">Biaya STNK</span>
                            <span id="resBiayaStnk" class="font-bold text-slate-900">Rp 0</span>
                        </div>
                    </div>

                    <div id="rowBiayaTnkb" class="hidden p-4 bg-slate-50">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-slate-700">Biaya TNKB</span>
                            <span id="resBiayaTnkb" class="font-bold text-slate-900">Rp 0</span>
                        </div>
                    </div>

                    <div id="rowAmnesti" class="hidden p-4 bg-emerald-50 border-t-2 border-emerald-200">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2 text-emerald-700">
                                <i data-lucide="gift" class="w-4 h-4"></i>
                                <span class="font-bold bill-name">Total Penghematan Amnesti</span>
                            </div>
                            <span id="resAmnesti" class="font-extrabold text-emerald-600">- Rp 0</span>
                        </div>
                    </div>
                </div>

                <div id="arrearsInfo" class="hidden bg-orange-50 border border-orange-200 text-orange-800 rounded-xl p-4 text-sm flex gap-3">
                    <i data-lucide="info" class="w-5 h-5 shrink-0 text-orange-500"></i>
                    <p>Kendaraan memiliki <strong id="arrearsYearText">0</strong> tahun tunggakan. Tahun terbaru dihitung sebagai <strong>PKB berjalan</strong>, sedangkan tahun sebelumnya dihitung sebagai <strong>tunggakan PKB</strong>.</p>
                </div>

                <div id="resPeringatan" class="hidden bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-4 text-sm flex gap-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 text-yellow-500"></i>
                    <p id="resPeringatanText"></p>
                </div>
            </div>

            <!-- TOTAL BOX -->
            <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl p-6 text-center text-white shadow-xl shadow-blue-900/20">
                <span class="text-blue-100 text-sm font-bold tracking-widest uppercase mb-2 block">Total Tagihan</span>
                <div id="resTotal" class="text-4xl md:text-5xl font-extrabold tracking-tight">Rp 0</div>
            </div>

            <p class="text-center text-xs text-slate-400 mt-6 mb-4">
                Estimasi perhitungan pajak ini berdasarkan data kendaraan terakhir yang terdaftar. Nilai akhir dapat berbeda sesuai penetapan di Samsat.
            </p>
            
        </div>
        
        <!-- DOWNLOAD BUTTON -->
        <div class="mt-6 flex justify-center">
            <button type="button" id="btnDownloadImg" class="hidden w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-emerald-500/30 transition-all items-center justify-center gap-2">
                <i data-lucide="download" class="w-5 h-5"></i> Unduh Bukti Tagihan
            </button>
        </div>
    </div>
</div>
