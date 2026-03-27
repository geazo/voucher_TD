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

                        <form method="POST" action="{{ route('topup.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="phone_filter" class="form-label fw-semibold">Nomor Whatsapp</label>
                                <input type="text" id="phone_filter" placeholder="081..." required
                                    class="form-control" />
                            </div>

                            <div class="mb-3">
                                <label for="customer_id" class="form-label fw-semibold">Pilih Customer</label>
                                <select name="customer_id" id="customer_id" required class="form-select">
                                    <option value="" selected>-- Pilih Customer --</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" data-phone="{{ $customer->notelp }}">
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

                            {{-- <div class="mb-4">
                                <label for="pin" class="form-label fw-semibold">PIN Sekuritas Kasir</label>
                                <input type="password" name="pin" id="pin" class="form-control" />
                            </div> --}}

                            <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                                PROSES TOPUP
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Logika Live Filter Dropdown Customer
            const phoneFilter = document.getElementById('phone_filter');
            const customerSelect = document.getElementById('customer_id');
            const allOptions = Array.from(customerSelect.options);
            phoneFilter.addEventListener('input', function() {
                // 1. BERSIHKAN INPUT KASIR:
                let searchTerm = this.value.replace(/\D/g, '').replace(/^0+/, '');
                const defaultOption = allOptions[0];
                customerSelect.innerHTML = '';

                const filteredOptions = allOptions.filter(option => {
                    let phone = option.getAttribute('data-phone');
                    if (!phone) return false;
                    // 2. BERSIHKAN NOMOR DATABASE:
                    let cleanPhone = phone.replace(/\D/g, '');
                    // 3. COCOKKAN INPUT KASIR DENGAN NOMOR DATABASE
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

            // Logika Isi Otomatis Nomor HP jika Dropdown dipilih manual
            customerSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value !== "") {
                    phoneFilter.value = selectedOption.getAttribute('data-phone');
                }
            });
        </script>
    @endsection
