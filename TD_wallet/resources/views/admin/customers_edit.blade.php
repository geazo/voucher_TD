@extends('layouts.app')

@section('content')
<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="mb-3">
                <a href="{{ route('customers.index') }}" class="text-decoration-none text-muted fw-bold">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Master Customer
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                    <h5 class="m-0 fw-bold"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Edit Data Customer</h5>
                </div>

                <div class="card-body p-4 pt-2">

                    <div class="row bg-light rounded p-3 mb-4 mx-0 border">
                        <div class="col-md-6 border-end">
                            <small class="text-muted d-block mb-1">Saldo Uang Aktif</small>
                            <span class="fs-5 fw-bold text-success">
                                Rp {{ number_format($customer->wallets->where('type', 'Uang')->first()->balance ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="col-md-6 ps-md-4 mt-2 mt-md-0">
                            <small class="text-muted d-block mb-1">Saldo Poin Aktif</small>
                            <span class="fs-5 fw-bold text-primary">
                                {{ number_format($customer->wallets->where('type', 'Poin')->first()->balance ?? 0, 0, ',', '.') }} Pts
                            </span>
                        </div>
                    </div>

                    <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                        @csrf
                        @method('PUT') <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-muted small">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control form-control-lg @error('nama') is-invalid @enderror" value="{{ old('nama', $customer->nama) }}" required>
                                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small">Nomor Telepon / WhatsApp</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-telephone"></i></span>
                                    <input type="text" name="notelp" class="form-control @error('notelp') is-invalid @enderror" value="{{ old('notelp', $customer->notelp) }}" required>
                                    @error('notelp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $customer->email) }}" placeholder="opsional@email.com">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-6 mt-4">
                                <label class="form-label fw-bold text-muted small">Tingkat Membership</label>
                                <select name="membership_id" class="form-select form-select-lg @error('membership_id') is-invalid @enderror">
                                    <option value="">-- Pilih Membership --</option>
                                    @foreach($memberships as $m)
                                        <option value="{{ $m->id }}" {{ old('membership_id', $customer->membership_id) == $m->id ? 'selected' : '' }}>
                                            {{ $m->name }} (Diskon: {{ $m->diskon_belanja + 0 }}%)
                                        </option>
                                    @endforeach
                                </select>
                                @error('membership_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mt-4">
                                <label class="form-label fw-bold text-muted small">Status Akun</label>
                                <select name="f_aktif" class="form-select form-select-lg @error('f_aktif') is-invalid @enderror" required>
                                    <option value="1" {{ old('f_aktif', $customer->f_aktif) == '1' ? 'selected' : '' }}> AKTIF (Bisa Transaksi)</option>
                                    <option value="0" {{ old('f_aktif', $customer->f_aktif) == '0' ? 'selected' : '' }}> NON-AKTIF (Diblokir Sementara)</option>
                                </select>
                                @error('f_aktif') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('customers.index') }}" class="btn btn-light border fw-bold px-4">Batal</a>
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
