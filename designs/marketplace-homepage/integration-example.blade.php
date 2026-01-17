{{-- 
    CONTOH INTEGRASI DENGAN LARAVEL BLADE
    File ini menunjukkan cara mengintegrasikan desain HTML dengan Laravel
--}}

@extends('layouts.app')

@section('title', 'Beranda - Rasa Group')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
{{-- Include CSS dari file terpisah atau inline seperti di index.html --}}
@endpush

@section('content')
    {{-- Hero Slider dengan data dari database --}}
    <div id="heroCarousel" class="carousel slide hero-slider" data-bs-ride="carousel">
        <div class="carousel-inner">
            @foreach($promos as $index => $promo)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                    <img src="{{ asset('storage/' . $promo->image) }}" class="d-block w-100" alt="{{ $promo->title }}">
                    <div class="carousel-caption">
                        <div class="container">
                            <h2>{{ $promo->title }}</h2>
                            <p>{{ $promo->description }}</p>
                            <a href="{{ $promo->link }}" class="btn btn-hero-primary btn-hero me-3">
                                {{ $promo->button_text }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        {{-- Controls --}}
    </div>

    {{-- Categories Section dengan data dari database --}}
    <section class="categories-section" id="categories">
        <div class="container">
            <div class="section-title">
                <h2>Kategori Produk</h2>
                <p>Pilih kategori sirup favorit Anda</p>
            </div>
            <div class="row g-4">
                @foreach($categories as $category)
                    <div class="col-6 col-md-4 col-lg-2">
                        <a href="{{ route('products.category', $category->slug) }}" class="category-card">
                            <div class="category-icon">
                                <i class="{{ $category->icon ?? 'bi bi-droplet-fill' }}"></i>
                            </div>
                            <h3>{{ $category->name }}</h3>
                            <p>{{ $category->description }}</p>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Products Section dengan data dari database --}}
    <section class="products-section" id="products">
        <div class="container">
            <div class="section-title">
                <h2>Produk Unggulan</h2>
                <p>Pilihan terbaik untuk kebutuhan minuman Anda</p>
            </div>
            <div class="row g-4">
                @forelse($products as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="{{ route('products.show', $product->slug) }}" class="product-card">
                            <div class="product-image-wrapper">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-image">
                                @if($product->is_new)
                                    <span class="product-badge new">NEW</span>
                                @endif
                                @if($product->discount_percentage > 0)
                                    <span class="product-badge sale">SALE</span>
                                @endif
                            </div>
                            <div class="product-info">
                                <div class="product-category">{{ $product->category->name }}</div>
                                <h3 class="product-name">{{ $product->name }}</h3>
                                <div class="product-rating">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= floor($product->rating))
                                            <i class="bi bi-star-fill"></i>
                                        @elseif($i - 0.5 <= $product->rating)
                                            <i class="bi bi-star-half"></i>
                                        @else
                                            <i class="bi bi-star"></i>
                                        @endif
                                    @endfor
                                    <span>({{ number_format($product->rating, 1) }})</span>
                                </div>
                                <div class="product-price-wrapper">
                                    <div class="product-price">
                                        <span class="price">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                        @if($product->original_price > $product->price)
                                            <span class="price-old">Rp {{ number_format($product->original_price, 0, ',', '.') }}</span>
                                        @endif
                                    </div>
                                    @if($product->discount_percentage > 0)
                                        <span class="price-discount">-{{ $product->discount_percentage }}%</span>
                                    @endif
                                </div>
                                <form action="{{ route('cart.add') }}" method="POST" class="add-to-cart-form">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
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

            {{-- Pagination --}}
            @if($products->hasPages())
                <div class="mt-5">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add to cart dengan AJAX
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('{{ route("cart.add") }}', {
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
                    // Update cart badge
                    const cartBadge = document.querySelector('.cart-badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                    }
                    
                    // Show success message
                    alert('Produk ditambahkan ke keranjang!');
                } else {
                    alert('Gagal menambahkan produk: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            });
        });
    });
</script>
@endpush

{{-- 
    CATATAN INTEGRASI:
    
    1. Controller harus menyediakan data:
       - $promos: Collection of promo banners
       - $categories: Collection of product categories
       - $products: Paginated collection of products
       
    2. Routes yang diperlukan:
       - Route::get('/', [HomeController::class, 'index'])->name('home');
       - Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
       - Route::get('/products/category/{slug}', [ProductController::class, 'category'])->name('products.category');
       - Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
       
    3. Model relationships:
       - Product belongsTo Category
       - Product hasMany Reviews (untuk rating)
       
    4. Database migrations:
       - promos table (id, title, description, image, link, button_text, active, created_at, updated_at)
       - products table perlu kolom: is_new, discount_percentage, original_price, rating
       
    5. Storage:
       - Pastikan storage link sudah dibuat: php artisan storage:link
       - Upload gambar promo ke storage/app/public/promos/
       - Upload gambar produk ke storage/app/public/products/
--}}

