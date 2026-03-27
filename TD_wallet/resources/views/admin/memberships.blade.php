@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0"><i class="bi bi-award me-2"></i>Master Membership</h4>
            <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-1"></i> Tambah Tier Baru
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3" style="width: 60px;">No</th>
                                <th>Nama Tier</th>
                                <th>Prefix Rekening</th> <th>Syarat Nominal</th>
                                <th>Bonus Topup</th>
                                <th>Diskon Item</th>
                                <th>User</th>
                                <th class="text-center" style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($memberships as $index => $item)
                                <tr>
                                    <td class="px-4">{{ $index + 1 }}</td>
                                    <td class="fw-bold">
                                        <span class="badge {{ $item->text_color ?? 'text-white' }} shadow-sm"
                                            style="background: linear-gradient(135deg, {{ $item->color_start ?? '#146c43' }} 0%, {{ $item->color_end ?? '#0a3622' }} 100%);">
                                            {{ $item->name }}
                                        </span>
                                    </td>
                                    <td><code class="fw-bold text-primary">{{ $item->prefix }}</code></td> <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                    <td class="text-success fw-bold">+{{ floatval($item->bonus_topup) }}%</td>
                                    <td class="text-danger fw-bold">{{ floatval($item->diskon_belanja) }}%</td>
                                    <td>{{ $item->customers()->count() }} Orang</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                            data-bs-target="#modalEdit{{ $item->id }}" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('memberships.destroy', $item->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus tier {{ $item->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i
                                                    class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                <div class="modal fade" id="modalEdit{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <form action="{{ route('memberships.update', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header bg-light border-bottom-0">
                                                    <h5 class="modal-title fw-bold">Edit Tier Membership</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row g-3">
                                                        <div class="col-12 col-md-6">
                                                            <label class="form-label fw-bold">Nama Tier</label>
                                                            <input type="text" name="name" class="form-control" value="{{ $item->name }}" required>
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <label class="form-label fw-bold">Kode Prefix Rekening</label>
                                                            <input type="text" name="prefix" class="form-control" maxlength="2"
                                                                value="{{ $item->prefix }}" onkeyup="this.value = this.value.toUpperCase()" required>
                                                            <small class="text-muted">Maks 2 karakter (Contoh: PL, GL)</small>
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <label class="form-label fw-bold">Syarat Nominal (Harga)</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text bg-light">Rp</span>
                                                                <input type="number" name="harga" class="form-control" value="{{ floatval($item->harga) }}" min="0" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <label class="form-label fw-bold text-success">Bonus Topup Poin</label>
                                                            <div class="input-group">
                                                                <input type="number" step="0.01" name="bonus_topup" class="form-control" value="{{ floatval($item->bonus_topup) }}" min="0" max="100" required>
                                                                <span class="input-group-text bg-light">%</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <label class="form-label fw-bold text-danger">Diskon Belanja Item</label>
                                                            <div class="input-group">
                                                                <input type="number" step="0.01" name="diskon_belanja" class="form-control" value="{{ floatval($item->diskon_belanja) }}" min="0" max="100" required>
                                                                <span class="input-group-text bg-light">%</span>
                                                            </div>
                                                        </div>

                                                        <div class="col-12 mt-4">
                                                            <hr class="m-0">
                                                            <small class="text-muted fw-bold">Pengaturan Warna Kartu & Badge UI</small>
                                                        </div>
                                                        <div class="col-12 col-md-4">
                                                            <label class="form-label fw-bold">Warna Awal (Start)</label>
                                                            <input type="color" name="color_start" class="form-control form-control-color w-100" value="{{ $item->color_start }}" required>
                                                        </div>
                                                        <div class="col-12 col-md-4">
                                                            <label class="form-label fw-bold">Warna Akhir (End)</label>
                                                            <input type="color" name="color_end" class="form-control form-control-color w-100" value="{{ $item->color_end }}" required>
                                                        </div>
                                                        <div class="col-12 col-md-4">
                                                            <label class="form-label fw-bold">Warna Teks UI</label>
                                                            <select name="text_color" class="form-select" required>
                                                                <option value="text-white" {{ $item->text_color == 'text-white' ? 'selected' : '' }}>Putih (Terang)</option>
                                                                <option value="text-dark" {{ $item->text_color == 'text-dark' ? 'selected' : '' }}>Hitam (Gelap)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top-0">
                                                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Belum ada data tier membership.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form action="{{ route('memberships.store') }}" method="POST">
                        @csrf
                        <div class="modal-header bg-light border-bottom-0">
                            <h5 class="modal-title fw-bold">Tambah Membership Baru</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold">Nama Tier</label>
                                    <input type="text" name="name" class="form-control" placeholder="Contoh: Platinum" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold">Kode Prefix Rekening</label>
                                    <input type="text" name="prefix" class="form-control" maxlength="2" onkeyup="this.value = this.value.toUpperCase()" placeholder="Contoh: PL" required>
                                    <small class="text-muted">Gunakan 2 huruf kapital unik.</small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold">Nominal (Harga)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">Rp</span>
                                        <input type="number" name="harga" class="form-control" min="0" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold text-success">Bonus Topup Poin</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" name="bonus_topup" class="form-control" min="0" max="100" required>
                                        <span class="input-group-text bg-light">%</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold text-danger">Diskon Belanja Item</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" name="diskon_belanja" class="form-control" min="0" max="100" required>
                                        <span class="input-group-text bg-light">%</span>
                                    </div>
                                </div>
                                <div class="col-12 mt-4">
                                    <hr class="m-0">
                                    <small class="text-muted fw-bold">Pengaturan Warna Kartu & Badge UI</small>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold">Warna Awal (Start)</label>
                                    <input type="color" name="color_start" class="form-control form-control-color w-100" value="#146c43" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold">Warna Akhir (End)</label>
                                    <input type="color" name="color_end" class="form-control form-control-color w-100" value="#0a3622" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold">Warna Teks UI</label>
                                    <select name="text_color" class="form-select" required>
                                        <option value="text-white" selected>Putih (Terang)</option>
                                        <option value="text-dark">Hitam (Gelap)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0">
                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Tier Baru</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
