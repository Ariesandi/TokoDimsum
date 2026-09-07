@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Proses Checkout</h2>

    @if(count($cartItems) > 0)
        <form id="checkoutForm" action="{{ route('checkout.process') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-lg-8">
                    <!-- Customer Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user"></i> Informasi Pelanggan
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">No. HP *</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" required>
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12">
                                    <label for="address" class="form-label">Alamat *</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2" required>{{ old('address', auth()->user()->address ?? '') }}</textarea>
                                    @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="city" class="form-label">Kota *</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}" required>
                                    @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="postal_code" class="form-label">Kode Pos *</label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror"
                                           id="postal_code" name="postal_code" value="{{ old('postal_code') }}" required>
                                    @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Metode Pembayaran -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-money-check-alt"></i> Metode Pembayaran
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" 
                                               id="transfer" value="transfer" checked>
                                        <label class="form-check-label" for="transfer">
                                            <i class="fas fa-university text-primary"></i> Transfer Bank
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <!-- Bank Transfer Details -->
                            <div id="transfer_details" class="mt-3">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> Instruksi Transfer Bank</h6>
                                    <p class="mb-2"><strong>Bank:</strong> Bank Central Asia (BCA)</p>
                                    <p class="mb-2"><strong>No. Rekening:</strong> 1234567890</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Notes -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-sticky-note"></i> Catatan Pesanan (Opsional)
                            </h5>
                        </div>
                        <div class="card-body">
                            <textarea class="form-control" name="notes" rows="3" 
                                      placeholder="Instruksi khusus untuk pesanan Anda...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="card sticky-top" style="top: 20px;">
                        <div class="card-header">
                            <h5 class="mb-0">Ringkasan Pesanan</h5>
                        </div>
                        <div class="card-body">
                            <!-- Cart Items -->
                            <div class="mb-3">
                                <h6 class="mb-2">Item ({{ count($cartItems) }})</h6>
                                @php $totalPrice = 0; @endphp
                                @foreach($cartItems as $item)
                                    @php 
                                        $product = $item['product'];
                                        $qty = $item['quantity'];
                                        $subtotal = $item['subtotal'];
                                        $totalPrice += $subtotal;
                                    @endphp
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            @php
                                                $nameShort = \Illuminate\Support\Str::limit($product->name, 8);
                                                $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="30">'
                                                    . '<rect width="100%" height="100%" fill="#dc3545"/>'
                                                    . '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="8" fill="#ffffff">'
                                                    . e($nameShort)
                                                    . '</text></svg>';
                                                $placeholderSvg = 'data:image/svg+xml;utf8,' . rawurlencode($svg);
                                            @endphp
                                            <img src="{{ $product->image ? asset('storage/' . $product->image) : $placeholderSvg }}" 
                                                 alt="{{ $product->name }}" class="me-2" style="width: 40px; height: 30px; object-fit: cover; border-radius: 3px;">
                                            <div>
                                                <small class="fw-bold">{{ $product->name }}</small>
                                                <br>
                                                <small class="text-muted">{{ $qty }}x</small>
                                            </div>
                                        </div>
                                        <small class="fw-bold">Rp {{ number_format($subtotal, 0, ',', '.') }}</small>
                                    </div>
                                @endforeach
                            </div>
                            
                            <hr>
                            
                            <!-- Pricing Breakdown -->
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span id="checkout-subtotal">Rp {{ number_format($totalPrice ?? ($total ?? 0), 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Pengiriman:</span>
                                <span class="text-success">Gratis</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Pajak (10%):</span>
                                <span id="checkout-tax">Rp {{ number_format(($totalPrice ?? ($total ?? 0)) * 0.1, 0, ',', '.') }}</span>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong id="checkout-total">Rp {{ number_format(($totalPrice ?? ($total ?? 0)) * 1.1, 0, ',', '.') }}</strong>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitOrder">
                                    <i class="fas fa-check"></i> Buat Pesanan
                                </button>
                            </div>
                            
                            <div class="text-center">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt"></i> Pesanan Anda aman dan terlindungi
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @else
        <!-- Empty Cart -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
                    <h3 class="text-muted mb-3">Keranjang Anda kosong</h3>
                    <p class="text-muted mb-4">Tambahkan beberapa produk ke keranjang sebelum checkout.</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary">
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
    const subtotal = Number('@json($total ?? 0)');
    const tax = subtotal * 0.1;
    
    function updateTotal() {
        let total = subtotal + tax;
        $('#checkout-total').text('Rp ' + total.toLocaleString('id-ID'));
    }
    updateTotal();
    
    // Form validation and submission
    $('#checkoutForm').on('submit', function(e) {
        e.preventDefault();
        
        // Basic validation
        let isValid = true;
        const requiredFields = ['name', 'email', 'phone', 'address', 'city', 'postal_code'];
        
        requiredFields.forEach(function(field) {
            const input = $(`#${field}`);
            if (!input.val().trim()) {
                input.addClass('is-invalid');
                isValid = false;
            } else {
                input.removeClass('is-invalid');
            }
        });
        
        // Email validation
        const email = $('#email').val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            $('#email').addClass('is-invalid');
            isValid = false;
        }
        
        const phone = $('#phone').val();
        const phoneRegex = /^(\+62|62|0)[0-9]{9,13}$/;
        if (phone && !phoneRegex.test(phone.replace(/[\s-]/g, ''))) {
            $('#phone').addClass('is-invalid');
            showAlert('danger', 'Harap masukkan nomor HP Indonesia yang valid');
            isValid = false;
        }
        
        if (!isValid) {
            showAlert('danger', 'Harap isi seluruh field wajib dengan benar');
            return;
        }
        
        // Show loading state
        const submitButton = $('#submitOrder');
        const originalText = submitButton.html();
        submitButton.html('<i class="fas fa-spinner fa-spin"></i> Memproses...').prop('disabled', true);
        
        // Submit form
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Pesanan berhasil dibuat! Mengarahkan...');
                    setTimeout(function() {
                        window.location.href = response.redirect_url || '{{ route("orders.index") }}';
                    }, 2000);
                } else {
                    showAlert('danger', response.message || 'Terjadi kesalahan saat memproses pesanan');
                    submitButton.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat memproses pesanan';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        // Handle validation errors
                        const errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach(function(field) {
                            $(`#${field}`).addClass('is-invalid');
                        });
                        message = 'Harap periksa formulir untuk kesalahan';
                    }
                }
                showAlert('danger', message);
                submitButton.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Remove validation errors on input
    $('input, textarea').on('input', function() {
        $(this).removeClass('is-invalid');
    });
    
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; max-width: 400px;" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $(alertHtml).appendTo('body');
        
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    }
});
</script>
@endpush