@extends('layouts.appC')

@section('content')
    <div class="container py-4">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">Halo, {{ $customer->nama }} 👋</h4>
                <p class="text-muted small mb-0">Selamat datang kembali di portal {{ config('app.name') }}</p>
            </div>
        </div>

        <div class="row g-2 mb-4">
            <div class="col-6">
                <div class="card bg-success text-white h-100 p-2 p-sm-3 border-0 shadow-sm" style="border-radius: 1rem;">
                    <p class="mb-1 text-white-50" style="font-size: 0.8rem;">Saldo Uang</p>

                    <div class="fw-bold text-nowrap" style="font-size: clamp(1rem, 4.5vw, 1.5rem);">
                        <span style="font-size: 0.8em;">Rp</span> {{ number_format($saldoUang, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="card bg-primary text-white h-100 p-2 p-sm-3 border-0 shadow-sm" style="border-radius: 1rem;">
                    <p class="mb-1 text-white-50" style="font-size: 0.8rem;">Saldo Poin</p>

                    <div class="fw-bold text-nowrap" style="font-size: clamp(1rem, 4.5vw, 1.5rem);">
                        {{ number_format($saldoPoin, 0, ',', '.') }} <span style="font-size: 0.8em;">Pts</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <a href="{{ route('customer.payment.auth') }}"
                class="btn btn-success w-100 py-3 shadow-sm rounded-4 d-flex justify-content-center align-items-center gap-2"
                style="background: linear-gradient(135deg, #198754, #146c43);">
                <i class="bi bi-qr-code-scan fs-4 text-white"></i>
                <span class="fs-5 fw-bold text-white">BAYAR SEKARANG</span>
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0">Riwayat Transaksi Terakhir</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($transactions as $t)
                        <a href="{{ route('customer.transaction.detail', $t->id) }}"
                            class="list-group-item list-group-item-action py-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0 fw-bold text-dark">
                                        {{ $t->type == 'kredit' ? 'Topup Saldo' : 'Pembayaran' }}</p>
                                    <small
                                        class="text-muted">{{ \Carbon\Carbon::parse($t->created_at)->format('d M Y, H:i') }}</small>
                                </div>
                                <div class="text-end d-flex align-items-center gap-3">
                                    <div>
                                        <p class="mb-0 fw-bold {{ $t->type == 'kredit' ? 'text-success' : 'text-danger' }}">
                                            {{ $t->type == 'kredit' ? '+' : '-' }}
                                            Rp {{ number_format($t->total_nominal, 0, ',', '.') }}
                                        </p>
                                        <span
                                            class="badge bg-light text-dark small border">{{ strtoupper($t->type) }}</span>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-4 text-center text-muted">Belum ada transaksi.</div>
                    @endforelse
                </div>
            </div>
            <div class="card-footer bg-white border-0">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
@endsection
