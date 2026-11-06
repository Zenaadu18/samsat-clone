# Agent Behavioral Rules & Execution Guide

## 1. Berpikir Sebelum Koding (Think Before You Code)
- Sebelum menulis atau mengubah baris kode apa pun, Anda **WAJIB** menganalisis struktur yang sudah ada terlebih dahulu.
- Lakukan pemetaan dependensi untuk memastikan perubahan tidak merusak modul lain.
- Tuliskan rencana eksekusi singkat secara internal sebelum melakukan implementasi.

## 2. Prinsip Kesederhanaan (Simplicity First & Code Reduction)
- **Aturan Emas:** Jika kode 200 baris bisa ditulis ulang secara bersih menjadi 50 baris tanpa mengurangi fitur atau performa, **lakukan rewrite segera**.
- Hindari *over-engineering*, abstraksi yang tidak perlu, atau penambahan pustaka (library) pihak ketiga jika fungsionalitas bawaan sudah cukup.
- Prioritaskan keterbacaan kode (clean code) dan minimalkan tingkat kompleksitas siklomatis (cyclomatic complexity).

## 3. Perubahan Bedah (Surgical Changes)
- Lakukan perubahan kode secara presisi dan terarah (*surgical style*). Jangan mengubah file atau fungsi yang tidak relevan dengan tugas.
- Pertahankan struktur dan format kode yang sudah ada di sekitar area yang Anda ubah.
- Jangan menghapus komentar penting atau dokumentasi yang sudah ada, kecuali kodenya memang di-rewrite menjadi lebih simpel.

## 4. Eksekusi Berbasis Target (Goal-Driven Execution)
- Fokus penuh pada penyelesaian masalah utama yang diminta. Jangan terdistraksi oleh *refactoring* massal yang di luar ruang lingkup tugas, kecuali untuk penyederhanaan kode di area kerja Anda.
- Validasi hasil kerja Anda dengan memastikan kode tersebut langsung siap di-build atau dites tanpa menyisakan error sintaks.
## 5. UI/UX Design Standards (Non-Generic AI Aesthetics)
Hindari tren desain AI generik (latar belakang hitam pekat `#000000`, teks neon ungu/biru yang terlalu terang, border tipis di setiap elemen, dan sudut membulat ekstrem). Gunakan panduan berikut untuk tampilan yang lebih profesional dan organik:

### A. Palet Warna & Kontras (Color Palette)
- **Background:** Hindari hitam pekat atau putih murni untuk area kerja utama. Gunakan warna off-white hangat (misal: `#FBFBFA`) untuk light mode, atau warna gelap berbasis slate/charcoal hangat (misal: `#121314` atau `#161719`) untuk dark mode.
- **Aksen & AI Vibe:** Dilarang menggunakan gradasi neon ungu-biru-pink yang pasaran. Sebagai gantinya, gunakan aksen monokromatik kontras tinggi, warna bumi (earthy tones), atau satu warna solid yang matang seperti Indigo dalam (`#3E51F7`) atau Emerald gelap.
- **Teks:** Gunakan hirarki kontras yang halus. Jangan gunakan teks putih/hitam 100%. Gunakan opasitas atau warna turunan (seperti `#E4E4E7` untuk teks utama dan `#A1A1AA` untuk teks sekunder pada dark mode).

### B. Tipografi & Tata Letak (Typography & Layout)
- **Jenis Font:** Jangan gunakan Inter secara mentah untuk semua elemen. Gunakan font sans-serif geometris atau humanis yang lebih berkarakter untuk Heading (seperti *Plus Jakarta Sans*, *Satoshi*, atau *Geist Sans*). Untuk teks panjang, gunakan sans yang sangat bersih atau font serif modern untuk kesan editorial premium.
- **Struktur Layout:** Utamakan tata letak berasaskan "ruang bernapas" (generous whitespace). Kurangi sekat-sekat border (`border-slice`) yang kaku. Gunakan teknik *layering* dengan bayangan halus (*subtle soft shadows*) atau perbedaan saturasi warna latar untuk memisahkan area, bukan garis border hitam di mana-mana.
- **Border Radius:** Gunakan standar sudut membulat yang modular dan modern. Hindari sudut membulat lingkaran penuh yang kekanak-kanakan untuk komponen kotak. Gunakan `radius: 8px` atau `10px` untuk tombol/input kecil, dan maksimal `14px` hingga `16px` untuk card besar.

### C. Komponen UI Modern

#### 1. Tombol Konfirmasi (Modern Confirmation Button)
- **Aestetika:** Tanpa border luar yang tebal. Gunakan warna solid dengan kontras tinggi atau micro-gradient lembut yang hampir tidak terlihat.
- **Interaksi:** Berikan efek transisi halus saat di-hover (misal: warna menggelap 5% atau sedikit bergeser ke atas `translate-y-[-1px]`) disertai *active state* yang terasa membal (*tactile/springy micro-interaction*).

#### 2. Toast Notifications (Success & Warning)
Jangan buat toast berbentuk kotak besar kaku di pojok layar. Gunakan gaya *floating pill* yang minimalis:
- **Layout & Posisi:** Letakkan di tengah bawah (bottom-center) atau tengah atas (top-center) dengan ukuran ringkas, hanya cukup untuk satu kalimat pendek.
- **Toast Success:** Latar belakang transparan dengan blur tinggi (*glassmorphism* tingkat rendah) atau warna hijau sage/emerald yang sangat redup (`#ECFDF5`), teks hijau tua (`#065F46`), dengan ikon centang minimalis tanpa lingkaran pembungkus tebal.
- **Toast Warning/Error:** Latar belakang amber/oranye redup yang elegan (bukan merah menyala), menggunakan teks cokelat/oranye gelap yang kontras dan mudah dibaca demi aksesibilitas.
- **Animasi:** Wajib menggunakan animasi masuk *fade-in* sekaligus *slide-up* yang cepat namun halus (durasi 200ms - 300ms dengan *ease-out*), dan keluar dengan *fade-out*.
