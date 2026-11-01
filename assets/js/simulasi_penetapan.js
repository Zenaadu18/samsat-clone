// simulasi_penetapan.js
// Logika interaktif untuk menu Simulasi Penetapan (Normal vs Pergub 54 vs Pergub 64)

let currentKanalBayar = 'loket';
let activeMode = 'nopol';
let lastSimulasiData = null;

// Format Rupiah
function formatRupiah(number) {
    const value = Number(number) || 0;
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}

document.addEventListener('DOMContentLoaded', function () {
    // 1. Inisialisasi Tanggal Simulasi Default (Hari ini)
    const today = new Date().toISOString().split('T')[0];
    const tglSimulasiEl = document.getElementById('tanggalSimulasi');
    if (tglSimulasiEl && !tglSimulasiEl.value) {
        tglSimulasiEl.value = today;
    }

    // 2. Tab Mode Switching (Nopol vs Manual)
    const tabNopolBtn = document.getElementById('tabNopolBtn');
    const tabManualBtn = document.getElementById('tabManualBtn');
    const formNopol = document.getElementById('formNopol');
    const formManual = document.getElementById('formManual');

    if (tabNopolBtn && tabManualBtn) {
        tabNopolBtn.addEventListener('click', function () {
            tabNopolBtn.classList.add('active');
            tabManualBtn.classList.remove('active');
            formNopol.style.display = 'block';
            formManual.style.display = 'none';
            activeMode = 'nopol';
        });

        tabManualBtn.addEventListener('click', function () {
            tabManualBtn.classList.add('active');
            tabNopolBtn.classList.remove('active');
            formNopol.style.display = 'none';
            formManual.style.display = 'block';
            activeMode = 'manual';
        });
    }

    // 3. Kanal Pembayaran Toggle (Loket vs Online)
    const kanalLoketBtn = document.getElementById('kanalLoketBtn');
    const kanalOnlineBtn = document.getElementById('kanalOnlineBtn');

    function setKanal(kanal) {
        currentKanalBayar = kanal;
        if (kanal === 'online') {
            kanalOnlineBtn.classList.add('active');
            kanalLoketBtn.classList.remove('active');
        } else {
            kanalLoketBtn.classList.add('active');
            kanalOnlineBtn.classList.remove('active');
        }

        // Jika sudah ada data simulasi yang tampil, jalankan ulang otomatis untuk melihat perbedaan langsung!
        if (lastSimulasiData) {
            jalankanSimulasi();
        }
    }

    if (kanalLoketBtn && kanalOnlineBtn) {
        kanalLoketBtn.addEventListener('click', () => setKanal('loket'));
        kanalOnlineBtn.addEventListener('click', () => setKanal('online'));
    }

    // 4. Submit Form Nopol
    const formNopolEl = document.getElementById('formNopolEl');
    if (formNopolEl) {
        formNopolEl.addEventListener('submit', function (e) {
            e.preventDefault();
            jalankanSimulasi();
        });
    }

    // 5. Submit Form Manual
    const formManualEl = document.getElementById('formManualEl');
    if (formManualEl) {
        formManualEl.addEventListener('submit', function (e) {
            e.preventDefault();
            jalankanSimulasi();
        });
    }

    // 6. Accordion Policy
    const policyHeader = document.getElementById('policyHeader');
    const policyContent = document.getElementById('policyContent');
    const policyIcon = document.getElementById('policyIcon');
    if (policyHeader && policyContent) {
        policyHeader.addEventListener('click', function () {
            const isOpen = policyContent.classList.toggle('open');
            if (policyIcon) {
                policyIcon.className = isOpen ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down';
            }
        });
    }

    // 7. Tombol Cetak & Salin
    const btnPrint = document.getElementById('btnPrint');
    if (btnPrint) {
        btnPrint.addEventListener('click', () => {
            updatePrintDate();
            window.print();
        });
    }

    const btnSalin = document.getElementById('btnSalin');
    if (btnSalin) {
        btnSalin.addEventListener('click', salinRingkasanSimulasi);
    }

    // 8. Logika Auto-Adjust Jenis Kendaraan -> Bobot & Kategori Kendaraan
    const manualJenisKendaraan = document.getElementById('manualJenisKendaraan');
    const manualBobot = document.getElementById('manualBobot');
    const manualKategoriRoda = document.getElementById('manualKategoriRoda');

    function highlightField(el) {
        if (!el) return;
        el.style.transition = 'all 0.3s ease';
        el.style.borderColor = '#2563eb';
        el.style.boxShadow = '0 0 0 3px rgba(37, 99, 235, 0.2)';
        setTimeout(() => {
            el.style.borderColor = '';
            el.style.boxShadow = '';
        }, 500);
    }

    if (manualJenisKendaraan && manualBobot && manualKategoriRoda) {
        // Ketika Jenis Kendaraan dipilih -> bobot auto menyesuaikan & kategori kendaraan menyesuaikan
        manualJenisKendaraan.addEventListener('change', function () {
            const selectedOpt = this.options[this.selectedIndex];
            if (!selectedOpt) return;

            const bobotVal = selectedOpt.getAttribute('data-bobot');
            const kategoriVal = selectedOpt.getAttribute('data-kategori');

            if (bobotVal) {
                manualBobot.value = bobotVal;
                highlightField(manualBobot);
            }

            if (kategoriVal) {
                manualKategoriRoda.value = kategoriVal;
                highlightField(manualKategoriRoda);
            }
        });

        // Sinkronisasi cerdas bila Kategori Kendaraan diubah langsung
        manualKategoriRoda.addEventListener('change', function () {
            const chosenKat = this.value;
            const currentOpt = manualJenisKendaraan.options[manualJenisKendaraan.selectedIndex];
            const currentKat = currentOpt ? currentOpt.getAttribute('data-kategori') : null;

            if (currentKat !== chosenKat) {
                if (chosenKat === 'R2') {
                    // Switch ke jenis roda 2
                    for (let i = 0; i < manualJenisKendaraan.options.length; i++) {
                        if (manualJenisKendaraan.options[i].getAttribute('data-kategori') === 'R2') {
                            manualJenisKendaraan.selectedIndex = i;
                            manualBobot.value = manualJenisKendaraan.options[i].getAttribute('data-bobot') || '1.000';
                            highlightField(manualJenisKendaraan);
                            highlightField(manualBobot);
                            break;
                        }
                    }
                } else {
                    // Switch ke jenis roda 4+ (default MINIBUS)
                    for (let i = 0; i < manualJenisKendaraan.options.length; i++) {
                        if (manualJenisKendaraan.options[i].value === 'MINIBUS') {
                            manualJenisKendaraan.selectedIndex = i;
                            manualBobot.value = manualJenisKendaraan.options[i].getAttribute('data-bobot') || '1.050';
                            highlightField(manualJenisKendaraan);
                            highlightField(manualBobot);
                            break;
                        }
                    }
                }
            }
        });
    }

    // 9. Auto-run jika ada parameter nopol di URL
    const urlParams = new URLSearchParams(window.location.search);
    const nopolParam = urlParams.get('nopol');
    if (nopolParam && nopolParam.trim() !== '') {
        const nopolInput = document.getElementById('nopolInput');
        if (nopolInput) {
            nopolInput.value = nopolParam.trim().toUpperCase();
            // Pastikan tab aktif adalah nopol
            activeMode = 'nopol';
            setTimeout(() => {
                jalankanSimulasi();
            }, 150);
        }
    }
});

