/* ================================================================
       ELEMENT
    ================================================================= */

    const form =
        document.getElementById('taxForm');

    const submitBtn =
        document.getElementById('submitBtn');

    const btnText =
        document.getElementById('btnText');

    const btnLoader =
        document.getElementById('btnLoader');

    const resultContainer =
        document.getElementById('resultContainer');

    const errorMsg =
        document.getElementById('errorMsg');


    /* ================================================================
       FORMAT RUPIAH
    ================================================================= */

    function formatRupiah(number) {

        const value =
            Number(number) || 0;

        return new Intl.NumberFormat(
            'id-ID',
            {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }
        ).format(value);
    }


    /* ================================================================
       HELPER ELEMENT
    ================================================================= */

    function show(id) {

        const element =
            document.getElementById(id);

        if (element) {
            element.style.display = '';
        }
    }


    function hide(id) {

        const element =
            document.getElementById(id);

        if (element) {
            element.style.display = 'none';
        }
    }


    function setText(id, value) {

        const element =
            document.getElementById(id);

        if (element) {
            element.textContent =
                value ?? '-';
        }
    }


    /* ================================================================
       SUBMIT
    ================================================================= */

    form.addEventListener(
        'submit',
        async function(e) {

            e.preventDefault();


            const nopol =
                document
                    .getElementById('nopol')
                    .value
                    .trim();


            if (!nopol) {

                errorMsg.textContent =
                    'Nomor polisi wajib diisi.';

                errorMsg.style.display =
                    'block';

                return;
            }


            /* --------------------------------------------------------
               LOADING
            -------------------------------------------------------- */

            btnText.style.display =
                'none';

            btnLoader.style.display =
                'block';

            submitBtn.disabled =
                true;

            errorMsg.style.display =
                'none';

            resultContainer.classList.remove(
                'active'
            );

            resultContainer.style.display =
                'none';


            try {

                /* ----------------------------------------------------
                   REQUEST
                -------------------------------------------------- */

                const response =
                    await fetch(
                        'calculate_tax.php',
                        {
                            method: 'POST',

                            headers: {
                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json'
                            },

                            body: JSON.stringify({
                                nopol: nopol
                            })
                        }
                    );


                let res;


                try {

                    res =
                        await response.json();

                } catch (jsonError) {

                    throw new Error(
                        'Response server tidak valid.'
                    );
                }


                if (
                    !response.ok ||
                    !res ||
                    res.status !== 'success'
                ) {

                    throw new Error(
                        res?.message ||
                        'Kendaraan tidak ditemukan.'
                    );
                }


                const d =
                    res.data;


                /* ====================================================
                   IDENTITAS
                ==================================================== */

                setText(
                    'resNopol',
                    d.nopol
                );

                setText(
                    'resPemilik',
                    d.pemilik || '-'
                );

                setText(
                    'resUnit',
                    d.unit ||
                    `${d.merk || ''} / ${d.tipe || ''}`
                );

                setText(
                    'resTahun',
                    d.tahun || '-'
                );

                /* --- Format & Tampilkan Masa Pajak/STNK --- */
                function formatDate(dateStr) {
                    if (!dateStr || dateStr === '-' || dateStr === '0000-00-00') return '-';
                    const d = new Date(dateStr);
                    return isNaN(d) ? dateStr : d.toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'});
                }
                
                setText('resSdNotice', formatDate(d.sd_notice));
                setText('resSdStnk', formatDate(d.sd_stnk));


                /* Helper untuk Akordeon Rincian per Kategori */
                function setupAccordion(wrapId, detailId, toggleIconId) {
                    const wrap = document.getElementById(wrapId);
                    const detail = document.getElementById(detailId);
                    const toggleIcon = document.getElementById(toggleIconId);
                    if (!wrap || !detail) return;

                    wrap.style.cursor = 'pointer';
                    wrap.title = 'Klik untuk melihat rincian & keringanan';

                    wrap.onclick = function() {
                        const isHidden = (detail.style.display === 'none' || !detail.style.display);
                        if (isHidden) {
                            detail.style.display = 'block';
                            if (toggleIcon) toggleIcon.textContent = '▲';
                        } else {
                            detail.style.display = 'none';
                            if (toggleIcon) toggleIcon.textContent = '▼';
                        }
                    };
                }

                const tahunTunggakan = Number(d.tahun_tunggakan) || 0;

                /* ====================================================
                   1. PKB (BERJALAN + TUNGGAKAN)
                ==================================================== */
                const pkbBerjalan = Number(d.pkb_berjalan) || 0;
                const pkbTunggakan = Number(d.pkb_tunggakan) || 0;
                const pkbTotalNet = pkbBerjalan + pkbTunggakan;

                const diskonPkbBerjalan = Number(d.diskon_pkb_berjalan) || Number(d.amnesti_diskon_pkb_berjalan) || 0;
                const diskonPkbTunggakan = Number(d.diskon_pkb_tunggakan) || Number(d.amnesti_diskon_pkb_tunggakan) || 0;
                const diskonPkbTotal = diskonPkbBerjalan + diskonPkbTunggakan;

                setText('resPkbTotal', formatRupiah(pkbTotalNet));
                setText('resPkbBerjalanVal', formatRupiah(pkbBerjalan));
                setText('resPkbTunggakanVal', formatRupiah(pkbTunggakan));
                setText('resPkbNetVal', formatRupiah(pkbTotalNet));

                const subPkbTunggakanWrap = document.getElementById('subPkbTunggakanWrap');
                if (subPkbTunggakanWrap) {
                    subPkbTunggakanWrap.style.display = (tahunTunggakan > 0 || pkbTunggakan > 0) ? 'flex' : 'none';
                }

                const subPkbDiskonWrap = document.getElementById('subPkbDiskonWrap');
                if (subPkbDiskonWrap) {
                    if (diskonPkbTotal > 0) {
                        subPkbDiskonWrap.style.display = 'flex';
                        setText('resPkbDiskonVal', '- ' + formatRupiah(diskonPkbTotal));
                    } else {
                        subPkbDiskonWrap.style.display = 'none';
                    }
                }

                if (tahunTunggakan > 0) {
                    setText('badgePkbTahun', `${tahunTunggakan} Thn Tunggakan`);
                    show('arrearsInfo');
                    setText('arrearsYearText', tahunTunggakan);
                } else {
                    setText('badgePkbTahun', 'Pokok');
                    hide('arrearsInfo');
                }
                setupAccordion('rowPkbWrap', 'pkbDetail', 'pkbToggleIcon');

                /* ====================================================
                   2. DENDA PKB (BERJALAN + TUNGGAKAN)
                ==================================================== */
                const dendaPkbBerjalan = Number(d.denda_pkb_berjalan) || 0;
                const dendaPkbTunggakan = Number(d.denda_pkb_tunggakan) || 0;
                const dendaPkbTotalNet = dendaPkbBerjalan + dendaPkbTunggakan;

                const diskonDendaPkbBerjalan = Number(d.diskon_denda_pkb_berjalan) || 0;
                const diskonDendaPkbTunggakan = Number(d.diskon_denda_pkb_tunggakan) || 0;
                const dendaPkbBerjalanKotor = Number(d.denda_pkb_berjalan_kotor) || (dendaPkbBerjalan + diskonDendaPkbBerjalan);
                const dendaPkbTunggakanKotor = Number(d.denda_pkb_tunggakan_kotor) || (dendaPkbTunggakan + diskonDendaPkbTunggakan);
                const diskonDendaPkbTotal = diskonDendaPkbBerjalan + diskonDendaPkbTunggakan;

                setText('resDendaPkbTotal', formatRupiah(dendaPkbTotalNet));
                setText('resDendaPkbBerjalanVal', formatRupiah(dendaPkbBerjalanKotor));
                setText('resDendaPkbTunggakanVal', formatRupiah(dendaPkbTunggakanKotor));
                setText('resDendaPkbNetVal', formatRupiah(dendaPkbTotalNet));

                const subDendaPkbTunggakanWrap = document.getElementById('subDendaPkbTunggakanWrap');
                if (subDendaPkbTunggakanWrap) {
                    subDendaPkbTunggakanWrap.style.display = (tahunTunggakan > 0 || dendaPkbTunggakanKotor > 0) ? 'flex' : 'none';
                }

                const subDendaPkbDiskonWrap = document.getElementById('subDendaPkbDiskonWrap');
                if (subDendaPkbDiskonWrap) {
                    if (diskonDendaPkbTotal > 0) {
                        subDendaPkbDiskonWrap.style.display = 'flex';
                        setText('resDendaPkbDiskonVal', '- ' + formatRupiah(diskonDendaPkbTotal));
                    } else {
                        subDendaPkbDiskonWrap.style.display = 'none';
                    }
                }
                setupAccordion('rowDendaPkbWrap', 'dendaPkbDetail', 'dendaPkbToggleIcon');

                /* ====================================================
                   3. SWDKLLJ (BERJALAN + TUNGGAKAN)
                ==================================================== */
                const swdklljBerjalan = Number(d.swdkllj_berjalan) || Number(d.swdkllj) || 0;
                const swdklljTunggakan = Number(d.swdkllj_tunggakan) || 0;
                const swdklljTotalNet = swdklljBerjalan + swdklljTunggakan;

                setText('resSwdklljTotal', formatRupiah(swdklljTotalNet));
                setText('resSwdklljBerjalanVal', formatRupiah(swdklljBerjalan));
                setText('resSwdklljTunggakanVal', formatRupiah(swdklljTunggakan));
                setText('resSwdklljNetVal', formatRupiah(swdklljTotalNet));

                const subSwdklljTunggakanWrap = document.getElementById('subSwdklljTunggakanWrap');
                if (subSwdklljTunggakanWrap) {
                    subSwdklljTunggakanWrap.style.display = (tahunTunggakan > 0 || swdklljTunggakan > 0) ? 'flex' : 'none';
                }
                setupAccordion('rowSwdklljWrap', 'swdklljDetail', 'swdklljToggleIcon');

                /* ====================================================
                   4. DENDA SWDKLLJ (BERJALAN + TUNGGAKAN)
                ==================================================== */
                const dendaSwdklljBerjalan = Number(d.denda_swdkllj_berjalan) || 0;
                const dendaSwdklljTunggakan = Number(d.denda_swdkllj_tunggakan) || 0;
                const dendaSwdklljTotalNet = dendaSwdklljBerjalan + dendaSwdklljTunggakan;

                const diskonDendaSwdklljBerjalan = Number(d.diskon_denda_swdkllj_berjalan) || 0;
                const diskonDendaSwdklljTunggakan = Number(d.diskon_denda_swdkllj_tunggakan) || 0;
                const dendaSwdklljBerjalanKotor = Number(d.denda_swdkllj_berjalan_kotor) || (dendaSwdklljBerjalan + diskonDendaSwdklljBerjalan);
                const dendaSwdklljTunggakanKotor = Number(d.denda_swdkllj_tunggakan_kotor) || (dendaSwdklljTunggakan + diskonDendaSwdklljTunggakan);
                const diskonDendaSwdklljTotal = diskonDendaSwdklljBerjalan + diskonDendaSwdklljTunggakan;

                setText('resDendaSwdklljTotal', formatRupiah(dendaSwdklljTotalNet));
                setText('resDendaSwdklljBerjalanVal', formatRupiah(dendaSwdklljBerjalanKotor));
                setText('resDendaSwdklljTunggakanVal', formatRupiah(dendaSwdklljTunggakanKotor));
                setText('resDendaSwdklljNetVal', formatRupiah(dendaSwdklljTotalNet));

                const subDendaSwdklljTunggakanWrap = document.getElementById('subDendaSwdklljTunggakanWrap');
                if (subDendaSwdklljTunggakanWrap) {
                    subDendaSwdklljTunggakanWrap.style.display = (tahunTunggakan > 0 || dendaSwdklljTunggakanKotor > 0) ? 'flex' : 'none';
                }

                const subDendaSwdklljDiskonWrap = document.getElementById('subDendaSwdklljDiskonWrap');
                if (subDendaSwdklljDiskonWrap) {
                    if (diskonDendaSwdklljTotal > 0) {
                        subDendaSwdklljDiskonWrap.style.display = 'flex';
                        setText('resDendaSwdklljDiskonVal', '- ' + formatRupiah(diskonDendaSwdklljTotal));
                    } else {
                        subDendaSwdklljDiskonWrap.style.display = 'none';
                    }
                }
                setupAccordion('rowDendaSwdklljWrap', 'dendaSwdklljDetail', 'dendaSwdklljToggleIcon');

                /* ====================================================
                   5. OPSEN PKB (BERJALAN + TUNGGAKAN)
                ==================================================== */
                const opsenBerjalan = Number(d.opsen_pkb_berjalan) || (d.pkb_berjalan ? Math.round(Number(d.pkb_berjalan) * 0.66) : Number(d.opsen_pkb) || 0);
                const opsenTunggakan = Number(d.opsen_pkb_tunggakan) || 0;
                const opsenTotalNet = Number(d.opsen_pkb) || (opsenBerjalan + opsenTunggakan);

                const diskonOpsenBerjalan = Number(d.diskon_opsen_pkb_berjalan) || 0;
                const diskonOpsenTunggakan = Number(d.diskon_opsen_pkb_tunggakan) || 0;
                const opsenTunggakanKotor = Number(d.opsen_pkb_tunggakan_kotor) || (opsenTunggakan + diskonOpsenTunggakan);
                const diskonOpsenTotal = diskonOpsenBerjalan + diskonOpsenTunggakan;

                setText('resOpsenTotal', formatRupiah(opsenTotalNet));
                setText('resOpsenBerjalanVal', formatRupiah(opsenBerjalan));
                setText('resOpsenTunggakanVal', formatRupiah(opsenTunggakanKotor));
                setText('resOpsenNetVal', formatRupiah(opsenTotalNet));

                const subOpsenTunggakanWrap = document.getElementById('subOpsenTunggakanWrap');
                if (subOpsenTunggakanWrap) {
                    subOpsenTunggakanWrap.style.display = (tahunTunggakan > 0 || opsenTunggakanKotor > 0) ? 'flex' : 'none';
                }

                const subOpsenDiskonWrap = document.getElementById('subOpsenDiskonWrap');
                if (subOpsenDiskonWrap) {
                    if (diskonOpsenTotal > 0) {
                        subOpsenDiskonWrap.style.display = 'flex';
                        setText('resOpsenDiskonVal', '- ' + formatRupiah(diskonOpsenTotal));
                    } else {
                        subOpsenDiskonWrap.style.display = 'none';
                    }
                }
                setupAccordion('rowOpsenWrap', 'opsenDetail', 'opsenToggleIcon');

                /* ====================================================
                   6. DENDA OPSEN PKB (BERJALAN + TUNGGAKAN)
                ==================================================== */
                const dendaOpsenBerjalan = Number(d.denda_opsen_berjalan) || 0;
                const dendaOpsenTunggakan = Number(d.denda_opsen_tunggakan) || 0;
                const dendaOpsenTotalNet = Number(d.denda_opsen_pkb) || Number(d.denda_opsen) || (dendaOpsenBerjalan + dendaOpsenTunggakan);

                const diskonDendaOpsenBerjalan = Number(d.diskon_denda_opsen_berjalan) || 0;
                const diskonDendaOpsenTunggakan = Number(d.diskon_denda_opsen_tunggakan) || 0;
                const dendaOpsenBerjalanKotor = Number(d.denda_opsen_berjalan_kotor) || (dendaOpsenBerjalan + diskonDendaOpsenBerjalan);
                const dendaOpsenTunggakanKotor = Number(d.denda_opsen_tunggakan_kotor) || (dendaOpsenTunggakan + diskonDendaOpsenTunggakan);
                const diskonDendaOpsenTotal = diskonDendaOpsenBerjalan + diskonDendaOpsenTunggakan;

                setText('resDendaOpsenTotal', formatRupiah(dendaOpsenTotalNet));
                setText('resDendaOpsenBerjalanVal', formatRupiah(dendaOpsenBerjalanKotor));
                setText('resDendaOpsenTunggakanVal', formatRupiah(dendaOpsenTunggakanKotor));
                setText('resDendaOpsenNetVal', formatRupiah(dendaOpsenTotalNet));

                const subDendaOpsenTunggakanWrap = document.getElementById('subDendaOpsenTunggakanWrap');
                if (subDendaOpsenTunggakanWrap) {
                    subDendaOpsenTunggakanWrap.style.display = (tahunTunggakan > 0 || dendaOpsenTunggakanKotor > 0) ? 'flex' : 'none';
                }

                const subDendaOpsenDiskonWrap = document.getElementById('subDendaOpsenDiskonWrap');
                if (subDendaOpsenDiskonWrap) {
                    if (diskonDendaOpsenTotal > 0) {
                        subDendaOpsenDiskonWrap.style.display = 'flex';
                        setText('resDendaOpsenDiskonVal', '- ' + formatRupiah(diskonDendaOpsenTotal));
                    } else {
                        subDendaOpsenDiskonWrap.style.display = 'none';
                    }
                }
                setupAccordion('rowDendaOpsenWrap', 'dendaOpsenDetail', 'dendaOpsenToggleIcon');

                /* ====================================================
                   7. STNK
                ==================================================== */
                const biayaStnk = Number(d.biaya_stnk) || 0;
                const biayaTnkb = Number(d.biaya_tnkb) || 0;
                const rowBiayaStnk = document.getElementById('rowBiayaStnk');
                const rowBiayaTnkb = document.getElementById('rowBiayaTnkb');

                if (d.stnk_wajib && biayaStnk > 0) {
                    if (rowBiayaStnk) rowBiayaStnk.style.display = 'flex';
                    setText('resBiayaStnk', formatRupiah(biayaStnk));
                } else {
                    if (rowBiayaStnk) rowBiayaStnk.style.display = 'none';
                }

                /* ====================================================
                   8. TNKB
                ==================================================== */
                if (d.stnk_wajib && biayaTnkb > 0) {
                    if (rowBiayaTnkb) rowBiayaTnkb.style.display = 'flex';
                    setText('resBiayaTnkb', formatRupiah(biayaTnkb));
                } else {
                    if (rowBiayaTnkb) rowBiayaTnkb.style.display = 'none';
                }


                /* ====================================================
                   AMNESTI (Penyesuaian Visual - Green Box)
                ==================================================== */
                const amnestiTotal = Number(d.amnesti_total_potongan) || 0;
                
                // Kotak Hijau sekarang bertugas menampilkan TOTAL SELURUH PENGHEMATAN 
                // (Diskon DP PKB 17.5% + Diskon Tunggakan 50% + Pembebasan Denda)
                if (d.amnesti_aktif && amnestiTotal > 0) {
                    show('rowAmnesti');
                    setText('resAmnesti', '- ' + formatRupiah(amnestiTotal));
                    
                    const amnestiLabel = document.querySelector('#rowAmnesti .bill-name');
                    if (amnestiLabel) {
                        amnestiLabel.textContent = 'Total Penghematan Amnesti';
                    }
                } else {
                    hide('rowAmnesti');
                }


                /* ====================================================
                   WARNING
                ==================================================== */

                if (d.peringatan) {

                    setText(
                        'resPeringatanText',
                        d.peringatan
                    );

                    show('resPeringatan');

                } else {

                    hide('resPeringatan');
                }


                /* ====================================================
                   TOTAL
                ==================================================== */

                setText(
                    'resTotal',
                    formatRupiah(
                        d.total_pajak
                    )
                );


                /* ====================================================
                   TAMPILKAN RESULT
                ==================================================== */

                resultContainer.style.display =
                    'block';


                requestAnimationFrame(
                    function() {

                        resultContainer.classList.add(
                            'active'
                        );

                    }
                );


                /* Scroll sedikit ke hasil */
                setTimeout(
                    function() {

                        resultContainer.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });

                    },
                    150
                );


            } catch (error) {

                console.error(error);


                errorMsg.textContent =
                    error.message ||
                    'Gagal terhubung ke server.';


                errorMsg.style.display =
                    'block';

            } finally {

                /* ----------------------------------------------------
                   RESTORE BUTTON
                -------------------------------------------------- */

                btnText.style.display =
                    'block';

                btnLoader.style.display =
                    'none';

                submitBtn.disabled =
                    false;
            }

        }
    );


    /* ================================================================
       AUTO UPPERCASE
    ================================================================= */

    document
        .getElementById('nopol')
        .addEventListener(
            'input',
            function() {

                this.value =
                    this.value
                        .toUpperCase();

            }
        );



    /* ================================================================
       AUTO SUBMIT JIKA ADA PARAMETER NOPOL DI URL
    ================================================================= */
    const urlParams = new URLSearchParams(window.location.search);
    const nopolParam = urlParams.get('nopol');
    if (nopolParam && nopolParam.trim() !== '') {
        const nopolInput = document.getElementById('nopol');
        if (nopolInput) {
            nopolInput.value = nopolParam.trim().toUpperCase();
            form.dispatchEvent(new Event('submit'));
        }
    }

    /* ================================================================
       DOWNLOAD GAMBAR (HTML2CANVAS)
    ================================================================= */
    const btnDownloadImg = document.getElementById('btnDownloadImg');
    
    if (btnDownloadImg) {
        btnDownloadImg.addEventListener('click', function() {
            const target = document.getElementById('resultContainer');
            const originalText = this.innerHTML;
            
            this.innerHTML = '⏳ Memproses Gambar...';
            this.disabled = true;

            // Sembunyikan tombol unduh sesaat agar tidak ikut terfoto
            this.style.display = 'none';

            html2canvas(target, {
                scale: 2, // Resolusi tinggi agar hasil tidak pecah
                backgroundColor: '#ffffff',
                useCORS: true
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'Info_Pajak_' + document.getElementById('resNopol').textContent + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
                
                // Kembalikan tombol ke kondisi semula
                btnDownloadImg.style.display = 'block';
                btnDownloadImg.innerHTML = originalText;
                btnDownloadImg.disabled = false;
            }).catch(err => {
                console.error(err);
                alert('Gagal membuat gambar. Pastikan browser Anda mendukung fitur ini.');
                btnDownloadImg.style.display = 'block';
                btnDownloadImg.innerHTML = originalText;
                btnDownloadImg.disabled = false;
            });
        });
    }