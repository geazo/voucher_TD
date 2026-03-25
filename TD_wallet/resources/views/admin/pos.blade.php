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
                                <div class="col-md-7">
                                    <label class="form-label fw-bold small text-muted">Pilih Layanan / Item</label>
                                    <select id="selectItem" class="form-select form-select-lg bg-light" required autofocus>
                                        <option value="">-- Pilih Item --</option>
                                        @foreach ($items as $item)
                                            <option value="{{ $item->id }}" data-name="{{ $item->nama }}"
                                                data-price="{{ $item->harga }}">
                                                {{ $item->nama }} - Rp {{ number_format($item->harga, 0, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-muted">Jumlah (Qty)</label>
                                    <div class="input-group">
                                        <input type="number" id="inputItemQty"
                                            class="form-control form-control-lg bg-light text-center" value="1"
                                            min="1" required>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="mt-4 p-3 bg-light rounded text-muted small">
                            <i class="bi bi-info-circle me-1"></i> Pilih item dari daftar layanan, tentukan jumlah (qty),
                            lalu tekan tombol <strong>+</strong> atau <strong>Enter</strong> untuk menambahkannya ke rincian
                            tagihan.
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
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">

                    <div class="modal-header bg-success text-white border-bottom-0 pb-3"
                        style="border-radius: 1rem 1rem 0 0;">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-check-circle-fill me-2"></i>Transaksi Berhasil
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4 bg-light" id="printArea">

                        <div class="text-center mb-4 pb-3 border-bottom border-dashed"
                            style="border-bottom: 2px dashed #dee2e6;">
                            <h4 class="fw-bold text-dark mb-0">TAMAN DAYU</h4>
                            <span class="badge bg-dark px-3 py-2 fs-6">{{ $invoice['invoice_number'] }}</span>
                        </div>

                        <div class="mb-4 small">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Tanggal</span>
                                <span class="fw-bold">{{ $invoice['waktu'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Customer</span>
                                <span class="fw-bold">{{ $invoice['customer_name'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Kasir</span>
                                <span class="fw-bold">{{ $invoice['kasir_name'] }}</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold text-muted border-bottom pb-2 mb-2">Rincian Item</h6>
                            @foreach ($invoice['items'] as $item)
                                <div class="d-flex justify-content-between align-items-start mb-2 small">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $item->item_name }}</div>
                                        <div class="text-muted">{{ $item->qty }} x Rp
                                            {{ number_format($item->price, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="fw-bold text-dark text-end">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="bg-white p-3 rounded-3 border shadow-sm mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted fw-bold">TOTAL TAGIHAN</span>
                                <span class="fw-bold fs-5 text-dark">Rp
                                    {{ number_format($invoice['total'], 0, ',', '.') }}</span>
                            </div>

                            <hr class="my-2" style="border-top: 1px solid #dee2e6;">

                            <div class="d-flex justify-content-between mb-1 small">
                                <span class="text-dark fw-bold"><i class="bi bi-wallet2 text-success me-1"></i>Dibayar
                                    (Uang)</span>
                                <span class="fw-bold text-success">Rp
                                    {{ number_format($invoice['tagihan_uang'], 0, ',', '.') }}</span>
                            </div>

                            @if ($invoice['tagihan_poin'] > 0)
                                <div class="d-flex justify-content-between mb-1 small">
                                    <span class="text-dark fw-bold"><i
                                            class="bi bi-star-fill text-primary me-1"></i>Dibayar (Poin)</span>
                                    <span
                                        class="fw-bold text-primary">{{ number_format($invoice['tagihan_poin'], 0, ',', '.') }}
                                        Pts</span>
                                </div>
                            @endif
                        </div>

                        @if (!empty($invoice['catatan']))
                            <div class="alert alert-warning p-2 mt-2 mb-0 small text-center border-0 rounded-3">
                                {{ $invoice['catatan'] }}
                            </div>
                        @endif

                    </div>

                    <div class="modal-footer border-top-0 d-flex justify-content-between bg-light"
                        style="border-radius: 0 0 1rem 1rem;">
                        <button type="button" class="btn btn-outline-secondary fw-bold"
                            data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary fw-bold" onclick="printInvoice()">
                            <i class="bi bi-printer me-1"></i> Cetak Struk
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Fungsi untuk Print Area Khusus Struk
            function printInvoice() {
                var printContents = document.getElementById('printArea').innerHTML;
                var originalContents = document.body.innerHTML;
                document.body.innerHTML = printContents;
                window.print();
                document.body.innerHTML = originalContents;
                location.reload(); // Refresh halaman setelah nge-print agar script JS kembali normal
            }

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

        // --- LOGIKA FORM ITEM ---
        document.getElementById('manualItemForm').addEventListener('submit', function(e) {
            e.preventDefault();

            let select = document.getElementById('selectItem');
            let qtyInput = document.getElementById('inputItemQty');

            if (select.value === '') return;

            // Ambil data dari dropdown option yang sedang dipilih
            let selectedOption = select.options[select.selectedIndex];
            let id = parseInt(select.value);
            let name = selectedOption.getAttribute('data-name');
            let price = parseInt(selectedOption.getAttribute('data-price'));
            let qty = parseInt(qtyInput.value);

            // Cek apakah item sudah ada di keranjang
            let existingItemIndex = cart.findIndex(item => item.id === id);

            if (existingItemIndex !== -1) {
                // Jika sudah ada, cukup tambahkan Qty nya saja
                cart[existingItemIndex].qty += qty;
                cart[existingItemIndex].subtotal = cart[existingItemIndex].qty * cart[existingItemIndex].price;
            } else {
                // Jika belum ada, masukkan sebagai item baru
                cart.push({
                    id: id,
                    name: name,
                    price: price,
                    qty: qty,
                    subtotal: price * qty
                });
            }

            renderCart();

            // Reset Input
            select.value = '';
            qtyInput.value = '1';
            select.focus();
        });

        // --- FUNGSI TAMBAH/KURANG QTY DI KERANJANG ---
        function increaseQty(id) {
            let item = cart.find(i => i.id === id);
            if (item) {
                item.qty += 1;
                item.subtotal = item.qty * item.price;
                renderCart();
            }
        }

        function decreaseQty(id) {
            let item = cart.find(i => i.id === id);
            if (item) {
                if (item.qty > 1) {
                    item.qty -= 1;
                    item.subtotal = item.qty * item.price;
                    renderCart();
                } else {
                    // Jika qty = 1 dan dikurangi lagi, hapus item dari keranjang
                    removeItem(id);
                }
            }
        }

        // --- FUNGSI HAPUS ITEM ---
        function removeItem(id) {
            cart = cart.filter(item => item.id !== id);
            renderCart();
        }

        // --- LOGIKA RENDER KERANJANG ---
        function renderCart() {
            const cartList = document.getElementById('cartItemsList');
            const emptyMsg = document.getElementById('emptyCartMsg');
            const btnPay = document.getElementById('btnPay');

            grandTotal = 0;
            cartList.innerHTML = '';

            if (cart.length === 0) {
                emptyMsg.style.display = 'block';
                btnPay.disabled = true;
            } else {
                emptyMsg.style.display = 'none';
                btnPay.disabled = false;

                cart.forEach(item => {
                    grandTotal += item.subtotal;

                    // HTML Rincian Item Diperbarui (Tanpa tombol + / -)
                    cartList.innerHTML += `
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-2">
                        <div>
                            <div class="fw-bold text-dark mb-1">
                                ${item.name}
                                <span class="badge bg-secondary ms-2">${item.qty} x</span>
                            </div>
                            <div class="text-muted small">@ Rp ${item.price.toLocaleString('id-ID')}</div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <div class="fw-bold text-end" style="width: 100px;">
                                Rp ${item.subtotal.toLocaleString('id-ID')}
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeItem(${item.id})">
                                <i class="bi bi-trash fs-5"></i>
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
