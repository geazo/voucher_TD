@extends('layouts.app')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 fw-bold">Master Data Operator</h5>
        <a href="{{ route('operators.create') }}" class="btn btn-sm btn-primary fw-bold">
            <i class="bi bi-person-plus me-1"></i> Tambah Operator
        </a>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success fw-bold"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>
        @endif

        <form action="{{ route('operators.index') }}" method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau role..." value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i> Cari</button>
                @if(request('search'))
                    <a href="{{ route('operators.index') }}" class="btn btn-outline-danger">Reset</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama Operator</th>
                        <th>Email Login</th>
                        <th>Role Akses</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operators as $op)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $op->nama }}</div>
                        </td>
                        <td class="text-muted">{{ $op->email }}</td>
                        <td>
                            @php
                                $badgeClass = match($op->role) {
                                    'superadmin' => 'bg-danger',
                                    'admin'      => 'bg-primary',
                                    'kasir'      => 'bg-success',
                                    default      => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} text-uppercase">{{ $op->role }}</span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('operators.edit', $op->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Data">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form action="{{ route('operators.destroy', $op->id) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus akun operator ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Permanen">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">Belum ada data operator.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $operators->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
