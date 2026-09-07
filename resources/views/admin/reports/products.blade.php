@extends('layouts.admin')

@section('title', 'Laporan Produk - Admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Laporan Produk</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Laporan Produk</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.reports.sales') }}" class="btn btn-success me-2">
                <i class="fas fa-chart-line"></i> Laporan Penjualan
            </a>
            <a href="{{ route('admin.reports.inventory') }}" class="btn btn-warning">
                <i class="fas fa-boxes"></i> Laporan Stok
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.products') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="start_date">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="{{ request('end_date', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="category_id">Kategori</label>
                        <select class="form-control" id="category_id" name="category_id">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.reports.products') }}" class="btn btn-secondary">
                            <i class="fas fa-refresh"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Product Summary -->
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
                                {{ $totalProducts }}
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
                                Produk Terjual
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $totalSoldProducts }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Revenue Produk
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                Rata-rata Harga
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($averagePrice, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tag fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Penjualan Produk per Kategori</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status Produk</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Selling Products -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Top 10 Produk Terlaris</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Ranking</th>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Terjual</th>
                            <th>Revenue</th>
                            <th>Stok</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topProducts as $index => $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($index < 3)
                                        <i class="fas fa-trophy text-warning me-2"></i>
                                    @endif
                                    <span class="font-weight-bold">#{{ $index + 1 }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($product->image)
                                        <img src="{{ Storage::url($product->image) }}" 
                                             alt="{{ $product->name }}" 
                                             class="rounded me-2" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded me-2 d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px; color: white; font-size: 18px;">
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
                                <strong>Rp {{ number_format($product->price, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                <span class="badge badge-success">{{ $product->total_sold }}</span>
                            </td>
                            <td>
                                <strong>Rp {{ number_format($product->revenue, 0, ',', '.') }}</strong>
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
                                @if($product->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Low Performing Products -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Produk dengan Performa Rendah</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Terjual</th>
                            <th>Revenue</th>
                            <th>Stok</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowPerformingProducts as $product)
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
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge badge-danger">{{ $product->total_sold ?? 0 }}</span>
                            </td>
                            <td>
                                Rp {{ number_format($product->revenue ?? 0, 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ $product->stock }}</span>
                            </td>
                            <td>
                                {{ $product->created_at->format('d M Y') }}
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

    <!-- All Products Performance -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Performa Semua Produk</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="productsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Terjual</th>
                            <th>Revenue</th>
                            <th>Stok</th>
                            <th>Rating</th>
                            <th>Status</th>
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
                                             style="width: 35px; height: 35px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded me-2 d-flex align-items-center justify-content-center" 
                                             style="width: 35px; height: 35px; color: white; font-size: 12px;">
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
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge badge-primary">{{ $product->total_sold ?? 0 }}</span>
                            </td>
                            <td>
                                <strong>Rp {{ number_format($product->revenue ?? 0, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                @if($product->stock <= 5)
                                    <span class="badge badge-danger">{{ $product->stock }}</span>
                                @elseif($product->stock <= 10)
                                    <span class="badge badge-warning">{{ $product->stock }}</span>
                                @else
                                    <span class="badge badge-success">{{ $product->stock }}</span>
                                @endif
                            </td>
                            <td>
                                @if($product->average_rating)
                                    <div class="d-flex align-items-center">
                                        <span class="me-1">{{ number_format($product->average_rating, 1) }}</span>
                                        <div class="text-warning">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $product->average_rating)
                                                    <i class="fas fa-star"></i>
                                                @else
                                                    <i class="far fa-star"></i>
                                                @endif
                                            @endfor
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Belum ada rating</span>
                                @endif
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Nonaktif</span>
                                @endif
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#productsTable').DataTable({
        "pageLength": 25,
        "order": [[ 4, "desc" ]], // Sort by revenue descending
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
                "targets": [0, 8], // Product name and actions columns
                "orderable": false
            }
        ]
    });

    // Category Sales Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($categorySales->pluck('name')) !!},
            datasets: [{
                label: 'Total Penjualan',
                data: {!! json_encode($categorySales->pluck('total_sold')) !!},
                backgroundColor: 'rgba(78, 115, 223, 0.8)',
                borderColor: 'rgb(78, 115, 223)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Product Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Produk Aktif', 'Produk Nonaktif'],
            datasets: [{
                data: [
                    {!! $activeProducts !!},
                    {!! $inactiveProducts !!}
                ],
                backgroundColor: [
                    '#28a745',
                    '#dc3545'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endpush
@endsection