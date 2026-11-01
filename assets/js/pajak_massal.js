const fileInput =
        document.getElementById('fileNopol');

    const dropArea =
        document.getElementById('dropArea');

    const fileInfo =
        document.getElementById('fileInfo');

    const fileName =
        document.getElementById('fileName');

    const fileMeta =
        document.getElementById('fileMeta');

    const removeFile =
        document.getElementById('removeFile');

    const btnProses =
        document.getElementById('btnProses');

    const form =
        document.getElementById('massForm');


    /* =========================================
       FORMAT FILE SIZE
    ========================================= */

    function formatFileSize(bytes) {

        if (bytes === 0) {
            return '0 Bytes';
        }

        const units = [
            'Bytes',
            'KB',
            'MB',
            'GB'
        ];

        const i =
            Math.floor(
                Math.log(bytes) /
                Math.log(1024)
            );

        return (
            parseFloat(
                (
                    bytes /
                    Math.pow(1024, i)
                ).toFixed(2)
            ) +
            ' ' +
            units[i]
        );
    }


    /* =========================================
       UPDATE FILE
    ========================================= */

    function updateFile(file) {

        if (!file) {

            resetFile();

            return;
        }


        const extension =
            file.name
                .split('.')
                .pop()
                .toLowerCase();


        if (extension !== 'txt') {

            alert(
                'Format file tidak valid. Silakan pilih file .TXT.'
            );

            resetFile();

            return;
        }


        fileName.textContent =
            file.name;


        /*
         * Hitung jumlah NOPOL
         */

        const reader =
            new FileReader();


        reader.onload =
            function(event) {

                const content =
                    event.target.result || '';

                const lines =
                    content
                        .split(/\r?\n/)
                        .map(line => line.trim())
                        .filter(Boolean);

                fileMeta.textContent =
                    formatFileSize(file.size) +
                    ' · ' +
                    lines.length +
                    ' nomor polisi terdeteksi';
            };


        reader.readAsText(file);


        fileInfo.classList.add('visible');

        dropArea.classList.add('has-file');

        btnProses.disabled = false;
    }


    /* =========================================
       RESET
    ========================================= */

    function resetFile() {

        fileInput.value = '';

        fileInfo.classList.remove('visible');

        dropArea.classList.remove('has-file');

        fileName.textContent = '-';

        fileMeta.textContent = '-';

        btnProses.disabled = true;
    }


    /* =========================================
       FILE INPUT
    ========================================= */

    fileInput.addEventListener(
        'change',
        function() {

            if (
                this.files &&
                this.files.length > 0
            ) {

                updateFile(
                    this.files[0]
                );

            } else {

                resetFile();

            }

        }
    );


    /* =========================================
       REMOVE FILE
    ========================================= */

    removeFile.addEventListener(
        'click',
        function(event) {

            event.preventDefault();

            event.stopPropagation();

            resetFile();

        }
    );


    /* =========================================
       DRAG & DROP
    ========================================= */

    [
        'dragenter',
        'dragover'
    ].forEach(eventName => {

        dropArea.addEventListener(
            eventName,
            function(event) {

                event.preventDefault();

                dropArea.classList.add(
                    'dragging'
                );

            }
        );

    });


    [
        'dragleave',
        'drop'
    ].forEach(eventName => {

        dropArea.addEventListener(
            eventName,
            function(event) {

                event.preventDefault();

                dropArea.classList.remove(
                    'dragging'
                );

            }
        );

    });


    dropArea.addEventListener(
        'drop',
        function(event) {

            const files =
                event.dataTransfer.files;

            if (
                files &&
                files.length > 0
            ) {

                const file =
                    files[0];

                try {

                    const dataTransfer =
                        new DataTransfer();

                    dataTransfer.items.add(file);

                    fileInput.files =
                        dataTransfer.files;

                } catch (error) {

                    /*
                     * Browser lama mungkin tidak
                     * mendukung DataTransfer.
                     * UI tetap akan menampilkan file.
                     */

                }

                updateFile(file);
            }

        }
    );


    /* =========================================
       SUBMIT
    ========================================= */

    form.addEventListener(
        'submit',
        function() {

            btnProses.disabled = true;

            btnProses.classList.add(
                'processing'
            );


            btnProses.innerHTML = `

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >

                    <path
                        d="M12 2v4"
                    />

                    <path
                        d="M12 18v4"
                    />

                    <path
                        d="m4.93 4.93 2.83 2.83"
                    />

                    <path
                        d="m16.24 16.24 2.83 2.83"
                    />

                    <path
                        d="M2 12h4"
                    />

                    <path
                        d="M18 12h4"
                    />

                    <path
                        d="m4.93 19.07 2.83-2.83"
                    />

                    <path
                        d="m16.24 7.76 2.83-2.83"
                    />

                </svg>

                Sedang Memproses Data...

            `;

        }
    );