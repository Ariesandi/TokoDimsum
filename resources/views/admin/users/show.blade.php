@extends('layouts.admin')

@section('title', 'Detail Pengguna - ' . $user->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Detail Pengguna</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Pengguna</a></li>
                    <li class="breadcrumb-item active">{{ $user->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- User Profile -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Profil Pengguna</h6>
                </div>
                <div class="card-body text-center">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}" 
                             alt="Avatar" class="rounded-circle mb-3" width="120" height="120">
                    @else
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                             style="width: 120px; height: 120px; color: white; font-size: 48px; font-weight: bold;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    
                    <h5 class="font-weight-bold">{{ $user->name }}</h5>
                    <p class="text-muted mb-2">{{ $user->email }}</p>
                    
                    @if($user->role == 'admin')
                        <span class="badge badge-danger mb-3">Admin</span>
                    @else
                        <span class="badge badge-primary mb-3">Customer</span>
                    @endif
                    
                    @if($user->email_verified_at)
                        <div class="text-success mb-2">
                            <i class="fas fa-check-circle"></i> Email Terverifikasi
                        </div>
                    @else
                        <div class="text-warning mb-2">
                            <i class="fas fa-exclamation-circle"></i> Email Belum Terverifikasi
                        </div>
                    @endif
                    
                    <small class="text-muted">Bergabung {{ $user->created_at->format('d F Y') }}</small>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Kontak</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Telepon:</strong><br>
                        {{ $user->phone ?? 'Tidak ada' }}
                    </div>
                    <div class="mb-3">
                        <strong>Alamat:</strong><br>
                        {{ $user->address ?? 'Tidak ada' }}
                    </div>
                    <div class="mb-3">
                        <strong>Tanggal Registrasi:</strong><br>
                        {{ $user->created_at->format('d F Y, H:i') }}
                    </div>
                    <div>
                        <strong>Terakhir Update:</strong><br>
                        {{ $user->updated_at->format('d F Y, H:i') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics and Orders -->
        <div class="col-lg-8">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Pesanan
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_orders'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Total Belanja
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($stats['total_spent'] ?? 0, 0, ',', '.') }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Rata-rata Order
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($stats['average_order'] ?? 0, 0, ',', '.') }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Pesanan Aktif
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_orders'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Riwayat Pesanan Terbaru</h6>
                </div>
                <div class="card-body">
                    @if($recentOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID Pesanan</th>
                                        <th>Tanggal</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                    <tr>
                                        <td>#{{ $order->id }}</td>
                                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @foreach($order->transactionDetails->take(2) as $detail)
                                                <small class="d-block">{{ $detail->quantity }}x {{ $detail->product->name }}</small>
                                            @endforeach
                                            @if($order->transactionDetails->count() > 2)
                                                <small class="text-muted">+{{ $order->transactionDetails->count() - 2 }} lainnya</small>
                                            @endif
                                        </td>
                                        <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                        <td>
                                            @switch($order->status)
                                                @case('pending')
                                                    <span class="badge badge-warning">Pending</span>
                                                    @break
                                                @case('confirmed')
                                                    <span class="badge badge-info">Dikonfirmasi</span>
                                                    @break
                                                @case('processing')
                                                    <span class="badge badge-primary">Diproses</span>
                                                    @break
                                                @case('ready')
                                                    <span class="badge badge-success">Siap</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge badge-success">Selesai</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge badge-danger">Dibatalkan</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-secondary">{{ ucfirst($order->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order) }}" 
                                               class="btn btn-sm btn-info" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($user->transactions()->count() > 10)
                            <div class="text-center mt-3">
                                <a href="{{ route('admin.orders.index', ['search' => $user->email]) }}" 
                                   class="btn btn-outline-primary">
                                    Lihat Semua Pesanan
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-cart fa-3x text-gray-300 mb-3"></i>
                            <h5 class="text-gray-600">Belum Ada Pesanan</h5>
                            <p class="text-gray-500">Pengguna ini belum pernah melakukan pemesanan.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Monthly Orders Chart -->
            @if(isset($monthlyOrders) && count($monthlyOrders) > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Grafik Pesanan Bulanan</h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyOrdersChart" width="400" height="200"></canvas>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="font-weight-bold text-primary mb-0">Aksi Pengguna</h6>
                        <div>
                            @if($user->role != 'admin')
                                <form action="{{ route('admin.users.toggle-role', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-info me-2" 
                                            onclick="return confirm('Yakin ingin mengubah role pengguna ini menjadi admin?')">
                                        <i class="fas fa-user-shield"></i> Jadikan Admin
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning me-2" 
                                            onclick="return confirm('Yakin ingin reset password pengguna ini?')">
                                        <i class="fas fa-key"></i> Reset Password
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('Yakin ingin menghapus pengguna ini? Aksi ini tidak dapat dibatalkan.')">
                                        <i class="fas fa-trash"></i> Hapus Pengguna
                                    </button>
                                </form>
                            @else
                                <span class="text-muted">Admin tidak dapat dihapus atau diubah rolenya</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@if(isset($monthlyOrders) && count($monthlyOrders) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Monthly Orders Chart
    const ctx = document.getElementById('monthlyOrdersChart').getContext('2d');
    const monthlyOrdersChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_keys($monthlyOrders)) !!},
            datasets: [{
                label: 'Jumlah Pesanan',
                data: {!! json_encode(array_values($monthlyOrders)) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Pesanan Bulanan'
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
});
</script>
@endif
@endpush
@endsection