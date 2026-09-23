<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Kalkulator Pajak Kendaraan Bermotor NTT</title>


    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <style>

        :root {

            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-soft: #eff6ff;

            --blue: #3b82f6;
            --blue-border: #bfdbfe;

            --green: #059669;
            --green-soft: #ecfdf5;

            --orange: #ea580c;
            --orange-soft: #fff7ed;

            --red: #dc2626;
            --red-soft: #fef2f2;

            --yellow: #d97706;
            --yellow-soft: #fffbeb;

            --background: #f3f6fb;
            --card: #ffffff;

            --text: #172033;
            --text-soft: #64748b;
            --border: #e2e8f0;

            --shadow:
                0 20px 50px rgba(15, 23, 42, 0.08);

            --radius: 20px;
        }


        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {

            font-family: 'Outfit', sans-serif;

            background:
                radial-gradient(
                    circle at top left,
                    rgba(37, 99, 235, 0.08),
                    transparent 35%
                ),
                linear-gradient(
                    180deg,
                    #f8fafc 0%,
                    #eef3f9 100%
                );

            color: var(--text);

            min-height: 100vh;

            padding: 40px 20px;
        }


        .container {

            width: 100%;

            max-width: 720px;

            margin: 0 auto;
        }


        /* ================================================================
           HEADER
        ================================================================ */

        .header {

            text-align: center;

            margin-bottom: 28px;
        }


        .logo {

            width: 58px;
            height: 58px;

            margin: 0 auto 15px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 17px;

            background:
                linear-gradient(
                    135deg,
                    var(--primary),
                    #60a5fa
                );

            color: white;

            font-size: 28px;

            box-shadow:
                0 10px 25px rgba(37, 99, 235, 0.25);
        }


        h1 {

            font-size: 30px;

            line-height: 1.2;

            font-weight: 800;

            color: #172033;

            margin-bottom: 8px;
        }


        .subtitle {

            color: var(--text-soft);

            font-size: 15px;

            line-height: 1.5;
        }


        /* ================================================================
           CARD
        ================================================================ */

        .card {

            background: var(--card);

            border: 1px solid rgba(226, 232, 240, 0.9);

            border-radius: var(--radius);

            padding: 28px;

            box-shadow: var(--shadow);
        }


        /* ================================================================
           FORM
        ================================================================ */

        .form-label {

            display: block;

            font-size: 14px;

            font-weight: 600;

            color: #334155;

            margin-bottom: 9px;
        }


        .input-wrapper {

            position: relative;

            margin-bottom: 14px;
        }


        .input-icon {

            position: absolute;

            left: 16px;

            top: 50%;

            transform: translateY(-50%);

            font-size: 19px;

            pointer-events: none;
        }


        input {

            width: 100%;

            height: 58px;

            padding: 0 18px 0 52px;

            border: 1.5px solid var(--border);

            border-radius: 14px;

            background: #f8fafc;

            color: var(--text);

            font-family: inherit;

            font-size: 18px;

            font-weight: 600;

            letter-spacing: 1.5px;

            text-transform: uppercase;

            outline: none;

            transition: 0.2s ease;
        }


        input::placeholder {

            color: #94a3b8;

            font-weight: 400;

            letter-spacing: 0;
        }


        input:focus {

            background: white;

            border-color: var(--primary);

            box-shadow:
                0 0 0 4px rgba(37, 99, 235, 0.10);
        }


        button {

            width: 100%;

            height: 56px;

            border: none;

            border-radius: 14px;

            background:
                linear-gradient(
                    135deg,
                    var(--primary),
                    var(--primary-dark)
                );

            color: white;

            font-family: inherit;

            font-size: 16px;

            font-weight: 700;

            cursor: pointer;

            transition: 0.2s ease;

            box-shadow:
                0 8px 20px rgba(37, 99, 235, 0.20);
        }


        button:hover {

            transform: translateY(-1px);

            box-shadow:
                0 12px 25px rgba(37, 99, 235, 0.25);
        }


        button:active {

            transform: translateY(0);
        }


        button:disabled {

            opacity: 0.7;

            cursor: not-allowed;

            transform: none;
        }


        /* ================================================================
           LOADER
        ================================================================ */

        .loader {

            display: none;

            width: 21px;
            height: 21px;

            border: 3px solid rgba(255,255,255,0.35);

            border-top-color: white;

            border-radius: 50%;

            animation: spin 0.8s linear infinite;

            margin: 0 auto;
        }


        @keyframes spin {

            to {
                transform: rotate(360deg);
            }
        }


        /* ================================================================
           ERROR
        ================================================================ */

        .error-message {

            display: none;

            margin-top: 16px;

            padding: 14px 16px;

            border-radius: 12px;

            background: var(--red-soft);

            border: 1px solid #fecaca;

            color: var(--red);

            font-size: 14px;

            line-height: 1.5;
        }


        /* ================================================================
           RESULT
        ================================================================ */

        .result-container {

            display: none;

            margin-top: 28px;

            opacity: 0;

            transform: translateY(12px);

            transition:
                opacity 0.35s ease,
                transform 0.35s ease;
        }


        .result-container.active {

            display: block;

            opacity: 1;

            transform: translateY(0);
        }


        /* ================================================================
           SECTION
        ================================================================ */

        .section {

            margin-bottom: 20px;
        }


        .section-header {

            display: flex;

            align-items: center;

            gap: 10px;

            margin-bottom: 12px;

            padding-bottom: 10px;

            border-bottom: 1px solid var(--border);
        }


        .section-icon {

            width: 36px;
            height: 36px;

            border-radius: 10px;

            background: var(--primary-soft);

            color: var(--primary);

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 19px;
        }


        .section-title {

            font-size: 16px;

            font-weight: 800;

            color: #1e293b;
        }


        /* ================================================================
           IDENTITAS
        ================================================================ */

        .identity-box {

            border: 1px solid var(--border);

            border-radius: 15px;

            overflow: hidden;

            background: white;
        }


        .identity-row {

            display: grid;

            grid-template-columns: 120px 1fr;

            padding: 13px 15px;

            border-bottom: 1px solid #eef2f7;

            gap: 15px;
        }


        .identity-row:last-child {

            border-bottom: none;
        }


        .identity-label {

            color: var(--text-soft);

            font-size: 13px;

            font-weight: 500;
        }


        .identity-value {

            color: var(--text);

            font-size: 14px;

            font-weight: 600;

            word-break: break-word;
        }


        .owner {

            font-size: 15px;

            font-weight: 800;

            color: #0f172a;
        }


        .nopol {

            display: inline-flex;

            align-items: center;

            padding: 4px 9px;

            border-radius: 7px;

            background: #eff6ff;

            border: 1px solid #dbeafe;

            color: var(--primary-dark);

            font-size: 14px;

            font-weight: 800;

            letter-spacing: 1px;
        }


        /* ================================================================
           BILL
        ================================================================ */

        .bill-box {

            border: 1px solid var(--border);

            border-radius: 15px;

            overflow: hidden;

            background: white;
        }


        .bill-row {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 15px;

            padding: 14px 16px;

            border-bottom: 1px solid #eef2f7;
        }


        .bill-row:last-child {

            border-bottom: none;
        }


        .bill-name {

            color: #475569;

            font-size: 14px;

            font-weight: 500;
        }


        .bill-value {

            color: #172033;

            font-size: 14px;

            font-weight: 700;

            text-align: right;

            white-space: nowrap;
        }


        .bill-row.current {

            background: #f8fbff;
        }


        .bill-row.current .bill-name {

            color: var(--primary-dark);

            font-weight: 600;
        }


        .bill-row.arrears {

            background: #fffaf0;
        }


        .bill-row.arrears .bill-name {

            color: #92400e;

            font-weight: 600;
        }


        .bill-row.penalty .bill-name {

            color: #b45309;
        }


        .bill-row.penalty .bill-value {

            color: #b45309;
        }


        /* ================================================================
           BADGE
        ================================================================ */

        .badge {

            display: inline-flex;

            align-items: center;

            padding: 3px 7px;

            border-radius: 6px;

            margin-left: 5px;

            font-size: 10px;

            font-weight: 700;
        }


        .badge-blue {

            background: #eff6ff;

            color: #2563eb;

            border: 1px solid #dbeafe;
        }


        .badge-orange {

            background: var(--orange-soft);

            color: var(--orange);

            border: 1px solid #fed7aa;
        }


        /* ================================================================
           TOTAL
        ================================================================ */

        .total-box {

            margin-top: 20px;

            padding: 22px;

            border-radius: 17px;

            background:
                linear-gradient(
                    135deg,
                    #eff6ff,
                    #f8fbff
                );

            border: 1px solid #bfdbfe;

            text-align: center;
        }


        .total-label {

            color: var(--primary-dark);

            font-size: 12px;

            font-weight: 800;

            letter-spacing: 1.2px;

            margin-bottom: 6px;
        }


        .total-value {

            color: #1e3a8a;

            font-size: 32px;

            line-height: 1.2;

            font-weight: 800;

            word-break: break-word;
        }


        /* ================================================================
           TUNGGAKAN INFO
        ================================================================ */

        .arrears-info {

            display: none;

            margin-top: 15px;

            padding: 13px 15px;

            border-radius: 12px;

            background: var(--yellow-soft);

            border: 1px solid #fde68a;

            color: #92400e;

            font-size: 13px;

            line-height: 1.5;
        }


        /* ================================================================
           WARNING
        ================================================================ */

        .warning {

            display: none;

            margin-top: 15px;

            padding: 13px 15px;

            border-radius: 12px;

            background: var(--yellow-soft);

            border: 1px solid #fde68a;

            color: #92400e;

            font-size: 13px;

            line-height: 1.5;
        }


        /* ================================================================
           FOOTER
        ================================================================ */

        .footer {

            text-align: center;

            color: #94a3b8;

            font-size: 12px;

            margin-top: 20px;
        }


        /* ================================================================
           RESPONSIVE
        ================================================================ */

        @media (max-width: 600px) {

            body {

                padding: 22px 12px;
            }


            .card {

                padding: 20px;

                border-radius: 17px;
            }


            h1 {

                font-size: 25px;
            }


            .identity-row {

                grid-template-columns: 95px 1fr;

                gap: 10px;

                padding: 12px;
            }


            .bill-row {

                padding: 13px 12px;
            }


            .bill-name,
            .bill-value {

                font-size: 13px;
            }


            .total-value {

                font-size: 27px;
            }
        }

    </style>

