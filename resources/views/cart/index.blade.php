@extends('layouts.app')

@section('title', 'Keranjang Belanja - Dimsum Mamah Haura')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold">
                <i class="fas fa-shopping-cart text-primary"></i> Keranjang Belanja
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Keranjang Belanja</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Use cartItems from controller instead of accessing session directly --}}
    @if(isset($cartItems) && count($cartItems) > 0)
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Item Keranjang ({{ count($cartItems) }})</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $computedTotal = 0; @endphp
                                    @foreach($cartItems as $item)
                                        @php 
                                            $product = $item['product'];
                                            $quantity = $item['quantity'];
                                            $subtotal = $item['subtotal'];
                                            $computedTotal += $subtotal; 
                                        @endphp
                                        <tr data-id="{{ $product->id }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @php
                                                        $nameShort = Str::limit($product->name, 8);
                                                        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="60">'
                                                            . '<rect width="100%" height="100%" fill="#dc3545"/>'
                                                            . '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="10" fill="#ffffff">'
                                                            . e($nameShort)
                                                            . '</text></svg>';
                                                        $placeholderSvg = 'data:image/svg+xml;utf8,' . rawurlencode($svg);
                                                    @endphp
                                                    <img src="{{ $product->image ? asset('storage/' . $product->image) : $placeholderSvg }}" 
                                                         alt="{{ $product->name }}" class="me-3" style="width: 80px; height: 60px; object-fit: cover; border-radius: 5px;">
                                                    <div>
                                                        <h6 class="mb-1">{{ $product->name }}</h6>
                                                        <small class="text-muted">{{ optional($product->category)->name ?? 'Tanpa Kategori' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <span class="fw-bold text-primary">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                            </td>
                                            <td class="align-middle">
                                                <div class="input-group" style="width: 120px;">
                                                    <button class="btn btn-outline-secondary btn-sm update-cart" 
                                                            data-id="{{ $product->id }}" data-action="decrease" type="button">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" class="form-control form-control-sm text-center quantity-input" 
                                                           value="{{ $quantity }}" min="1" data-id="{{ $product->id }}" readonly>
                                                    <button class="btn btn-outline-secondary btn-sm update-cart" 
                                                            data-id="{{ $product->id }}" data-action="increase" type="button">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <span class="fw-bold subtotal">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                            </td>
                                            <td class="align-middle">
                                                <button class="btn btn-outline-danger btn-sm remove-from-cart" data-id="{{ $product->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Cart Actions -->
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left"></i> Lanjut Belanja
                                </a>
                            </div>
                            <div class="col-md-6 text-end">
                                <form action="{{ route('cart.clear') }}" method="POST" class="d-inline" id="clearCartForm">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-trash"></i> Kosongkan Keranjang
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Ringkasan Pesanan</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span id="cart-subtotal">Rp {{ number_format($computedTotal ?? ($total ?? 0), 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span class="text-success">Gratis</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Pajak (10%):</span>
                            <span id="cart-tax">Rp {{ number_format(($computedTotal ?? ($total ?? 0)) * 0.1, 0, ',', '.') }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong class="text-primary" id="cart-total">Rp {{ number_format(($computedTotal ?? ($total ?? 0)) * 1.1, 0, ',', '.') }}</strong>
                        </div>
                        
                        <div class="d-grid">
                            <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-credit-card"></i> Lanjut ke Pembayaran
                            </a>
                        </div>
                        
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt"></i> Checkout aman dengan enkripsi SSL
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Promo Code -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Kode Promo</h6>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Masukkan kode promo">
                                <button class="btn btn-outline-secondary" type="button">
                                    Terapkan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Empty Cart -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
                    <h3 class="text-muted mb-3">Keranjang Anda kosong</h3>
                    <p class="text-muted mb-4">Sepertinya Anda belum menambahkan item ke keranjang.</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag"></i> Mulai Belanja
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Update cart quantity
    $('.update-cart').on('click', function() {
        const button = $(this);
        const productId = button.data('id');
        const action = button.data('action');
        const quantityInput = $(`.quantity-input[data-id="${productId}"]`);
        let currentQuantity = parseInt(quantityInput.val());
        
        if (action === 'increase') {
            currentQuantity++;
        } else if (action === 'decrease' && currentQuantity > 1) {
            currentQuantity--;
        } else {
            return; // Don't allow quantity to go below 1
        }
        
        // Show loading state
        button.prop('disabled', true);
        
        $.ajax({
            url: '{{ url("cart/update") }}/' + productId,
            method: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                quantity: currentQuantity
            },
            success: function(response) {
                if (response.success) {
                    // Update quantity input
                    quantityInput.val(currentQuantity);
                    
                    // Update subtotal for this row
                    const row = $(`tr[data-id="${productId}"]`);
                    row.find('.subtotal').text('Rp ' + response.subtotal.toLocaleString('id-ID'));
                    
                    // Update cart totals
                    updateCartTotals(response.cartTotal);
                    
                    // Update cart count in navbar
                    $('#cart-count').text(response.cartCount);
                    
                    showAlert('success', 'Keranjang berhasil diperbarui');
                } else {
                    showAlert('danger', response.message || 'Terjadi kesalahan saat memperbarui keranjang');
                }
                button.prop('disabled', false);
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat memperbarui keranjang';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('danger', message);
                button.prop('disabled', false);
            }
        });
    });
    
    // Remove item from cart
    $('.remove-from-cart').on('click', function() {
        const button = $(this);
        const productId = button.data('id');
        
        if (!confirm('Apakah Anda yakin ingin menghapus item ini dari keranjang?')) {
            return;
        }
        
        button.prop('disabled', true);
        
        $.ajax({
            url: '{{ url("cart/remove") }}/' + productId,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Remove the row
                    $(`tr[data-id="${productId}"]`).fadeOut(300, function() {
                        $(this).remove();
                        
                        // Check if cart is empty
                        if (response.cartCount === 0) {
                            location.reload();
                        } else {
                            // Update cart totals
                            updateCartTotals(response.cartTotal);
                            
                            // Update cart count in navbar
                            $('#cart-count').text(response.cartCount);
                        }
                    });
                    
                    showAlert('success', 'Item berhasil dihapus dari keranjang');
                } else {
                    showAlert('danger', response.message || 'Terjadi kesalahan saat menghapus item');
                    button.prop('disabled', false);
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat menghapus item';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('danger', message);
                button.prop('disabled', false);
            }
        });
    });
    
    // Clear cart
    $('#clearCartForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!confirm('Apakah Anda yakin ingin mengosongkan seluruh keranjang?')) {
            return;
        }
        
        const form = $(this);
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    showAlert('danger', response.message || 'Terjadi kesalahan saat mengosongkan keranjang');
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat mengosongkan keranjang';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('danger', message);
            }
        });
    });
    
    function updateCartTotals(cartTotal) {
        const tax = cartTotal * 0.1;
        const total = cartTotal + tax;
        
        $('#cart-subtotal').text('Rp ' + cartTotal.toLocaleString('id-ID'));
        $('#cart-tax').text('Rp ' + tax.toLocaleString('id-ID'));
        $('#cart-total').text('Rp ' + total.toLocaleString('id-ID'));
    }
    
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
        }, 3000);
    }
});
</script>
@endpush