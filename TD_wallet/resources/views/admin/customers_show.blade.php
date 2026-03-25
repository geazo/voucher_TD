@extends('layouts.app') @section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Detail Customer</h4>
        <a href="{{ route('customers.index') }}" class="btn btn-light border shadow-sm fw-bold">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card shadow-sm border-0 h-100" style="border-top: 4px solid #0dcaf0;">
                <div class="card-body">
                    <h6 class="fw-bold text-muted mb-3">Informasi Customer</h6>
                    <h5 class="fw-bold text-dark mb-1">{{ $customer->nama }}</h5>
                    <p class="mb-1 small text-muted"><i class="bi bi-telephone me-2"></i>{{ $customer->no_telp ?? '-' }}</p>
                    <p class="mb-3 small text-muted"><i class="bi bi-envelope me-2"></i>{{ $customer->email ?? '-' }}</p>
                    <span class="badge {{ $customer->membership === 'Gold' ? 'bg-warning text-dark' : 'border text-dark' }}">
                        {{ $customer->membership ?? 'Reguler' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card shadow-sm border-0 h-100 bg-success text-white">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h6 class="fw-bold opacity-75 mb-2"><i class="bi bi-wallet2 me-2"></i>Saldo Uang Aktif</h6>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($dompetUang->balance ?? 0, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 bg-primary text-white">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h6 class="fw-bold opacity-75 mb-2"><i class="bi bi-star-fill me-2"></i>Saldo Poin Aktif</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($dompetPoin->balance ?? 0, 0, ',', '.') }} Pts</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Transaksi & Mutasi Saldo</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3">Waktu</th>
                            <th>Jenis Transaksi</th>
                            <th>Nominal</th>
                            <th>Sisa Saldo</th>
                            <th>Tgl Kedaluwarsa</th>
                            <th>Kasir/Petugas</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr>
                                <td class="px-4">{{ $tx->created_at->format('d M Y, H:i') }}</td>
                                <td>
                                    @if($tx->type === 'kredit')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">Kredit (Topup)</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Debit (Keluar)</span>
                                    @endif
                                    <br><small class="text-muted">{{ $tx->keterangan ?? '-' }}</small>
                                </td>
                                <td class="fw-bold {{ $tx->type === 'kredit' ? 'text-success' : 'text-danger' }}">
                                    {{ $tx->type === 'kredit' ? '+' : '-' }} {{ number_format($tx->nominal, 0, ',', '.') }}
                                </td>
                                <td>
                                    @if($tx->type === 'kredit')
                                        Rp {{ number_format($tx->sisa_saldo, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($tx->expired_at)
                                        <span class="{{ \Carbon\Carbon::parse($tx->expired_at)->isPast() ? 'text-danger fw-bold' : '' }}">
                                            {{ \Carbon\Carbon::parse($tx->expired_at)->format('d M Y') }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $tx->operator->nama ?? 'Sistem' }}</td>
                                <td class="text-center">

                                    @if($tx->type === 'kredit' && $tx->sisa_saldo > 0)
                                        <a href="{{ route('admin.extensions.create', ['transaction_id' => $tx->id]) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Ajukan Perpanjangan Masa Aktif Saldo">
                                            <i class="bi bi-calendar-plus"></i> Perpanjang
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat transaksi untuk customer ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0 pt-3">
            {{ $transactions->links() }}
        </div>
    </div>

</div>
@endsection
