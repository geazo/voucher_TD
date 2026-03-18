@extends('layouts.appF')

@section('content')
    <form method="POST" action="/topup
    " class="max-w-xl p-6 mx-auto mt-10 bg-white rounded-lg shadow">
        @csrf

        <h2 class="mb-6 text-2xl font-bold text-center text-green-600">Topup Form</h2>

        <div class="mb-4">
            <label for="phone" class="block mb-1 font-medium text-gray-700">Nomor Whatsapp</label>
            <input type="text" id="phone_filter" placeholder="081234567890" required
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" />
        </div>

        <div class="mb-4">
            <label for="customer_id" class="block mb-1 font-medium text-gray-700">List Customer</label>
            <select name="customer_id" id="customer_id" required
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="" selected>-- Pilih Customer --</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" data-phone="{{ $customer->notelp }}">
                        {{ $customer->name }} ({{ $customer->notelp }})
                    </option>
                @endforeach
            </select>
        </div>

        <hr class="mb-6">

        <div class="mb-4">
            <label for="amount" class="block mb-1 font-medium text-gray-700">Jumlah Topup (Rp)</label>
            <input type="text" name="amount" id="amount" placeholder="0" required
                class="w-full px-4 py-2 text-xl font-bold border rounded-lg focus:ring-2 focus:ring-green-500" />
        </div>

        <div class="mb-6">
            <label for="pin" class="block mb-1 font-medium text-gray-700">PIN Sekuritas Kasir</label>
            <input type="password" name="pin" id="pin" 
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" />
        </div>

        <button type="submit"
            class="w-full py-3 text-white transition bg-green-600 rounded-lg font-bold hover:bg-green-700">
            PROSES TOPUP
        </button>
    </form>

    <script>
        const amountInput = document.getElementById('amount');

        amountInput.addEventListener('input', function(e) {
            // 1. Hapus semua karakter yang BUKAN angka
            let value = this.value.replace(/[^0-9]/g, '');

            // 2. Jika kosong, biarkan kosong
            if (value === "") {
                this.value = "";
                return;
            }

            // 3. Format angka menjadi ribuan dengan titik (Locale Indonesia)
            this.value = new Intl.NumberFormat('id-ID').format(value);
        });

        const phoneFilter = document.getElementById('phone_filter');
        const customerSelect = document.getElementById('customer_id');
        const allOptions = Array.from(customerSelect.options);

        phoneFilter.addEventListener('input', function() {
            const searchTerm = this.value;
            const defaultOption = allOptions[0]; // Opsi "-- Pilih Customer --"

            // 1. Bersihkan dropdown
            customerSelect.innerHTML = '';

            // 2. Filter opsi berdasarkan nomor telepon
            const filteredOptions = allOptions.filter(option => {
                const phone = option.getAttribute('data-phone');
                return phone && phone.includes(searchTerm);
            });

            // 3. Jika tidak ada hasil tampilkan pesan "Customer tidak ditemukan"
            if (filteredOptions.length === 0 && searchTerm !== "") {
                defaultOption.text = "Customer tidak ditemukan";
                defaultOption.disabled = true;
            } else {
                defaultOption.text = "-- Pilih Customer --";
                defaultOption.disabled = false;
            }

            // 4. Masukkan kembali opsi default
            customerSelect.appendChild(defaultOption);

            // 5. Masukkan hasil filter (kecuali opsi kosong itu sendiri)
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
    </script>
@endsection
