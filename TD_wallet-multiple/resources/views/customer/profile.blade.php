@extends('layouts.appC')

@section('content')
    <div class="container py-4">
        <h4 class="fw-bold mb-4">Profil Saya</h4>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3">Informasi Akun</h6>

                        <div class="mb-3">
                            <small class="text-muted d-block">Nama Lengkap</small>
                            <span class="fw-semibold">{{ $customer->nama }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Nomor Whatsapp</small>
                            <span class="fw-semibold">{{ $customer->notelp }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Email</small>
                            <span class="fw-semibold">{{ $customer->email ?? 'Belum ditambahkan' }}</span>
                        </div>
                        <div class="mb-0">
                            @php
                                $m = $customer->membership;

                                // Logika Fallback: Jika null tampilkan 'Customer'
                                $tierName = $m ? $m->name : 'Customer';
                                $textColor = $m ? $m->text_color : 'text-muted';

                                // Style Badge: Gradien Mewah untuk Member, Putih Clean untuk Customer biasa
                                $badgeStyle = $m
                                    ? "background: linear-gradient(135deg, {$m->color_start} 0%, {$m->color_end} 100%); border: none;"
                                    : 'background-color: #ffffff; border: 1px solid #dee2e6;';
                            @endphp

                            <small class="text-muted d-block mb-1">Status Keanggotaan</small>
                            <span class="badge {{ $textColor }} shadow-sm px-3 py-2"
                                style="{{ $badgeStyle }} letter-spacing: 0.5px; font-size: 0.85rem; border-radius: 8px;">
                                <i class="bi {{ $m ? 'bi-star-fill' : 'bi-person-badge' }} me-1"></i>
                                {{ strtoupper($tierName) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3">Ganti Password</h6>

                        <form action="{{ route('customer.profile.password') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Password Saat Ini</label>
                                <input type="password" name="current_password"
                                    class="form-control @error('current_password') is-invalid @enderror" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-primary fw-bold px-4">Simpan Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
