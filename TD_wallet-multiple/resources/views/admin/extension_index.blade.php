@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <h4 class="fw-bold mb-4"><i class="bi bi-check2-square me-2"></i>Approval Ekstensi Saldo</h4>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">Tanggal Request</th>
                                <th>Customer</th>
                                <th>Saldo Terkait</th>
                                <th>Permintaan</th>
                                <th>Alasan</th>
                                <th>Status</th>
                                <th class="text-center">Aksi (Super Admin)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($extensions as $ext)
                                <tr>
                                    <td class="px-4">{{ $ext->created_at->format('d M Y, H:i') }}</td>
                                    <td class="fw-bold">{{ $ext->customer->nama }}</td>
                                    <td>
                                        <div class="text-success fw-bold">
                                            Rp {{ number_format($ext->nominal_uang ?? 0, 0, ',', '.') }}
                                        </div>
                                        <div class="text-primary fw-bold mb-1" style="font-size: 0.85rem;">
                                            {{ number_format($ext->nominal_poin ?? 0, 0, ',', '.') }} Pts
                                        </div>
                                        <small class="text-danger">Exp:
                                            {{ \Carbon\Carbon::parse($ext->transaction->expired_at)->format('d M Y') }}</small>
                                    </td>
                                    <td><span class="badge bg-primary">+ {{ $ext->tambahan_hari }} Hari</span></td>
                                    <td style="max-width: 250px; white-space: normal;">
                                        <small class="text-muted">{{ $ext->alasan }}</small>
                                    </td>
                                    <td>
                                        @if ($ext->status == 'pending')
                                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i>
                                                Pending</span>
                                        @elseif($ext->status == 'approved')
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i>
                                                Disetujui</span>
                                        @else
                                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Ditolak</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($ext->status == 'pending')
                                            <div class="d-flex justify-content-center gap-1">
                                                <form action="{{ route('superadmin.extensions.process', $ext->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Yakin setujui perpanjangan ini?');">
                                                    @csrf
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-sm btn-success" title="Setujui"><i
                                                            class="bi bi-check-lg"></i></button>
                                                </form>

                                                <form action="{{ route('superadmin.extensions.process', $ext->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Yakin tolak permintaan ini?');">
                                                    @csrf
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        title="Tolak"><i class="bi bi-x-lg"></i></button>
                                                </form>
                                            </div>
                                        @else
                                            <small class="text-muted">Diproses
                                                oleh:<br>{{ $ext->operator->nama ?? 'Admin' }}</small>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada pengajuan perpanjangan
                                        saldo saat ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top-0 pt-3">
                {{ $extensions->links() }}
            </div>
        </div>
    </div>
@endsection
