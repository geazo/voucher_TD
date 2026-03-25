@extends('layouts.app') {{-- Sesuaikan layout admin Anda --}}

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Master Data Layanan / Item</h4>
        <button type="button" class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="bi bi-plus-lg me-1"></i> Tambah Item
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4">Nama Layanan / Item</th>
                            <th class="py-3 px-4">Harga Dasar</th>
                            <th class="py-3 px-4 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                        <tr>
                            <td class="px-4 fw-bold">{{ $item->nama }}</td>
                            <td class="px-4 text-success fw-bold">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                            <td class="px-4 text-end">
                                <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $item->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <form action="{{ route('admin.items.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus item ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>

                        <div class="modal fade" id="editItemModal{{ $item->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title fw-bold">Edit Item</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('admin.items.update', $item->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Nama Item</label>
                                                <input type="text" name="nama" class="form-control" value="{{ $item->nama }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Harga (Rp)</label>
                                                <input type="number" name="harga" class="form-control" value="{{ $item->harga }}" min="0" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary fw-bold">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">Belum ada data item.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">Tambah Item Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.items.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Layanan / Item</label>
                        <input type="text" name="nama" class="form-control" placeholder="" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Harga (Rp)</label>
                        <input type="number" name="harga" class="form-control" placeholder="Contoh: 100000" min="0" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-bold">Simpan Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
