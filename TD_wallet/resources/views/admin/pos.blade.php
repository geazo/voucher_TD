@extends('layouts.app')

@section('content')
    <div class="container-fluid py-3 px-4">

        @if (session('success'))
            <div class="alert alert-success fw-bold"><i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-lg-7">
                <h5 class="fw-bold mb-3">Tambah Item Tagihan</h5>
                <div class="card border-0 shadow-sm border-top border-success border-4">
                    <div class="card-body p-4">

                        <form id="manualItemForm">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">Nama Layanan / Item</label>
                                    <input type="text" id="inputItemName" class="form-control form-control-lg bg-light"
                                        placeholder="Item Name" required autofocus>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Harga (Rp)</label>
                                    <input type="number" id="inputItemPrice" class="form-control form-control-lg bg-light"
                                        placeholder="" min="1000" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="mt-4 p-3 bg-light rounded text-muted small">
                            <i class="bi bi-info-circle me-1"></i> Ketik nama item dan harga secara manual, lalu tekan
                            tombol <strong>+</strong> atau tekan <strong>Enter</strong> untuk memasukkannya ke rincian
                            tagihan di sebelah kanan.
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-bottom border-2">
                        <h5 class="fw-bold mb-0">Rincian Tagihan</h5>
                    </div>
                    <div class="card-body p-0 d-flex flex-column">

                        <div class="p-3 flex-grow-1" style="max-height: 400px; overflow-y: auto;" id="cartContainer">
                            <div class="text-center text-muted my-5" id="emptyCartMsg">
                                <i class="bi bi-receipt fs-1 d-block mb-2 opacity-50"></i>
                                Belum ada tagihan
                            </div>
                            <div id="cartItemsList"></div>
                        </div>

                        <div class="p-3 bg-light border-top">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold fs-5">Total:</span>
                                <span class="fw-bold fs-3 text-success" id="grandTotalText">Rp 0</span>
                            </div>

                            <button type="button" class="btn btn-dark w-100 py-3 fw-bold fs-5" id="btnPay" disabled
                                data-bs-toggle="modal" data-bs-target="#qrScannerModal">
                                <i class="bi bi-qr-code-scan me-2"></i> BAYAR VIA QR
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="qrScannerModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-camera-video me-2"></i> Scan QR Customer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                        onclick="stopScanner()"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <h4 class="fw-bold text-success mb-3" id="modalTotalText">Rp 0</h4>
                    <p class="text-muted small mb-3">Arahkan kamera ke QR Code di HP Customer.</p>

                    <div id="reader" style="width: 100%; border-radius: 8px; overflow:hidden;" class="mb-3 bg-light">
                    </div>

                    <form id="paymentForm" action="{{ route('scan.process') }}" method="POST">
                        @csrf
                        <input type="hidden" name="qr_payload" id="qr_payload_input">
                        <input type="hidden" name="nominal_total" id="nominal_total_input">
                        <input type="hidden" name="cart_data" id="cart_data_input">
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    @if (session('success_invoice'))
        @php $invoice = session('success_invoice'); @endphp
        <div class="modal fade" id="invoiceModal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">

                    <div class="modal-header bg-success text-white border-bottom-0 pb-3"
                        style="border-radius: 1rem 1rem 0 0;">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-check-circle-fill me-2"></i>Transaksi Berhasil
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4 bg-light">
                        <div class="text-center mb-4">
                            <p class="text-muted mb-1">Total Tagihan</p>
                            <h2 class="fw-bold text-dark mb-0">Rp {{ number_format($invoice['total'], 0, ',', '.') }}</h2>
                        </div>

                        <div class="bg-white p-3 rounded-3 border shadow-sm">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Nama Customer</span>
                                <span class="fw-bold">{{ $invoice['customer_name'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Waktu Transaksi</span>
                                <span class="fw-bold">{{ $invoice['waktu'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Nama Kasir</span>
                                <span class="fw-bold">{{ $invoice['kasir_name'] }}</span>
                            </div>

                            @if (!empty($invoice['catatan']))
                                <div class="alert alert-warning p-2 mt-2 mb-0 small text-center border-0 rounded-3">
                                    {{ $invoice['catatan'] }}
                                </div>
                            @endif

                            <hr class="border-dashed my-3" style="border-top: 2px dashed #dee2e6;">

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-dark fw-bold"><i class="bi bi-wallet2 text-success me-2"></i>Uang
                                    Terpotong</span>
                                <span class="fw-bold">Rp {{ number_format($invoice['tagihan_uang'], 0, ',', '.') }}</span>
                            </div>

                            @if ($invoice['tagihan_poin'] > 0)
                                <div class="d-flex justify-content-between">
                                    <span class="text-dark fw-bold"><i class="bi bi-star text-primary me-2"></i>Poin
                                        Terpotong</span>
                                    <span
                                        class="fw-bold text-primary">{{ number_format($invoice['tagihan_poin'], 0, ',', '.') }}
                                        Pts</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="modal-footer border-top-0 d-flex justify-content-between bg-light"
                        style="border-radius: 0 0 1rem 1rem;">
                        <button type="button" class="btn btn-outline-secondary fw-bold"
                            data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary fw-bold" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Cetak Struk
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Script kebal JS error untuk memunculkan modal otomatis
            window.addEventListener('load', function() {
                if (typeof bootstrap !== 'undefined') {
                    var myModalEl = document.getElementById('invoiceModal');
                    var invoiceModal = new bootstrap.Modal(myModalEl);
                    invoiceModal.show();
                }
            });
        </script>
    @endif

    <script>
        let cart = [];
        let grandTotal = 0;
        let html5QrCode;

        // --- LOGIKA FORM MANUAL ---
        document.getElementById('manualItemForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Mencegah halaman me-refresh

            let nameInput = document.getElementById('inputItemName');
            let priceInput = document.getElementById('inputItemPrice');

            let name = nameInput.value.trim();
            let price = parseInt(priceInput.value);

            if (name !== '' && price > 0) {
                // Gunakan timestamp sebagai ID unik sementara agar item dengan nama sama tidak menumpuk otomatis
                let uniqueId = new Date().getTime();

                cart.push({
                    id: uniqueId,
                    name: name,
                    price: price,
                    qty: 1,
                    subtotal: price
                });
                renderCart();

                // Kosongkan form dan kembalikan kursor ke input nama
                nameInput.value = '';
                priceInput.value = '';
                nameInput.focus();
            }
        });

        // --- LOGIKA KERANJANG ---
        function removeItem(id) {
            cart = cart.filter(item => item.id !== id);
            renderCart();
        }

        function renderCart() {
            // Targetkan div list yang baru, bukan container utamanya
            const cartList = document.getElementById('cartItemsList');
            const emptyMsg = document.getElementById('emptyCartMsg');
            const btnPay = document.getElementById('btnPay');

            grandTotal = 0;
            cartList.innerHTML = ''; // Hanya bersihkan daftar item, pesan kosong tetap aman

            if (cart.length === 0) {
                emptyMsg.style.display = 'block';
                btnPay.disabled = true;
            } else {
                emptyMsg.style.display = 'none';
                btnPay.disabled = false;

                cart.forEach(item => {
                    grandTotal += item.subtotal;
                    cartList.innerHTML += `
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                        <div>
                            <div class="fw-bold text-dark">${item.name}</div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-bold">Rp ${item.subtotal.toLocaleString('id-ID')}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 border-0" onclick="removeItem(${item.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                });
            }

            let formattedTotal = 'Rp ' + grandTotal.toLocaleString('id-ID');
            document.getElementById('grandTotalText').innerText = formattedTotal;
            document.getElementById('modalTotalText').innerText = formattedTotal;

            document.getElementById('nominal_total_input').value = grandTotal;
            document.getElementById('cart_data_input').value = JSON.stringify(cart);
        }

        // --- LOGIKA SCANNER KAMERA ---
        const modal = document.getElementById('qrScannerModal');

        modal.addEventListener('shown.bs.modal', function() {
            html5QrCode = new Html5Qrcode("reader");
            const config = {
                fps: 10,
                qrbox: {
                    width: 250,
                    height: 250
                }
            };

            html5QrCode.start({
                facingMode: "environment"
            }, config, (decodedText) => {
                html5QrCode.stop().then(() => {
                    let audio = new Audio('https://www.soundjay.com/buttons/sounds/button-09.mp3');
                    audio.play();
                    document.getElementById('qr_payload_input').value = decodedText;
                    document.getElementById('paymentForm').submit();
                });
            }).catch(err => {});
        });

        function stopScanner() {
            if (html5QrCode) {
                html5QrCode.stop().catch(err => console.log(err));
            }
        }
    </script>
@endsection
