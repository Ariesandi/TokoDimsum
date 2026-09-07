@extends('layouts.app')

@section('title', 'Beranda - Dimsum Mamah Haura')

@section('content')
<!-- Hero Section -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-3">Selamat Datang di Dimsum Mamah Haura</h1>
                <p class="lead mb-4">Cicipi dimsum dengan rasa autentik, terbuat dari bahan-bahan terbaik dan resep yang sudah teruji. Setiap gigitan penuh dengan kehangatan dan kasih sayang, siap menemani momen santai Anda.</p>
                <a href="{{ route('products.index') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-shopping-bag me-2"></i>Belanja Sekarang
                </a>
            </div>
            <div class="col-lg-6 text-center">
                @php
                    // SVG placeholder (kotak merah bertuliskan "Dimsum Lezat")
                    $heroSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="500" height="400">'
                        . '<rect width="100%" height="100%" fill="#dc3545"/>'
                        . '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="28" fill="#ffffff">Dimsum Lezat</text>'
                        . '</svg>';
                    $heroPlaceholder = 'data:image/svg+xml;utf8,' . rawurlencode($heroSvg);

                    // Cek apakah ada gambar hero yang diupload ke public/images
                    $candidates = [
                        public_path('images/hero.webp') => asset('images/hero.webp'),
                        public_path('images/hero.jpg') => asset('images/hero.jpg'),
                        public_path('images/hero.jpeg') => asset('images/hero.jpeg'),
                        public_path('images/hero.png') => asset('images/hero.png'),
                        // Juga cek di storage/app/public jika pengguna meletakkan di sana
                        storage_path('app/public/hero.webp') => asset('storage/hero.webp'),
                        storage_path('app/public/hero.jpg') => asset('storage/hero.jpg'),
                        storage_path('app/public/hero.jpeg') => asset('storage/hero.jpeg'),
                        storage_path('app/public/hero.png') => asset('storage/hero.png'),
                    ];
                    $heroImageUrl = null;
                    foreach ($candidates as $diskPath => $url) {
                        if (file_exists($diskPath)) {
                            $version = @filemtime($diskPath) ?: time();
                            $heroImageUrl = $url . '?v=' . $version; // cache-busting agar perubahan gambar langsung terlihat
                            break;
                        }
                    }
                @endphp
                @if($heroImageUrl)
                    <img src="{{ $heroImageUrl }}" alt="Dimsum Mamah Haura" class="img-fluid rounded shadow" style="max-height: 400px; width: 100%; object-fit: cover;">
                @else
                    <img src="{{ $heroPlaceholder }}" alt="Dimsum" class="img-fluid rounded shadow">
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Kategori Unggulan -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Kategori Kami</h2>
        <div class="row">
            @foreach($categories as $category)
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center product-card">
                    @php
                        $catNameShort = Str::limit($category->name, 12);
                        $catSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="180">'
                            . '<rect width="100%" height="100%" fill="#e9ecef"/>'
                            . '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="18" fill="#6c757d">'
                            . e($catNameShort)
                            . '</text></svg>';
                        $catPlaceholder = 'data:image/svg+xml;utf8,' . rawurlencode($catSvg);
                        $categoryImage = $category->image ? asset('storage/' . $category->image) : $catPlaceholder;
                    @endphp
                    <img src="{{ $categoryImage }}" alt="{{ $category->name }}" class="card-img-top" style="height: 180px; object-fit: cover;" />
                    <div class="card-body">
                        <h5 class="card-title">{{ $category->name }}</h5>
                        <p class="card-text">{{ $category->description }}</p>
                        <a href="{{ route('products.category', $category->id) }}" class="btn btn-primary">
                            Lihat Produk
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Produk Unggulan -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Produk Unggulan</h2>
        <div class="row">
            @foreach($featuredProducts as $product)
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
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <p class="card-text flex-grow-1">{{ Str::limit($product->description, 80) }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 text-primary mb-0">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            @if($product->stock > 0)
                                <span class="badge bg-success">Stok Tersedia</span>
                            @else
                                <span class="badge bg-danger">Stok Habis</span>
                            @endif
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-eye"></i> Lihat
                            </a>
                            @if($product->stock > 0)
                                @auth
                                    <form action="{{ route('cart.add') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
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
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                Lihat Semua Produk <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Mengapa Memilih Kami?</h2>
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <div class="mb-3">
                    <i class="fas fa-award fa-3x text-primary"></i>
                </div>
                <h4>Kualitas Premium</h4>
                <p>Kami hanya menggunakan bahan-bahan terbaik untuk memastikan setiap gigitan terasa sempurna.</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="mb-3">
                    <i class="fas fa-shipping-fast fa-3x text-primary"></i>
                </div>
                <h4>Proses Cepat</h4>
                <p>Proses cepat dan terpercaya untuk menghadirkan dimsum segar ke depan pintu Anda.</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="mb-3">
                    <i class="fas fa-heart fa-3x text-primary"></i>
                </div>
                <h4>Dibuat dengan Cinta</h4>
                <p>Resep tradisional turun-temurun yang dibuat dengan penuh perhatian.</p>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="py-5 bg-dark text-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h3 class="mb-3">Hubungi Kami</h3>
                <p class="mb-4">Klik tombol di bawah untuk langsung terhubung via WhatsApp untuk pertanyaan atau pemesanan.</p>
                @php
                    $waNumber = '62123456789';
                    $waText = urlencode('Halo Dimsum Mamah Haura, saya ingin bertanya tentang produk.');
                @endphp
                <a href="https://wa.me/{{ $waNumber }}?text={{ $waText }}" target="_blank" rel="noopener" class="btn btn-success btn-lg">
                    <i class="fab fa-whatsapp me-2"></i> Hubungi via WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Add to cart functionality
    $('form[action*="cart/add"]').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
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
            error: function() {
                alert('Gagal menambahkan produk ke keranjang');
            }
        });
    });
});
</script>
@endpush