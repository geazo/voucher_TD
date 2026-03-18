@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="m-0 fw-bold">{{ isset($operator) ? 'Edit Operator' : 'Tambah Operator Baru' }}</h5>
            </div>

            <div class="card-body p-4">
                <form action="{{ isset($operator) ? route('operators.update', $operator->id) : route('operators.store') }}" method="POST">
                    @csrf
                    @if(isset($operator)) @method('PUT') @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $operator->nama ?? '') }}" required>
                        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Login</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $operator->email ?? '') }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Role / Hak Akses</label>
                        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="kasir" {{ old('role', $operator->role ?? '') == 'kasir' ? 'selected' : '' }}>KASIR (Hanya bisa transaksi)</option>
                            <option value="admin" {{ old('role', $operator->role ?? '') == 'admin' ? 'selected' : '' }}>ADMIN (Bisa lihat riwayat)</option>
                            <option value="superadmin" {{ old('role', $operator->role ?? '') == 'superadmin' ? 'selected' : '' }}>SUPERADMIN (Bisa akses semua master)</option>
                        </select>
                        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Password Login</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ isset($operator) ? '' : 'required' }}>
                        @if(isset($operator))
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                        @endif
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('operators.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-success fw-bold">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
