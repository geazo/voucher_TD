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
                        <h5 class="m-0 fw-bold"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Edit Data Customer
                        </h5>
                    </div>

                    <div class="card-body p-4 pt-2">

                        <div class="mb-4">
                            <h6 class="fw-bold text-muted border-bottom pb-2 mb-3">Daftar Dompet Aktif</h6>
                            <div class="row g-3">
                                @php
                                    // Kelompokkan dompet berdasarkan tier
                                    $walletsGrouped = $customer->wallets->groupBy(function ($item) {
                                        return $item->membership_id ?: 'default';
                                    });
                                @endphp

                                @forelse($walletsGrouped as $memId => $wallets)
                                    @php
                                        $dompetUang = $wallets->where('type', 'Uang')->first();
                                        $dompetPoin = $wallets->where('type', 'Poin')->first();
                                        $membership = $wallets->first()->membership ?? null;

                                        $tierName = $membership ? $membership->name : 'Reguler (Default)';
                                        $textColor = $membership ? $membership->text_color : 'text-dark';
                                        $bgStyle = $membership
                                            ? "background: linear-gradient(135deg, {$membership->color_start} 0%, {$membership->color_end} 100%); border: none;"
                                            : 'background-color: #f8f9fa; border: 1px solid #dee2e6;';
                                    @endphp

                                    <div class="col-md-6">
                                        <div class="p-3 rounded-4 shadow-sm {{ $textColor }}"
                                            style="{{ $bgStyle }}">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold"
                                                    style="letter-spacing: 0.5px;">{{ strtoupper($tierName) }}</span>
                                                <small class="opacity-75"
                                                    style="font-family: monospace;">{{ $dompetUang->no_rekening ?? '-' }}</small>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-end mt-3">
                                                <div>
                                                    <small class="d-block opacity-75" style="font-size: 0.75rem;">Saldo
                                                        Uang</small>
                                                    <span class="fw-bold fs-6">Rp
                                                        {{ number_format($dompetUang->balance ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="text-end">
                                                    <small class="d-block opacity-75" style="font-size: 0.75rem;">Saldo
                                                        Poin</small>
                                                    <span
                                                        class="fw-bold fs-6">{{ number_format($dompetPoin->balance ?? 0, 0, ',', '.') }}
                                                        Pts</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-light border text-center text-muted mb-0">
                                            Belum ada dompet yang terdaftar untuk pelanggan ini.
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h6 class="fw-bold text-muted border-bottom pb-2 mb-3">Informasi Profil</h6>

                        <div class="row g-3">
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
                                <label class="form-label fw-bold text-muted small">Kota Domisili</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-geo-alt"></i></span>
                                    <input type="text" name="kota_domisili" class="form-control @error('kota_domisili') is-invalid @enderror" value="{{ old('kota_domisili', $customer->kota_domisili) }}" placeholder="Contoh: Surabaya" required>
                                    @error('kota_domisili') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="col-md-6 mt-4">
                                <label class="form-label fw-bold text-muted small">Jenis Kelamin</label>
                                <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                    <option value="">-- Pilih Jenis Kelamin --</option>
                                    <option value="Laki-laki" {{ old('gender', $customer->gender) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="Perempuan" {{ old('gender', $customer->gender) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                    <option value="Lainnya" {{ old('gender', $customer->gender) == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                                @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12 mt-4">
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