// Eksekusi Panggilan API Simulasi
async function jalankanSimulasi() {
    const errorBox = document.getElementById('simulasiError');
    const resultBox = document.getElementById('simulasiResult');
    const submitBtn = document.getElementById('btnSubmitNopol') || document.getElementById('btnSubmitManual');
    const btnSpinner = document.getElementById('spinnerNopol') || document.getElementById('spinnerManual');
    const btnText = document.getElementById('btnTextNopol') || document.getElementById('btnTextManual');

    errorBox.style.display = 'none';

    if (submitBtn) submitBtn.disabled = true;
    if (btnSpinner) btnSpinner.style.display = 'block';
    if (btnText) btnText.style.display = 'none';

    try {
        let payload = {
            mode: activeMode,
            kanal_bayar: currentKanalBayar,
            tanggal_simulasi: document.getElementById('tanggalSimulasi').value,
            is_mutasi: document.getElementById('isMutasi').checked
        };

        if (activeMode === 'nopol') {
            const nopol = document.getElementById('nopolInput').value.trim();
            if (!nopol) {
                throw new Error('Nomor polisi harus diisi.');
            }
            payload.nopol = nopol;
        } else {
            // Mode Manual
            const njkb = Number(document.getElementById('manualNjkb').value);
            if (!njkb || njkb <= 0) {
                throw new Error('Nilai NJKB harus lebih besar dari 0.');
            }
            payload.njkb = njkb;
            payload.jenis_kendaraan = document.getElementById('manualJenisKendaraan') ? document.getElementById('manualJenisKendaraan').value : '';
            payload.bobot = Number(document.getElementById('manualBobot').value) || 1.0;
            payload.is_roda23 = (document.getElementById('manualKategoriRoda').value === 'R2');
            payload.k_guna = document.getElementById('manualKGuna').value;
            payload.sd_notice = document.getElementById('manualSdNotice').value;
            payload.sd_stnk = document.getElementById('manualSdStnk').value;
            payload.upt_asal = document.getElementById('manualUptAsal').value;
        }

        const response = await fetch('api_simulasi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const json = await response.json();

        if (!response.ok || json.status !== 'success') {
            throw new Error(json.message || 'Terjadi kesalahan saat memproses simulasi.');
        }

        lastSimulasiData = json.data;
        renderSimulasiResult(json.data);

        resultBox.style.display = 'block';
        resultBox.scrollIntoView({ behavior: 'smooth', block: 'start' });

    } catch (err) {
        errorBox.textContent = err.message || 'Gagal memproses simulasi.';
        errorBox.style.display = 'block';
    } finally {
        if (submitBtn) submitBtn.disabled = false;
        if (btnSpinner) btnSpinner.style.display = 'none';
        if (btnText) btnText.style.display = 'inline';
    }
}

// Render Hasil ke UI
function renderSimulasiResult(data) {
    const k = data.kendaraan;
    const sim = data.simulasi;
    const meta = sim.metadata;
    const komp = sim.komparasi;
    const bbnkb = sim.bbnkb;

    // 1. Vehicle Infobar
    const infoBar = document.getElementById('vehicleInfoBar');
    infoBar.classList.add('active');

    document.getElementById('infoNopol').textContent = k.nopol;
    document.getElementById('infoUnit').textContent = k.unit || `${k.merk} ${k.tipe}`;
    document.getElementById('infoRoda').textContent = meta.kategori_roda;
    document.getElementById('infoNotice').textContent = k.sd_notice || '-';

    const statusBadge = document.getElementById('infoStatus');
    statusBadge.textContent = meta.status_pembayaran;
    statusBadge.className = 'status-badge';
    if (meta.tahun_tunggakan > 0) {
        statusBadge.classList.add('status-menunggak');
    } else if (meta.status_pembayaran.includes('Taat')) {
        statusBadge.classList.add('status-taat');
    } else {
        statusBadge.classList.add('status-terlambat');
    }

    // Helper fungsi safe set text
    const safeSetText = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    };

    // 2. Tiga Kartu Komparasi
    // A. Normal
    const norm = komp.normal;
    safeSetText('normTotal', formatRupiah(norm.total_bayar));
    safeSetText('normDp', formatRupiah(norm.dp_pkb));
    safeSetText('normPkbBerjalan', formatRupiah(norm.pkb_berjalan));
    safeSetText('normPkbTunggakan', formatRupiah(norm.pkb_tunggakan));
    safeSetText('normOpsen', formatRupiah(norm.opsen_pkb_total));
    safeSetText('normDendaPkb', formatRupiah(norm.denda_pkb + norm.denda_opsen));
    safeSetText('normSwdkllj', formatRupiah(norm.swdkllj_total));
    safeSetText('normStnk', formatRupiah(norm.biaya_stnk_tnkb));

    // B. Pergub 54
    const p54 = komp.pergub54;
    safeSetText('p54Total', formatRupiah(p54.total_bayar));
    safeSetText('p54Hemat', `Hemat ${formatRupiah(p54.total_hemat)}`);
    safeSetText('p54Dp', formatRupiah(p54.dp_pkb));
    if (p54.pengurangan_dp_pct > 0) {
        safeSetText('p54DpDiskon', `(-${p54.pengurangan_dp_pct}%)`);
    } else {
        safeSetText('p54DpDiskon', '');
    }
    safeSetText('p54PkbBerjalan', formatRupiah(p54.pkb_berjalan));
    safeSetText('p54PkbTunggakan', formatRupiah(p54.pkb_tunggakan));
    safeSetText('p54Opsen', formatRupiah(p54.opsen_pkb_total));
    safeSetText('p54DendaPkb', p54.denda_dibebaskan > 0 ? 'Dibebaskan' : 'Rp 0');
    safeSetText('p54Swdkllj', formatRupiah(p54.swdkllj_total));
    safeSetText('p54Stnk', formatRupiah(p54.biaya_stnk_tnkb));
    safeSetText('p54Ket', p54.keterangan);

    // C. Pergub 64
    const ran = komp.ranpergub;
    safeSetText('ranTotal', formatRupiah(ran.total_bayar));
    safeSetText('ranHemat', `Hemat ${formatRupiah(ran.total_hemat)}`);
    safeSetText('ranDp', formatRupiah(ran.dp_pkb));
    if (ran.pengurangan_dp_pct > 0) {
        safeSetText('ranDpDiskon', `(-${ran.pengurangan_dp_pct}%)`);
    } else {
        safeSetText('ranDpDiskon', '');
    }
    safeSetText('ranPkbBerjalan', formatRupiah(ran.pkb_berjalan));
    if (ran.diskon_pkb_berjalan_pct > 0) {
        safeSetText('ranBerjalanDiskon', `(Diskon ${ran.diskon_pkb_berjalan_pct}%)`);
    } else {
        safeSetText('ranBerjalanDiskon', '');
    }

    const ranPkbTunggakanEl = document.getElementById('ranPkbTunggakan');
    if (ranPkbTunggakanEl) {
        if (ran.diskon_pkb_tunggakan_pct === 100) {
            ranPkbTunggakanEl.textContent = 'DIHAPUS 100% (Rp 0)';
            ranPkbTunggakanEl.className = 'val free-tag';
        } else {
            ranPkbTunggakanEl.textContent = formatRupiah(ran.pkb_tunggakan);
            ranPkbTunggakanEl.className = 'val';
        }
    }

    safeSetText('ranOpsen', formatRupiah(ran.opsen_pkb_total));
    safeSetText('ranDendaPkb', 'Dihapus 100%');
    safeSetText('ranSwdkllj', formatRupiah(ran.swdkllj_total));
    safeSetText('ranStnk', formatRupiah(ran.biaya_stnk_tnkb));
    safeSetText('ranKet', ran.keterangan);

    // 3. Selisih Pergub 64 vs Pergub 54
    const selisih = sim.selisih_ranpergub_vs_pergub54;
    const selisihEl = document.getElementById('selisihRanVsP54');
    if (selisihEl) {
        if (selisih > 0) {
            selisihEl.innerHTML = `<span style="color: #047857; font-weight: 800;"><i class="fa-solid fa-arrow-trend-down"></i> Pergub 64 Lebih Hemat ${formatRupiah(selisih)} dibanding Pergub 54!</span>`;
        } else if (selisih === 0) {
            selisihEl.innerHTML = `<span style="color: #475569; font-weight: 700;">Nominal penetapan sama antara Pergub 64 dan Pergub 54 untuk skenario ini.</span>`;
        } else {
            selisihEl.innerHTML = `<span>Selisih: ${formatRupiah(Math.abs(selisih))}</span>`;
        }
    }

    // 4. Tabel Detail Komparasi
    safeSetText('tdNormDp', formatRupiah(norm.dp_pkb));
    safeSetText('tdP54Dp', formatRupiah(p54.dp_pkb));
    safeSetText('tdRanDp', formatRupiah(ran.dp_pkb));

    safeSetText('tdNormBerjalan', formatRupiah(norm.pkb_berjalan));
    safeSetText('tdP54Berjalan', formatRupiah(p54.pkb_berjalan));
    safeSetText('tdRanBerjalan', formatRupiah(ran.pkb_berjalan));

    document.getElementById('tdNormTunggakan').textContent = formatRupiah(norm.pkb_tunggakan);
    document.getElementById('tdP54Tunggakan').textContent = formatRupiah(p54.pkb_tunggakan);
    document.getElementById('tdRanTunggakan').textContent = ran.pkb_tunggakan === 0 && norm.pkb_tunggakan > 0 ? 'Dihapus 100% (Rp 0)' : formatRupiah(ran.pkb_tunggakan);

    document.getElementById('tdNormOpsen').textContent = formatRupiah(norm.opsen_pkb_total);
    document.getElementById('tdP54Opsen').textContent = formatRupiah(p54.opsen_pkb_total);
    document.getElementById('tdRanOpsen').textContent = formatRupiah(ran.opsen_pkb_total);

    document.getElementById('tdNormDenda').textContent = formatRupiah(norm.denda_pkb + norm.denda_opsen);
    document.getElementById('tdP54Denda').textContent = p54.denda_dibebaskan > 0 ? 'Bebas 100%' : 'Rp 0';
    document.getElementById('tdRanDenda').textContent = 'Bebas 100%';

    document.getElementById('tdNormSwdkllj').textContent = formatRupiah(norm.swdkllj_total);
    document.getElementById('tdP54Swdkllj').textContent = formatRupiah(p54.swdkllj_total);
    document.getElementById('tdRanSwdkllj').textContent = formatRupiah(ran.swdkllj_total);

    document.getElementById('tdNormStnk').textContent = formatRupiah(norm.biaya_stnk_tnkb);
    document.getElementById('tdP54Stnk').textContent = formatRupiah(p54.biaya_stnk_tnkb);
    document.getElementById('tdRanStnk').textContent = formatRupiah(ran.biaya_stnk_tnkb);

    document.getElementById('tdNormTotal').textContent = formatRupiah(norm.total_bayar);
    document.getElementById('tdP54Total').textContent = formatRupiah(p54.total_bayar);
    document.getElementById('tdRanTotal').textContent = formatRupiah(ran.total_bayar);

    document.getElementById('tdNormHemat').textContent = 'Rp 0';
    document.getElementById('tdP54Hemat').textContent = formatRupiah(p54.total_hemat);
    document.getElementById('tdRanHemat').textContent = formatRupiah(ran.total_hemat);

    // 5. Tabel BBNKB
    if (bbnkb) {
        document.getElementById('bbnkbKategori').textContent = bbnkb.is_roda23 ? 'Roda 2 & 3 (Pengurangan DP 15%)' : 'Roda 4+ (Pengurangan DP 20%)';
        document.getElementById('bbnkbNormDp').textContent = formatRupiah(bbnkb.normal.dp_bbnkb);
        document.getElementById('bbnkbP54Dp').textContent = formatRupiah(bbnkb.pergub54.dp_bbnkb);
        document.getElementById('bbnkbRanDp').textContent = `${formatRupiah(bbnkb.ranpergub.dp_bbnkb)} (-${bbnkb.ranpergub.pengurangan_dp_pct}%)`;

        document.getElementById('bbnkbNormPokok').textContent = formatRupiah(bbnkb.normal.pokok);
        document.getElementById('bbnkbP54Pokok').textContent = formatRupiah(bbnkb.pergub54.pokok);
        document.getElementById('bbnkbRanPokok').textContent = formatRupiah(bbnkb.ranpergub.pokok);

        document.getElementById('bbnkbNormOpsen').textContent = formatRupiah(bbnkb.normal.opsen);
        document.getElementById('bbnkbP54Opsen').textContent = formatRupiah(bbnkb.pergub54.opsen);
        document.getElementById('bbnkbRanOpsen').textContent = formatRupiah(bbnkb.ranpergub.opsen);

        document.getElementById('bbnkbNormTotal').textContent = formatRupiah(bbnkb.normal.total);
        document.getElementById('bbnkbP54Total').textContent = formatRupiah(bbnkb.pergub54.total);
        document.getElementById('bbnkbRanTotal').textContent = formatRupiah(bbnkb.ranpergub.total);

        document.getElementById('bbnkbRanHemat').textContent = `Hemat ${formatRupiah(bbnkb.ranpergub.total_hemat)}`;
    }
}

