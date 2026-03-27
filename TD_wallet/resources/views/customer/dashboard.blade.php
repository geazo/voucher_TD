@extends('layouts.appC')

@section('content')
    <div class="container py-4">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">Halo, {{ $customer->nama }} 👋</h4>
                <p class="text-muted small mb-0">Selamat datang kembali di portal {{ config('app.name') }}</p>
            </div>
        </div>

        @php
            $membership = $customer->membership;

            // Logika Fallback jika Membership NULL
            $tierName   = $membership ? $membership->name : 'Customer';
            $textColor  = $membership ? $membership->text_color : 'text-dark';

            // Style Card: Gradient jika member, Putih Border jika 'Customer' biasa
            $cardStyle  = $membership
                ? "background: linear-gradient(135deg, {$membership->color_start} 0%, {$membership->color_end} 100%);"
                : "background-color: #ffffff; border: 1px solid #dee2e6 !important;";

            // Warna Badge Tier di dalam kartu
            $badgeStyle = $membership
                ? "background-color: rgba(255,255,255,0.2); color: inherit;"
                : "background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6;";
        @endphp

        <a href="{{ route('customer.saldo.info') }}" class="text-decoration-none">
            <div class="card {{ $textColor }} mb-4 border-0 shadow-sm"
                style="border-radius: 1.25rem; {{ $cardStyle }} position: relative; overflow: hidden;">

                @if($membership)
                <div style="position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
                @endif

                <div class="card-body p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <p class="mb-1 opacity-75" style="font-size: 0.85rem;">Member ID</p>
                            <h5 class="fw-bold mb-0" style="letter-spacing: 2px; font-family: monospace;">
                                {{ $customer->wallets->first()->no_rekening ?? 'N/A' }}
                            </h5>
                        </div>
                        <div class="text-end">
                            <span class="badge shadow-sm mb-2" style="{{ $badgeStyle }} letter-spacing: 1px; padding: 0.5em 1em;">
                                {{ strtoupper($tierName) }}
                            </span><br>
                            <i class="bi bi-chevron-right opacity-50 fs-5"></i>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 border-end border-dark border-opacity-10">
                            <p class="mb-1 opacity-75" style="font-size: 0.8rem;">Saldo Uang</p>
                            <div class="fw-bold text-nowrap" style="font-size: clamp(1.1rem, 5vw, 1.6rem);">
                                <span style="font-size: 0.7em;">Rp</span> {{ number_format($saldoUang, 0, ',', '.') }}
                            </div>
                        </div>

                        <div class="col-6 ps-3">
                            <p class="mb-1 opacity-75" style="font-size: 0.8rem;">Saldo Poin</p>
                            <div class="fw-bold text-nowrap" style="font-size: clamp(1.1rem, 5vw, 1.6rem);">
                                {{ number_format($saldoPoin, 0, ',', '.') }} <span style="font-size: 0.7em;" class="opacity-75">Pts</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>

        <div class="mb-4">
            <a href="{{ route('customer.payment.auth') }}"
                class="btn btn-success w-100 py-3 shadow-sm rounded-4 d-flex justify-content-center align-items-center gap-2"
                style="background: linear-gradient(135deg, #198754, #146c43); border: none;">
                <i class="bi bi-qr-code-scan fs-4"></i>
                <span class="fs-5 fw-bold">BAYAR SEKARANG</span>
            </a>
        </div>

        <div class="card border-0 shadow-sm mb-5">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0 text-dark">Riwayat Transaksi Terakhir</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($transactions as $t)
                        @php
                            if ($t->type === 'kredit') {
                                $judul = 'Topup Saldo';
                                $icon = 'bi-wallet2 text-success';
                                $tanda = '+';
                                $warna = 'text-success';
                            } elseif ($t->type === 'adjustment') {
                                $judul = 'Penyesuaian / Expired';
                                $icon = 'bi-calendar-x text-warning';
                                $tanda = '-';
                                $warna = 'text-warning';
                            } else {
                                $judul = 'Pembayaran';
                                $icon = 'bi-cart-dash text-danger';
                                $tanda = '-';
                                $warna = 'text-danger';
                            }
                        @endphp

                        <a href="{{ route('customer.transaction.detail', $t->id) }}"
                            class="list-group-item list-group-item-action py-3 border-bottom border-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-light rounded-circle d-flex justify-content-center align-items-center"
                                        style="width: 42px; height: 42px;">
                                        <i class="bi {{ $icon }} fs-5"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-bold text-dark" style="font-size: 0.95rem;">{{ $judul }}</p>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($t->created_at)->format('d M Y, H:i') }}</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="mb-0 fw-bold {{ $warna }}">
                                        {{ $tanda }} {{ number_format($t->total_nominal, 0, ',', '.') }}
                                    </p>
                                    <span class="badge bg-light text-muted fw-normal border" style="font-size: 0.65rem;">
                                        {{ strtoupper($t->type) }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-5 text-center text-muted">
                            <i class="bi bi-receipt fs-1 d-block mb-2 opacity-25"></i>
                            Belum ada riwayat transaksi.
                        </div>
                    @endforelse
                </div>
            </div>
            @if($transactions->hasPages())
            <div class="card-footer bg-white border-0 pt-2 pb-3">
                {{ $transactions->links() }}
            </div>
            @endif
        </div>
    </div>
@endsection
