@extends('layouts.appC')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm border-top border-warning border-4">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-shield-lock-fill text-warning" style="font-size: 3rem;"></i>
                        <h4 class="fw-bold mt-2">Ganti Password Default</h4>
                        <p class="text-muted small">Demi keamanan akun Anda, silakan buat password baru sebelum mengakses dashboard.</p>
                    </div>

                    @if(session('warning'))
                        <div class="alert alert-warning small">{{ session('warning') }}</div>
                    @endif

                    <form action="{{ route('customer.force_password.update') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold">Password Baru</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-bold text-dark py-2">
                            SIMPAN PASSWORD & LANJUTKAN
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <form method="POST" action="{{ route('customer.logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-link text-danger text-decoration-none small">Batal dan Log Out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
