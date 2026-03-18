@extends('layouts.appC')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 75vh;">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5">

                <div class="text-center mb-4">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-key text-success fs-1"></i>
                    </div>
                    <h3 class="fw-bold text-success">Lupa Password?</h3>
                    <p class="text-muted small">Masukkan email yang terdaftar pada akun Anda. Kami akan mengirimkan instruksi untuk mengatur ulang password.</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('customer.password.email') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">Alamat Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                            class="form-control form-control-lg @error('email') is-invalid @enderror"
                            placeholder="example@gmail.com" />

                        @error('email')
                            <div class="invalid-feedback fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold mb-3">
                        KIRIM LINK RESET
                    </button>

                    <div class="text-center">
                        <a href="{{ route('customer.login') }}" class="text-muted text-decoration-none small">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Login
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