// Salin Ringkasan Simulasi ke Clipboard (WhatsApp / Catatan)
function salinRingkasanSimulasi() {
    if (!lastSimulasiData) return;

    const k = lastSimulasiData.kendaraan;
    const sim = lastSimulasiData.simulasi;
    const meta = sim.metadata;
    const komp = sim.komparasi;

    const teks = `*SIMULASI PENETAPAN PAJAK KENDARAAN NTT*
Nomor Polisi: ${k.nopol}
Unit: ${k.unit} (${meta.kategori_roda})
Jatuh Tempo: ${k.sd_notice}
Status: ${meta.status_pembayaran}
Kanal Pembayaran: ${meta.kanal_bayar.toUpperCase()}
Tanggal Simulasi: ${meta.tanggal_simulasi_formatted}

*1. PENETAPAN NORMAL (Tanpa Amnesty)*
Total Bayar: ${formatRupiah(komp.normal.total_bayar)}

*2. PERGUB 54 (Sept - Okt 2026)*
Total Bayar: ${formatRupiah(komp.pergub54.total_bayar)}
Total Penghematan: ${formatRupiah(komp.pergub54.total_hemat)}

*3. Pergub 64 (Nov - Des 2026)*
Total Bayar: ${formatRupiah(komp.ranpergub.total_bayar)}
Total Penghematan: ${formatRupiah(komp.ranpergub.total_hemat)}
Ket: ${komp.ranpergub.keterangan}

*Keuntungan Pergub 64 vs Pergub 54:*
Lebih Hemat: ${formatRupiah(sim.selisih_ranpergub_vs_pergub54)}

_Dihasilkan otomatis melalui Portal Layanan Bapenda NTT._`;

    navigator.clipboard.writeText(teks).then(() => {
        alert('Ringkasan simulasi berhasil disalin ke clipboard!');
    }).catch(() => {
        alert('Gagal menyalin ringkasan teks.');
    });
}

