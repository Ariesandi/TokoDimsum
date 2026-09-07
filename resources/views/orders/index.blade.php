@extends('layouts.app')

@section('title', 'My Orders - Dimsum Mamah Haura')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold">
                <i class="fas fa-list-alt text-primary"></i> Pesanan Saya
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pesanan Saya</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Order Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('orders.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status Pesanan</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Diproses</option>
                                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Dikirim</option>
                                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Selesai</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">Dari Tanggal</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">Sampai Tanggal</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($orders->count() > 0)
        <!-- Orders List -->
        <div class="row">
            @foreach($orders as $order)
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <strong>Order #{{ $order->id }}</strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> {{ $order->created_at->format('d M Y, H:i') }}
                                </small>
                            </div>
                            <div class="col-md-3">
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'confirmed' => 'info',
                                        'processing' => 'primary',
                                        'shipped' => 'success',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }} fs-6">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                            <div class="col-md-3 text-end">
                                <strong class="text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Order Items Preview -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <h6 class="mb-2">Item ({{ $order->transactionDetails->count() }}):</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($order->transactionDetails->take(3) as $item)
                                        <div class="d-flex align-items-center bg-light rounded p-2">
                                            @php
                                                $productNameShort = isset($item) && $item->product ? Str::limit($item->product->name, 6) : 'Item';
                                                $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="30">'
                                                    . '<rect width="100%" height="100%" fill="#dc3545"/>'
                                                    . '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="7" fill="#ffffff">'
                                                    . e($productNameShort)
                                                    . '</text></svg>';
                                                $placeholderSvgDataUri = 'data:image/svg+xml;utf8,' . rawurlencode($svg);
                                            @endphp
                                            <img src="{{ $item->product->image ? asset('storage/' . $item->product->image) : $placeholderSvgDataUri }}" 
                                                 alt="{{ $item->product->name }}" class="me-2 rounded" style="width: 40px; height: 30px; object-fit: cover;">
                                            <div>
                                                <small class="fw-bold">{{ $item->product->name }}</small><br>
                                                <small class="text-muted">{{ $item->quantity }}x</small>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($order->transactionDetails->count() > 3)
                                        <div class="d-flex align-items-center bg-light rounded p-2">
                                            <small class="text-muted">+{{ $order->transactionDetails->count() - 3 }} item lainnya</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Order Info -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <small class="text-muted">Metode Pembayaran:</small><br>
                                <span>{{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Alamat Pengiriman:</small><br>
                                <span>{{ Str::limit($order->customer_address . ', ' . $order->city, 50) }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">No. HP:</small><br>
                                <span>{{ $order->customer_phone }}</span>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye"></i> Lihat Detail
                            </a>
                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-search"></i> Lacak Pesanan
                            </a>
                            
                            @if($order->status === 'pending')
                                <button class="btn btn-outline-danger btn-sm cancel-order" data-order-id="{{ $order->id }}">
                                    <i class="fas fa-times"></i> Atur Ulang Filter
                                </button>
                            @endif
                            
                            @if($order->status === 'delivered')
                                <button class="btn btn-outline-success btn-sm reorder" data-order-id="{{ $order->id }}">
                                    <i class="fas fa-redo"></i> Pesan Lagi
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($orders->hasPages())
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    {{ $orders->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
        @endif
    @else
        <!-- No Orders -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-5x text-muted mb-4"></i>
                    <h3 class="text-muted mb-3">Tidak ada pesanan</h3>
                    @if(request()->hasAny(['status', 'date_from', 'date_to']))
                        <p class="text-muted mb-4">Tidak ada pesanan yang cocok dengan filter Anda. Coba ubah kriteria pencarian.</p>
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-primary me-2">
                            <i class="fas fa-times"></i> Reset Filter
                        </a>
                    @else
                        <p class="text-muted mb-4">Anda belum melakukan pesanan. Mulai berbelanja untuk melihat pesanan di sini.</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Mulai Belanja
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Batalkan Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin membatalkan pesanan ini?</p>
                <div class="mb-3">
                    <label for="cancel_reason" class="form-label">Alasan pembatalan (opsional):</label>
                    <textarea class="form-control" id="cancel_reason" rows="3" placeholder="Silakan berikan alasan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Pertahankan Pesanan</button>
                <button type="button" class="btn btn-danger" id="confirmCancel">Batalkan Pesanan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let orderToCancel = null;
    
    // Cancel order functionality
    $('.cancel-order').on('click', function() {
        orderToCancel = $(this).data('order-id');
        $('#cancelOrderModal').modal('show');
    });
    
    $('#confirmCancel').on('click', function() {
        if (!orderToCancel) return;
        
        const button = $(this);
        const originalText = button.html();
        button.html('<i class=\"fas fa-spinner fa-spin\"></i> Membatalkan...').prop('disabled', true);
        
        $.ajax({
            url: `/orders/${orderToCancel}/cancel`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                reason: $('#cancel_reason').val()
            },
            success: function(response) {
                if (response.success) {
                    $('#cancelOrderModal').modal('hide');
                    showAlert('success', 'Pesanan berhasil dibatalkan');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('danger', response.message || 'Terjadi kesalahan saat membatalkan pesanan');
                }
                button.html(originalText).prop('disabled', false);
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat membatalkan pesanan';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('danger', message);
                button.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Reorder functionality
    $('.reorder').on('click', function() {
        const orderId = $(this).data('order-id');
        const button = $(this);
        const originalText = button.html();
        
        button.html('<i class=\"fas fa-spinner fa-spin\"></i> Menambahkan...').prop('disabled', true);
        
        $.ajax({
            url: `/orders/${orderId}/reorder`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Item berhasil ditambahkan ke keranjang!');
                    // Update cart count
                    $('#cart-count').text(response.cartCount);
                } else {
                    showAlert('danger', response.message || 'Terjadi kesalahan saat menambahkan ke keranjang');
                }
                button.html(originalText).prop('disabled', false);
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat menambahkan ke keranjang';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('danger', message);
                button.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Auto-submit filter form on change
    $('#status').on('change', function() {
        $(this).closest('form').submit();
    });
    
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; max-width: 350px;" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $(alertHtml).appendTo('body');
        
        setTimeout(function() {
            $('.alert').alert('close');
        }, 4000);
    }
});
</script>
@endpush