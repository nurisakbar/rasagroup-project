@extends('layouts.shop')

@section('title', 'Beranda')

@push('styles')
<style>
    :root {
        --primary-color: #8B4513;
        --primary-dark: #654321;
        --secondary-color: #D2691E;
        --accent-color: #F4A460;
        --dark-color: #2C1810;
        --light-bg: #F5F5F5;
        --white: #FFFFFF;
        --text-dark: #333333;
        --text-light: #666666;
        --border-color: #E0E0E0;
    }

    body {
        font-family: 'Inter', 'Poppins', sans-serif;
    }

    /* Hero Section */
    .hero-section {
        position: relative;
        height: 600px;
        background: linear-gradient(135deg, var(--dark-color) 0%, var(--primary-dark) 100%);
        display: flex;
        align-items: center;
        overflow: hidden;
    }

    .hero-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0.3;
        background-image: url('https://images.unsplash.com/photo-1511920170033-f8396924c348?w=1920');
        background-size: cover;
        background-position: center;
    }

    .hero-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        position: relative;
        z-index: 1;
        color: var(--white);
    }

    .hero-content h1 {
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 20px;
        line-height: 1.2;
    }

    .hero-content p {
        font-size: 1.2rem;
        margin-bottom: 30px;
        opacity: 0.9;
    }

    .hero-buttons {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .btn-primary {
        background: var(--primary-color);
        color: var(--white);
        padding: 15px 35px;
        border: none;
        border-radius: 30px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(139, 69, 19, 0.4);
        color: var(--white);
    }

    .btn-outline {
        background: transparent;
        color: var(--white);
        padding: 15px 35px;
        border: 2px solid var(--white);
        border-radius: 30px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-outline:hover {
        background: var(--white);
        color: var(--primary-color);
    }

    /* Categories Section */
    .categories-section {
        padding: 60px 0;
        background: var(--white);
    }

    .section-title {
        text-align: center;
        margin-bottom: 50px;
    }

    .section-title h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 10px;
    }

    .section-title p {
        font-size: 1.1rem;
        color: var(--text-light);
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 30px;
    }

    .category-card {
        background: var(--white);
        border: 1px solid var(--border-color);
        border-radius: 15px;
        padding: 30px 20px;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border-color: var(--primary-color);
        text-decoration: none;
        color: inherit;
    }

    .category-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: var(--white);
    }

    .category-card h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 8px;
    }

    .category-card p {
        font-size: 0.9rem;
        color: var(--text-light);
    }

    /* Products Section */
    .products-section {
        padding: 60px 0;
        background: var(--light-bg);
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 30px;
    }

    .product-card {
        background: var(--white);
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        text-decoration: none;
        color: inherit;
    }

    .product-image {
        width: 100%;
        height: 250px;
        object-fit: cover;
        background: var(--light-bg);
    }

    .product-info {
        padding: 20px;
    }

    .product-category {
        font-size: 0.85rem;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .product-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 10px;
    }

    .product-rating {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 15px;
    }

    .product-rating i {
        color: #FFA500;
        font-size: 0.9rem;
    }

    .product-rating span {
        font-size: 0.85rem;
        color: var(--text-light);
    }

    .product-price {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .price {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--primary-color);
    }

    .price-old {
        font-size: 1rem;
        color: var(--text-light);
        text-decoration: line-through;
    }

    .btn-add-cart {
        width: 100%;
        background: var(--primary-color);
        color: var(--white);
        padding: 12px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: block;
        text-align: center;
    }

    .btn-add-cart:hover {
        background: var(--primary-dark);
        color: var(--white);
        text-decoration: none;
    }

    /* Features Section */
    .features-section {
        padding: 60px 0;
        background: var(--white);
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }

    .feature-box {
        text-align: center;
        padding: 30px 20px;
    }

    .feature-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: var(--white);
    }

    .feature-box h4 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 10px;
        color: var(--text-dark);
    }

    .feature-box p {
        font-size: 0.9rem;
        color: var(--text-light);
    }

    /* About Section */
    .about-section {
        padding: 80px 0;
        background: var(--light-bg);
    }

    .about-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: center;
    }

    .about-image {
        width: 100%;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }

    .about-text h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 20px;
    }

    .about-text p {
        font-size: 1.1rem;
        color: var(--text-light);
        margin-bottom: 20px;
        line-height: 1.8;
    }

    .about-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
        margin-top: 40px;
    }

    .stat-box {
        text-align: center;
        padding: 20px;
        background: var(--white);
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.9rem;
        color: var(--text-light);
    }

    @media (max-width: 768px) {
        .hero-content h1 {
            font-size: 2.5rem;
        }

        .hero-buttons {
            flex-direction: column;
        }

        .about-content {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-bg"></div>
        <div class="hero-content">
            <h1>Premium Coffee & Equipment</h1>
            <p>Temukan kopi berkualitas tinggi dan peralatan kopi terbaik untuk pengalaman kopi yang sempurna</p>
            <div class="hero-buttons">
                <a href="{{ route('hubs.index') }}" class="btn-primary">Belanja Sekarang</a>
                <a href="#products" class="btn-outline">Jelajahi Produk</a>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section">
        <div class="container">
            <div class="section-title">
                <h2>Kategori Produk</h2>
                <p>Pilih kategori yang Anda cari</p>
            </div>
            <div class="categories-grid">
                <a href="{{ route('hubs.index') }}" class="category-card">
                    <div class="category-icon">
                        <i class="bi bi-cup-hot"></i>
                    </div>
                    <h3>Biji Kopi</h3>
                    <p>Kopi berkualitas premium</p>
                </a>
                <a href="{{ route('hubs.index') }}" class="category-card">
                    <div class="category-icon">
                        <i class="bi bi-cup-straw"></i>
                    </div>
                    <h3>Kopi Instan</h3>
                    <p>Praktis dan nikmat</p>
                </a>
                <a href="{{ route('hubs.index') }}" class="category-card">
                    <div class="category-icon">
                        <i class="bi bi-funnel"></i>
                    </div>
                    <h3>Brewing Equipment</h3>
                    <p>Alat seduh kopi terbaik</p>
                </a>
                <a href="{{ route('hubs.index') }}" class="category-card">
                    <div class="category-icon">
                        <i class="bi bi-cpu"></i>
                    </div>
                    <h3>Espresso Machine</h3>
                    <p>Mesin espresso profesional</p>
                </a>
                <a href="{{ route('hubs.index') }}" class="category-card">
                    <div class="category-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h3>Aksesoris</h3>
                    <p>Pelengkap kopi Anda</p>
                </a>
                <a href="{{ route('hubs.index') }}" class="category-card">
                    <div class="category-icon">
                        <i class="bi bi-gift"></i>
                    </div>
                    <h3>Gift Set</h3>
                    <p>Paket hadiah spesial</p>
                </a>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section" id="products">
        <div class="container">
            <div class="section-title">
                <h2>Produk Unggulan</h2>
                <p>Pilihan terbaik untuk pengalaman kopi Anda</p>
            </div>
            <div class="products-grid">
                @forelse($products as $product)
                    <a href="{{ route('products.show', $product) }}" class="product-card">
                        <img src="{{ $product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/400x300/8B4513/fff?text=' . urlencode($product->name) }}" 
                             alt="{{ $product->name }}" class="product-image">
                        <div class="product-info">
                            <div class="product-category">{{ $product->category->name ?? 'Produk' }}</div>
                            <div class="product-name">{{ $product->name }}</div>
                            <div class="product-rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-half"></i>
                                <span>(4.5)</span>
                            </div>
                            <div class="product-price">
                                <span class="price">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            </div>
                            <span class="btn-add-cart">Lihat Detail</span>
                        </div>
                    </a>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">Belum ada produk tersedia</p>
                    </div>
                @endforelse
            </div>
            @if($products->count() > 0)
            <div class="text-center mt-5">
                <a href="{{ route('hubs.index') }}" class="btn-primary">
                    <i class="bi bi-grid me-2"></i>Lihat Semua Produk
                </a>
            </div>
            @endif
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="features-grid">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h4>Gratis Ongkir</h4>
                    <p>Untuk pembelian di atas Rp 500.000</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4>Garansi Resmi</h4>
                    <p>Semua produk bergaransi resmi</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h4>Support 24/7</h4>
                    <p>Tim kami siap membantu kapan saja</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <h4>Pengembalian Mudah</h4>
                    <p>Return dalam 7 hari tanpa ribet</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <div>
                    <img src="https://images.unsplash.com/photo-1442512595331-e89e73853f31?w=600" alt="About" class="about-image">
                </div>
                <div class="about-text">
                    <h2>Tentang Rasa Group</h2>
                    <p>Rasa Group adalah produsen dan distributor sirup berkualitas tinggi yang berdiri sejak 2010. Kami berkomitmen menyediakan berbagai varian sirup dengan rasa yang nikmat dan menyegarkan untuk memenuhi kebutuhan minuman Anda.</p>
                    <p>Dengan pengalaman lebih dari 14 tahun di industri minuman, kami memahami bahwa setiap produk memiliki cerita. Itulah mengapa kami memilih setiap produk dengan teliti untuk memastikan kualitas terbaik.</p>
                    <div class="about-stats">
                        <div class="stat-box">
                            <div class="stat-number">20+</div>
                            <div class="stat-label">Varian Rasa</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number">1000+</div>
                            <div class="stat-label">Pelanggan</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number">500+</div>
                            <div class="stat-label">Kota Terjangkau</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number">14+</div>
                            <div class="stat-label">Tahun Pengalaman</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
