@extends('layouts.app')

@section('title', $product->name . ' - Dimsum Mamah Haura')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Produk</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.category', $product->category->id) }}">{{ $product->category->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Product Image -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                @php
                    $productNameShort = Str::limit($product->name, 12);
                    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400">'
                        . '<rect width="100%" height="100%" fill="#dc3545"/>'
                        . '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="24" fill="#ffffff">'
                        . e($productNameShort)
                        . '</text></svg>';
                    $placeholderSvgDataUri = 'data:image/svg+xml;utf8,' . rawurlencode($svg);
                @endphp
                <img src="{{ $product->image ? asset('storage/' . $product->image) : $placeholderSvgDataUri }}" 
                     class="card-img-top" alt="{{ $product->name }}" style="height: 400px; object-fit: cover;">
                
                @if($product->stock <= 5 && $product->stock > 0)
                    <div class="position-absolute top-0 end-0 m-3">
                        <span class="badge bg-warning fs-6">Stok Menipis</span>
                    </div>
                @elseif($product->stock == 0)
                    <div class="position-absolute top-0 end-0 m-3">
                        <span class="badge bg-danger fs-6">Stok Habis</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Product Details -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h1 class="card-title display-6 fw-bold mb-3">{{ $product->name }}</h1>
                    
                    <!-- Category -->
                    <div class="mb-3">
                        <span class="badge bg-primary fs-6">
                            <i class="fas fa-tag"></i> {{ $product->category->name }}
                        </span>
                    </div>
                    
                    <!-- Price -->
                    <div class="mb-4">
                        <span class="display-5 text-primary fw-bold">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </span>
                    </div>
                    
                    <!-- Stock Info -->
                    <div class="mb-4">
                        <div class="row">
                            <div class="col-6">
                                <strong>Stok Tersedia:</strong>
                                <span class="{{ $product->stock > 10 ? 'text-success' : ($product->stock > 0 ? 'text-warning' : 'text-danger') }}">
                                    {{ $product->stock }} unit
                                </span>
                            </div>
                            <div class="col-6">
                                <strong>Status:</strong>
                                @if($product->stock > 0)
                                    <span class="badge bg-success">Tersedia</span>
                                @else
                                    <span class="badge bg-danger">Stok Habis</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="mb-4">
                        <h5>Deskripsi</h5>
                        <p class="text-muted">{{ $product->description }}</p>
                    </div>
                    
                    {{-- Pilihan Saus dihapus --}}
                    
                    <!-- Add to Cart Form -->
                    @if($product->stock > 0)
                        @auth
                            <form action="{{ route('cart.add') }}" method="POST" id="addToCartForm">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="quantity" class="form-label">Jumlah</label>
                                        <div class="input-group">
                                            <button type="button" class="btn btn-outline-secondary" id="decreaseQty">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" class="form-control text-center" id="quantity" name="quantity" 
                                                   value="1" min="1" max="{{ $product->stock }}">
                                            <button type="button" class="btn btn-outline-secondary" id="increaseQty">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex">
                                    <button type="submit" class="btn btn-primary btn-lg flex-fill">
                                        <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                                    </button>
                                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-lg">
                                        <i class="fas fa-arrow-left"></i> Kembali ke Produk
                                    </a>
                                </div>
                            </form>
                        @else
                            <div class="d-grid gap-2 d-md-flex">
                                <a href="{{ route('login') }}" class="btn btn-primary btn-lg flex-fill">
                                    <i class="fas fa-sign-in-alt"></i> Masuk untuk Membeli
                                </a>
                                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-arrow-left"></i> Kembali ke Produk
                                </a>
                            </div>
                        @endauth
                    @else
                        <div class="d-grid gap-2 d-md-flex">
                            <button class="btn btn-secondary btn-lg flex-fill" disabled>
                                <i class="fas fa-times"></i> Stok Habis
                            </button>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> Kembali ke Produk
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="mb-4">Produk Terkait</h3>
            <div class="row">
                @foreach($relatedProducts as $relatedProduct)
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 product-card">
                        @php
                            $relatedNameShort = Str::limit($relatedProduct->name, 10);
                            $svgRelated = '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="200">'
                                . '<rect width="100%" height="100%" fill="#dc3545"/>'
                                . '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="16" fill="#ffffff">'
                                . e($relatedNameShort)
                                . '</text></svg>';
                            $placeholderRelatedSvg = 'data:image/svg+xml;utf8,' . rawurlencode($svgRelated);
                        @endphp
                        <img src="{{ $relatedProduct->image ? asset('storage/' . $relatedProduct->image) : $placeholderRelatedSvg }}" 
                             class="card-img-top" alt="{{ $relatedProduct->name }}" style="height: 200px; object-fit: cover;">
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $relatedProduct->name }}</h5>
                            <p class="card-text flex-grow-1">{{ Str::limit($relatedProduct->description, 60) }}</p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="h6 text-primary mb-0">Rp {{ number_format($relatedProduct->price, 0, ',', '.') }}</span>
                                @if($relatedProduct->stock > 0)
                                    <span class="badge bg-success">Tersedia</span>
                                @else
                                    <span class="badge bg-danger">Stok Habis</span>
                                @endif
                            </div>
                            
                            <div class="mt-auto">
                                <a href="{{ route('products.show', $relatedProduct->id) }}" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Quantity controls
    $('#increaseQty').on('click', function() {
        const qtyInput = $('#quantity');
        const currentQty = parseInt(qtyInput.val());
        const maxQty = parseInt(qtyInput.attr('max'));
        
        if (currentQty < maxQty) {
            qtyInput.val(currentQty + 1);
        }
    });
    
    $('#decreaseQty').on('click', function() {
        const qtyInput = $('#quantity');
        const currentQty = parseInt(qtyInput.val());
        const minQty = parseInt(qtyInput.attr('min'));
        
        if (currentQty > minQty) {
            qtyInput.val(currentQty - 1);
        }
    });
    
    // Validate quantity input
    $('#quantity').on('input', function() {
        const qtyInput = $(this);
        const currentQty = parseInt(qtyInput.val());
        const minQty = parseInt(qtyInput.attr('min'));
        const maxQty = parseInt(qtyInput.attr('max'));
        
        if (currentQty < minQty) {
            qtyInput.val(minQty);
        } else if (currentQty > maxQty) {
            qtyInput.val(maxQty);
        }
    });
    
    // Add to cart form submission (tanpa toppings)
    $('#addToCartForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const button = form.find('button[type="submit"]');
        const originalText = button.html();
        
        // Show loading state
        button.html('<i class="fas fa-spinner fa-spin"></i> Menambahkan ke Keranjang...').prop('disabled', true);
        
        // Build payload tanpa toppings
        const payload = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            product_id: form.find('input[name="product_id"]').val(),
            quantity: form.find('input[name="quantity"]').val()
        };
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: payload,
            headers: {
                'X-CSRF-TOKEN': payload._token,
                'Accept': 'application/json'
            },
            success: function(response) {
                // Restore button
                button.html(originalText).prop('disabled', false);
                
                if(response.success) {
                    // Update cart count
                    $('#cart-count').text(response.cart_count);
                    
                    // Show success message
                    $('<div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;" role="alert">' +
                        response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>').appendTo('body');
                    
                    // Auto dismiss after 3 seconds
                    setTimeout(function() {
                        $('.alert').alert('close');
                    }, 3000);
                }
            },
            error: function(xhr) {
                // Restore button
                button.html(originalText).prop('disabled', false);
                
                let msg = 'Gagal menambahkan produk ke keranjang';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
            }
        });
    });
});
</script>
@endpush