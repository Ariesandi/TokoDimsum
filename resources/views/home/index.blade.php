@extends('layouts.app')

@section('title', 'Dimsum Mamah Haura - Halaman Utama')

@section('content')
<!-- Hero Section -->
<section class="hero-section bg-gradient-to-r from-red-600 to-red-800 text-white py-20">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-4">Dimsum Mamah Haura</h1>
        <p class="text-xl mb-8">Nikmati kelezatan dimsum autentik dengan cita rasa yang tak terlupakan</p>
        <a href="{{ route('products.index') }}" class="bg-white text-red-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
            Lihat Menu
        </a>
    </div>
</section>

<!-- Produk Unggulan -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-800 mb-4">Produk Unggulan</h2>
            <p class="text-gray-600 text-lg">Pilihan terbaik dari menu dimsum kami</p>
        </div>
        
        @if($featuredProducts->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach($featuredProducts as $product)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
                <div class="aspect-w-1 aspect-h-1">
                    <img src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/no-image.jpg') }}" 
                         alt="{{ $product->name }}" 
                         class="w-full h-48 object-cover">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ $product->name }}</h3>
                    <p class="text-gray-600 mb-4 text-sm">{{ Str::limit($product->description, 80) }}</p>
                    <div class="flex justify-between items-center">
                        <span class="text-2xl font-bold text-red-600">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                        <button data-product-id="{{ $product->id }}"
                                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300 add-to-cart-btn">
                            <i class="fas fa-cart-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <div class="text-gray-400 mb-4">
                <i class="fas fa-box-open text-6xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Belum Ada Produk Unggulan</h3>
            <p class="text-gray-500">Produk unggulan akan segera hadir</p>
        </div>
        @endif
        
        <div class="text-center mt-12">
            <a href="{{ route('products.index') }}" class="bg-red-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-red-700 transition duration-300">
                Lihat Semua Produk
            </a>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-4xl font-bold text-gray-800 mb-6">Tentang Dimsum Mamah Haura</h2>
                <p class="text-gray-600 text-lg mb-6">
                    Dimsum Mamah Haura hadir dengan komitmen untuk menyajikan dimsum berkualitas tinggi 
                    dengan cita rasa autentik yang telah diwariskan turun temurun. Setiap dimsum dibuat 
                    dengan bahan-bahan pilihan dan resep rahasia keluarga.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="bg-red-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-utensils text-red-600 text-2xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800 mb-2">Resep Autentik</h3>
                        <p class="text-gray-600 text-sm">Resep turun temurun dengan cita rasa original</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-red-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-leaf text-red-600 text-2xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800 mb-2">Bahan Segar</h3>
                        <p class="text-gray-600 text-sm">Menggunakan bahan-bahan segar pilihan terbaik</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-red-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-heart text-red-600 text-2xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800 mb-2">Dibuat dengan Cinta</h3>
                        <p class="text-gray-600 text-sm">Setiap dimsum dibuat dengan penuh perhatian</p>
                    </div>
                </div>
            </div>
            <div>
                <img src="{{ asset('images/about-dimsum.jpg') }}" alt="About Dimsum" class="rounded-lg shadow-lg w-full">
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="bg-red-600 text-white py-16">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold mb-4">Siap Menikmati Dimsum Lezat?</h2>
        <p class="text-xl mb-8">Pesan sekarang dan rasakan kelezatan dimsum autentik kami</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('products.index') }}" class="bg-white text-red-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                Pesan Sekarang
            </a>
            <a href="#" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-red-600 transition duration-300">
                Hubungi Kami
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function addToCart(productId) {
    fetch('{{ route("cart.add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            updateCartCount();
            
            // Show success message
            showNotification('Produk berhasil ditambahkan ke keranjang!', 'success');
        } else {
            showNotification(data.message || 'Gagal menambahkan produk ke keranjang', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat menambahkan produk', 'error');
    });
}

// Event delegation for add-to-cart buttons
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.add-to-cart-btn');
    if (btn) {
        const productId = parseInt(btn.getAttribute('data-product-id'), 10);
        if (!Number.isNaN(productId)) {
            addToCart(productId);
        }
    }
});

function updateCartCount() {
    fetch('{{ route("cart.count") }}')
    .then(response => response.json())
    .then(data => {
        const cartCount = document.getElementById('cart-count');
        if (cartCount) {
            cartCount.textContent = data.count;
            cartCount.style.display = data.count > 0 ? 'inline' : 'none';
        }
    })
    .catch(error => console.error('Error updating cart count:', error));
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
    
    if (type === 'success') {
        notification.classList.add('bg-green-500', 'text-white');
    } else if (type === 'error') {
        notification.classList.add('bg-red-500', 'text-white');
    } else {
        notification.classList.add('bg-blue-500', 'text-white');
    }
    
    notification.innerHTML = `
        <div class="flex items-center">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
}

// Update cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});
</script>
@endpush