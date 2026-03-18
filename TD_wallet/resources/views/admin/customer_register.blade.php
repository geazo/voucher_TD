@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="fw-bold text-success mb-4">Registrasi Customer Baru</h4>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Pendaftaran Gagal:</strong>
                            <ul class="mb-0 mt-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('customers.store') }}" method="POST" id="formRegister">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" placeholder="Nama Customer" required>
                        </div>


                        <div class="mb-3">
                            <label class="form-label fw-bold">Nomor Whatsapp</label>
                            <input type="tel" id="phone" class="form-control" required>

                            <input type="hidden" name="notelp" id="full_phone">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Email (Untuk Login)</label>
                            <input type="email" name="email" class="form-control" placeholder="customer@email.com">
                        </div>

                        <div class="alert alert-light border small text-muted">
                            * Instruksikan customer untuk mengganti password setelah pertama kali login password pertama
                            adalah <strong>password</strong>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold">SUBMIT</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
        const input = document.querySelector("#phone");
        const fullPhoneInput = document.querySelector("#full_phone");
        const form = document.querySelector("#formRegister");

        // Inisialisasi Library
        const iti = window.intlTelInput(input, {
            initialCountry: "id", // Default Indonesia
            separateDialCode: true, // Memisahkan kode negara (+62) dengan input angka
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@21.0.8/build/js/utils.js",
        });

        // Setiap kali user ngetik, update nilai hidden input
        const handleChange = () => {
            // iti.getNumber() akan mengembalikan format lengkap, misal: +628123456789
            fullPhoneInput.value = iti.getNumber();
        };

        form.addEventListener('submit', function() {
            fullPhoneInput.value = iti.getNumber();
        });

        input.addEventListener('change', handleChange);
        input.addEventListener('keyup', handleChange);
    </script>
    <style>
        /* Agar input menyesuaikan dengan lebar Bootstrap */
        .iti {
            width: 100%;
        }
    </style>
@endsection
