@extends('layouts.admin')

@section('title', 'Detail Produk - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Detail Produk: {{ $product->name }}</h4>
                    <div>
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
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
                        <div class="col-md-4">
                            <!-- Gambar Produk -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Gambar Produk</h5>
                                </div>
                                <div class="card-body text-center">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 300px;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 300px;">
                                            <div class="text-center">
                                                <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Tidak ada gambar</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Status dan Aksi -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Status & Aksi</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Status:</strong>
                                        <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-danger' }} ms-2">
                                            {{ $product->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Produk Unggulan:</strong>
                                        <span class="badge {{ $product->is_featured ? 'bg-warning' : 'bg-light text-dark' }} ms-2">
                                            {{ $product->is_featured ? 'Ya' : 'Tidak' }}
                                        </span>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Stok:</strong>
                                        <span class="badge {{ $product->stock > 10 ? 'bg-success' : ($product->stock > 0 ? 'bg-warning' : 'bg-danger') }} ms-2">
                                            {{ $product->stock }} unit
                                        </span>
                                    </div>
                                    <hr>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Edit Produk
                                        </a>
                                        <button type="button" class="btn btn-danger js-delete-product" data-product-id="{{ $product->id }}">
                                            <i class="fas fa-trash"></i> Hapus Produk
                                        </button>
                                        <a href="{{ route('products.show', $product->id) }}" class="btn btn-info" target="_blank">
                                            <i class="fas fa-external-link-alt"></i> Lihat di Website
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <!-- Informasi Dasar -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Informasi Dasar</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-3"><strong>ID Produk:</strong></div>
                                        <div class="col-md-9">{{ $product->id }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-3"><strong>Nama Produk:</strong></div>
                                        <div class="col-md-9">{{ $product->name }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-3"><strong>Kategori:</strong></div>
                                        <div class="col-md-9">
                                            <span class="badge bg-secondary">{{ $product->category->name }}</span>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-3"><strong>Harga:</strong></div>
                                        <div class="col-md-9">
                                            <span class="h5 text-primary">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-3"><strong>Deskripsi:</strong></div>
                                        <div class="col-md-9">
                                            @if($product->description)
                                                <p class="mb-0">{{ $product->description }}</p>
                                            @else
                                                <em class="text-muted">Tidak ada deskripsi</em>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-3"><strong>Dibuat:</strong></div>
                                        <div class="col-md-9">{{ $product->created_at->format('d M Y H:i') }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-3"><strong>Diupdate:</strong></div>
                                        <div class="col-md-9">{{ $product->updated_at->format('d M Y H:i') }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Opsi saus dihapus dari halaman detail produk admin --}}
                            {{-- Pilihan Saus dihapus dari halaman detail produk admin --}}

                            <!-- Statistik Penjualan -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Statistik Penjualan</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <div class="border rounded p-3">
                                                <h3 class="text-primary mb-1">{{ $product->transactionDetails->count() }}</h3>
                                                <small class="text-muted">Total Pesanan</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border rounded p-3">
                                                <h3 class="text-success mb-1">{{ $product->transactionDetails->sum('quantity') }}</h3>
                                                <small class="text-muted">Total Terjual</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border rounded p-3">
                                                <h3 class="text-info mb-1">Rp {{ number_format($product->transactionDetails->sum('subtotal'), 0, ',', '.') }}</h3>
                                                <small class="text-muted">Total Pendapatan</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Riwayat Pesanan Terbaru -->
                            @if($product->transactionDetails->count() > 0)
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Riwayat Pesanan Terbaru</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Tanggal</th>
                                                        <th>ID Transaksi</th>
                                                        <th>Pelanggan</th>
                                                        <th>Jumlah</th>
                                                        <th>Harga</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($product->transactionDetails->take(10) as $detail)
                                                        <tr>
                                                            <td>{{ $detail->transaction->created_at->format('d/m/Y') }}</td>
                                                            <td>
                                                                <a href="{{ route('admin.orders.show', $detail->transaction->id) }}" class="text-decoration-none">
                                                                    #{{ $detail->transaction->id }}
                                                                </a>
                                                            </td>
                                                            <td>{{ $detail->transaction->user->name }}</td>
                                                            <td>{{ $detail->quantity }}</td>
                                                            <td>Rp {{ number_format($detail->unit_price ?? $detail->price ?? 0, 0, ',', '.') }}</td>
                                                            <td>Rp {{ number_format($detail->subtotal ?? (($detail->unit_price ?? $detail->price ?? 0) * $detail->quantity), 0, ',', '.') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @if($product->transactionDetails->count() > 10)
                                            <div class="text-center mt-3">
                                                <small class="text-muted">Menampilkan 10 pesanan terbaru dari {{ $product->transactionDetails->count() }} total pesanan</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus produk <strong>{{ $product->name }}</strong>?</p>
                <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait produk ini.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Attach delete button handler without inline onclick
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.js-delete-product');
    if (btn) {
        const productId = btn.getAttribute('data-product-id');
        deleteProduct(productId);
    }
});

// Fungsi hapus produk
function deleteProduct(productId) {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

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