// Update tanggal cetak resmi saat mencetak
function updatePrintDate() {
    const el = document.getElementById('printDate');
    if (el) {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        el.textContent = now.toLocaleDateString('id-ID', options) + ' WITA';
    }
}

window.addEventListener('beforeprint', updatePrintDate);

// Unduh Gambar Single Matrix / Komparasi Mandiri
window.downloadSingleMatrix = function (elementId, matrixName) {
    const targetEl = document.getElementById(elementId);
    if (!targetEl) {
        alert('Elemen perbandingan tidak ditemukan!');
        return;
    }

    if (typeof html2canvas === 'undefined') {
        alert('Pustaka html2canvas belum dimuat, silakan muat ulang halaman.');
        return;
    }

    // Ambil identitas nopol untuk penamaan file
    let nopolStr = 'Kendaraan';
    const vhNopol = document.getElementById('vhNopol');
    const inputNopol = document.getElementById('inputNopol');
    if (vhNopol && vhNopol.textContent.trim() && vhNopol.textContent.trim() !== '-') {
        nopolStr = vhNopol.textContent.trim().replace(/\s+/g, '_');
    } else if (inputNopol && inputNopol.value.trim()) {
        nopolStr = inputNopol.value.trim().toUpperCase().replace(/\s+/g, '_');
    }

    // Sembunyikan tombol download di dalam kartu saat pemotretan gambar
    const btns = targetEl.querySelectorAll('.btn-download-card, .btn-download-card-ran');
    const originalDisplays = [];
    btns.forEach(btn => {
        originalDisplays.push(btn.style.display);
        btn.style.display = 'none';
    });

    // Indikator loading sementara pada tombol yang diklik
    const activeBtn = event && event.currentTarget ? event.currentTarget : null;
    let oldBtnHtml = '';
    if (activeBtn) {
        oldBtnHtml = activeBtn.innerHTML;
        activeBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
    }

    // Potret elemen dengan html2canvas (skala tinggi 2.5 untuk ketajaman retina/HD)
    html2canvas(targetEl, {
        scale: 2.5,
        backgroundColor: '#ffffff',
        useCORS: true,
        logging: false,
        onclone: function (clonedDoc) {
            // Pastikan elemen clone memiliki border dan padding yang rapi
            const clonedEl = clonedDoc.getElementById(elementId);
            if (clonedEl) {
                clonedEl.style.boxShadow = 'none';
                clonedEl.style.margin = '0';
            }
        }
    }).then(canvas => {
        // Kembalikan tombol ke display semula
        btns.forEach((btn, idx) => {
            btn.style.display = originalDisplays[idx] || '';
        });
        if (activeBtn) {
            activeBtn.innerHTML = oldBtnHtml;
        }

        // Trigger unduh gambar PNG
        const imgData = canvas.toDataURL('image/png');
        const downloadLink = document.createElement('a');
        const tglStr = new Date().toISOString().split('T')[0];
        downloadLink.download = `Matrix_${matrixName}_${nopolStr}_${tglStr}.png`;
        downloadLink.href = imgData;
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }).catch(err => {
        console.error('Gagal mengambil gambar matrix:', err);
        btns.forEach((btn, idx) => {
            btn.style.display = originalDisplays[idx] || '';
        });
        if (activeBtn) {
            activeBtn.innerHTML = oldBtnHtml;
        }
        alert('Terjadi kesalahan saat memproses gambar.');
    });
};

