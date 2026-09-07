@extends('layouts.app')

@section('title', 'Order Details #' . $order->id . ' - Dimsum Mamah Haura')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-5 fw-bold">
                        <i class="fas fa-receipt text-primary"></i> Order Details
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">My Orders</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Order #{{ $order->id }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
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
                    <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }} fs-4 px-3 py-2">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Summary Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-0">
                                <i class="fas fa-hashtag"></i> Order #{{ $order->id }}
                            </h4>
                        </div>
                        <div class="col-md-6 text-end">
                            <h4 class="mb-0">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3">
                                <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                                <h6>Order Date</h6>
                                <p class="text-muted mb-0">{{ $order->created_at->format('d M Y') }}</p>
                                <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3">
                                <i class="fas fa-credit-card fa-2x text-success mb-2"></i>
                                <h6>Metode Pembayaran</h6>
                                <p class="text-muted mb-0">{{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3">
                                <i class="fas fa-shopping-bag fa-2x text-info mb-2"></i>
                                <h6>Total Item</h6>
                                <p class="text-muted mb-0">{{ $order->transactionDetails->sum('quantity') }} item</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3">
                                <i class="fas fa-truck fa-2x text-warning mb-2"></i>
                                <h6>Delivery Status</h6>
                                <p class="text-muted mb-0">
                                    @if($order->status === 'delivered')
                                        Terkirim
                                    @elseif($order->status === 'cancelled')
                                        Dibatalkan
                                    @else
                                        In Progress
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Item Pesanan ({{ $order->transactionDetails->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
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
                                                $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="60">'
                                                    . '<rect width="100%" height="100%" fill="#dc3545"/>'
                                                    . '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="10" fill="#ffffff">'
                                                    . e($productNameShort)
                                                    . '</text></svg>';
                                                $placeholderSvgDataUri = 'data:image/svg+xml;utf8,' . rawurlencode($svg);
                                            @endphp
                                            <img src="{{ $item->product->image ? asset('storage/' . $item->product->image) : $placeholderSvgDataUri }}" 
                                                 alt="{{ $item->product->name }}" class="me-3 rounded" style="width: 80px; height: 60px; object-fit: cover;">
                                            <div>
                                                <h6 class="mb-1">{{ $item->product->name }}</h6>
                                                @if($item->product->description)
                                                    <small class="text-muted">{{ Str::limit($item->product->description, 60) }}</small>
                                                @endif
                                                @if($item->product->category)
                                                    <br><span class="badge bg-light text-dark">{{ $item->product->category->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="fw-bold">Rp {{ number_format($item->unit_price ?? $item->price ?? optional($item->product)->price ?? 0, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge bg-primary fs-6">{{ $item->quantity }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="fw-bold text-success">Rp {{ number_format(($item->subtotal ?? (($item->unit_price ?? $item->price ?? optional($item->product)->price ?? 0) * $item->quantity)), 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Order Tracking Timeline -->
            <div class="card mb-4">
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

            <!-- Order Notes -->
            @if($order->notes)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-sticky-note"></i> Order Notes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ $order->notes }}
                    </div>
                </div>
            </div>
            @endif

            <!-- Payment Instructions -->
            @if($order->payment_method === 'transfer' && in_array($order->status, ['pending', 'confirmed']))
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-university"></i> Payment Instructions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Please complete your payment</h6>
                        <p class="mb-3">Transfer the exact amount to the following account:</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded">
                                    <p class="mb-1"><strong>Bank:</strong> BCA</p>
                                    <p class="mb-1"><strong>Account Number:</strong> 
                                        <span id="account-number">1234567890</span>
                                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyToClipboard('account-number')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </p>
                                    <p class="mb-1"><strong>Account Name:</strong> Dimsum Mamah Haura</p>
                                    <p class="mb-0"><strong>Amount:</strong> 
                                        <span class="text-danger fw-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded">
                                    <h6>Important Notes:</h6>
                                    <ul class="mb-0 small">
                                        <li>Transfer the exact amount</li>
                                        <li>Include order ID in transfer notes</li>
                                        <li>Payment will be verified within 1-2 hours</li>
                                        <li>Contact us if payment is not confirmed</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator"></i> Order Summary
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $subtotal = $order->transactionDetails->sum(function($d) {
                            return ($d->subtotal ?? (($d->unit_price ?? $d->price ?? optional($d->product)->price ?? 0) * $d->quantity));
                        });
                    @endphp
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal ({{ $order->transactionDetails->sum('quantity') }} items)</span>
                        <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    
                    <hr>
                    <div class="d-flex justify-content-between mb-0">
                        <strong>Total</strong>
                        <strong class="text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user"></i> Customer Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Name</label>
                        <p class="mb-0 fw-bold">{{ $order->customer_name ?: optional($order->user)->name ?: 'Tidak tersedia' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Email</label>
                        <p class="mb-0">{{ $order->customer_email ?: optional($order->user)->email ?: 'Tidak tersedia' }}</p>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-muted">Telepon</label>
                        <p class="mb-0">{{ $order->customer_phone ?: optional($order->user)->phone ?: 'Tidak tersedia' }}</p>
                    </div>
                </div>
            </div>

            <!-- Alamat Pengiriman -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-map-marker-alt"></i> Alamat Pengiriman
                    </h5>
                </div>
                <div class="card-body">
                    <address class="mb-0">
                        {{ $order->customer_address ?: optional($order->user)->address ?: 'Tidak tersedia' }}<br>
                        {{ $order->city ?: '' }}
                    </address>
                </div>
            </div>

            <!-- Payment Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card"></i> Pembayaran
                    </h5>
                    @if($order->payment_proof)
                        <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> Lihat Bukti
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Metode:</strong> {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</p>
                    <p class="mb-3">
                        <strong>Status Pembayaran:</strong>
                        @php
                            $payColors = ['pending' => 'warning', 'verified' => 'success', 'rejected' => 'danger'];
                        @endphp
                        <span class="badge bg-{{ $payColors[$order->payment_status ?? 'pending'] ?? 'secondary' }}">
                            {{ ['pending'=>'Menunggu Verifikasi','verified'=>'Terverifikasi','rejected'=>'Ditolak'][$order->payment_status ?? 'pending'] ?? ucfirst($order->payment_status ?? 'pending') }}
                        </span>
                    </p>

                    @if($order->payment_status !== 'verified' && $order->payment_method === 'transfer')
                        <form action="{{ route('orders.upload-proof', $order->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="payment_proof" class="form-label">Upload Bukti Transfer (JPG, PNG, WEBP)</label>
                                <input type="file" name="payment_proof" id="payment_proof" accept="image/*" class="form-control" required>
                                @error('payment_proof')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Catatan (opsional)</label>
                                <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Tambahkan catatan jika perlu..."></textarea>
                                @error('notes')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Upload Bukti Pembayaran
                            </button>
                        </form>
                    @elseif($order->payment_status === 'verified' && $order->tracking_number)
                        <div class="alert alert-success mt-3">
                            <i class="fas fa-check-circle"></i> Pembayaran terverifikasi. Nomor Resi: <strong>{{ $order->tracking_number }}</strong>
                        </div>
                    @endif

                    @if($order->payment_notes)
                        <div class="mt-3">
                            <small class="text-muted">
                                <strong>Admin Notes:</strong> {{ $order->payment_notes }}
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
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
                            <i class="fas fa-print"></i> Cetak Pesanan
                        </button>
                        
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Pesanan
                        </a>
                    </div>
                </div>
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
/* Timeline Styles */
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
    .btn, .modal, nav, .breadcrumb, .card-header {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
        margin-bottom: 20px !important;
    }
    
    .container {
        max-width: 100% !important;
        padding: 0 !important;
    }
    
    .col-lg-4 .card:last-child {
        display: none !important;
    }
}

.table th {
    border-top: none;
}

.badge {
    font-size: 0.875em;
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
        button.html('<i class="fas fa-spinner fa-spin"></i> Cancelling...').prop('disabled', true);
        
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
                    showAlert('success', 'Order cancelled successfully');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('danger', response.message || 'Error cancelling order');
                }
                button.html(originalText).prop('disabled', false);
            },
            error: function(xhr) {
                let message = 'Error cancelling order';
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
                    showAlert('success', 'Items added to cart successfully!');
                    // Update cart count
                    $('#cart-count').text(response.cartCount);
                } else {
                    showAlert('danger', response.message || 'Error adding items to cart');
                }
                button.html(originalText).prop('disabled', false);
            },
            error: function(xhr) {
                let message = 'Error adding items to cart';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('danger', message);
                button.html(originalText).prop('disabled', false);
            }
        });
    });

    // Copy to clipboard function
    window.copyToClipboard = function(elementId) {
        const element = document.getElementById(elementId);
        const text = element.textContent;
        
        navigator.clipboard.writeText(text).then(function() {
            showAlert('success', 'Account number copied to clipboard!');
        }, function(err) {
            showAlert('danger', 'Failed to copy account number');
        });
    };
    
    // Show alert function
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
            $('.alert').fadeOut();
        }, 3000);
    }
});
</script>
@endpush