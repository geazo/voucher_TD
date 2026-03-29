@extends('layouts.appC')

@section('content')
    <div class="container py-2">
        <div class="row justify-content-center">
            <div class="col-md-5 text-center">
                <h5 class="fw-bold mb-4">Keamanan Transaksi</h5>

                <i class="bi bi-shield-lock text-success mb-3 d-block" style="font-size: 4rem;"></i>
                <p class="text-muted mb-4">Masukkan password Anda untuk menampilkan QR Code pembayaran.</p>
                @php
                    $tierName = $membership ? $membership->name : 'Reguler (Default)';
                    $bgColor = $membership
                        ? "background: linear-gradient(135deg, {$membership->color_start} 0%, {$membership->color_end} 100%);"
                        : 'background-color: #212529;'; // bg-dark untuk reguler
                    $textColor = $membership ? $membership->text_color : 'text-white';
                @endphp

                <div class="card border-0 shadow-sm mb-4 mx-auto" style="border-radius: 0.75rem; max-width: 250px;">
                    <div class="card-body py-2 px-3 text-center {{ $textColor }}"
                        style="{{ $bgColor }} border-radius: 0.75rem; position: relative; overflow: hidden;">

                        @if ($membership)
                            <div
                                style="position: absolute; top: -15px; right: -15px; width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 50%;">
                            </div>
                        @endif

                        <p class="mb-0 opacity-75" style="font-size: 0.7rem;">Member Card:</p>
                        <h6 class="fw-bold mb-1 mt-1" style="letter-spacing: 0.5px;">
                            <i class="bi bi-credit-card-2-front me-1"></i>{{ strtoupper($tierName) }}
                        </h6>
                    </div>
                </div>

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('customer.payment.verify') }}" method="POST">
                    @csrf
                    <input type="hidden" name="membership_id" value="{{ request('membership_id') }}">

                    <div class="mb-3">
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
