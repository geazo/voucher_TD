@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 border-top border-primary border-4">
                    <div class="card-header bg-white pb-0 pt-3 border-0">
                        <h5 class="fw-bold"><i class="bi bi-calendar-plus me-2"></i>Form Pengajuan Ekstensi Saldo</h5>
                    </div>
                    <div class="card-body p-4">

                        <div class="alert alert-info border-0 mb-4">
                            <div class="row">
                                <div class="col-sm-6">
                                    <span class="text-muted small">Nama Customer:</span><br>
                                    <strong class="text-dark">{{ $transaction->wallet->customer->nama }}</strong>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted small">Sisa Saldo:</span><br>
                                    <strong class="text-dark">Rp
                                        {{ number_format($transaction->sisa_saldo, 0, ',', '.') }}</strong>
                                </div>
                                <div class="col-sm-6 mt-2">
                                    <span class="text-muted small">Tanggal Expired Saat Ini:</span><br>
                                    <strong
                                        class="text-danger">{{ \Carbon\Carbon::parse($transaction->expired_at)->format('d M Y') }}</strong>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('admin.extensions.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="transaction_id" value="{{ $transaction->id }}">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Tambahan Masa Aktif (Hari)</label>
                                <div class="input-group">
                                    <input type="number" name="tambahan_hari" class="form-control" value="30"
                                        min="1" max="365" required>
                                    <span class="input-group-text">Hari</span>
                                </div>
                                <small class="text-muted">Berapa hari saldo ini ingin diperpanjang dari tanggal
                                    kedaluwarsanya?</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Alasan Pengajuan (Dari Telepon)</label>
                                <textarea name="alasan" rows="3" class="form-control"
                                    placeholder="Contoh: Customer komplain sakit 1 bulan, meminta perpanjangan..." required></textarea>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" onclick="history.back()" class="btn btn-light border">Batal</button>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i> Kirim
                                    Pengajuan</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
