@extends('layouts.appC')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 75vh;">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5">

                <div class="text-center mb-4">
                    <h3 class="fw-bold text-success">Buat Password Baru</h3>
                    <p class="text-muted small">Silakan masukkan password baru Anda.</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('customer.password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alamat Email</label>
                        <input type="email" name="email" value="{{ $email ?? old('email') }}" required readonly
                            class="form-control form-control-lg bg-light" />
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Baru</label>
                        <input type="password" name="password" required autofocus
                            class="form-control form-control-lg" placeholder="Minimal 6 karakter" />
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" required
                            class="form-control form-control-lg" placeholder="Ulangi password baru" />
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold">
                        SIMPAN PASSWORD BARU
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
