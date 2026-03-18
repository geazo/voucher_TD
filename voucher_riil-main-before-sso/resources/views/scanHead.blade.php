<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Scan Guest Barcode</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
    <link href="{{ asset('css/tabler.min.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .flipped {
            transform: scale(-1, 1);
        }
    </style>
</head>

<body>
    <div>
        <a href="{{ url('/dashboard') }}" class="mx-2 my-2 btn btn-primary"> Back </a>
    </div>
    <div id="reader" width="600px"></div>
    <div id="message-container" class="my-3 text-center"></div>
    <div class="m-1 text-center">
        <button onclick="mirrorCamera()" class="btn btn-primary">Mirror reader</button>
    </div>

    <!-- Modal Alert Message -->
    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alertModalLabel">Informasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body" id="alertModalBody">
                    <!-- Isi pesan akan diisi dengan JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal konfirmasi scan -->
    <div class="modal fade" id="confirmScanModal" tabindex="-1" aria-labelledby="confirmScanLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Scan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    Apakah anda yakin ingin menggunakan voucher ini?
                    <hr>
                    {{-- <pre id="voucherMessage" style="white-space: pre-wrap; font-size: 14px;" class="px-3 text-start"></pre> --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="confirmScanBtn">Ya, Gunakan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> <!-- jQuery versi terbaru -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            const msg = params.get('msg');
            if (msg) {
                document.getElementById('message-container').innerHTML = `
                    <div class="alert alert-info" role="alert">
                        ${msg}
                    </div>
                `;
            }
        });
    </script>

    <script>
        let isSubmitting = false;
        let html5Qr;
        let currentScanId = null;

        const config = {
            fps: 10,
            qrbox: {
                width: 300,
                height: 300
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            const msg = params.get('msg');
            if (msg) {
                showAlertModal(msg, true);
            }

            html5Qr = new Html5Qrcode("reader");
            html5Qr.start({
                    facingMode: "environment"
                }, config, onScanSuccess, onScanFailure)
                .catch(err => {
                    console.error("❌ Gagal memulai kamera:", err);
                });

            // Konfirmasi tombol Ya
            document.getElementById("confirmScanBtn").addEventListener("click", function() {
                if (currentScanId) {
                    bootstrap.Modal.getInstance(document.getElementById('confirmScanModal')).hide();
                    register(currentScanId);
                    currentScanId = null;
                }
            });
        });

        // function showAlertModal(message) {
        //     document.getElementById('alertModalBody').textContent = message;
        //     new bootstrap.Modal(document.getElementById('alertModal')).show();
        // }

        function showAlertModal(message, isSuccess = false) {
            const titleEl = document.getElementById('alertModalLabel');
            const bodyEl = document.getElementById('alertModalBody');

            titleEl.textContent = isSuccess ? 'Informasi Berhasil' : 'Informasi Gagal';
            bodyEl.textContent = message;

            new bootstrap.Modal(document.getElementById('alertModal')).show();
        }

        function onScanSuccess(decodedText, decodedResult) {
            if (isSubmitting) return;
            currentScanId = decodedText;
            html5Qr.pause();
            // new bootstrap.Modal(document.getElementById('confirmScanModal')).show();
            fetchVoucherDetail(decodedText);
        }

        async function fetchVoucherDetail(id) {
            isSubmitting = true;
            try {
                const response = await fetch("{{ route('scanHead.barcode') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute(
                            "content")
                    },
                    body: JSON.stringify({
                        id: id,
                        preview: true
                    })
                });

                const data = await response.json();

                if (data.error) {
                    showAlertModal(data.error, false);
                    await html5Qr.resume();
                    return;
                }

                // langsung tampilkan modal konfirmasi tanpa detail voucher
                new bootstrap.Modal(document.getElementById('confirmScanModal')).show();

            } catch (error) {
                showAlertModal("Gagal mengambil data voucher.");
                await html5Qr.resume();
            } finally {
                isSubmitting = false;
            }
        }

        async function register(id) {
            isSubmitting = true;
            try {
                const response = await fetch("{{ route('scanHead.barcode') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute(
                            "content")
                    },
                    body: JSON.stringify({
                        id: id
                    })
                });

                const data = await response.json();

                // if (data.redirect) {
                //     window.location.href = data.redirect;
                // } else if (data.success) {
                //     showAlertModal(data.success, true);
                // }

                if (data.success) {
                    showAlertModal(data.success, true);
                } else if (data.error) {
                    showAlertModal(data.error, false);
                }

            } catch (error) {
                showAlertModal("Terjadi kesalahan, coba lagi.", false);
            } finally {
                isSubmitting = false;
                await html5Qr.resume();
            }
        }

        function onScanFailure(error) {
            // Tidak perlu tampilkan apapun untuk error kecil
        }

        function mirrorCamera() {
            document.getElementById("reader").classList.toggle("flipped");
        }
    </script>
</body>

</html>
