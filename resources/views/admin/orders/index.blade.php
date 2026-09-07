@extends('layouts.admin')

@section('title', 'Manajemen Transaksi - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Manajemen Transaksi</h4>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button class="btn btn-success" onclick="exportOrders()">
                            <i class="fas fa-download"></i> Ekspor
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Total Pesanan</h6>
                                            <h4 class="mb-0">{{ $stats['total_orders'] ?? 0 }}</h4>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-shopping-cart fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Selesai</h6>
                                            <h4 class="mb-0">{{ $stats['completed_orders'] ?? 0 }}</h4>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Menunggu</h6>
                                            <h4 class="mb-0">{{ $stats['pending_orders'] ?? 0 }}</h4>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Total Pendapatan</h6>
                                            <h4 class="mb-0">Rp {{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}</h4>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-money-bill-wave fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Filter -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('admin.orders.index') }}" class="d-flex">
                                <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan ID, nama, email, atau telepon..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary ms-2">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary ms-2" title="Hapus Pencarian">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </form>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end gap-2">
                                <select class="form-select" style="width: auto;" onchange="filterByStatus(this.value)">
                                    <option value="">Semua Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>Ready</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Pelanggan</th>
                                    <th>Item</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Pembayaran</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td>
                                            <strong>#{{ $order->id }}</strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $order->customer_name ?? $order->user->name ?? 'Tidak tersedia' }}</strong><br>
                                                <small class="text-muted">{{ $order->customer_email ?? $order->user->email ?? 'Tidak tersedia' }}</small><br>
                                                <small class="text-muted">{{ $order->customer_phone ?? 'Tidak tersedia' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $order->transactionDetails->count() }} items</span>
                                            <br>
                                            <small class="text-muted">{{ $order->transactionDetails->sum('quantity') }} qty</small>
                                        </td>
                                        <td>
                                            <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'confirmed' => 'info',
                                                    'processing' => 'primary',
                                                    'ready' => 'success',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                                $statusColor = $statusColors[$order->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $statusColor }}">{{ ucfirst($order->status) }}</span>
                                        </td>
                                        <td>
                                            @if($order->payment_status)
                                                <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($order->payment_status) }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">Tidak Ada</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $order->created_at->format('d M Y') }}</small><br>
                                            <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-info" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($order->status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-success" onclick="updateOrderStatus({{ $order->id }}, 'confirmed')" title="Konfirmasi Pesanan">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif
                                                @if(in_array($order->status, ['confirmed', 'processing']))
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="updateOrderStatus({{ $order->id }}, 'ready')" title="Tandai Siap">
                                                        <i class="fas fa-clock"></i>
                                                    </button>
                                                @endif
                                                @if($order->status == 'ready')
                                                    <button type="button" class="btn btn-sm btn-success" onclick="updateOrderStatus({{ $order->id }}, 'completed')" title="Selesaikan Pesanan">
                                                        <i class="fas fa-check-double"></i>
                                                    </button>
                                                @endif
                                                @if(!in_array($order->status, ['completed', 'cancelled']))
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="updateOrderStatus({{ $order->id }}, 'cancelled')" title="Batalkan Pesanan">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Tidak ada transaksi ditemukan</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($orders->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $orders->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('admin.orders.index') }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_from" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_to" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount_min" class="form-label">Jumlah Minimal</label>
                                <input type="number" class="form-control" id="amount_min" name="amount_min" value="{{ request('amount_min') }}" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount_max" class="form-label">Jumlah Maksimal</label>
                                <input type="number" class="form-control" id="amount_max" name="amount_max" value="{{ request('amount_max') }}" placeholder="1000000">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="filter_status" class="form-label">Status</label>
                        <select class="form-select" id="filter_status" name="status">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>Ready</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary">Atur Ulang Filter</a>
                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function filterByStatus(status) {
    const url = new URL(window.location.href);
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    window.location.href = url.toString();
}

function updateOrderStatus(orderId, status) {
    if (confirm(`Apakah Anda yakin ingin mengubah status pesanan menjadi ${status}?`)) {
        fetch(`/admin/orders/${orderId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal mengubah status pesanan: ' + (data.message || 'Terjadi kesalahan'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal mengubah status pesanan');
        });
    }
}

function exportOrders() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'excel');
    window.location.href = `{{ route('admin.orders.index') }}?${params.toString()}`;
}

// Memuat ulang otomatis setiap 30 detik untuk pembaruan real-time
setInterval(() => {
    if (!document.hidden) {
        location.reload();
    }
}, 30000);
</script>
@endpush