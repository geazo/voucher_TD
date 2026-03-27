@extends('layouts.appC')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5 text-center">
                <h5 class="fw-bold mb-4">Keamanan Transaksi</h5>

                <i class="bi bi-shield-lock text-success mb-3 d-block" style="font-size: 4rem;"></i>
                <p class="text-muted mb-4">Masukkan password Anda untuk menampilkan QR Code pembayaran.</p>

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('customer.payment.verify') }}" method="POST">
                    @csrf
                    <input type="hidden" name="membership_id" value="{{ request('membership_id') }}">

                    <div class="mb-3">
                        <label class="form-label">Masukkan Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100"> TAMPILKAN QR </button>
                </form>

                <a href="{{ route('customer.dashboard') }}"
                    class="btn btn-link text-muted mt-3 text-decoration-none">Batal</a>
            </div>
        </div>
    </div>
@endsection
