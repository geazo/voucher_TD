@extends('layouts.app')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0 mt-3">
                <div class="card-body p-4">
                    <h2 class="mb-4 text-center text-success fw-bold">Form Topup</h2>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form id="topupForm" method="POST" action="{{ route('topup.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="customer_id" class="form-label fw-semibold">Pilih Customer</label>
                            <select name="customer_id" id="customer_id" required placeholder="Search customer..." class="form-select text-dark">
                                <option value="" selected></option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" data-nama="{{ $customer->nama }}"
                                        data-phone="{{ $customer->notelp }}" data-email="{{ $customer->email }}">
                                        {{ $customer->nama }} ({{ $customer->notelp }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <label for="membership_id" class="form-label fw-semibold">Pilih Paket Topup</label>
                            <select name="membership_id" id="membership_id" required class="form-select text-dark">
                                <option value="" class="text-dark">-- Pilih Paket Membership --</option>
                                @foreach ($packages as $pkg)
                                    <option value="{{ $pkg->id }}" class="text-dark">
                                        {{ $pkg->name }} - Rp {{ number_format($pkg->harga, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="button" id="btnTriggerModal" class="btn btn-success w-100 py-2 fw-bold mt-2">
                            PROSES TOPUP
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmTopupModal" tabindex="-1" aria-labelledby="confirmTopupModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold text-dark" id="confirmTopupModalLabel">
                        <i class="bi bi-shield-check text-success me-2"></i>Konfirmasi Topup
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4">
                    <p class="text-muted mb-3">Pastikan data pelanggan dan paket di bawah ini sudah benar sebelum memproses:
                    </p>

                    <div class="bg-light p-3 rounded-3 mb-2 border">
                        <table class="table table-borderless table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width: 35%;">Customer</td>
                                    <td style="width: 5%;">:</td>
                                    <td class="fw-bold text-dark" id="modalCustName">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">No. WhatsApp</td>
                                    <td>:</td>
                                    <td class="fw-bold text-dark" id="modalCustPhone">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email</td>
                                    <td>:</td>
                                    <td class="fw-bold text-dark" id="modalCustEmail">-</td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <hr class="my-1">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted align-middle">Paket Dipilih</td>
                                    <td class="align-middle">:</td>
                                    <td class="fw-bold text-success fs-5" id="modalPkgName">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4 mt-2">
                    <button type="button" class="btn btn-light border fw-bold w-100 mb-2"
                        data-bs-dismiss="modal">Kembali</button>
                    <button type="button" id="btnSubmitForm" class="btn btn-success fw-bold w-100 m-0 shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> PROSES TOPUP
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Inisialisasi Tom Select
            new TomSelect("#customer_id", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                maxOptions: 50
            });

            const topupForm = document.getElementById('topupForm');
            const btnTriggerModal = document.getElementById('btnTriggerModal');
            const btnSubmitForm = document.getElementById('btnSubmitForm');
            const customerSelect = document.getElementById('customer_id');
            const packageSelect = document.getElementById('membership_id');

            const confirmModal = new bootstrap.Modal(document.getElementById('confirmTopupModal'));

            // 2. Logika Modal Konfirmasi
            btnTriggerModal.addEventListener('click', function() {
                // Validasi manual: karena tag <select> asli disembunyikan oleh Tom Select,
                // kita cek isinya secara manual agar form tidak error "not focusable"
                if (customerSelect.value === "" || packageSelect.value === "") {
                    topupForm.reportValidity();
                    return;
                }

                // Ambil Data dari Inputan
                const selectedCustOption = customerSelect.options[customerSelect.selectedIndex];
                const selectedPkgOption = packageSelect.options[packageSelect.selectedIndex];

                // Ekstrak data dari atribut yang kita titipkan tadi
                const custName = selectedCustOption.getAttribute('data-nama');
                const custPhone = selectedCustOption.getAttribute('data-phone');
                const custEmail = selectedCustOption.getAttribute('data-email');

                // Masukkan Data ke dalam Modal
                document.getElementById('modalCustName').textContent = custName || '-';
                document.getElementById('modalCustPhone').textContent = custPhone || '-';
                document.getElementById('modalCustEmail').textContent = custEmail ? custEmail :
                    'Tidak ada email';
                document.getElementById('modalPkgName').textContent = selectedPkgOption.text;

                // Tampilkan Modal
                confirmModal.show();
            });

            // 3. Logika Tombol Submit di Modal
            btnSubmitForm.addEventListener('click', function() {
                this.disabled = true;
                this.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
                topupForm.submit();
            });
        });
    </script>
@endsection
