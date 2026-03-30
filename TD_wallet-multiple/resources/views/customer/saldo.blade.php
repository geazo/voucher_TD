@extends('layouts.appC')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('customer.dashboard') }}" class="btn btn-light border-0 shadow-sm rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
            <i class="bi bi-arrow-left fs-5"></i>
        </a>
        <h5 class="fw-bold mb-0">Informasi Saldo & Masa Aktif</h5>
    </div>

    @php
        $tierName   = $membership ? $membership->name : 'Customer';
        $textColor  = $membership ? $membership->text_color : 'text-dark';

        $cardStyle  = $membership
            ? "background: linear-gradient(135deg, {$membership->color_start} 0%, {$membership->color_end} 100%);"
            : "background-color: #ffffff; border: 1px solid #dee2e6 !important;";

        $badgeStyle = $membership
            ? "background-color: rgba(255,255,255,0.2); color: inherit;"
            : "background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6;";
    @endphp

    <div class="card {{ $textColor }} mb-4 border-0 shadow-sm" style="border-radius: 1.25rem; {{ $cardStyle }} position: relative; overflow: hidden;">
        @if($membership)
        <div style="position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
        @endif

        <div class="card-body p-4 position-relative">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <p class="mb-1 opacity-75" style="font-size: 0.85rem;">Member ID</p>
                    <h5 class="fw-bold mb-0" style="letter-spacing: 2px; font-family: monospace;">
                        {{ $dompetUang->no_rekening ?? 'N/A' }}
                    </h5>
                </div>
                <div class="text-end">
                    <span class="badge shadow-sm" style="{{ $badgeStyle }} letter-spacing: 1px; padding: 0.5em 1em;">
                        {{ strtoupper($tierName) }}
                    </span>
                </div>
            </div>

            <div class="row">
                <div class="col-6 border-end border-dark border-opacity-10">
                    <p class="mb-1 opacity-75" style="font-size: 0.8rem;">Total Saldo Uang</p>
                    <div class="fw-bold text-nowrap" style="font-size: clamp(1rem, 4.5vw, 1.5rem);">
                        <span style="font-size: 0.8em;">Rp</span> {{ number_format($dompetUang->balance ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="col-6 ps-3">
                    <p class="mb-1 opacity-75" style="font-size: 0.8rem;">Total Saldo Poin</p>
                    <div class="fw-bold text-nowrap" style="font-size: clamp(1rem, 4.5vw, 1.5rem);">
                        {{ number_format($dompetPoin->balance ?? 0, 0, ',', '.') }} <span style="font-size: 0.8em;" class="opacity-75">Pts</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h6 class="fw-bold mb-3 px-1 text-muted" style="font-size: 0.9rem;">Rincian Saldo Aktif Saat Ini</h6>

    <div class="list-group shadow-sm border-0 mb-5" style="border-radius: 1rem; overflow: hidden;">
        @forelse($groupedTopups as $time => $transactions)
            @php
                $expiredDate = \Carbon\Carbon::parse($transactions->first()->expired_at);
                $isWarning = $expiredDate->diffInDays(now()) <= 30 && $expiredDate->isFuture();
                $isExpired = $expiredDate->isPast();

                $sisaUang = 0;
                $sisaPoin = 0;
                foreach($transactions as $tx) {
                    if($tx->wallet->type == 'Uang') $sisaUang += $tx->sisa_saldo;
                    if($tx->wallet->type == 'Poin') $sisaPoin += $tx->sisa_saldo;
                }
            @endphp

            <div class="list-group-item list-group-item-action p-3 p-md-4 border-0 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">
                            <i class="bi bi-download me-1"></i>Topup pada: {{ \Carbon\Carbon::parse($time)->format('d M Y') }}
                        </small>
                        <span class="badge {{ $isExpired ? 'bg-danger text-danger' : ($isWarning ? 'bg-warning text-dark' : 'bg-success text-success') }} bg-opacity-10 border border-{{ $isExpired ? 'danger' : ($isWarning ? 'warning' : 'success') }} px-2 py-1" style="font-size: 0.7rem;">
                            <i class="bi bi-clock-history me-1"></i> Exp: {{ $expiredDate->format('d M Y') }}
                        </span>
                    </div>
                </div>

                <div class="row g-2 bg-light rounded-3 p-2 mx-0">
                    <div class="col-6 border-end border-secondary border-opacity-10">
                        <small class="text-muted d-block" style="font-size: 0.7rem;">Sisa Uang</small>
                        <span class="fw-bold text-success" style="font-size: 0.95rem;">Rp {{ number_format($sisaUang, 0, ',', '.') }}</span>
                    </div>
                    <div class="col-6 ps-3">
                        <small class="text-muted d-block" style="font-size: 0.7rem;">Sisa Poin</small>
                        <span class="fw-bold text-primary" style="font-size: 0.95rem;">{{ number_format($sisaPoin, 0, ',', '.') }} Pts</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="list-group-item p-5 text-center border-0">
                <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3" style="width: 64px; height: 64px;">
                    <i class="bi bi-wallet2 text-muted fs-3"></i>
                </div>
                <h6 class="fw-bold text-dark">Belum ada saldo aktif</h6>
                <p class="text-muted small mb-0">Silakan lakukan Topup di kasir terdekat.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
