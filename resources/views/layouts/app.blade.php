<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dimsum Mamah Haura')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand {
            font-weight: bold;
            color: #dc3545 !important;
        }
        .product-card {
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .footer {
            background-color: #343a40;
            color: white;
            margin-top: 50px;
        }
    </style>
    @stack('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    @php
        $logoCandidates = ['images/logo.webp', 'images/logo.svg', 'images/logo.png', 'images/logo.jpg', 'images/logo.jpeg'];
        $logoAsset = null;
        foreach ($logoCandidates as $relPath) {
            $abs = public_path($relPath);
            if (file_exists($abs)) {
                $ts = @filemtime($abs);
                $logoAsset = asset($relPath) . ($ts ? ('?v=' . $ts) : '');
                break;
            }
        }
    @endphp
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <img src="{{ $logoAsset ?? asset('images/logo.png') }}" alt="Dimsum Mamah Haura" class="me-2" style="height:32px;">
                <span class="visually-hidden">Dimsum Mamah Haura</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('products.index') }}">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('about') }}">Tentang Kami</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            Kategori
                        </a>
                        <ul class="dropdown-menu">
                            @foreach(App\Models\Category::all() as $category)
                                <li><a class="dropdown-item" href="{{ route('products.category', $category->id) }}">{{ $category->name }}</a></li>
                            @endforeach
                        </ul>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="{{ route('cart.index') }}">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="cart-badge" id="cart-count">{{ session('cart') ? count(session('cart')) : 0 }}</span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('orders.index') }}">Pesanan Saya</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Keluar</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Masuk</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Daftar</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @if(session('success'))
            <div class="container mt-3">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container mt-3">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-2">
                        <img src="{{ $logoAsset ?? asset('images/logo.png') }}" alt="Dimsum Mamah Haura" class="me-2" style="height:36px;">
                        <h5 class="mb-0">Dimsum Mamah Haura</h5>
                    </div>
                    <p>“Dimsum rumahan terbaik, hangat dan selalu fresh.”</p>
                </div>
                <div class="col-md-3">
                    <h6>Tautan Cepat</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('home') }}" class="text-light">Beranda</a></li>
                        <li><a href="{{ route('products.index') }}" class="text-light">Produk</a></li>
                        <li><a href="{{ route('about') }}" class="text-light">Tentang Kami</a></li>
                        <li><a href="https://wa.me/62123456789?text={{ urlencode('Halo Dimsum Mamah Haura, saya ingin bertanya tentang produk.') }}" target="_blank" rel="noopener" class="text-light">Hubungi Kami</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Informasi Kontak</h6>
                    <p class="mb-1"><i class="fas fa-phone me-2"></i>+62 123 456 789</p>
                    <p class="mb-1"><i class="fas fa-envelope me-2"></i>info@dimsummamah.com</p>
                    <p><i class="fas fa-map-marker-alt me-2"></i>Jakarta, Indonesia</p>
                </div>
            </div>
            <hr class="my-3">
            <div class="text-center">
                <p>&copy; 2025 Dimsum Mamah Haura. Hak cipta dilindungi.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @stack('scripts')
</body>
</html>