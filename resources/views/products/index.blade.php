@extends('layouts.app')

@section('title', 'Produk - Dimsum Mamah Haura')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold text-center mb-3">Produk Kami</h1>
            <p class="text-center text-muted">Temukan pilihan dimsum autentik kami yang lezat</p>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('products.index') }}" id="filterForm">
                        <div class="row g-3">
                            <!-- Search -->
                            <div class="col-md-4">
                                <label for="search" class="form-label">Cari Produk</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="Cari berdasarkan nama...">
                            </div>
                            
                            <!-- Category Filter -->
                            <div class="col-md-3">
                                <label for="category" class="form-label">Kategori</label>
                                <select class="form-select" id="category" name="category">
                                    @php
                                        $selectedCategoryId = request()->input('category');
                                        $routeCategoryParam = request()->route('category');
                                        if ($selectedCategoryId === null && $routeCategoryParam) {
                                            $selectedCategoryId = $routeCategoryParam instanceof \App\Models\Category
                                                ? $routeCategoryParam->id
                                                : $routeCategoryParam;
                                        }
                                    @endphp
                                    <option value="">Semua Kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                {{ (string)$selectedCategoryId === (string)$category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Price Range -->
                            <div class="col-md-2">
                                <label for="min_price" class="form-label">Harga Minimum</label>
                                <input type="number" class="form-control" id="min_price" name="min_price" 
                                       value="{{ request('min_price') }}" placeholder="0">
                            </div>
                            
                            <div class="col-md-2">
                                <label for="max_price" class="form-label">Harga Maksimum</label>
                                <input type="number" class="form-control" id="max_price" name="max_price" 
                                       value="{{ request('max_price') }}" placeholder="100000">
                            </div>
                            
                            <!-- Sort -->
                            <div class="col-md-1">
                                <label for="sort" class="form-label">Urutkan</label>
                                <select class="form-select" id="sort" name="sort">
                                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nama A-Z</option>
                                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nama Z-A</option>
                                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Harga Rendah-Tinggi</option>
                                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Harga Tinggi-Rendah</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Info -->
    <div class="row mb-3">
        <div class="col-12">
            <p class="text-muted">
                Menampilkan {{ $products->firstItem() ?? 0 }} sampai {{ $products->lastItem() ?? 0 }} 
                dari {{ $products->total() }} hasil
                @if(request('search'))
                    untuk "{{ request('search') }}"
                @endif
            </p>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row">
        @forelse($products as $product)
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 product-card">
                @php
                    $nameShort = Str::limit($product->name, 10);
                    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="200">'
                        . '<rect width="100%" height="100%" fill="#dc3545"/>'
                        . '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="16" fill="#ffffff">'
                        . e($nameShort)
                        . '</text></svg>';
                    $placeholderSvg = 'data:image/svg+xml;utf8,' . rawurlencode($svg);
                @endphp
                <img src="{{ $product->image ? asset('storage/' . $product->image) : $placeholderSvg }}" 
                     class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                
                @if($product->stock <= 5 && $product->stock > 0)
                    <div class="position-absolute top-0 end-0 m-2">
                        <span class="badge bg-warning">Stok Menipis</span>
                    </div>
                @elseif($product->stock == 0)
                    <div class="position-absolute top-0 end-0 m-2">
                        <span class="badge bg-danger">Stok Habis</span>
                    </div>
                @endif
                
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">{{ $product->name }}</h5>
                    <p class="card-text flex-grow-1">{{ Str::limit($product->description, 80) }}</p>
                    
                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="fas fa-tag"></i> {{ $product->category->name }}
                        </small>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="h5 text-primary mb-0">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                        <small class="text-muted">Stok: {{ $product->stock }}</small>
                    </div>
                    
                    <div class="mt-auto">
                        <div class="d-grid gap-2">
                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye"></i> Lihat Detail
                            </a>
                            
                            @if($product->stock > 0)
                                @auth
                                    <form action="{{ route('cart.add') }}" method="POST" class="add-to-cart-form">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-sign-in-alt"></i> Masuk untuk Membeli
                                    </a>
                                @endauth
                            @else
                                <button class="btn btn-secondary btn-sm" disabled>
                                    <i class="fas fa-times"></i> Stok Habis
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Produk tidak ditemukan</h4>
                <p class="text-muted">Coba ubah kriteria pencarian Anda atau lihat semua produk.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Lihat Semua Produk
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-center">
                {{ $products->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form on filter change
    $('#category, #sort').on('change', function() {
        $('#filterForm').submit();
    });
    
    // Add to cart functionality
    $('.add-to-cart-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const button = form.find('button[type="submit"]');
        const originalText = button.html();
        
        // Show loading state
        button.html('<i class="fas fa-spinner fa-spin"></i> Menambahkan...').prop('disabled', true);
        
        // Build payload explicitly to avoid missing fields in serialize edge cases
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
                if(response.success) {
                    // Update cart count
                    $('#cart-count').text(response.cart_count);
                    
                    // Show success message
                    showAlert('success', response.message);
                    
                    // Reset button
                    button.html(originalText).prop('disabled', false);
                } else {
                    showAlert('danger', response.message || 'Terjadi kesalahan saat menambahkan produk ke keranjang');
                    button.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat menambahkan produk ke keranjang';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    // Collect validation messages
                    const errors = xhr.responseJSON.errors;
                    const msgs = [];
                    Object.keys(errors).forEach(function(key) {
                        errors[key].forEach(function(m) { msgs.push(m); });
                    });
                    if (msgs.length) message = msgs.join('\n');
                } else if(xhr.responseJSON && xhr.responseJSON.message) {
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
                 style="top: 20px; right: 20px; z-index: 9999; max-width: 300px;" role="alert">
                ${message.replace(/\n/g, '<br>')}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $(alertHtml).appendTo('body');
        
        // Auto dismiss after 3 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 3000);
    }
});
</script>
@endpush