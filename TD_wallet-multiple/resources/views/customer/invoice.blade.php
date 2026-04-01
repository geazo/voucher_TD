@extends('layouts.appC')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm" style="border-radius: 1rem;">

                <div class="card-header bg-success text-white border-bottom-0 pb-3 pt-3" style="border-radius: 1rem 1rem 0 0;">
                    <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2"></i> Detail Transaksi</h5>
                </div>

                <div class="card-body p-4 bg-light">
                    <div class="text-center mb-4 pb-3 border-bottom border-dashed" style="border-bottom: 2px dashed #dee2e6;">
                        <h4 class="fw-bold text-dark mb-0">TAMAN DAYU</h4>

                        <span class="badge bg-dark px-3 py-2 fs-6">{{ $invoiceData['invoice_number'] }}</span>
                    </div>

                    <div class="mb-4 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Tanggal</span>
                            <span class="fw-bold">{{ $invoiceData['waktu'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Petugas/Kasir</span>
                            <span class="fw-bold">{{ $invoiceData['kasir_name'] }}</span>
                        </div>
                    </div>

                    @if(isset($invoiceData['items']) && count($invoiceData['items']) > 0)
                        <div class="mb-4">
                            <h6 class="fw-bold text-muted border-bottom pb-2 mb-2">Rincian Item</h6>
                            @foreach($invoiceData['items'] as $item)
                                <div class="d-flex justify-content-between align-items-start mb-2 small">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $item['item_name'] ?? $item->item_name }}</div>
                                        <div class="text-muted">{{ $item['qty'] ?? $item->qty }} x Rp {{ number_format($item['price'] ?? $item->price, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="fw-bold text-dark text-end">
                                        Rp {{ number_format($item['subtotal'] ?? $item->subtotal, 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="bg-white p-3 rounded-3 border shadow-sm mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted fw-bold">TOTAL NOMINAL</span>
                            <span class="fw-bold fs-5 text-dark">Rp {{ number_format($invoiceData['total'], 0, ',', '.') }}</span>
                        </div>

                        <hr class="my-2" style="border-top: 1px solid #dee2e6;">

                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-dark fw-bold"><i class="bi bi-wallet2 text-success me-1"></i>Dibayar (Uang)</span>
                            <span class="fw-bold text-success">Rp {{ number_format($invoiceData['tagihan_uang'], 0, ',', '.') }}</span>
                        </div>

                        @if (isset($invoiceData['tagihan_poin']) && $invoiceData['tagihan_poin'] > 0)
                            <div class="d-flex justify-content-between mb-1 small">
                                <span class="text-dark fw-bold"><i class="bi bi-star-fill text-primary me-1"></i>Dibayar (Poin)</span>
                                <span class="fw-bold text-primary">{{ number_format($invoiceData['tagihan_poin'], 0, ',', '.') }} Pts</span>
                            </div>
                        @endif

                        @if (isset($invoiceData['tagihan_tunai']) && $invoiceData['tagihan_tunai'] > 0)
                            <div class="d-flex justify-content-between mb-1 small">
                                <span class="text-dark fw-bold"><i class="bi bi-cash-coin text-warning me-1"></i>Tagihan (Tunai)</span>
                                <span class="fw-bold text-warning">Rp {{ number_format($invoiceData['tagihan_tunai'], 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>

                    @if (!empty($invoiceData['catatan']))
                        <div class="alert alert-secondary p-2 mt-2 mb-0 small text-center border-0 rounded-3">
                            {{ $invoiceData['catatan'] }}
                        </div>
                    @endif

                </div>

                <div class="card-footer bg-white border-top-0 d-flex justify-content-center p-3" style="border-radius: 0 0 1rem 1rem;">
                    <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-secondary fw-bold w-100">
                        <i class="bi bi-arrow-left me-2"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
