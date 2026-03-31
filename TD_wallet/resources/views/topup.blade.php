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

                    <form method="POST" action="{{ route('topup.store') }}" id="topupForm">
                        @csrf

                        <div class="mb-3">
                            <label for="customer_id" class="form-label fw-semibold">Pilih Customer</label>
                            <select name="customer_id" id="customer_id" required placeholder="Search Customer...">
                                <option value="" selected></option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" data-nama="{{ $customer->nama }}"
                                        data-notelp="{{ $customer->notelp }}"
                                        data-email="{{ $customer->email ?? 'Tidak ada email' }}">
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

                        <button type="button" id="btnTriggerModal" class="btn btn-success w-100 py-2 fw-bold">
                            PROSES TOPUP
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-shield-check me-2"></i>Konfirmasi Topup</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border border-success border-opacity-25 mb-0">
                        <p class="text-center text-muted small mb-3">Pastikan data pelanggan dan paket sudah benar sebelum
                            memproses.</p>
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="text-muted" width="35%">Nama Customer</td>
                                <td width="5%">:</td>
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
                                <td class="text-muted">Paket Dipilih</td>
                                <td>:</td>
                                <td class="fw-bold text-success" id="modalPkgName">-</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success fw-bold px-4" id="btnConfirmSubmit">
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

            // 2. Deklarasi Variabel
            const topupForm = document.getElementById('topupForm');
            const btnTriggerModal = document.getElementById('btnTriggerModal');
            const btnConfirmSubmit = document.getElementById('btnConfirmSubmit');
            const customerSelect = document.getElementById('customer_id');
            const packageSelect = document.getElementById('membership_id');

            const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));

            // 3. Event Listener Tombol Proses
            btnTriggerModal.addEventListener('click', function() {

                if (customerSelect.value === "" || packageSelect.value === "") {

                    topupForm.reportValidity();
                    return;
                }


                const selectedCustOption = customerSelect.options[customerSelect.selectedIndex];
                const selectedPkgOption = packageSelect.options[packageSelect.selectedIndex];

                document.getElementById('modalCustName').textContent = selectedCustOption.getAttribute('data-nama');
                document.getElementById('modalCustPhone').textContent = selectedCustOption.getAttribute('data-notelp');
                document.getElementById('modalCustEmail').textContent = selectedCustOption.getAttribute('data-email');
                document.getElementById('modalPkgName').textContent = selectedPkgOption.text;

                confirmModal.show();
            });

            // 4. Event Listener Tombol Konfirmasi di Modal
            btnConfirmSubmit.addEventListener('click', function() {
                // Ubah teks tombol jadi loading agar kasir tidak klik 2x
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
                this.disabled = true;

                // Submit form ke Laravel
                topupForm.submit();
            });

        });
    </script>
@endsection
