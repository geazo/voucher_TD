@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 border-top {{ $isRecovery ? 'border-warning' : 'border-primary' }} border-4">
                    <div class="card-header bg-white pb-0 pt-3 border-0">
                        <h5 class="fw-bold">
                            @if($isRecovery)
                                <i class="bi bi-arrow-counterclockwise text-warning me-2"></i>Form Pengajuan Pemulihan Saldo
                            @else
                                <i class="bi bi-calendar-plus text-primary me-2"></i>Form Pengajuan Ekstensi Saldo
                            @endif
                        </h5>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card-body p-4">

                        @if($isRecovery)
                            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4">
                                <i class="bi bi-exclamation-triangle fs-3 me-3 text-warning"></i>
                                <div>
                                    <strong>Perhatian!</strong> Saldo ini sudah <strong> (Expired)</strong>.
                                    Pengajuan ini akan memulihkan nominal yang hangus dengan menyuntikkan saldo baru ke akun pelanggan jika disetujui oleh Super Admin.
                                </div>
                            </div>
                        @endif

                        <div class="alert {{ $isRecovery ? 'alert-secondary' : 'alert-info' }} border-0 mb-4 shadow-sm"
                             style="background-color: {{ $isRecovery ? '#fffdf7' : '#f8fbff' }};">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <span class="text-muted small">Nama Customer:</span><br>
                                    <strong class="text-dark fs-5">{{ $transaction->wallet->customer->nama }}</strong>
                                </div>

                                <div class="col-12 mb-3 pb-3 border-bottom" style="border-color: rgba(0,0,0,0.05) !important;">
                                    <span class="text-muted small"><i class="bi bi-clock-history me-1"></i>Waktu Topup Awal:</span><br>
                                    <strong class="text-dark">{{ \Carbon\Carbon::parse($transaction->created_at)->format('d M Y, H:i') }} WIB</strong>
                                </div>

                                <div class="col-sm-6 mb-2">
                                    <span class="text-muted small">
                                        <i class="bi bi-wallet2 me-1"></i>{{ $isRecovery ? 'Nominal Uang Hangus:' : 'Sisa Saldo Uang:' }}
                                    </span><br>
                                    <strong class="{{ $isRecovery ? 'text-danger' : 'text-success' }} fs-5">
                                        Rp {{ number_format($saldoUang, 0, ',', '.') }}
                                    </strong>
                                </div>

                                <div class="col-sm-6 mb-2">
                                    <span class="text-muted small">
                                        <i class="bi bi-star-fill text-warning me-1"></i>{{ $isRecovery ? 'Nominal Poin Hangus:' : 'Sisa Saldo Poin:' }}
                                    </span><br>
                                    <strong class="{{ $isRecovery ? 'text-danger' : 'text-primary' }} fs-5">
                                        {{ number_format($saldoPoin, 0, ',', '.') }} Pts
                                    </strong>
                                </div>

                                <div class="col-12 mt-2 pt-3 border-top" style="border-color: rgba(0,0,0,0.05) !important;">
                                    <span class="text-muted small"><i class="bi bi-calendar-x me-1"></i>Tanggal Kedaluwarsa:</span><br>
                                    <strong class="{{ $isRecovery ? 'text-muted text-decoration-line-through' : 'text-danger' }}">
                                        {{ \Carbon\Carbon::parse($transaction->expired_at)->format('d M Y') }}
                                    </strong>
                                    @if($isRecovery)
                                        <span class="badge bg-danger ms-2">SUDAH LEWAT</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('admin.extensions.store') }}" method="POST">
                            @csrf

                            <input type="hidden" name="transaction_id" value="{{ $transaction->id }}">
                            <input type="hidden" name="is_recovery" value="{{ $isRecovery ? 1 : 0 }}">
                            <input type="hidden" name="nominal_uang" value="{{ $saldoUang }}">
                            <input type="hidden" name="nominal_poin" value="{{ $saldoPoin }}">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Tambahan Masa Aktif (Hari)</label>
                                <div class="input-group">
                                    <input type="number" name="tambahan_hari" class="form-control" value="30" min="1" max="365" required>
                                    <span class="input-group-text">Hari</span>
                                </div>
                                <small class="text-muted">
                                    @if($isRecovery)
                                        Berapa hari saldo ini akan diaktifkan kembali, dihitung mulai dari <strong>hari persetujuan Super Admin</strong>?
                                    @else
                                        Berapa hari saldo ini ingin diperpanjang dari <strong>tanggal kedaluwarsanya</strong>?
                                    @endif
                                </small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Alasan Pengajuan</label>
                                <textarea name="alasan" rows="3" class="form-control @error('alasan') is-invalid @enderror"
                                    placeholder="Contoh: Customer komplain sakit 1 bulan, meminta kebijakan perpanjangan..." required>{{ old('alasan') }}</textarea>
                                @error('alasan')
                                    <div class="invalid-feedback mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('customers.show', $transaction->wallet->customer_id) }}" class="btn btn-light border">
                                    Batal
                                </a>
                                <button type="submit" class="btn {{ $isRecovery ? 'btn-warning text-dark fw-bold' : 'btn-primary' }}">
                                    <i class="bi bi-send me-1"></i> Kirim Pengajuan
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
