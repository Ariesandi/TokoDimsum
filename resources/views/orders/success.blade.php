@extends('layouts.app')

@section('title', 'Order Success - Dimsum Mamah Haura')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Message -->
            <div class="text-center mb-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle fa-5x text-success"></i>
                </div>
                <h1 class="display-4 fw-bold text-success mb-3">Pesanan Berhasil Dibuat!</h1>
                <p class="lead text-muted mb-4">
                    Thank you for your order. We've received your order and will process it shortly.
                </p>
            </div>

            @if(isset($order))
            <!-- Order Details -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt"></i> Order Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Order Number:</strong>
                            <span class="text-primary">#{{ $order->id }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Order Date:</strong>
                            {{ $order->created_at->format('d M Y, H:i') }}
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Metode Pembayaran:</strong>
                            {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <span class="badge bg-warning">{{ ucfirst($order->status) }}</span>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <strong>Total Amount:</strong>
                            <span class="h5 text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-shipping-fast"></i> Informasi Pengiriman
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Shipped to:</h6>
                            <p class="mb-1"><strong>{{ $order->customer_name ?: optional($order->user)->name ?: 'Tidak tersedia' }}</strong></p>
                            <p class="mb-1">{{ $order->customer_phone ?: optional($order->user)->phone ?: 'Tidak tersedia' }}</p>
                            <address class="mb-0">
                                {{ $order->customer_address ?: optional($order->user)->address ?: 'Tidak tersedia' }}<br>
                                {{ $order->city }}
                            </address>
                        </div>
                        <div class="col-md-6">
                            <strong>Telepon:</strong><br>
                            {{ $order->customer_phone ?: optional($order->user)->phone ?: 'Tidak tersedia' }}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <strong>Alamat Pengiriman:</strong><br>
                            {{ $order->customer_address ?: optional($order->user)->address ?: 'Tidak tersedia' }}<br>
                            {{ $order->city }}, {{ $order->postal_code }}
                        </div>
                    </div>
                    @if($order->notes)
                    <div class="row mt-2">
                        <div class="col-12">
                            <strong>Order Notes:</strong><br>
                            <em>{{ $order->notes }}</em>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Order Items
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
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
                                                <h6 class="mb-1">{{ $item->product->name }}</h6>
                                                <small class="text-muted">{{ optional($item->product->category)->name }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        Rp {{ number_format($item->unit_price ?? $item->price ?? optional($item->product)->price ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="align-middle">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="align-middle">
                                        <strong>Rp {{ number_format(($item->subtotal ?? (($item->unit_price ?? $item->price ?? optional($item->product)->price ?? 0) * $item->quantity)), 0, ',', '.') }}</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Payment Instructions -->
            @if(isset($order) && $order->payment_method === 'transfer')
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-university"></i> Instruksi Pembayaran
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <h6><i class="fas fa-info-circle"></i> Silakan selesaikan pembayaran Anda</h6>
                        <p class="mb-0">Transfer total nominal ke rekening bank di bawah dan simpan bukti transfer Anda.</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Bank:</strong> Bank Central Asia (BCA)<br>
                            <strong>Account Number:</strong> 1234567890<br>
                            <strong>Account Name:</strong> Dimsum Mamah Haura
                        </div>
                        <div class="col-md-6">
                            <strong>Amount to Transfer:</strong><br>
                            <span class="h4 text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> Silakan selesaikan pembayaran dalam 24 jam untuk menghindari pembatalan pesanan.
                        </small>
                    </div>
                </div>
            </div>
            @endif

            <!-- Next Steps -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks"></i> Langkah Selanjutnya
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <div class="mb-2">
                                <i class="fas fa-credit-card fa-2x text-primary"></i>
                            </div>
                            <h6>1. Pembayaran</h6>
                            <small class="text-muted">
                                @if(isset($order) && $order->payment_method === 'transfer')
                                    Selesaikan transfer bank
                                @else
                                    Siapkan uang tunai saat pengantaran
                                @endif
                            </small>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="mb-2">
                                <i class="fas fa-cogs fa-2x text-warning"></i>
                            </div>
                            <h6>2. Diproses</h6>
                            <small class="text-muted">Kami akan menyiapkan pesanan Anda</small>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="mb-2">
                                <i class="fas fa-truck fa-2x text-success"></i>
                            </div>
                            <h6>3. Pengiriman</h6>
                            <small class="text-muted">Pesanan Anda akan dikirimkan</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center">
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    @if(isset($order))
                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-search"></i> Lacak Pesanan
                        </a>
                    @endif
                    <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-shopping-bag"></i> Lanjut Belanja
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-home"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="text-center mt-4">
                <div class="card">
                    <div class="card-body">
                        <h6><i class="fas fa-headset"></i> Butuh Bantuan?</h6>
                        <p class="mb-2">Hubungi layanan pelanggan kami jika Anda memiliki pertanyaan tentang pesanan Anda.</p>
                        <div class="d-flex justify-content-center gap-3">
                            <span><i class="fas fa-phone text-primary"></i> +62 123 456 789</span>
                            <span><i class="fas fa-envelope text-primary"></i> support@dimsummamah.com</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh page every 30 seconds to check payment status (for bank transfer)
    const shouldAutoRefresh = JSON.parse("@json(isset($order) && $order->payment_method === 'transfer' && $order->status === 'pending')");
    if (shouldAutoRefresh) {
        setInterval(function() {
            // You can implement AJAX call to check payment status
            // For now, we'll just show a reminder
            console.log('Checking payment status...');
        }, 30000);
    }
    
    // Copy account number functionality
    $('.copy-account').on('click', function() {
        const accountNumber = '1234567890';
        navigator.clipboard.writeText(accountNumber).then(function() {
            showAlert('success', 'Account number copied to clipboard!');
        }).catch(function() {
            showAlert('warning', 'Please copy the account number manually: ' + accountNumber);
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