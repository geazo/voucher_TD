@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">
                <i class="bi bi-shield-check text-primary me-2"></i>Approval Ekstensi & Pemulihan Saldo
            </h4>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">Tanggal Request</th>
                                <th>Customer</th>
                                <th>Jenis Pengajuan</th>
                                <th>Nominal Terkait</th>
                                <th>Permintaan</th>
                                <th>Alasan</th>
                                <th>Status</th>
                                <th class="text-center">Aksi (Super Admin)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($extensions as $ext)
                                @php
                                    // Pengecekan aman jika ada data lama yang belum punya kolom baru
                                    $isRecovery = $ext->is_recovery ?? false;
                                    $uang = $ext->nominal_uang ?? ($ext->transaction->sisa_saldo ?? 0);
                                    $poin = $ext->nominal_poin ?? 0;
                                @endphp
                                <tr>
                                    <td class="px-4 text-muted small">
                                        {{ $ext->created_at->format('d M Y') }}<br>
                                        {{ $ext->created_at->format('H:i') }} WIB
                                    </td>

                                    <td>
                                        <span class="fw-bold text-dark">{{ $ext->customer->nama ?? 'N/A' }}</span><br>
                                        <small class="text-muted">{{ $ext->customer->notelp ?? '-' }}</small>
                                    </td>

                                    <td>
                                        @if ($isRecovery)
                                            <span class="badge bg-warning text-dark border border-warning shadow-sm">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>Pemulihan
                                            </span>
                                        @else
                                            <span class="badge bg-info text-dark border border-info shadow-sm">
                                                <i class="bi bi-calendar-plus me-1"></i>Perpanjangan
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="text-success fw-bold" style="font-size: 0.95rem;">
                                            Rp {{ number_format($uang, 0, ',', '.') }}
                                        </div>
                                        <div class="text-primary fw-bold mb-1" style="font-size: 0.85rem;">
                                            {{ number_format($poin, 0, ',', '.') }} Pts
                                        </div>
                                        <small class="text-danger" style="font-size: 0.75rem;">
                                            <i class="bi bi-clock-history"></i> Exp:
                                            {{ \Carbon\Carbon::parse($ext->transaction->expired_at)->format('d M Y') }}
                                        </small>
                                    </td>

                                    <td>
                                        <span class="badge bg-primary rounded-pill px-3 py-2">
                                            + {{ $ext->tambahan_hari }} Hari
                                        </span>
                                    </td>

                                    <td style="max-width: 250px; white-space: normal;">
                                        <small class="text-muted d-block" style="line-height: 1.4;">
                                            "{{ $ext->alasan }}"
                                        </small>
                                    </td>

                                    <td>
                                        @if ($ext->status == 'pending')
                                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i>
                                                Menunggu</span>
                                        @elseif($ext->status == 'approved')
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i>
                                                Disetujui</span>
                                        @else
                                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Ditolak</span>
                                        @endif
                                    </td>

                                    <td class="text-center px-4">
                                        @if ($ext->status == 'pending')
                                            <div class="d-flex justify-content-center gap-2">
                                                <form action="{{ route('superadmin.extensions.process', $ext->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('{{ $isRecovery ? 'PERHATIAN: Ini akan menyuntikkan saldo baru ke akun pelanggan karena saldo lama sudah hangus. Lanjutkan?' : 'Yakin setujui perpanjangan masa aktif saldo ini?' }}');">
                                                    @csrf
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-sm btn-success fw-bold shadow-sm"
                                                        title="Setujui">
                                                        <i class="bi bi-check-lg me-1"></i>
                                                    </button>
                                                </form>

                                                <form action="{{ route('superadmin.extensions.process', $ext->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Yakin ingin menolak permintaan ini?');">
                                                    @csrf
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger fw-bold"
                                                        title="Tolak">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <div class="text-muted small">
                                                Diproses oleh:<br>
                                                <strong class="text-dark">{{ $ext->operator->nama ?? 'Admin' }}</strong>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                        Belum ada pengajuan perpanjangan atau pemulihan saldo saat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($extensions->hasPages())
                <div class="card-footer bg-white border-top-0 pt-3 pb-3">
                    {{ $extensions->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