</head>


<body>


<div class="container">


    <!-- ================================================================
         HEADER
    ================================================================= -->

    <div class="header">

        <div class="logo">
            🚘
        </div>

        <h1>Samsat PKB NTT</h1>

        <p class="subtitle">
            Kalkulator Pajak Kendaraan Bermotor<br>
            Provinsi Nusa Tenggara Timur
        </p>

    </div>


    <!-- ================================================================
         MAIN CARD
    ================================================================= -->

    <div class="card">


        <!-- FORM -->

        <form id="taxForm">

            <label
                class="form-label"
                for="nopol"
            >
                Nomor Polisi
            </label>


            <div class="input-wrapper">

                <div class="input-icon">
                    🚗
                </div>

                <input
                    type="text"
                    id="nopol"
                    placeholder="Contoh: DH3156PF"
                    autocomplete="off"
                    required
                >

            </div>


            <button
                type="submit"
                id="submitBtn"
            >

                <span id="btnText">
                    Hitung Pajak
                </span>

                <div
                    class="loader"
                    id="btnLoader"
                ></div>

            </button>

        </form>


        <!-- ERROR -->

        <div
            class="error-message"
            id="errorMsg"
        ></div>


        <!-- ============================================================
             RESULT
        ============================================================= -->

        <div
            class="result-container"
            id="resultContainer"
        >


            <!-- ========================================================
                 IDENTITAS KENDARAAN
            ========================================================= -->

            <div class="section">

                <div class="section-header">

                    <div class="section-icon">
                        🚘
                    </div>

                    <div class="section-title">
                        Identitas Kendaraan
                    </div>

                </div>


                <div class="identity-box">


                    <div class="identity-row">

                        <div class="identity-label">
                            Nopol
                        </div>

                        <div class="identity-value">
                            <span
                                class="nopol"
                                id="resNopol"
                            >
                                -
                            </span>
                        </div>

                    </div>


                    <div class="identity-row">

                        <div class="identity-label">
                            Pemilik
                        </div>

                        <div
                            class="identity-value owner"
                            id="resPemilik"
                        >
                            -
                        </div>

                    </div>


                    <div class="identity-row">

                        <div class="identity-label">
                            Unit
                        </div>

                        <div
                            class="identity-value"
                            id="resUnit"
                        >
                            -
                        </div>

                    </div>


                    <div class="identity-row">

                        <div class="identity-label">
                            Tahun
                        </div>

                        <div
                            class="identity-value"
                            id="resTahun"
                        >
                            -
                        </div>

                    </div>


                </div>

            </div>


            <!-- ========================================================
                 RINCIAN TAGIHAN
            ========================================================= -->

            <div class="section">

                <div class="section-header">

                    <div class="section-icon">
                        🏦
                    </div>

                    <div class="section-title">
                        Rincian Biaya Tagihan
                    </div>

                </div>


                <div class="bill-box">


                    <!-- PKB BERJALAN -->

                    <div class="bill-row current">

                        <div class="bill-name">
                            PKB
                            <span
                                class="badge badge-blue"
                                id="badgePkbBerjalan"
                            >
                                Berjalan
                            </span>
                        </div>

                        <div
                            class="bill-value"
                            id="resPkbBerjalan"
                        >
                            Rp 0
                        </div>

                    </div>


                    <!-- TUNGGAKAN PKB -->

                    <div
                        class="bill-row arrears"
                        id="rowPkbTunggakan"
                        style="display:none;"
                    >

                        <div class="bill-name">
                            Tunggakan PKB
                            <span
                                class="badge badge-orange"
                                id="badgeTunggakan"
                            >
                                -
                            </span>
                        </div>

                        <div
                            class="bill-value"
                            id="resPkbTunggakan"
                        >
                            Rp 0
                        </div>

                    </div>


                    <!-- DENDA PKB -->

                    <div
                        class="bill-row penalty"
                        id="rowDendaPkb"
                        style="display:none;"
                    >

                        <div class="bill-name">
                            Denda PKB
                        </div>

                        <div
                            class="bill-value"
                            id="resDendaPkb"
                        >
                            Rp 0
                        </div>

                    </div>


                    <!-- SWDKLLJ -->

                    <div class="bill-row">

                        <div class="bill-name">
                            SWDKLLJ
                        </div>

                        <div
                            class="bill-value"
                            id="resSwdkllj"
                        >
                            Rp 0
                        </div>

                    </div>


                    <!-- DENDA SWDKLLJ -->

                    <div
                        class="bill-row penalty"
                        id="rowDendaSwdkllj"
                        style="display:none;"
                    >

                        <div class="bill-name">
                            Denda SWDKLLJ
                        </div>

                        <div
                            class="bill-value"
                            id="resDendaSwdkllj"
                        >
                            Rp 0
                        </div>

                    </div>


                    <!-- OPSEN -->

                    <div
                        class="bill-row"
                        id="rowOpsen"
                        style="display:none;"
                    >

                        <div class="bill-name">
                            Opsen PKB
                        </div>

                        <div
                            class="bill-value"
                            id="resOpsen"
                        >
                            Rp 0
                        </div>

                    </div>


                    <!-- DENDA OPSEN -->

                    <div
                        class="bill-row penalty"
                        id="rowDendaOpsen"
                        style="display:none;"
                    >

                        <div class="bill-name">
                            Denda Opsen PKB
                        </div>

                        <div
                            class="bill-value"
                            id="resDendaOpsen"
                        >
                            Rp 0
                        </div>

                    </div>


                    <!-- STNK -->

                    <div
                        class="bill-row"
                        id="rowBiayaStnk"
                        style="display:none;"
                    >

                        <div class="bill-name">
                            Biaya STNK
                        </div>

                        <div
                            class="bill-value"
                            id="resBiayaStnk"
                        >
                            Rp 0
                        </div>

                    </div>


                    <!-- TNKB -->

                    <div
                        class="bill-row"
                        id="rowBiayaTnkb"
                        style="display:none;"
                    >

                        <div class="bill-name">
                            Biaya TNKB
                        </div>

                        <div
                            class="bill-value"
                            id="resBiayaTnkb"
                        >
                            Rp 0
                        </div>

                    </div>


                </div>


                <!-- TUNGGAKAN INFO -->

                <div
                    class="arrears-info"
                    id="arrearsInfo"
                >
                    ⚠️ Kendaraan memiliki
                    <strong id="arrearsYearText">0</strong>
                    tahun tunggakan.
                    Tahun terbaru dihitung sebagai
                    <strong>PKB berjalan</strong>, sedangkan
                    tahun sebelumnya dihitung sebagai
                    <strong>tunggakan PKB</strong>.
                </div>


            </div>


            <!-- ========================================================
                 TOTAL
            ========================================================= -->

            <div class="total-box">

                <div class="total-label">
                    TOTAL BAYAR
                </div>

                <div
                    class="total-value"
                    id="resTotal"
                >
                    Rp 0
                </div>

            </div>


            <!-- WARNING -->

            <div
                class="warning"
                id="resPeringatan"
            >

                ⚠️
                <span id="resPeringatanText"></span>

            </div>


            <div class="footer">

                Estimasi perhitungan pajak berdasarkan
                data kendaraan yang tersedia.

            </div>


        </div>

    </div>

