@extends('layouts.app')

@section('title', 'Lacak Pesanan #' . $order->id . ' - Dimsum Mamah Haura')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold">
                <i class="fas fa-search text-primary"></i> Lacak Pesanan
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">My Orders</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Lacak Pesanan #{{ $order->id }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">
                                <i class="fas fa-receipt"></i> Order #{{ $order->id }}
                            </h5>
                        </div>
                        <div class="col-md-6 text-end">
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
                                {{ ['pending'=>'Menunggu','confirmed'=>'Dikonfirmasi','processing'=>'Diproses','shipped'=>'Dikirim','delivered'=>'Selesai','cancelled'=>'Dibatalkan'][$order->status] ?? ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Order Date:</strong><br>
                            <span class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Total Amount:</strong><br>
                            <span class="text-primary fw-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Metode Pembayaran:</strong><br>
                            <span class="text-muted">{{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Estimated Delivery:</strong><br>
                            <span class="text-muted">
                                @if($order->status === 'delivered')
                                    Delivered
                                @elseif($order->status === 'cancelled')
                                    Cancelled
                                @else
                                    {{ $order->created_at->addDays(3)->format('d M Y') }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Tracking Timeline -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-route"></i> Order Progress
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $statuses = [
                            'pending' => ['icon' => 'clock', 'title' => 'Order Placed', 'desc' => 'Your order has been received and is being reviewed'],
                            'confirmed' => ['icon' => 'check-circle', 'title' => 'Order Confirmed', 'desc' => 'Your order has been confirmed and payment verified'],
                            'processing' => ['icon' => 'cog', 'title' => 'Preparing Order', 'desc' => 'Your dimsum is being prepared with love'],
                            'shipped' => ['icon' => 'truck', 'title' => 'Order Shipped', 'desc' => 'Your order is on the way to your address'],
                            'delivered' => ['icon' => 'home', 'title' => 'Pesanan Diterima', 'desc' => 'Pesanan Anda berhasil diterima']
                        ];
                        
                        $currentStatusIndex = array_search($order->status, array_keys($statuses));
                        $isCancelled = $order->status === 'cancelled';
                    @endphp
                    
                    @if($isCancelled)
                        <!-- Cancelled Status -->
                        <div class="timeline">
                            <div class="timeline-item completed">
                                <div class="timeline-marker bg-success">
                                    <i class="fas fa-clock text-white"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Order Placed</h6>
                                    <p class="text-muted mb-1">Your order was successfully placed</p>
                                    <small class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</small>
                                </div>
                            </div>
                            <div class="timeline-item cancelled">
                                <div class="timeline-marker bg-danger">
                                    <i class="fas fa-times text-white"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1 text-danger">Order Cancelled</h6>
                                    <p class="text-muted mb-1">Your order has been cancelled</p>
                                    <small class="text-muted">{{ $order->updated_at->format('d M Y, H:i') }}</small>
                                    @if($order->cancel_reason)
                                        <div class="mt-2">
                                            <small class="text-muted"><strong>Reason:</strong> {{ $order->cancel_reason }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Normal Status Timeline -->
                        <div class="timeline">
                            @foreach($statuses as $statusKey => $statusInfo)
                                @php
                                    $statusIndex = array_search($statusKey, array_keys($statuses));
                                    $isCompleted = $statusIndex <= $currentStatusIndex;
                                    $isCurrent = $statusIndex === $currentStatusIndex;
                                @endphp
                                
                                <div class="timeline-item {{ $isCompleted ? 'completed' : '' }} {{ $isCurrent ? 'current' : '' }}">
                                    <div class="timeline-marker {{ $isCompleted ? 'bg-success' : ($isCurrent ? 'bg-primary' : 'bg-light') }}">
                                        <i class="fas fa-{{ $statusInfo['icon'] }} {{ $isCompleted || $isCurrent ? 'text-white' : 'text-muted' }}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1 {{ $isCurrent ? 'text-primary' : '' }}">{{ $statusInfo['title'] }}</h6>
                                        <p class="text-muted mb-1">{{ $statusInfo['desc'] }}</p>
                                        @if($isCompleted)
                                            <small class="text-muted">
                                                @if($statusKey === 'pending')
                                                    {{ $order->created_at->format('d M Y, H:i') }}
                                                @else
                                                    {{ $order->updated_at->format('d M Y, H:i') }}
                                                @endif
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Shipping Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-map-marker-alt"></i> Alamat Pengiriman
                    </h5>
                </div>
                <div class="card-body">
                    <address class="mb-0">
                        <strong>{{ $order->customer_name ?: optional($order->user)->name ?: 'Tidak tersedia' }}</strong><br>
                        {{ $order->customer_address ?: optional($order->user)->address ?: 'Tidak tersedia' }}<br>
                        {{ $order->city ?: '' }}<br>
                        <strong>Telepon:</strong> {{ $order->customer_phone ?: optional($order->user)->phone ?: 'Tidak tersedia' }}<br>
                        <strong>Email:</strong> {{ $order->customer_email ?: optional($order->user)->email ?: 'Tidak tersedia' }}
                    </address>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card"></i> Payment Information
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Metode Pembayaran:</strong> {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</p>
                    <p><strong>Total Amount:</strong> <span class="text-primary fw-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span></p>
                    
                    @if($order->payment_method === 'transfer' && $order->status === 'pending')
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Payment Instructions</h6>
                            <p class="mb-2">Please transfer to:</p>
                            <p class="mb-1"><strong>Bank:</strong> BCA</p>
                            <p class="mb-1"><strong>Account:</strong> 1234567890</p>
                            <p class="mb-1"><strong>Name:</strong> Dimsum Mamah Haura</p>
                            <p class="mb-0"><strong>Amount:</strong> Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-bag"></i> Order Items ({{ $order->transactionDetails->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->transactionDetails as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @php
                                                $productNameShort = isset($item) && $item->product ? Str::limit($item->product->name, 10) : 'Product';
                                                $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="60" height="45">'
                                                    . '<rect width="100%" height="100%" fill="#dc3545"/>'
                                                    . '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="9" fill="#ffffff">'
                                                    . e($productNameShort)
                                                    . '</text></svg>';
                                                $placeholderSvgDataUri = 'data:image/svg+xml;utf8,' . rawurlencode($svg);
                                            @endphp
                                            <img src="{{ $item->product->image ? asset('storage/' . $item->product->image) : $placeholderSvgDataUri }}" 
                                                 alt="{{ $item->product->name }}" class="me-3" style="width: 60px; height: 45px; object-fit: cover; border-radius: 5px;">
                                            <div>
                                                <h6 class="mb-0">{{ $item->product->name }}</h6>
                                                @if($item->product->description)
                                                    <small class="text-muted">{{ Str::limit($item->product->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>Rp {{ number_format($item->unit_price ?? $item->price ?? optional($item->product)->price ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td><strong>Rp {{ number_format(($item->subtotal ?? (($item->unit_price ?? $item->price ?? optional($item->product)->price ?? 0) * $item->quantity)), 0, ',', '.') }}</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <th colspan="3">Total</th>
                                    <th class="text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Notes -->
    @if($order->notes)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-sticky-note"></i> Catatan Pesanan
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $order->notes }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-primary">
                    <i class="fas fa-eye"></i> Lihat Detail Lengkap
                </a>
                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Pesanan
                </a>
                
                @if($order->status === 'pending')
                    <button class="btn btn-outline-danger cancel-order" data-order-id="{{ $order->id }}">
                        <i class="fas fa-times"></i> Batalkan Pesanan
                    </button>
                @endif
                
                @if($order->status === 'delivered')
                    <button class="btn btn-outline-success reorder" data-order-id="{{ $order->id }}">
                        <i class="fas fa-redo"></i> Pesan Lagi
                    </button>
                @endif
                
                <button class="btn btn-outline-info" onclick="window.print()">
                    <i class="fas fa-print"></i> Cetak
                </button>
            </div>
        </div>
    </div>
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

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #fff;
    box-shadow: 0 0 0 3px #e9ecef;
}

.timeline-item.completed .timeline-marker {
    box-shadow: 0 0 0 3px #28a745;
}

.timeline-item.current .timeline-marker {
    box-shadow: 0 0 0 3px #007bff;
    animation: pulse 2s infinite;
}

.timeline-item.cancelled .timeline-marker {
    box-shadow: 0 0 0 3px #dc3545;
}

.timeline-content {
    padding-left: 15px;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 3px #007bff;
    }
    50% {
        box-shadow: 0 0 0 8px rgba(0, 123, 255, 0.3);
    }
    100% {
        box-shadow: 0 0 0 3px #007bff;
    }
}

@media print {
    .btn, .modal, nav, .card-header {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endpush

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
        button.html('<i class="fas fa-spinner fa-spin"></i> Membatalkan...').prop('disabled', true);
        
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
        
        button.html('<i class="fas fa-spinner fa-spin"></i> Adding...').prop('disabled', true);
        
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