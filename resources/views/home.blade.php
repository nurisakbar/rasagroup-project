@extends('layouts.shop')

@section('title', 'Beranda')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --primary-color: #E63946;
        --primary-dark: #C1121F;
        --secondary-color: #F77F00;
        --accent-color: #FCBF49;
        --dark-color: #1D3557;
        --light-bg: #F8F9FA;
        --white: #FFFFFF;
        --text-dark: #2B2D42;
        --text-light: #6C757D;
        --border-color: #E0E0E0;
        --success-color: #06A77D;
    }

    /* Hero Slider */
    .hero-slider {
        margin-top: 0;
    }

    .carousel-item {
        height: 500px;
        position: relative;
        overflow: hidden;
    }

    .carousel-item img {
        object-fit: cover;
        height: 100%;
        width: 100%;
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
        image-rendering: auto;
        -ms-interpolation-mode: bicubic;
        backface-visibility: hidden;
        transform: translateZ(0);
    }

    .carousel-caption {
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        bottom: 0;
        left: 0;
        right: 0;
        padding: 3rem 2rem;
        text-align: left;
    }

    .carousel-caption h2 {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }

    .carousel-caption p {
        font-size: 1.2rem;
        margin-bottom: 1.5rem;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    }

    .btn-hero {
        padding: 12px 35px;
        font-weight: 600;
        border-radius: 30px;
        font-size: 1rem;
        transition: all 0.3s;
    }

    .btn-hero-primary {
        background: var(--primary-color);
        color: white;
        border: none;
    }

    .btn-hero-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(230, 57, 70, 0.4);
        color: white;
    }

    .btn-hero-outline {
        background: transparent;
        color: white;
        border: 2px solid white;
    }

    .btn-hero-outline:hover {
        background: white;
        color: var(--primary-color);
    }

    .carousel-control-prev,
    .carousel-control-next {
        width: 50px;
        height: 50px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0.8;
        transition: all 0.3s;
    }

    .carousel-control-prev {
        left: 20px;
    }

    .carousel-control-next {
        right: 20px;
    }

    .carousel-control-prev:hover,
    .carousel-control-next:hover {
        background: rgba(255,255,255,0.3);
        opacity: 1;
    }

    /* Section Title */
    .section-title {
        text-align: center;
        margin-bottom: 3rem;
        padding-top: 4rem;
    }

    .section-title h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .section-title p {
        font-size: 1.1rem;
        color: var(--text-light);
    }

    .section-title::before {
        content: '';
        display: block;
        width: 80px;
        height: 4px;
        background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        margin: 0 auto 1rem;
        border-radius: 2px;
    }

    /* Categories Section */
    .categories-section {
        background: var(--white);
        padding-bottom: 4rem;
    }

    .category-card {
        background: var(--white);
        border: 2px solid var(--border-color);
        border-radius: 20px;
        padding: 2rem 1.5rem;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
        display: block;
        height: 100%;
    }

    .category-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(230, 57, 70, 0.15);
        border-color: var(--primary-color);
        text-decoration: none;
        color: inherit;
    }

    .category-icon {
        width: 90px;
        height: 90px;
        margin: 0 auto 1.5rem;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: var(--white);
        transition: transform 0.3s;
    }

    .category-card:hover .category-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .category-card h3 {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .category-card p {
        font-size: 0.9rem;
        color: var(--text-light);
        margin: 0;
    }

    /* Products Section */
    .products-section {
        background: var(--light-bg);
        padding-bottom: 4rem;
    }

    .product-card {
        background: var(--white);
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.3s;
        box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        text-decoration: none;
        color: inherit;
        display: block;
        height: 100%;
    }

    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        text-decoration: none;
        color: inherit;
    }

    .product-image-wrapper {
        position: relative;
        overflow: hidden;
        height: 280px;
        background: var(--light-bg);
    }

    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }

    .product-card:hover .product-image {
        transform: scale(1.1);
    }

    .product-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: var(--primary-color);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .product-badge.new {
        background: var(--success-color);
    }

    .product-badge.sale {
        background: var(--secondary-color);
    }

    .product-info {
        padding: 1.5rem;
    }

    .product-category {
        font-size: 0.8rem;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .product-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.75rem;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-rating {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 1rem;
    }

    .product-rating i {
        color: #FFA500;
        font-size: 0.9rem;
    }

    .product-rating span {
        font-size: 0.85rem;
        color: var(--text-light);
        margin-left: 5px;
    }

    .product-price-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .product-price {
        display: flex;
        flex-direction: column;
    }

    .price {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--primary-color);
        line-height: 1;
    }

    .price-old {
        font-size: 0.9rem;
        color: var(--text-light);
        text-decoration: line-through;
        margin-top: 0.25rem;
    }

    .price-discount {
        font-size: 0.85rem;
        color: var(--success-color);
        font-weight: 600;
    }

    .btn-add-cart {
        width: 100%;
        background: var(--primary-color);
        color: white;
        padding: 12px;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-add-cart:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(230, 57, 70, 0.3);
        color: white;
    }

    .btn-add-cart i {
        font-size: 1.1rem;
    }

    /* Distributors Section */
    .distributors-section {
        background: linear-gradient(135deg, var(--dark-color) 0%, var(--primary-dark) 100%);
        padding: 4rem 0;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .distributors-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
        opacity: 0.3;
    }

    .distributors-section .section-title {
        color: white;
        padding-top: 0;
    }

    .distributors-section .section-title h2 {
        color: white;
    }

    .distributors-section .section-title p {
        color: rgba(255, 255, 255, 0.9);
    }

    .distributors-section .section-title::before {
        background: linear-gradient(to right, var(--accent-color), var(--secondary-color));
    }

    .distributor-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        overflow: hidden;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
        height: 100%;
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
    }

    .distributor-card:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        border-color: var(--accent-color);
    }

    .distributor-image-wrapper {
        position: relative;
        width: 100%;
        height: 200px;
        overflow: hidden;
        background: rgba(0, 0, 0, 0.3);
    }

    .distributor-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }

    .distributor-card:hover .distributor-image {
        transform: scale(1.1);
    }

    .distributor-image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, transparent, rgba(0, 0, 0, 0.5));
    }

    .distributor-card-content {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .distributor-icon {
        width: 60px;
        height: 60px;
        margin: -30px auto 1rem;
        background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: var(--dark-color);
        transition: transform 0.3s;
        position: relative;
        z-index: 2;
        border: 3px solid rgba(255, 255, 255, 0.2);
    }

    .distributor-card:hover .distributor-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .distributor-card h3 {
        font-size: 1.3rem;
        font-weight: 600;
        color: white;
        margin-bottom: 0.5rem;
    }

    .distributor-card p {
        font-size: 0.95rem;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 1rem;
    }

    .distributor-info {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
    }

    .distributor-info-item {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.9);
    }

    .distributor-info-item i {
        color: var(--accent-color);
        font-size: 1rem;
    }

    .distributor-badge {
        display: inline-block;
        background: var(--accent-color);
        color: var(--dark-color);
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .carousel-caption h2 {
            font-size: 2rem;
        }

        .carousel-caption p {
            font-size: 1rem;
        }

        .carousel-item {
            height: 400px;
        }

        .section-title h2 {
            font-size: 2rem;
        }

        .category-icon {
            width: 70px;
            height: 70px;
            font-size: 2rem;
        }

        .product-image-wrapper {
            height: 220px;
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 40px;
            height: 40px;
        }

        .carousel-control-prev {
            left: 10px;
        }

        .carousel-control-next {
            right: 10px;
        }

        .distributor-card-content {
            padding: 1.25rem;
        }

        .distributor-image-wrapper {
            height: 150px;
        }

        .distributor-icon {
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
            margin-top: -25px;
        }

        .distributor-card h3 {
            font-size: 1.1rem;
        }

        .distributor-info-item {
            font-size: 0.8rem;
        }
    }

    @media (max-width: 576px) {
        .carousel-item {
            height: 300px;
        }

        .carousel-caption {
            padding: 1.5rem 1rem;
        }

        .carousel-caption h2 {
            font-size: 1.5rem;
        }

        .btn-hero {
            padding: 10px 25px;
            font-size: 0.9rem;
        }
    }
