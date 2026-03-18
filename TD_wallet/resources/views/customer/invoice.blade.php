@extends('layouts.appC') @section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <div class="card border-0 shadow-lg" style="border-radius: 1rem; overflow: hidden;">
                <div class="bg-success text-white text-center py-4">
                    <i class="bi bi-check-circle-fill mb-2" style="font-size: 3rem;"></i>
                    <h4 class="fw-bold mb-0">{{ $invoiceData['jenis'] ?? 'Transaksi Berhasil' }}</h4>
                    <p class="text-white-50 small mb-0">{{ $invoiceData['waktu'] }}</p>
                </div>

                <div class="card-body p-4 bg-white">
                    <div class="text-center mb-4">
                        <p class="text-muted mb-1">Total Transaksi</p>
                        <h2 class="fw-bold text-dark">Rp {{ number_format($invoiceData['total'], 0, ',', '.') }}</h2>
                    </div>

                    <hr class="border-dashed mb-4" style="border-top: 2px dashed #e0e0e0;">

                    <h6 class="fw-bold text-muted mb-3">Rincian Pembayaran</h6>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-dark"><i class="bi bi-wallet2 text-success me-2"></i>Saldo Uang</span>
                        <span class="fw-bold">Rp {{ number_format($invoiceData['tagihan_uang'], 0, ',', '.') }}</span>
                    </div>

                    @if($invoiceData['tagihan_poin'] > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-dark"><i class="bi bi-star text-warning me-2"></i>Potongan Poin</span>
                        <span class="fw-bold text-primary">{{ number_format($invoiceData['tagihan_poin'], 0, ',', '.') }} Pts</span>
                    </div>
                    @endif

                    <hr class="border-dashed my-4" style="border-top: 2px dashed #e0e0e0;">

                    <a href="{{ route('customer.dashboard') }}" class="btn btn-light border w-100 py-3 fw-bold text-dark rounded-pill shadow-sm hover-bg-light">
                        Kembali ke Beranda
                    </a>
                </div>
            </div>

            <div class="text-center mt-3">
                <small class="text-muted">Terima kasih telah bermain di Taman Dayu Golf Club & Resort.</small>
            </div>

        </div>
    </div>
</div>
@endsection