</div>


<script>

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


                /* ====================================================
                   PKB BERJALAN
                ==================================================== */

                setText(
                    'resPkbBerjalan',
                    formatRupiah(
                        d.pkb_berjalan
                    )
                );


                /* ====================================================
                   TUNGGAKAN PKB
                ==================================================== */

                const tahunTunggakan =
                    Number(
                        d.tahun_tunggakan
                    ) || 0;


                const pkbTunggakan =
                    Number(
                        d.pkb_tunggakan
                    ) || 0;


                if (
                    tahunTunggakan > 0 &&
                    pkbTunggakan > 0
                ) {

                    show('rowPkbTunggakan');

                    setText(
                        'resPkbTunggakan',
                        formatRupiah(
                            pkbTunggakan
                        )
                    );


                    setText(
                        'badgeTunggakan',
                        `${tahunTunggakan} tahun`
                    );


                    show('arrearsInfo');

                    setText(
                        'arrearsYearText',
                        tahunTunggakan
                    );

                } else {

                    hide('rowPkbTunggakan');

                    hide('arrearsInfo');
                }


                /* ====================================================
                   DENDA PKB
                ==================================================== */

                const dendaPkb =
                    Number(
                        d.denda_pkb
                    ) || 0;


                if (dendaPkb > 0) {

                    show('rowDendaPkb');

                    setText(
                        'resDendaPkb',
                        formatRupiah(
                            dendaPkb
                        )
                    );

                } else {

                    hide('rowDendaPkb');
                }


                /* ====================================================
                   SWDKLLJ
                ==================================================== */

                setText(
                    'resSwdkllj',
                    formatRupiah(
                        d.swdkllj
                    )
                );


                /* ====================================================
                   DENDA SWDKLLJ
                ==================================================== */

                const dendaSwdkllj =
                    Number(
                        d.denda_swdkllj
                    ) || 0;


                if (dendaSwdkllj > 0) {

                    show('rowDendaSwdkllj');

                    setText(
                        'resDendaSwdkllj',
                        formatRupiah(
                            dendaSwdkllj
                        )
                    );

                } else {

                    hide('rowDendaSwdkllj');
                }


                /* ====================================================
                   OPSEN PKB
                ==================================================== */

                const opsen =
                    Number(
                        d.opsen_pkb
                    ) || 0;


                if (opsen > 0) {

                    show('rowOpsen');

                    setText(
                        'resOpsen',
                        formatRupiah(
                            opsen
                        )
                    );

                } else {

                    hide('rowOpsen');
                }


                /* ====================================================
                   DENDA OPSEN
                ==================================================== */

                const dendaOpsen =
                    Number(
                        d.denda_opsen_pkb
                    ) || 0;


                if (dendaOpsen > 0) {

                    show('rowDendaOpsen');

                    setText(
                        'resDendaOpsen',
                        formatRupiah(
                            dendaOpsen
                        )
                    );

                } else {

                    hide('rowDendaOpsen');
                }


                /* ====================================================
                   STNK
                ==================================================== */

                const biayaStnk =
                    Number(
                        d.biaya_stnk
                    ) || 0;


                const biayaTnkb =
                    Number(
                        d.biaya_tnkb
                    ) || 0;


                if (
                    d.stnk_wajib &&
                    biayaStnk > 0
                ) {

                    show('rowBiayaStnk');

                    setText(
                        'resBiayaStnk',
                        formatRupiah(
                            biayaStnk
                        )
                    );

                } else {

                    hide('rowBiayaStnk');
                }


                /* ====================================================
                   TNKB
                ==================================================== */

                if (
                    d.stnk_wajib &&
                    biayaTnkb > 0
                ) {

                    show('rowBiayaTnkb');

                    setText(
                        'resBiayaTnkb',
                        formatRupiah(
                            biayaTnkb
                        )
                    );

                } else {

                    hide('rowBiayaTnkb');
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

</script>


</body>

</html>