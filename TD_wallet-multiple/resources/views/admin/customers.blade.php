@extends('layouts.app')

@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold">Master Data Customer</h5>

            <div class="d-flex gap-2">
                <a href="{{ route('customers.export') }}" class="btn btn-sm btn-success fw-bold">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                </a>

                <a href="{{ route('customers.create') }}" class="btn btn-sm btn-primary fw-bold">
                    <i class="bi bi-person-plus me-1"></i> Tambah Customer
                </a>
            </div>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success fw-bold"><i
                        class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
            @endif

            <form action="{{ route('customers.index') }}" method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari nama, email, atau nomor telepon..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i> Cari</button>
                    @if (request('search'))
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-danger">Reset</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama & Kontak</th>
                            <th>Membership</th>
                            <th>Saldo Uang</th>
                            <th>Saldo Poin</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $c)
                            @php
                                // 1. Kelompokkan dompet berdasarkan membership_id
                                $walletsGrouped = $c->wallets->groupBy(function ($item) {
                                    return $item->membership_id ?: 'default';
                                });

                                // 2. Logika Smart Visibility: Sembunyikan 'default' jika ada tier premium
                                if ($walletsGrouped->count() > 1 && $walletsGrouped->has('default')) {
                                    $walletsGrouped->forget('default');
                                }

                                // 3. Fallback: Jika customer baru dibuat dan belum punya dompet sama sekali
                                if ($walletsGrouped->isEmpty()) {
                                    $walletsGrouped->put('default', collect());
                                }
                            @endphp

                            @foreach ($walletsGrouped as $memId => $wallets)
                                @php
                                    $dompetUang = $wallets->where('type', 'Uang')->first();
                                    $dompetPoin = $wallets->where('type', 'Poin')->first();

                                    // Ambil objek membership dari salah satu dompet
                                    $membership = $wallets->first() ? $wallets->first()->membership : null;

                                    $tierName = $membership ? $membership->name : 'Customer';
                                    $textColor = $membership ? $membership->text_color : 'text-muted';
                                    $bgStyle = $membership
                                        ? "background: linear-gradient(135deg, {$membership->color_start} 0%, {$membership->color_end} 100%); border: none;"
                                        : 'background-color: #ffffff; border: 1px solid #dee2e6;';
                                @endphp

                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $c->nama }}</div>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-telephone me-1"></i>{{ $c->notelp }}
                                        </small>
                                        @if ($c->email)
                                            <small class="text-muted">
                                                <i class="bi bi-envelope me-1"></i>{{ $c->email }}
                                            </small>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge {{ $textColor }} shadow-sm px-2 py-1 mb-1"
                                            style="{{ $bgStyle }} letter-spacing: 0.5px; font-size: 0.75rem;">
                                            {{ strtoupper($tierName) }}
                                        </span>
                                        <br>
                                        <small class="text-muted" style="font-family: monospace;">
                                            {{ $dompetUang->no_rekening ?? 'Belum ada rekening' }}
                                        </small>
                                    </td>

                                    <td class="text-success fw-bold">
                                        Rp {{ number_format($dompetUang->balance ?? 0, 0, ',', '.') }}
                                    </td>

                                    <td class="text-primary fw-bold">
                                        {{ number_format($dompetPoin->balance ?? 0, 0, ',', '.') }} Pts
                                    </td>

                                    <td>
                                        @if ($c->f_aktif)
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success border border-success">Aktif</span>
                                        @else
                                            <span
                                                class="badge bg-danger bg-opacity-10 text-danger border border-danger">Non-Aktif</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('customers.show', ['customer' => $c->id, 'membership_id' => $memId === 'default' ? '' : $memId]) }}"
                                                class="btn btn-sm btn-outline-info me-1" title="Lihat Detail & Riwayat">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a href="{{ route('customers.edit', $c->id) }}"
                                                class="btn btn-sm btn-outline-primary" title="Edit Data">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <form action="{{ route('customers.destroy', $c->id) }}" method="POST"
                                                class="m-0"
                                                onsubmit="return confirm('Apakah Anda yakin ingin mengubah status customer ini?')">
                                                @csrf
                                                @method('DELETE')

                                                @if ($c->f_aktif)
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        title="Non-Aktifkan Customer">
                                                        <i class="bi bi-person-dash"></i>
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn btn-sm btn-outline-success"
                                                        title="Aktifkan Customer">
                                                        <i class="bi bi-person-check"></i>
                                                    </button>
                                                @endif
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
                                    Data customer tidak ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $customers->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
