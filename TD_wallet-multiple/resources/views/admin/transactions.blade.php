@extends('layouts.app')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 fw-bold">Riwayat Transaksi Master</h5>

        <a href="{{ route('admin.transactions.export', ['type' => $type]) }}" class="btn btn-sm btn-success fw-bold">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </a>
    </div>

    <div class="card-header bg-white p-0 border-bottom">
        <ul class="nav nav-tabs nav-fill border-0">
            <li class="nav-item">
                <a class="nav-link py-3 {{ $type == 'topup' ? 'active fw-bold text-primary border-bottom border-primary border-3' : 'text-muted' }}"
                   href="{{ route('admin.transactions', ['type' => 'topup']) }}">
                    <i class="bi bi-wallet2 me-2"></i> Riwayat Topup Membership
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3 {{ $type == 'payment' ? 'active fw-bold text-success border-bottom border-success border-3' : 'text-muted' }}"
                   href="{{ route('admin.transactions', ['type' => 'payment']) }}">
                    <i class="bi bi-cart-check me-2"></i> Riwayat Pembayaran & Penyesuaian
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Waktu</th>
                        <th>Customer</th>
                        <th>Dompet</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th>Keterangan</th> <th>Operator</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                    @php
                        // Logika Tampilan Berdasarkan Tipe Transaksi
                        if ($t->type === 'kredit') {
                            $warnaText  = 'text-success';
                            $warnaBadge = 'bg-success';
                            $tanda      = '+';
                            $label      = 'TOPUP';
                        } elseif ($t->type === 'adjustment') {
                            $warnaText  = 'text-warning'; // Pembeda untuk sistem/expired
                            $warnaBadge = 'bg-warning text-dark';
                            $tanda      = '-';
                            $label      = 'ADJUSTMENT';
                        } else {
                            $warnaText  = 'text-danger';
                            $warnaBadge = 'bg-danger';
                            $tanda      = '-';
                            $label      = 'PAYMENT';
                        }
                    @endphp
                    <tr>
                        <td>
                            <small class="text-muted">{{ $t->created_at->format('d/m/Y') }}</small><br>
                            {{ $t->created_at->format('H:i') }}
                        </td>
                        <td>
                            <div class="fw-bold">{{ $t->wallet->customer->nama ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $t->wallet->customer->notelp ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="badge {{ $t->wallet->type == 'Uang' ? 'bg-info' : 'bg-secondary' }}">
                                {{ $t->wallet->type }}
                            </span>
                        </td>
                        <td class="fw-bold {{ $warnaText }}">
                            {{ $tanda }} {{ $t->wallet->type == 'Uang' ? 'Rp ' : '' }}{{ number_format($t->nominal, 0, ',', '.') }}
                        </td>
                        <td>
                            <span class="badge {{ $warnaBadge }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td style="max-width: 250px;">
                            <small class="text-muted text-wrap d-inline-block text-truncate" style="max-width: 100%;" title="{{ $t->keterangan }}">
                                {{ $t->keterangan ?? '-' }}
                            </small>
                        </td>
                        <td>
                            <div class="small fw-bold">{{ $t->operator->nama ?? 'System' }}</div>
                            <small class="text-muted" style="font-size: 0.75rem;">{{ strtoupper($t->operator->role ?? 'AUTO') }}</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                            Belum ada riwayat transaksi di kategori ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
