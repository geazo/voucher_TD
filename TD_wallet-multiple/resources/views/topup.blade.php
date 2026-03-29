@extends('layouts.app')

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
                            <label for="phone_filter" class="form-label fw-semibold">Nomor Whatsapp</label>
                            <input type="text" id="phone_filter" placeholder="081..." required class="form-control" />
                        </div>

                        <div class="mb-3">
                            <label for="customer_id" class="form-label fw-semibold">Pilih Customer</label>
                            <select name="customer_id" id="customer_id" required class="form-select">
                                <option value="" selected>-- Pilih Customer --</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" data-phone="{{ $customer->notelp }}"
                                        data-email="{{ $customer->email }}">
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

    <script>
        // Logika Live Filter Dropdown Customer (Sama seperti aslinya)
        const phoneFilter = document.getElementById('phone_filter');
        const customerSelect = document.getElementById('customer_id');
        const allOptions = Array.from(customerSelect.options);

        phoneFilter.addEventListener('input', function() {
            let searchTerm = this.value.replace(/\D/g, '').replace(/^0+/, '');
            const defaultOption = allOptions[0];
            customerSelect.innerHTML = '';

            const filteredOptions = allOptions.filter(option => {
                let phone = option.getAttribute('data-phone');
                if (!phone) return false;
                let cleanPhone = phone.replace(/\D/g, '');
                return cleanPhone.includes(searchTerm);
            });

            if (filteredOptions.length === 0 && searchTerm !== "") {
                defaultOption.text = "Customer tidak ditemukan";
                defaultOption.disabled = true;
            } else {
                defaultOption.text = "-- Pilih Customer --";
                defaultOption.disabled = false;
            }

            customerSelect.appendChild(defaultOption);

            filteredOptions.forEach(option => {
                if (option.value !== "") {
                    customerSelect.appendChild(option);
                }
            });
        });

        customerSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value !== "") {
                phoneFilter.value = selectedOption.getAttribute('data-phone');
            }
        });

        // ==============================================
        // Modal konfirmasi sebelum submit form
        // ==============================================
        document.addEventListener('DOMContentLoaded', function() {
            const topupForm = document.getElementById('topupForm');
            const btnTriggerModal = document.getElementById('btnTriggerModal');
            const btnSubmitForm = document.getElementById('btnSubmitForm');

            // modal bootstrap 5
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmTopupModal'));
            btnTriggerModal.addEventListener('click', function() {
                // 1. Validasi Form Bawaan HTML (Cek apakah select sudah dipilih)
                if (!topupForm.checkValidity()) {
                    topupForm.reportValidity(); // Memunculkan tooltip "Please fill out this field"
                    return;
                }
                // 2. Ambil Data dari Inputan
                const selectedCustOption = customerSelect.options[customerSelect.selectedIndex];
                const selectedPkgOption = document.getElementById('membership_id').options[document
                    .getElementById('membership_id').selectedIndex];
                const rawCustText = selectedCustOption.text;
                const custName = rawCustText.split('(')[0].trim();
                const custPhone = selectedCustOption.getAttribute('data-phone');
                const custEmail = selectedCustOption.getAttribute('data-email');
                // 3. Masukkan Data ke dalam Modal
                document.getElementById('modalCustName').textContent = custName;
                document.getElementById('modalCustPhone').textContent = custPhone || '-';
                document.getElementById('modalCustEmail').textContent = custEmail ? custEmail : 'Tidak ada email';
                document.getElementById('modalPkgName').textContent = selectedPkgOption.text;

                // 4. Tampilkan Modal
                confirmModal.show();
            });

            // 5. Logika Tombol Submit di Modal
            btnSubmitForm.addEventListener('click', function() {
                this.disabled = true;
                this.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
                topupForm.submit();
            });
        });
    </script>
@endsection
