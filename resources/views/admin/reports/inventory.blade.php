@extends('layouts.admin')

@section('title', 'Laporan Stok - Admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Laporan Stok</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Laporan Stok</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.reports.sales') }}" class="btn btn-success me-2">
                <i class="fas fa-chart-line"></i> Laporan Penjualan
            </a>
            <a href="{{ route('admin.reports.customers') }}" class="btn btn-primary">
                <i class="fas fa-users"></i> Laporan Pelanggan
            </a>
        </div>
    </div>

    <!-- Inventory Summary -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Produk
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $products->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Produk Aktif
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $products->where('is_active', true)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Stok Rendah
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $lowStockProducts->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Produk Nonaktif
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $inactiveProducts->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    @if($lowStockProducts->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-warning">
            <h6 class="m-0 font-weight-bold text-white">
                <i class="fas fa-exclamation-triangle"></i> Peringatan Stok Rendah
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Stok Tersisa</th>
                            <th>Harga</th>
                            <th>Total Terjual</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockProducts as $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($product->image)
                                        <img src="{{ Storage::url($product->image) }}" 
                                             alt="{{ $product->name }}" 
                                             class="rounded me-2" 
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded me-2 d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px; color: white; font-size: 14px;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $product->category->name ?? 'Tidak tersedia' }}</span>
                            </td>
                            <td>
                                <span class="badge badge-warning">{{ $product->stock }}</span>
                            </td>
                            <td>
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge badge-success">{{ $product->total_sold ?? 0 }}</span>
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.products.edit', $product->id) }}" 
                                   class="btn btn-sm btn-warning" title="Edit Stok">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Inactive Products -->
    @if($inactiveProducts->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-times-circle"></i> Produk Nonaktif
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Harga</th>
                            <th>Tanggal Nonaktif</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inactiveProducts as $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($product->image)
                                        <img src="{{ Storage::url($product->image) }}" 
                                             alt="{{ $product->name }}" 
                                             class="rounded me-2" 
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded me-2 d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px; color: white; font-size: 14px;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $product->category->name ?? 'Tidak tersedia' }}</span>
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ $product->stock }}</span>
                            </td>
                            <td>
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </td>
                            <td>
                                {{ $product->updated_at->format('d M Y') }}
                            </td>
                            <td>
                                <a href="{{ route('admin.products.edit', $product->id) }}" 
                                   class="btn btn-sm btn-primary" title="Aktifkan">
                                    <i class="fas fa-check"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- All Products Inventory -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Semua Produk</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="inventoryTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Harga</th>
                            <th>Total Terjual</th>
                            <th>Status</th>
                            <th>Update Terakhir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($product->image)
                                        <img src="{{ Storage::url($product->image) }}" 
                                             alt="{{ $product->name }}" 
                                             class="rounded me-2" 
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded me-2 d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px; color: white; font-size: 14px;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        @if($product->is_featured)
                                            <span class="badge badge-warning ms-1">Unggulan</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $product->category->name ?? 'Tidak tersedia' }}</span>
                            </td>
                            <td>
                                @if($product->stock <= 10)
                                    <span class="badge badge-warning">{{ $product->stock }}</span>
                                @elseif($product->stock <= 5)
                                    <span class="badge badge-danger">{{ $product->stock }}</span>
                                @else
                                    <span class="badge badge-success">{{ $product->stock }}</span>
                                @endif
                            </td>
                            <td>
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge badge-primary">{{ $product->total_sold ?? 0 }}</span>
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                {{ $product->updated_at->format('d M Y H:i') }}
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.products.show', $product->id) }}" 
                                       class="btn btn-sm btn-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product->id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">

<script>
$(document).ready(function() {
    $('#inventoryTable').DataTable({
        "pageLength": 25,
        "order": [[ 2, "asc" ]], // Sort by stock ascending (low stock first)
        "language": {
            "lengthMenu": "Tampilkan _MENU_ produk per halaman",
            "zeroRecords": "Tidak ada produk ditemukan",
            "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
            "infoEmpty": "Tidak ada produk tersedia",
            "infoFiltered": "(difilter dari _MAX_ total produk)",
            "search": "Cari:",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        },
        "columnDefs": [
            {
                "targets": [0, 7], // Product name and actions columns
                "orderable": false
            }
        ]
    });

    // Highlight low stock rows
    $('#inventoryTable tbody tr').each(function() {
        const stockCell = $(this).find('td:eq(2)');
        const stockBadge = stockCell.find('.badge');
        
        if (stockBadge.hasClass('badge-warning') || stockBadge.hasClass('badge-danger')) {
            $(this).addClass('table-warning');
        }
    });
});
</script>
@endpush
@endsection