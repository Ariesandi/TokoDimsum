@extends('layouts.admin')

@section('title', 'Laporan Penjualan - Admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Laporan Penjualan</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Laporan Penjualan</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.reports.export-sales', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
               class="btn btn-success me-2">
                <i class="fas fa-download"></i> Export CSV
            </a>
            <a href="{{ route('admin.reports.inventory') }}" class="btn btn-info me-2">
                <i class="fas fa-boxes"></i> Laporan Stok
            </a>
            <a href="{{ route('admin.reports.customers') }}" class="btn btn-primary">
                <i class="fas fa-users"></i> Laporan Pelanggan
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.sales') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="{{ $startDate }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="{{ $endDate }}" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.reports.sales') }}" class="btn btn-secondary">
                            <i class="fas fa-refresh"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sales Summary -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Pesanan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $salesSummary->sum('total_orders') }}
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
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Pendapatan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($salesSummary->sum('total_revenue'), 0, ',', '.') }}
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Rata-rata Pesanan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($salesSummary->avg('average_order_value') ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                Produk Terjual
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $productSales->sum('total_sold') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Chart -->
    @if($salesSummary->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Grafik Penjualan Harian</h6>
        </div>
        <div class="card-body">
            <canvas id="salesChart" width="400" height="100"></canvas>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Product Sales -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Penjualan per Produk</h6>
                </div>
                <div class="card-body">
                    @if($productSales->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga</th>
                                        <th>Terjual</th>
                                        <th>Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($productSales as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $product->total_sold }}</span>
                                        </td>
                                        <td>
                                            <strong>Rp {{ number_format($product->total_revenue, 0, ',', '.') }}</strong>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada data penjualan</h5>
                            <p class="text-muted">Belum ada penjualan pada periode ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Category Sales -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Penjualan per Kategori</h6>
                </div>
                <div class="card-body">
                    @if($categorySales->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Kategori</th>
                                        <th>Terjual</th>
                                        <th>Pendapatan</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalCategoryRevenue = $categorySales->sum('total_revenue'); @endphp
                                    @foreach($categorySales as $category)
                                    <tr>
                                        <td>{{ $category->category_name }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $category->total_sold }}</span>
                                        </td>
                                        <td>
                                            <strong>Rp {{ number_format($category->total_revenue, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            @php 
                                                $percentage = $totalCategoryRevenue > 0 ? ((float)$category->total_revenue / $totalCategoryRevenue) * 100 : 0;
                                            @endphp
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: {{ $percentage }}%" 
                                                     aria-valuenow="{{ $percentage }}" 
                                                     aria-valuemin="0" aria-valuemax="100">
                                                    {{ number_format($percentage, 1) }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada data kategori</h5>
                            <p class="text-muted">Belum ada penjualan pada periode ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Sales Summary -->
    @if($salesSummary->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Ringkasan Penjualan Harian</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Total Pesanan</th>
                            <th>Total Pendapatan</th>
                            <th>Rata-rata Pesanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salesSummary as $summary)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($summary->order_date)->format('d M Y') }}</td>
                            <td>
                                <span class="badge badge-primary">{{ $summary->total_orders }}</span>
                            </td>
                            <td>
                                <strong>Rp {{ number_format($summary->total_revenue, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                Rp {{ number_format($summary->average_order_value, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-info">
                            <th>Total</th>
                            <th>
                                <span class="badge badge-primary">{{ $salesSummary->sum('total_orders') }}</span>
                            </th>
                            <th>
                                <strong>Rp {{ number_format($salesSummary->sum('total_revenue'), 0, ',', '.') }}</strong>
                            </th>
                            <th>
                                Rp {{ number_format($salesSummary->avg('average_order_value') ?? 0, 0, ',', '.') }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if($salesSummary->count() > 0)
// Sales Chart
const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($salesSummary->pluck('order_date')->map(function($date) { return \Carbon\Carbon::parse($date)->format('d M'); })) !!},
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: {!! json_encode($salesSummary->pluck('total_revenue')) !!},
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Jumlah Pesanan',
            data: {!! json_encode($salesSummary->pluck('total_orders')) !!},
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Pendapatan (Rp)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Jumlah Pesanan'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        },
        plugins: {
            title: {
                display: true,
                text: 'Grafik Penjualan Periode {!! \Carbon\Carbon::parse($startDate)->format("d M Y") !!} - {!! \Carbon\Carbon::parse($endDate)->format("d M Y") !!}'
            }
        }
    }
});
@endif

// Date validation
document.getElementById('start_date').addEventListener('change', function() {
    const startDate = this.value;
    const endDateInput = document.getElementById('end_date');
    
    if (startDate) {
        endDateInput.min = startDate;
        if (endDateInput.value && endDateInput.value < startDate) {
            endDateInput.value = startDate;
        }
    }
});

document.getElementById('end_date').addEventListener('change', function() {
    const endDate = this.value;
    const startDateInput = document.getElementById('start_date');
    
    if (endDate) {
        startDateInput.max = endDate;
        if (startDateInput.value && startDateInput.value > endDate) {
            startDateInput.value = endDate;
        }
    }
});
</script>
@endpush
@endsection