</style>
@endpush

@section('content')
    <!-- Hero Slider -->
    <div id="heroCarousel" class="carousel slide hero-slider" data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="https://images.unsplash.com/photo-1551538827-9c037cb4f32a?w=2560&q=100&auto=format&fit=crop" class="d-block w-100" alt="Promo 1" loading="eager">
                <div class="carousel-caption">
                    <div class="container">
                        <h2>Sirup Premium dengan Rasa Terbaik</h2>
                        <p>Nikmati berbagai varian sirup berkualitas tinggi dengan harga spesial</p>
                        <a href="{{ route('hubs.index') }}" class="btn btn-hero-primary btn-hero me-3">Belanja Sekarang</a>
                        <a href="#products" class="btn btn-hero-outline btn-hero">Lihat Katalog</a>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <img src="https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=2560&q=100&auto=format&fit=crop" class="d-block w-100" alt="Promo 2" loading="eager">
                <div class="carousel-caption">
                    <div class="container">
                        <h2>Diskon Hingga 30% untuk Semua Produk</h2>
                        <p>Promo terbatas! Dapatkan produk favorit Anda dengan harga terbaik</p>
                        <a href="{{ route('hubs.index') }}" class="btn btn-hero-primary btn-hero me-3">Lihat Promo</a>
                        <a href="#products" class="btn btn-hero-outline btn-hero">Pelajari Lebih Lanjut</a>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=2560&q=100&auto=format&fit=crop" class="d-block w-100" alt="Promo 3" loading="eager">
                <div class="carousel-caption">
                    <div class="container">
                        <h2>Gratis Ongkir untuk Pembelian di Atas Rp 100.000</h2>
                        <p>Belanja lebih hemat dengan gratis ongkir ke seluruh Indonesia</p>
                        <a href="{{ route('hubs.index') }}" class="btn btn-hero-primary btn-hero me-3">Mulai Belanja</a>
                        <a href="#products" class="btn btn-hero-outline btn-hero">Syarat & Ketentuan</a>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>

    <!-- Categories Section -->
    <section class="categories-section" id="categories">
        <div class="container">
            <div class="section-title">
                <h2>Kategori Produk</h2>
                <p>Pilih kategori sirup favorit Anda</p>
            </div>
            <div class="row g-4">
                @forelse($categories as $category)
                    <div class="col-6 col-md-4 col-lg-2">
                        <a href="{{ route('hubs.index', ['category' => $category->slug]) }}" class="category-card">
                            <div class="category-icon">
                                <i class="{{ $category->icon ?? 'bi bi-droplet-fill' }}"></i>
                            </div>
                            <h3>{{ $category->name }}</h3>
                            <p>{{ $category->description ?? 'Lihat produk' }}</p>
                        </a>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">Belum ada kategori tersedia</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Distributors Section -->
    <section class="distributors-section" id="distributors">
        <div class="container">
            <div class="section-title">
                <h2>Lokasi Distributor Kami</h2>
                <p>Temukan distributor terdekat di kota Anda</p>
            </div>
            <div class="row g-4">
                <!-- Jakarta -->
                <div class="col-6 col-md-6 col-lg-3">
                    <div class="distributor-card">
                        <div class="distributor-image-wrapper">
                            <img src="https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=400&q=80" alt="Jakarta" class="distributor-image">
                            <div class="distributor-image-overlay"></div>
                        </div>
                        <div class="distributor-card-content">
                            <div class="distributor-icon">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <h3>Jakarta</h3>
                            <p>Distributor Utama</p>
                            <div class="distributor-info">
                                <div class="distributor-info-item">
                                    <i class="bi bi-telephone-fill"></i>
                                    <span>021-12345678</span>
                                </div>
                                <div class="distributor-info-item">
                                    <i class="bi bi-envelope-fill"></i>
                                    <span>jakarta@rasagroup.com</span>
                                </div>
                                <div class="distributor-info-item">
                                    <i class="bi bi-clock-fill"></i>
                                    <span>Senin - Jumat: 08:00 - 17:00</span>
                                </div>
                            </div>
                            <span class="distributor-badge">Tersedia</span>
                        </div>
                    </div>
                </div>

                <!-- Bandung -->
                <div class="col-6 col-md-6 col-lg-3">
                    <div class="distributor-card">
                        <div class="distributor-image-wrapper">
                            <img src="https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&q=80" alt="Bandung" class="distributor-image">
                            <div class="distributor-image-overlay"></div>
                        </div>
                        <div class="distributor-card-content">
                            <div class="distributor-icon">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <h3>Bandung</h3>
                            <p>Distributor Cabang</p>
                            <div class="distributor-info">
                                <div class="distributor-info-item">
                                    <i class="bi bi-telephone-fill"></i>
                                    <span>022-87654321</span>
                                </div>
                                <div class="distributor-info-item">
                                    <i class="bi bi-envelope-fill"></i>
                                    <span>bandung@rasagroup.com</span>
                                </div>
                                <div class="distributor-info-item">
                                    <i class="bi bi-clock-fill"></i>
                                    <span>Senin - Sabtu: 08:00 - 16:00</span>
                                </div>
                            </div>
                            <span class="distributor-badge">Tersedia</span>
                        </div>
                    </div>
                </div>

                <!-- Surabaya -->
                <div class="col-6 col-md-6 col-lg-3">
                    <div class="distributor-card">
                        <div class="distributor-image-wrapper">
                            <img src="https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&q=80" alt="Surabaya" class="distributor-image">
                            <div class="distributor-image-overlay"></div>
                        </div>
                        <div class="distributor-card-content">
                            <div class="distributor-icon">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <h3>Surabaya</h3>
                            <p>Distributor Cabang</p>
                            <div class="distributor-info">
                                <div class="distributor-info-item">
                                    <i class="bi bi-telephone-fill"></i>
                                    <span>031-11223344</span>
                                </div>
                                <div class="distributor-info-item">
                                    <i class="bi bi-envelope-fill"></i>
                                    <span>surabaya@rasagroup.com</span>
                                </div>
                                <div class="distributor-info-item">
                                    <i class="bi bi-clock-fill"></i>
                                    <span>Senin - Jumat: 08:00 - 17:00</span>
                                </div>
                            </div>
                            <span class="distributor-badge">Tersedia</span>
                        </div>
                    </div>
                </div>

                <!-- Yogyakarta -->
                <div class="col-6 col-md-6 col-lg-3">
                    <div class="distributor-card">
                        <div class="distributor-image-wrapper">
                            <img src="https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&q=80" alt="Yogyakarta" class="distributor-image">
                            <div class="distributor-image-overlay"></div>
                        </div>
                        <div class="distributor-card-content">
                            <div class="distributor-icon">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <h3>Yogyakarta</h3>
                            <p>Distributor Cabang</p>
                            <div class="distributor-info">
                                <div class="distributor-info-item">
                                    <i class="bi bi-telephone-fill"></i>
                                    <span>0274-55667788</span>
                                </div>
                                <div class="distributor-info-item">
                                    <i class="bi bi-envelope-fill"></i>
                                    <span>yogyakarta@rasagroup.com</span>
                                </div>
                                <div class="distributor-info-item">
                                    <i class="bi bi-clock-fill"></i>
                                    <span>Senin - Sabtu: 08:00 - 16:00</span>
                                </div>
                            </div>
                            <span class="distributor-badge">Tersedia</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="text-center mt-5">
                <a href="{{ route('about') }}" class="btn btn-hero-outline btn-hero me-3">
                    <i class="bi bi-geo-alt-fill me-2"></i>Lihat Semua Distributor
                </a>
                <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem; margin-top: 1.5rem; margin-bottom: 1rem;">
                    Ingin menjadi distributor di kota Anda?
                </p>
                <a href="{{ route('contact') }}" class="btn btn-hero-primary btn-hero">
                    <i class="bi bi-envelope-fill me-2"></i>Hubungi Kami
                </a>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section" id="products">
        <div class="container">
            <div class="section-title">
                <h2>Produk Unggulan</h2>
                <p>Pilihan terbaik untuk kebutuhan minuman Anda</p>
            </div>
            <div class="row g-4">
                @forelse($products as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="{{ route('products.show', $product) }}" class="product-card">
                            <div class="product-image-wrapper">
                                <img src="{{ $product->image_url ? asset($product->image_url) : 'https://via.placeholder.com/400x300/E63946/fff?text=' . urlencode($product->name) }}" 
                                     alt="{{ $product->name }}" class="product-image">
                                @if($product->created_at->isToday())
                                    <span class="product-badge new">NEW</span>
                                @endif
                            </div>
                            <div class="product-info">
                                <div class="product-category">{{ $product->category->name ?? 'Produk' }}</div>
                                <h3 class="product-name">{{ $product->name }}</h3>
                                <div class="product-rating">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-half"></i>
                                    <span>(4.5)</span>
                                </div>
                                <div class="product-price-wrapper">
                                    <div class="product-price">
                                        <span class="price">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <form action="{{ route('cart.store', $product) }}" method="POST" class="add-to-cart-form">
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn-add-cart">
                                        <i class="bi bi-cart-plus"></i>
                                        Tambah ke Keranjang
                                    </button>
                                </form>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">Belum ada produk tersedia</p>
                    </div>
                @endforelse
            </div>

            @if($products->count() > 0)
            <div class="text-center mt-5">
                <a href="{{ route('hubs.index') }}" class="btn btn-primary btn-lg px-5 py-3" style="border-radius: 30px;">
                    <i class="bi bi-grid me-2"></i>Lihat Semua Produk
                </a>
            </div>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
<script>
    // Smooth scroll untuk anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add to cart dengan AJAX
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const button = this.querySelector('.btn-add-cart');
            const originalText = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = '<i class="bi bi-hourglass-split"></i> Menambahkan...';
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.innerHTML = '<i class="bi bi-check-circle"></i> Ditambahkan!';
                    button.style.background = 'var(--success-color)';
                    
                    // Update cart badge jika ada
                    const cartBadge = document.querySelector('.cart-badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count || parseInt(cartBadge.textContent) + 1;
                    }
                    
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.style.background = '';
                        button.disabled = false;
                    }, 2000);
                } else {
                    alert('Gagal menambahkan produk: ' + (data.message || 'Silakan login terlebih dahulu'));
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
                button.innerHTML = originalText;
                button.disabled = false;
            });
        });
    });
</script>
@endpush
