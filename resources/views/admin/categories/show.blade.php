@extends('layouts.admin')

@section('title', 'Detail Kategori - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Detail Kategori: {{ $category->name }}</h4>
                    <div>
                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <h5>Informasi Kategori</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="150"><strong>ID:</strong></td>
                                        <td>{{ $category->id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nama:</strong></td>
                                        <td>{{ $category->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Slug:</strong></td>
                                        <td><code>{{ $category->slug }}</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Deskripsi:</strong></td>
                                        <td>
                                            @if($category->description)
                                                {{ $category->description }}
                                            @else
                                                <span class="text-muted">Tidak ada deskripsi</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jumlah Produk:</strong></td>
                                        <td>
                                            <span class="badge bg-info">{{ $category->products_count ?? 0 }} produk</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Dibuat:</strong></td>
                                        <td>{{ $category->created_at->format('d M Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Diperbarui:</strong></td>
                                        <td>{{ $category->updated_at->format('d M Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-4">
                                <h5>Gambar Kategori</h5>
                                @if($category->image)
                                    <div class="border rounded p-3 text-center">
                                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="img-fluid" style="max-height: 300px;">
                                    </div>
                                @else
                                    <div class="border rounded p-3 text-center bg-light">
                                        <i class="fas fa-image fa-3x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Tidak ada gambar</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($category->products_count > 0)
                        <div class="mt-4">
                            <h5>Produk dalam Kategori Ini</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Gambar</th>
                                            <th>Nama Produk</th>
                                            <th>Harga</th>
                                            <th>Stok</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($category->products as $product)
                                            <tr>
                                                <td>
                                                    @if($product->image)
                                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>{{ $product->name }}</td>
                                                <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                                <td>
                                                    <span class="badge {{ $product->stock > 10 ? 'bg-success' : ($product->stock > 0 ? 'bg-warning' : 'bg-danger') }}">
                                                        {{ $product->stock }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $product->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-info" title="Lihat Produk">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="mt-4">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Belum ada produk dalam kategori ini.
                            </div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Kategori
                            </a>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash"></i> Hapus Kategori
                            </button>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                                <i class="fas fa-list"></i> Daftar Kategori
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus kategori <strong>{{ $category->name }}</strong>?</p>
                @if($category->products_count > 0)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Kategori ini memiliki {{ $category->products_count }} produk. 
                        Anda harus memindahkan atau menghapus semua produk terlebih dahulu.
                    </div>
                @else
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Tindakan ini tidak dapat dibatalkan!
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                @if($category->products_count == 0)
                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                @else
                    <button type="button" class="btn btn-danger" disabled>Tidak Dapat Dihapus</button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto hide alerts
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>
@endpush