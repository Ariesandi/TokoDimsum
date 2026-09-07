@extends('layouts.admin')

@section('title', 'Laporan Pelanggan - Admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Laporan Pelanggan</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Laporan Pelanggan</li>
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
            <form method="GET" action="{{ route('admin.reports.customers') }}">
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
                        <label for="customer_type">Tipe Pelanggan</label>
                        <select class="form-control" id="customer_type" name="customer_type">
                            <option value="">Semua Pelanggan</option>
                            <option value="new" {{ request('customer_type') == 'new' ? 'selected' : '' }}>Pelanggan Baru</option>
                            <option value="returning" {{ request('customer_type') == 'returning' ? 'selected' : '' }}>Pelanggan Lama</option>
                            <option value="vip" {{ request('customer_type') == 'vip' ? 'selected' : '' }}>Pelanggan VIP</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.reports.customers') }}" class="btn btn-secondary">
                            <i class="fas fa-refresh"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Customer Summary -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Pelanggan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $totalCustomers }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                Pelanggan Aktif
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $activeCustomers }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                                Pelanggan Baru (Bulan Ini)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $newCustomersThisMonth }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
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
                                Rata-rata Pembelian
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($averageOrderValue, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                    <h6 class="m-0 font-weight-bold text-primary">Registrasi Pelanggan Bulanan</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="customerRegistrationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Distribusi Tipe Pelanggan</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="customerTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Top 10 Pelanggan Terbaik</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Ranking</th>
                            <th>Pelanggan</th>
                            <th>Email</th>
                            <th>Total Pesanan</th>
                            <th>Total Pembelian</th>
                            <th>Rata-rata Pesanan</th>
                            <th>Terakhir Pesan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCustomers as $index => $customer)
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
                                    @if($customer->avatar)
                                        <img src="{{ Storage::url($customer->avatar) }}" 
                                             alt="{{ $customer->name }}" 
                                             class="rounded-circle me-2" 
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px; color: white; font-size: 16px;">
                                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $customer->name }}</strong>
                                        @if($customer->phone)
                                            <br><small class="text-muted">{{ $customer->phone }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $customer->email }}</td>
                            <td>
                                <span class="badge badge-primary">{{ $customer->orders_count }}</span>
                            </td>
                            <td>
                                <strong>Rp {{ number_format($customer->total_spent, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                Rp {{ number_format($customer->average_order, 0, ',', '.') }}
                            </td>
                            <td>
                                @if($customer->last_order_date)
                                    {{ $customer->last_order_date->format('d M Y') }}
                                @else
                                    <span class="text-muted">Belum pernah</span>
                                @endif
                            </td>
                            <td>
                                @if($customer->orders_count >= 10)
                                    <span class="badge badge-warning">VIP</span>
                                @elseif($customer->orders_count >= 5)
                                    <span class="badge badge-success">Loyal</span>
                                @elseif($customer->orders_count >= 2)
                                    <span class="badge badge-info">Regular</span>
                                @else
                                    <span class="badge badge-secondary">Baru</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- All Customers -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Semua Pelanggan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="customersTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Pelanggan</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Tanggal Daftar</th>
                            <th>Total Pesanan</th>
                            <th>Total Pembelian</th>
                            <th>Terakhir Aktif</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($customer->avatar)
                                        <img src="{{ Storage::url($customer->avatar) }}" 
                                             alt="{{ $customer->name }}" 
                                             class="rounded-circle me-2" 
                                             style="width: 35px; height: 35px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                             style="width: 35px; height: 35px; color: white; font-size: 14px;">
                                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $customer->name }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->phone ?? '-' }}</td>
                            <td>{{ $customer->created_at->format('d M Y') }}</td>
                            <td>
                                <span class="badge badge-primary">{{ $customer->orders_count ?? 0 }}</span>
                            </td>
                            <td>
                                <strong>Rp {{ number_format($customer->total_spent ?? 0, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                @if($customer->last_login_at)
                                    {{ $customer->last_login_at->diffForHumans() }}
                                @else
                                    <span class="text-muted">Belum pernah login</span>
                                @endif
                            </td>
                            <td>
                                @if(($customer->orders_count ?? 0) >= 10)
                                    <span class="badge badge-warning">VIP</span>
                                @elseif(($customer->orders_count ?? 0) >= 5)
                                    <span class="badge badge-success">Loyal</span>
                                @elseif(($customer->orders_count ?? 0) >= 2)
                                    <span class="badge badge-info">Regular</span>
                                @else
                                    <span class="badge badge-secondary">Baru</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.users.show', $customer->id) }}" 
                                       class="btn btn-sm btn-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $customer->id) }}" 
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
    $('#customersTable').DataTable({
        "pageLength": 25,
        "order": [[ 5, "desc" ]], // Sort by total spent descending
        "language": {
            "lengthMenu": "Tampilkan _MENU_ pelanggan per halaman",
            "zeroRecords": "Tidak ada pelanggan ditemukan",
            "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
            "infoEmpty": "Tidak ada pelanggan tersedia",
            "infoFiltered": "(difilter dari _MAX_ total pelanggan)",
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
                "targets": [0, 8], // Customer name and actions columns
                "orderable": false
            }
        ]
    });

    // Customer Registration Chart
    const registrationCtx = document.getElementById('customerRegistrationChart').getContext('2d');
    new Chart(registrationCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyRegistrations->pluck('month')) !!},
            datasets: [{
                label: 'Registrasi Pelanggan',
                data: {!! json_encode($monthlyRegistrations->pluck('count')) !!},
                borderColor: 'rgb(78, 115, 223)',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
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
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Customer Type Chart
    const typeCtx = document.getElementById('customerTypeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pelanggan Baru', 'Pelanggan Regular', 'Pelanggan Loyal', 'Pelanggan VIP'],
            datasets: [{
                data: [
                    {!! $customerTypes['new'] ?? 0 !!},
                    {!! $customerTypes['regular'] ?? 0 !!},
                    {!! $customerTypes['loyal'] ?? 0 !!},
                    {!! $customerTypes['vip'] ?? 0 !!}
                ],
                backgroundColor: [
                    '#6c757d',
                    '#17a2b8',
                    '#28a745',
                    '#ffc107'
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