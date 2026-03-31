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

                        <button type="button" class="btn btn-sm btn-outline-danger fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                            <i class="bi bi-key me-1"></i> Reset Password
                        </button>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success mx-4 mt-3 mb-0">{{ session('success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger mx-4 mt-3 mb-0">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="card-body p-4 pt-3">

                        <div class="row bg-light rounded p-3 mb-4 mx-0 border">
                            <div class="col-md-6 border-end">
                                <small class="text-muted d-block mb-1">Saldo Uang Aktif</small>
                                <span class="fs-5 fw-bold text-success">
                                    Rp
                                    {{ number_format($customer->wallets->where('type', 'Uang')->first()->balance ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="col-md-6 ps-md-4 mt-2 mt-md-0">
                                <small class="text-muted d-block mb-1">Saldo Poin Aktif</small>
                                <span class="fs-5 fw-bold text-primary">
                                    {{ number_format($customer->wallets->where('type', 'Poin')->first()->balance ?? 0, 0, ',', '.') }}
                                    Pts
                                </span>
                            </div>
                        </div>

                        <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-12 mt-2">
                                    <h6 class="fw-bold border-bottom pb-2 mb-0">Informasi Pribadi</h6>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-muted small">Nama Lengkap</label>
                                    <input type="text" name="nama"
                                        class="form-control @error('nama') is-invalid @enderror"
                                        value="{{ old('nama', $customer->nama) }}" required>
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small d-block">Jenis Kelamin</label>
                                    <select name="gender" class="form-select @error('gender') is-invalid @enderror"
                                        required>
                                        <option value="">-- Pilih Gender --</option>
                                        <option value="Laki-laki" {{ old('gender', $customer->gender) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="Perempuan" {{ old('gender', $customer->gender) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                        <option value="Lainnya" {{ old('gender', $customer->gender) == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                    @error('gender')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small">Kota Domisili</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-geo-alt"></i></span>
                                        <input type="text" name="kota_domisili"
                                            class="form-control @error('kota_domisili') is-invalid @enderror"
                                            value="{{ old('kota_domisili', $customer->kota_domisili) }}" placeholder="Cth: Surabaya"
                                            required>
                                        @error('kota')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>


                                <div class="col-12 mt-4">
                                    <h6 class="fw-bold border-bottom pb-2 mb-0">Kontak & Keamanan</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small">Nomor Telepon / WhatsApp</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-telephone"></i></span>
                                        <input type="text" name="notelp"
                                            class="form-control @error('notelp') is-invalid @enderror"
                                            value="{{ old('notelp', $customer->notelp) }}" required>
                                        @error('notelp')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                                        <input type="email" name="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            value="{{ old('email', $customer->email) }}" placeholder="opsional@email.com">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <h6 class="fw-bold border-bottom pb-2 mb-0">Status Keanggotaan</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small">Tingkat Membership</label>
                                    <select name="membership_id"
                                        class="form-select @error('membership_id') is-invalid @enderror">
                                        <option value="">-- Pilih Membership --</option>
                                        @foreach ($memberships as $m)
                                            <option value="{{ $m->id }}"
                                                {{ old('membership_id', $customer->membership_id) == $m->id ? 'selected' : '' }}>
                                                {{ $m->name }} (Diskon: {{ $m->diskon_belanja + 0 }}%)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('membership_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small">Status Akun</label>
                                    <select name="f_aktif" class="form-select @error('f_aktif') is-invalid @enderror"
                                        required>
                                        <option value="1" {{ old('f_aktif', $customer->f_aktif) == '1' ? 'selected' : '' }}>AKTIF (Bisa Transaksi)</option>
                                        <option value="0" {{ old('f_aktif', $customer->f_aktif) == '0' ? 'selected' : '' }}>NON-AKTIF (Diblokir)</option>
                                    </select>
                                    @error('f_aktif')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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

    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="resetPasswordModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Reset Sandi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p>Apakah Anda yakin ingin mereset kata sandi untuk pelanggan <strong>{{ $customer->nama }}</strong>?</p>
                    <div class="alert alert-warning mb-0 border-warning">
                        Kata sandi akan dikembalikan ke *default*, yaitu: <br>
                        <span class="fs-5 fw-bold font-monospace mt-1 d-block text-center text-dark bg-white border rounded py-1">password</span>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary fw-bold" data-bs-dismiss="modal">Batal</button>
                    <form action="{{ route('customers.reset_password', $customer->id) }}" method="POST" class="m-0">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-danger fw-bold">
                            <i class="bi bi-check2-circle me-1"></i> Ya, Reset Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
