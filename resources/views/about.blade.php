@extends('layouts.app')

@section('content')
<section id="tentang_kami" class="py-5 bg-light">
    <div class="container">
        <div class="row mb-4">
            <div class="col text-center">
                <h2 class="fw-bold">Tentang Kami</h2>
                <p class="lead text-muted mt-2">Toko Dimsum Mamah Haura adalah toko dimsum rumahan berkualitas yang menyediakan berbagai pilihan dimsum lezat dan bergizi, dibuat dengan bahan-bahan terbaik dan resep tradisional. Kami juga melayani kebutuhan reseller yang ingin menyediakan produk dimsum berkualitas tinggi untuk bisnis kuliner mereka. Dengan komitmen pada rasa yang autentik dan harga yang kompetitif, Toko Dimsum Mamah Haura siap memenuhi pesanan Anda dengan layanan cepat dan kualitas yang terjamin.</p>
            </div>
        </div>
        <div class="row g-4 align-items-stretch">
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            @php
                                $ownerCandidates = ['owner.jpg','owner.jpeg','owner.png','owner.webp'];
                                $ownerUrl = null;
                                foreach ($ownerCandidates as $cand) {
                                    // Cek di public/images
                                    if (file_exists(public_path('images/' . $cand))) { $ownerUrl = asset('images/' . $cand); break; }
                                    // Cek di storage/app/public/images (web path: /storage/images)
                                    if (file_exists(storage_path('app/public/images/' . $cand))) { $ownerUrl = asset('storage/images/' . $cand); break; }
                                }
                            @endphp
                            @if ($ownerUrl)
                                <img src="{{ $ownerUrl }}" alt="Foto Owner" class="rounded-circle me-3 border" style="width:64px; height:64px; object-fit: cover;" loading="lazy">
                            @else
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary">
                                    <i class="fas fa-user-tie fa-lg"></i>
                                </div>
                            @endif
                            <div>
                                <h5 class="mb-0">Owner</h5>
                                <small class="text-muted">Ibu (Pemilik)</small>
                            </div>
                        </div>
                        <p>
                            Dimsum Mamah Haura dimulai dari dapur rumahan yang penuh cinta. Ibu adalah sosok di balik resep yang lezat, memastikan setiap bahan dipilih dengan kualitas terbaik dan setiap dimsum dibuat dengan standar kebersihan yang tinggi.
                        </p>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Bahan segar pilihan</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Rasa autentik yang terjaga</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Dibuat harian, siap dinikmati hangat</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            @php
                                $devCandidates = ['developer.jpg','developer.jpeg','developer.png','developer.webp','web-developer.jpg','web-developer.png','webdev.jpg','webdev.png'];
                                $devUrl = null;
                                foreach ($devCandidates as $cand) {
                                    if (file_exists(public_path('images/' . $cand))) { $devUrl = asset('images/' . $cand); break; }
                                    if (file_exists(storage_path('app/public/images/' . $cand))) { $devUrl = asset('storage/images/' . $cand); break; }
                                }
                            @endphp
                            @if ($devUrl)
                                <img src="{{ $devUrl }}" alt="Foto Web Developer" class="rounded-circle me-3 border" style="width:64px; height:64px; object-fit: cover;" loading="lazy">
                            @else
                                <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3 text-info">
                                    <i class="fas fa-code fa-lg"></i>
                                </div>
                            @endif
                            <div>
                                <h5 class="mb-0">Web Developer</h5>
                                <small class="text-muted">Saya (Pengembang Web)</small>
                            </div>
                        </div>
                        <p>
                            Website ini saya kembangkan untuk memudahkan pelanggan memesan dimsum favorit secara praktis, aman, dan nyaman. Saya juga terus meningkatkan fitur serta tampilan agar pengalaman belanja semakin menyenangkan.
                        </p>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Pemesanan online yang mudah</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Informasi produk jelas dan transparan</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Dukungan dan pembaruan berkala</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-5">
            <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg"><i class="fas fa-shopping-basket me-2"></i>Lihat Produk</a>
        </div>
    </div>
</section>
@endsection