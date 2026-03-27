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
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('customers.store') }}" method="POST" id="formRegister">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" placeholder="Nama Customer" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Kota Domisili</label>
                            <input type="text" name="kota_domisili" class="form-control" placeholder="Surabaya" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Gender</label>
                            <select name="gender" class="form-select" required>
                                <option value="">-- Pilih Gender --</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nomor Whatsapp</label>
                            <input type="tel" id="phone" class="form-control" required>
                            <input type="hidden" name="notelp" id="full_phone">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Email (Untuk Login)</label>
                            <input type="email" name="email" class="form-control" placeholder="customer@email.com"
                                required>
                        </div>

                        <div class="alert alert-light border small text-muted">
                            * Instruksikan customer untuk mengganti password setelah pertama kali login. Password pertama
                            adalah <strong>password</strong>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold">SUBMIT</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <script>
        // 1. Inisialisasi Tel Input
        const input = document.querySelector("#phone");
        const fullPhoneInput = document.querySelector("#full_phone");
        const form = document.querySelector("#formRegister");

        const iti = window.intlTelInput(input, {
            initialCountry: "id",
            separateDialCode: true,
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@21.0.8/build/js/utils.js",
        });

        const handleChange = () => {
            fullPhoneInput.value = iti.getNumber();
        };

        form.addEventListener('submit', function() {
            fullPhoneInput.value = iti.getNumber();
        });

        input.addEventListener('change', handleChange);
        input.addEventListener('keyup', handleChange);
    </script>

    <style>
        .iti {
            width: 100%;
        }

        /* Sedikit penyesuaian untuk Select2 agar tingginya pas dengan form-control bootstrap */
        .select2-container .select2-selection--single {
            height: 38px;
        }

        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.375rem;
            border: 1px solid #dee2e6;
        }
    </style>
@endsection
